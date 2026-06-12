<?php

namespace App\Models;

use CodeIgniter\Model;

class PluginConfig extends Model
{
    protected $table = 'plugin_config';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'plugin_id',
        'key',
        'value',
        'is_control',
    ];

    public function exists(string $pluginId, string $key): bool
    {
        $builder = $this->db->table('plugin_config');
        $builder->where('plugin_id', $pluginId)->where('key', $key);

        return ($builder->get()->getNumRows() === 1);
    }

    public function getValue(string $pluginId, string $key): ?string
    {
        $builder = $this->db->table('plugin_config');
        $query = $builder->getWhere(['plugin_id' => $pluginId, 'key' => $key], 1);

        if ($query->getNumRows() === 1) {
            return $query->getRow()->value;
        }

        return null;
    }

    public function setValue(string $pluginId, string $key, string $value, bool $isControl = false): bool
    {
        $builder = $this->db->table('plugin_config');

        if ($this->exists($pluginId, $key)) {
            return $builder->update(
                ['value' => $value],
                ['plugin_id' => $pluginId, 'key' => $key]
            );
        }

        return $builder->insert([
            'plugin_id'  => $pluginId,
            'key'        => $key,
            'value'      => $value,
            'is_control' => $isControl ? 1 : 0,
        ]);
    }

    public function getPluginSettings(string $pluginId): array
    {
        $builder = $this->db->table('plugin_config');
        $builder->where('plugin_id', $pluginId)->where('is_control', 0);
        $query = $builder->get();

        $settings = [];
        foreach ($query->getResult() as $row) {
            $settings[$row->key] = $row->value;
        }

        return $settings;
    }

    public function deleteKey(string $pluginId, string $key): bool
    {
        $builder = $this->db->table('plugin_config');
        return $builder->delete(['plugin_id' => $pluginId, 'key' => $key]);
    }

    public function deleteAllForPlugin(string $pluginId): bool
    {
        $builder = $this->db->table('plugin_config');
        return $builder->delete(['plugin_id' => $pluginId]);
    }

    public function batchSave(string $pluginId, array $data): bool
    {
        $this->db->transBegin();

        foreach ($data as $key => $value) {
            if (!$this->setValue($pluginId, $key, $value)) {
                $this->db->transRollback();
                return false;
            }
        }

        $this->db->transCommit();
        return true;
    }

    public function getAll(): array
    {
        $builder = $this->db->table('plugin_config');
        $query = $builder->get();

        $configs = [];
        foreach ($query->getResult() as $row) {
            $configs[$row->plugin_id][$row->key] = $row->value;
        }

        return $configs;
    }
}
