<?php

namespace App\Models;

use CodeIgniter\Model;

class PluginConfig extends Model
{
    protected $table = 'plugin_config';
    protected $primaryKey = 'key';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'key',
        'value'
    ];

    public function exists(string $key): bool
    {
        $builder = $this->db->table('plugin_config');
        $builder->where('key', $key);

        return ($builder->get()->getNumRows() === 1);
    }

    public function get(string $key): ?string
    {
        $builder = $this->db->table('plugin_config');
        $query = $builder->getWhere(['key' => $key], 1);

        if ($query->getNumRows() === 1) {
            return $query->getRow()->value;
        }

        return null;
    }

    public function set(string $key, string $value): bool
    {
        $builder = $this->db->table('plugin_config');
        
        if ($this->exists($key)) {
            return $builder->update(['value' => $value], ['key' => $key]);
        }
        
        return $builder->insert(['key' => $key, 'value' => $value]);
    }

    public function getPluginSettings(string $pluginId): array
    {
        $builder = $this->db->table('plugin_config');
        $builder->like('key', $pluginId . '_', 'after');
        $query = $builder->get();
        
        $settings = [];
        foreach ($query->getResult() as $row) {
            $key = str_replace($pluginId . '_', '', $row->key);
            $settings[$key] = $row->value;
        }
        
        return $settings;
    }

    public function deleteKey(string $key): bool
    {
        $builder = $this->db->table('plugin_config');
        return $builder->delete(['key' => $key]);
    }

    public function deleteAllStartingWith(string $prefix): bool
    {
        $builder = $this->db->table('plugin_config');
        $builder->like('key', $prefix, 'after');
        return $builder->delete();
    }

    public function batchSave(array $data): bool
    {
        $success = true;

        $this->db->transStart();

        foreach ($data as $key => $value) {
            $success &= $this->set($key, $value);
        }

        $this->db->transComplete();

        return $success && $this->db->transStatus();
    }

    public function getAll(): array
    {
        $builder = $this->db->table('plugin_config');
        $query = $builder->get();
        
        $configs = [];
        foreach ($query->getResult() as $row) {
            $configs[$row->key] = $row->value;
        }
        
        return $configs;
    }
}