<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * -------------------------------------------------------------------
 * AUTOLOADER CONFIGURATION
 * -------------------------------------------------------------------
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 *
 * NOTE: If you use an identical key in $psr4 or $classmap, then
 *       the values in this file will overwrite the framework's values.
 *
 * NOTE: This class is required prior to Autoloader instantiation,
 *       and does not extend BaseConfig.
 *
 * @immutable
 */
class Autoload extends AutoloadConfig
{
    /**
     * -------------------------------------------------------------------
     * Namespaces
     * -------------------------------------------------------------------
     * This maps the locations of any namespaces in your application to
     * their location on the file system. These are used by the autoloader
     * to locate files the first time they have been instantiated.
     *
     * The '/app' and '/system' directories are already mapped for you.
     * you may change the name of the 'App' namespace if you wish,
     * but this should be done prior to creating any namespaced classes,
     * else you will need to modify all of those classes for this to work.
     *
     * Prototype:
     *   $psr4 = [
     *       'CodeIgniter' => SYSTEMPATH,
     *       'App'         => APPPATH
     *   ];
     *
     * @var array<string, list<string>|string>
     */
    public $psr4 = [
        APP_NAMESPACE => APPPATH, // For custom app namespace
        'Config'      => APPPATH . 'Config',
		'dompdf' => APPPATH . 'ThirdParty/dompdf/src'
    ];

