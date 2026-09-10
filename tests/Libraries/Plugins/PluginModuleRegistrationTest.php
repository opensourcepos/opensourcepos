<?php

namespace Tests\Libraries\Plugins;

use App\Libraries\Plugins\PluginManager;
use App\Models\PluginConfig;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use ReflectionProperty;
use Tests\Support\TestModulePlugin;

class PluginModuleRegistrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = null;

    private TestModulePlugin $plugin;
    private PluginConfig $configModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = new TestModulePlugin();
        $this->configModel = new PluginConfig();
        $this->cleanupTestRows();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestRows();
        parent::tearDown();
    }

    private function cleanupTestRows(): void
    {
        $db = Database::connect();
        $db->table('permissions')->like('permission_id', 'testmodule', 'after')->delete();
        $db->table('modules')->like('module_id', 'testmodule', 'after')->delete();
        $db->table('plugin_config')->where('plugin_id', 'testmodule')->delete();
    }

    private function managerWithPlugin(): PluginManager
    {
        $manager = new PluginManager();
        $property = new ReflectionProperty(PluginManager::class, 'plugins');
        $property->setAccessible(true);
        $property->setValue($manager, ['testmodule' => $this->plugin]);
        return $manager;
    }

    public function testRegisterModuleWritesRows(): void
    {
        $this->assertTrue($this->plugin->publicRegisterModule('testmodule'));

        $db = Database::connect();
        $this->assertSame(1, $db->table('modules')->where('module_id', 'testmodule')->countAllResults());
        $this->assertSame(1, $db->table('permissions')->where('permission_id', 'testmodule')->countAllResults());
        $this->assertSame(1, $db->table('grants')->where('permission_id', 'testmodule')->where('person_id', 1)->countAllResults());
    }

    public function testRegisterModuleRejectsUnprefixedId(): void
    {
        $this->assertFalse($this->plugin->publicRegisterModule('unrelated'));
        $this->assertSame(0, Database::connect()->table('modules')->where('module_id', 'unrelated')->countAllResults());
    }

    public function testRegisterSubPermissionRejectsUnprefixedId(): void
    {
        $this->plugin->publicRegisterModule('testmodule');
        $this->assertFalse($this->plugin->publicRegisterSubPermission('unrelated_extra', 'testmodule'));
        $this->assertSame(0, Database::connect()->table('permissions')->where('permission_id', 'unrelated_extra')->countAllResults());
    }

    public function testDoubleRegisterIsIdempotent(): void
    {
        $this->plugin->publicRegisterModule('testmodule');
        $this->plugin->publicRegisterModule('testmodule');

        $this->assertSame(1, Database::connect()->table('modules')->where('module_id', 'testmodule')->countAllResults());
    }

    public function testUnregisterModuleDeletesRows(): void
    {
        $this->plugin->publicRegisterModule('testmodule');
        $this->plugin->publicRegisterSubPermission('testmodule_extra', 'testmodule');

        $this->plugin->publicUnregisterModule('testmodule');

        $db = Database::connect();
        $this->assertSame(0, $db->table('modules')->where('module_id', 'testmodule')->countAllResults());
        $this->assertSame(0, $db->table('permissions')->where('module_id', 'testmodule')->countAllResults());
    }

    public function testUnregisterSubPermissionDeletesRow(): void
    {
        $this->plugin->publicRegisterModule('testmodule');
        $this->plugin->publicRegisterSubPermission('testmodule_extra', 'testmodule');

        $this->plugin->publicUnregisterSubPermission('testmodule_extra');

        $this->assertSame(0, Database::connect()->table('permissions')->where('permission_id', 'testmodule_extra')->countAllResults());
    }

    public function testUninstallAutoUnregistersByConvention(): void
    {
        $manager = $this->managerWithPlugin();
        $manager->enablePlugin('testmodule');

        $db = Database::connect();
        $this->assertSame(1, $db->table('modules')->where('module_id', 'testmodule')->countAllResults());
        $this->assertSame(1, $db->table('permissions')->where('permission_id', 'testmodule_extra')->countAllResults());

        $this->assertTrue($manager->uninstallPlugin('testmodule'));

        $this->assertSame(0, $db->table('modules')->where('module_id', 'testmodule')->countAllResults());
        $this->assertSame(0, $db->table('permissions')->like('permission_id', 'testmodule', 'after')->countAllResults());
        $this->assertSame('0', $this->configModel->getValue('testmodule', 'installed'));
    }

    public function testUninstallCleansLegacyInstallWithNoRegistrationRecord(): void
    {
        // Simulate a plugin installed before automatic cleanup existed:
        // module rows present, installed=1, nothing else recorded anywhere.
        $db = Database::connect();
        $db->table('modules')->insert([
            'module_id'     => 'testmodule',
            'name_lang_key' => 'module_testmodule',
            'desc_lang_key' => 'module_testmodule_desc',
            'sort'          => 500,
        ]);
        $db->table('permissions')->insert([
            'permission_id' => 'testmodule',
            'module_id'     => 'testmodule',
        ]);
        $this->configModel->setValue('testmodule', 'installed', '1', true);

        $manager = $this->managerWithPlugin();
        $this->assertTrue($manager->uninstallPlugin('testmodule'));

        $this->assertSame(0, $db->table('modules')->where('module_id', 'testmodule')->countAllResults());
        $this->assertSame(0, $db->table('permissions')->where('module_id', 'testmodule')->countAllResults());
    }

    public function testUninstallDoesNotTouchOtherModules(): void
    {
        $manager = $this->managerWithPlugin();
        $manager->enablePlugin('testmodule');

        $db = Database::connect();
        $coreModulesBefore = $db->table('modules')->countAllResults();

        $manager->uninstallPlugin('testmodule');

        $this->assertSame($coreModulesBefore - 1, $db->table('modules')->countAllResults());
        $this->assertSame(1, $db->table('modules')->where('module_id', 'sales')->countAllResults());
    }
}
