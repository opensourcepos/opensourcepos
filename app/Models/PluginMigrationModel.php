<?php

namespace App\Models;

use CodeIgniter\Model;

class PluginMigrationModel extends Model
{
    protected $table = 'plugin_migrations';
    protected $primaryKey = 'plugin_id';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = false;
    protected $allowedFields = ['plugin_id', 'version', 'ran_at'];

    public function getVersion(string $pluginId): int
    {
        $row = $this->db->table('plugin_migrations')
            ->where('plugin_id', $pluginId)
            ->get()
            ->getRow();

        return $row ? (int) $row->version : 0;
    }

    public function setVersion(string $pluginId, int $version): bool
    {
        $builder = $this->db->table('plugin_migrations');
        $exists = $builder->where('plugin_id', $pluginId)->countAllResults() > 0;

        if (!$exists) {
            return $builder->insert([
                'plugin_id' => $pluginId,
                'version'   => $version,
                'ran_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return $builder->update(
            ['version' => $version, 'ran_at' => date('Y-m-d H:i:s')],
            ['plugin_id' => $pluginId]
        );
    }
}
