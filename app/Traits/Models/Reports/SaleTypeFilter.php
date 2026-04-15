<?php

namespace App\Traits\Models\Reports;

use CodeIgniter\Database\BaseBuilder;

trait SaleTypeFilter
{
    protected function applySaleTypeFilter(BaseBuilder $builder, string $saleType, bool $usePrefix = true): void
    {
        $prefix = $usePrefix ? 'sales.' : '';
        
        if ($saleType === 'complete') {
            $builder->where("{$prefix}sale_status", COMPLETED);
            $builder->groupStart();
            $builder->where("{$prefix}sale_type", SALE_TYPE_POS);
            $builder->orWhere("{$prefix}sale_type", SALE_TYPE_INVOICE);
            $builder->orWhere("{$prefix}sale_type", SALE_TYPE_RETURN);
            $builder->groupEnd();
        } elseif ($saleType === 'sales') {
            $builder->where("{$prefix}sale_status", COMPLETED);
            $builder->groupStart();
            $builder->where("{$prefix}sale_type", SALE_TYPE_POS);
            $builder->orWhere("{$prefix}sale_type", SALE_TYPE_INVOICE);
            $builder->groupEnd();
        } elseif ($saleType === 'quotes') {
            $builder->where("{$prefix}sale_status", SUSPENDED);
            $builder->where("{$prefix}sale_type", SALE_TYPE_QUOTE);
        } elseif ($saleType === 'work_orders') {
            $builder->where("{$prefix}sale_status", SUSPENDED);
            $builder->where("{$prefix}sale_type", SALE_TYPE_WORK_ORDER);
        } elseif ($saleType === 'canceled') {
            $builder->where("{$prefix}sale_status", CANCELED);
        } elseif ($saleType === 'returns') {
            $builder->where("{$prefix}sale_status", COMPLETED);
            $builder->where("{$prefix}sale_type", SALE_TYPE_RETURN);
        }
    }
}