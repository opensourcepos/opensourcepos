<?php

namespace App\Models;

use CodeIgniter\Model;

abstract class BaseModel extends Model
{
    public function getTableName(): string
    {
        return $this->table;
    }

    public function getPrimaryKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getAllowedFields(): array
    {
        return $this->allowedFields;
    }

    public function getUseAutoIncrement(): bool
    {
        return $this->useAutoIncrement;
    }

    public function getUseSoftDeletes(): bool
    {
        return $this->useSoftDeletes;
    }
}