    /**
     * -------------------------------------------------------------------
     * Class Map
     * -------------------------------------------------------------------
     * The class map provides a map of class names and their exact
     * location on the drive. Classes loaded in this manner will have
     * slightly faster performance because they will not have to be
     * searched for within one or more directories as they would if they
     * were being autoloaded through a namespace.
     *
     * Prototype:
     *   $classmap = [
     *       'MyClass'   => '/path/to/class/file.php'
     *   ];
     *
     * @var array<string, string>
     */
	public $classmap = [
		//Controllers
		'Attributes' => '/App/Controllers/Attributes.php',
		'Cashups' => '/App/Controllers/Cashups.php',
		'Config' => '/App/Controllers/Config.php',
		'Customers' => '/App/Controllers/Customers.php',
		'Employees' => '/App/Controllers/Employees.php',
		'Expenses' => '/App/Controllers/Expenses.php',
		'Expenses_categories' => '/App/Controllers/Expenses_categories.php',
		'Giftcards' => '/App/Controllers/Giftcards.php',
		'Home' => '/App/Controllers/Home.php',
		'Item_kits' => '/App/Controllers/Item_kits.php',
		'Items' => '/App/Controllers/Items.php',
		'Login' => '/App/Controllers/Login.php',
		'Messages' => '/App/Controllers/Messages.php',
		'No_access' => '/App/Controllers/No_access.php',
		'Office' => '/App/Controllers/Office.php',
		'Persons' => '/App/Controllers/Persons.php',
		'Receivings' => '/App/Controllers/Receivings.php',
		'Reports' => '/App/Controllers/Reports.php',
		'Sales' => '/App/Controllers/Sales.php',
		'Secure_Controller' => '/App/Controllers/Secure_Controller.php',
		'Suppliers' => '/App/Controllers/Suppliers.php',
		'Tax_categories' => '/App/Controllers/Tax_categories.php',
		'Tax_codes' => '/App/Controllers/Tax_codes.php',
		'Tax_jurisdictions' => '/App/Controllers/Tax_jurisdictions.php',
		'Taxes' => '/App/Controllers/Taxes.php',

		//Models
		'Appconfig' => '/App/Models/Appconfig.php',
		'Attribute' => '/App/Models/Attribute.php',
		'Cashup' => '/App/Models/Cashup.php',
		'Customer' => '/App/Models/Customer.php',
		'Customer_rewards' => '/App/Models/Customer_rewards.php',
		'Dinner_table' => '/App/Models/Dinner_table.php',
		'Employee' => '/App/Models/Employee.php',
		'Expense' => '/App/Models/Expense.php',
		'Expense_category' => '/App/Models/Expense_category.php',
		'Giftcard' => '/App/Models/Giftcard.php',
		'Inventory' => '/App/Models/Inventory.php',
		'Item_kit' => '/App/Models/Item_kit.php',
		'Item_kit_items' => '/App/Models/Item_kit_items.php',
		'Item_quantity' => '/App/Models/Item_quantity.php',
		'Item_taxes' => '/App/Models/Item_taxes.php',
		'Module' => '/App/Models/Module.php',
		'Person' => '/App/Models/Person.php',
		'Receiving' => '/App/Models/Receiving.php',
		'Rewards' => '/App/Models/Rewards.php',
		'Sale' => '/App/Models/Sale.php',
		'Stock_location' => '/App/Models/Stock_location.php',
		'Supplier' => '/App/Models/Supplier.php',
		'Tax' => '/App/Models/Tax.php',
		'Tax_category' => '/App/Models/Tax_category.php',
		'Tax_code' => '/App/Models/Tax_code.php',
		'Tax_jurisdiction' => '/App/Models/Tax_jurisdiction.php',

		//Reports
		'Report' => '/App/Models/Reports/Report.php',
		'Detailed_receiving' => '/App/Models/Reports/Detailed_receiving.php',
		'Detailed_sales' => '/App/Models/Reports/Detailed_sales.php',
		'Inventory_low' => '/App/Models/Reports/Inventory_low.php',
		'Inventory_summary' => '/App/Models/Reports/Inventory_summary.php',
		'Specific_customer' => '/App/Models/Reports/Specific_customer.php',
		'Specific_discount' => '/App/Models/Reports/Specific_discount.php',
		'Specific_employee' => '/App/Models/Reports/Specific_employee.php',
		'Specific_supplier' => '/App/Models/Reports/Specific_supplier.php',
		'Summary_categories' => '/App/Models/Reports/Summary_categories.php',
		'Summary_customers' => '/App/Models/Reports/Summary_customers.php',
		'Summary_discounts' => '/App/Models/Reports/Summary_discounts.php',
		'Summary_employees' => '/App/Models/Reports/Summary_employees.php',
		'Summary_expenses_categories' => '/App/Models/Reports/Summary_expenses_categories.php',
		'Summary_items' => '/App/Models/Reports/Summary_items.php',
		'Summary_payments' => '/App/Models/Reports/Summary_payments.php',
		'Summary_report' => '/App/Models/Reports/Summary_report.php',
		'Summary_sales' => '/App/Models/Reports/Summary_sales.php',
		'Summary_sales_taxes' => '/App/Models/Reports/Summary_sales_taxes.php',
		'Summary_suppliers' => '/App/Models/Reports/Summary_suppliers.php',
		'Summary_taxes' => '/App/Models/Reports/Summary_taxes.php',

		//Tokens
		'Token' => '/App/Models/Tokens/Token.php',
		'Token_barcode_ean' => '/App/Models/Tokens/Token_barcode_ean.php',
		'Token_barcode_price' => '/App/Models/Tokens/Token_barcode_price.php',
		'Token_barcode_weight' => '/App/Models/Tokens/Token_barcode_weight.php',
		'Token_customer' => '/App/Models/Tokens/Token_customer.php',
		'Token_invoice_count' => '/App/Models/Tokens/Token_invoice_count.php',
		'Token_invoice_sequence' => '/App/Models/Tokens/Token_invoice_sequence.php',
		'Token_quote_sequence' => '/App/Models/Tokens/Token_quote_sequence.php',
		'Token_suspended_invoice_count' => '/App/Models/Tokens/Token_suspended_invoice_count.php',
		'Token_work_order_sequence' => '/App/Models/Tokens/Token_work_order_sequence.php',
		'Token_year_invoice_count' => '/App/Models/Tokens/Token_year_invoice_count.php',
		'Token_year_quote_count' => '/App/Models/Tokens/Token_year_quote_count.php',

		//Libraries
		'Barcode_lib' => '/App/Libraries/Barcode_lib.php',
		'Email_lib' => '/App/Libraries/Email_lib.php',
		'Item_lib' => '/App/Libraries/Item_lib.php',
		'Mailchimp_lib' => '/App/Libraries/Mailchimp_lib.php',
		'MY_Email' => '/App/Libraries/MY_Email.php',
		'MY_Migration' => '/App/Libraries/MY_Migration.php',
		'Receving_lib' => '/App/Libraries/Receiving_lib.php',
		'Sale_lib' => '/App/Libraries/Sale_lib.php',
		'Sms_lib' => '/App/Libraries/Sms_lib.php',
		'Tax_lib' => '/App/Libraries/Tax_lib.php',
		'Token_lib' => '/App/Libraries/Token_lib.php',

		//Miscellaneous
		'Rounding_mode' => '/App/Models/Enums/Rounding_mode.php'
	];

    /**
     * -------------------------------------------------------------------
     * Files
     * -------------------------------------------------------------------
     * The files array provides a list of paths to __non-class__ files
     * that will be autoloaded. This can be useful for bootstrap operations
     * or for loading functions.
     *
     * Prototype:
     *   $files = [
     *       '/path/to/my/file.php',
     *   ];
     *
     * @var list<string>
     */
    public $files = [];

    /**
     * -------------------------------------------------------------------
     * Helpers
     * -------------------------------------------------------------------
     * Prototype:
     *   $helpers = [
     *       'form',
     *   ];
     *
     * @var list<string>
     */
    public $helpers = [
		'form',
		'cookie',
		'tabular',
		'locale',
		'security'
	];
}
