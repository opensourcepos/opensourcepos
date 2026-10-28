<?php

namespace Tests\Support;

use App\Models\Expense;
use App\Models\Expense_category;

trait ExpenseFixtureTrait
{
    protected function createExpenseCategory(array $overrides = []): int
    {
        $categoryData = array_merge([
            'category_name'        => 'Test Category ' . uniqid(),
            'category_description' => '',
            'deleted'               => 0,
        ], $overrides);

        model(Expense_category::class)->save_value($categoryData);

        return (int) $categoryData['expense_category_id'];
    }

    protected function createExpense(int $employeeId, int $categoryId, array $overrides = []): int
    {
        $expenseData = array_merge([
            'date'                => date('Y-m-d H:i:s'),
            'amount'              => 0,
            'payment_type'        => 'Cash',
            'expense_category_id' => $categoryId,
            'description'         => 'Test expense',
            'employee_id'         => $employeeId,
            'deleted'             => 0,
        ], $overrides);

        model(Expense::class)->save_value($expenseData);

        return (int) $expenseData['expense_id'];
    }
}
