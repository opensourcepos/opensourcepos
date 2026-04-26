<?php

namespace App\Traits\Models\Reports;

use CodeIgniter\Database\BaseBuilder;
use Config\OSPOS;

trait ReportDateFilter
{
    protected function buildDateWhereClause(array $inputs, string $dateColumn = 'sale_time'): string
    {
        $config = config(OSPOS::class)->settings;
        
        if (empty($config['date_or_time_format'])) {
            return "DATE({$dateColumn}) BETWEEN " . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
        }
        return "{$dateColumn} BETWEEN " . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
    }

    protected function applyDateFilter(BaseBuilder $builder, array $inputs, string $tablePrefix = 'sales', string $column = 'sale_time'): void
    {
        $config = config(OSPOS::class)->settings;
        
        if (empty($config['date_or_time_format'])) {
            $builder->where("DATE({$tablePrefix}.{$column}) BETWEEN " . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
        } else {
            $builder->where("{$tablePrefix}.{$column} BETWEEN " . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
        }
    }
}