<?php

namespace Herrera\Phar\Update;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Update;
use Herrera\PHPUnit\TestCase;
use Herrera\Version\Parser;
use Phar;

class ManagerTest extends TestCase
{
    /** @var Manager */
    private $manager;

    /** @var Manifest */
    private $manifest;

    public function testGetManifest()
    {
        $this->assertSame($this->manifest, $this->manager->getManifest());
    }

    public function testGetRunningFile()
    {
        $this->assertEquals(
            realpath($_SERVER['argv'][0]),
            $this->manager->getRunningFile()
        );
    }

    /**
     * @depends testGetRunningFile
     */
    public function testSetRunningFile()
    {
        $file = $this->createFile();

        $this->manager->setRunningFile($file);

        $this->assertEquals($file, $this->manager->getRunningFile());
    }

    public function testSetRunningFileNotExist()
    {
        $this->setExpectedException(
            'Herrera\\Phar\\Update\\Exception\\InvalidArgumentException',
            'The file "/does/not/exist" is not a file or it does not exist.'
        );

        $this->manager->setRunningFile('/does/not/exist');
    }

    /**
     * @depends testSetRunningFile
     */
    public function testUpdate()
    {
        unlink($currentFile = $this->createFile('current.phar'));
        unlink($newFile = $this->createFile('new.phar'));

        $current = new Phar($currentFile);
        $current->addFromString('test.php', '<?php echo "current";');
        $current->setStub($current->createDefaultStub('test.php'));

        $new = new Phar($newFile);
        $new->addFromString('test.php', '<?php echo "new";');
        $new->setStub($new->createDefaultStub('test.php'));

        unset($current, $new);

        $manager = new Manager(new Manifest(array(new Update(
            'new.phar',
            sha1_file($newFile),
            $newFile,
            Parser::toVersion('1.0.1')
        ))));

        $manager->setRunningFile($currentFile);

        $this->assertTrue($manager->update('1.0.0'));
        $this->assertEquals('new', exec('php ' . escapeshellarg($currentFile)));
    }

    public function testUpdateNone()
    {
        $manager = new Manager(new Manifest(array(new Update(
            'new.phar',
            'test',
            'test',
            Parser::toVersion('2.0.1')
        ))));

        $manager->setRunningFile($this->createFile());

        $this->assertFalse($manager->update('1.0.0', true));
    }

    protected function setUp()
    {
        $this->manifest = new Manifest();
        $this->manager = new Manager($this->manifest);
    }
}
