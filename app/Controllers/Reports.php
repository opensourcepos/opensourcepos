<?php

namespace App\Controllers;

use App\Models\Attribute;
use App\Models\Customer;
use App\Models\Stock_location;
use App\Models\Supplier;
use App\Models\Reports\Detailed_receivings;
use App\Models\Reports\Detailed_sales;
use App\Models\Reports\Inventory_low;
use App\Models\Reports\Inventory_summary;
use App\Models\Reports\Specific_customer;
use App\Models\Reports\Specific_discount;
use App\Models\Reports\Specific_employee;
use App\Models\Reports\Specific_supplier;
use App\Models\Reports\Summary_categories;
use App\Models\Reports\Summary_customers;
use App\Models\Reports\Summary_discounts;
use App\Models\Reports\Summary_employees;
use App\Models\Reports\Summary_expenses_categories;
use App\Models\Reports\Summary_items;
use App\Models\Reports\Summary_payments;
use App\Models\Reports\Summary_sales;
use App\Models\Reports\Summary_sales_taxes;
use App\Models\Reports\Summary_suppliers;
use App\Models\Reports\Summary_taxes;
use Config\OSPOS;
use Config\Services;

class Reports extends Secure_Controller
{
	private Attribute $attribute;
	private array $config;
	private Customer $customer;
	private Stock_location $stock_location;
	private Summary_sales $summary_sales;
	private Summary_sales_taxes $summary_sales_taxes;
	private Summary_categories $summary_categories;
	private Summary_expenses_categories $summary_expenses_categories;
	private Summary_customers $summary_customers;
	private Summary_items $summary_items;
	private Summary_suppliers $summary_suppliers;
	private Summary_employees $summary_employees;
	private Summary_taxes $summary_taxes;
	private Summary_discounts $summary_discounts;
	private Summary_payments $summary_payments;
	private Detailed_sales $detailed_sales;
	private Supplier $supplier;
	private Detailed_receivings $detailed_receivings;
	private Inventory_summary $inventory_summary;

	public function __construct()
	{
		parent::__construct('reports');
		$request = Services::request();
		$method_name = $request->getUri()->getSegment(2);
		$exploder = explode('_', $method_name);

		$this->attribute = config(Attribute::class);
		$this->config = config(OSPOS::class)->settings;
		$this->customer = model(Customer::class);
		$this->stock_location = model(Stock_location::class);
		$this->summary_sales = model(Summary_sales::class);
		$this->summary_sales_taxes = model(Summary_sales_taxes::class);
		$this->summary_categories = model(Summary_categories::class);
		$this->summary_expenses_categories = model(Summary_expenses_categories::class);
		$this->summary_customers = model(Summary_customers::class);
		$this->summary_items = model(Summary_items::class);
		$this->summary_suppliers = model(Summary_suppliers::class);
		$this->summary_employees = model(Summary_employees::class);
		$this->summary_taxes = model(Summary_taxes::class);
		$this->summary_discounts = model(Summary_discounts::class);
		$this->summary_payments = model(Summary_payments::class);
		$this->detailed_sales = model(Detailed_sales::class);
		$this->supplier = model(Supplier::class);
		$this->detailed_receivings = model(Detailed_receivings::class);
		$this->inventory_summary = model(Inventory_summary::class);

		if(sizeof($exploder) > 1)
		{
			preg_match('/(?:inventory)|([^_.]*)(?:_graph|_row)?$/', $method_name, $matches);
			preg_match('/^(.*?)([sy])?$/', array_pop($matches), $matches);
			$submodule_id = $matches[1] . ((count($matches) > 2) ? $matches[2] : 's');

			// check access to report submodule
			if(!$this->employee->has_grant('reports_' . $submodule_id, $this->employee->get_logged_in_employee_info()->person_id))
			{
				redirect('no_access/reports/reports_' . $submodule_id);
			}
		}

		helper('report');
	}

	/**
	 * @return void
	 */
	public function index(): void
	{
		$this->getIndex();
	}

	/**
	 * Initial Report listing screen
	 */
	public function getIndex(): void
	{
		$person_id = $this->session->get('person_id');
		$grants = $this->employee->get_employee_grants($this->session->get('person_id'));
		$permissions_ids = array_column($grants, 'permission_id');

		$data = [
			'person_id' => $person_id,
			'grants' => $grants,
			'permission_ids' => $permissions_ids,
		];

		echo view('reports/listing', $data);
	}

	/**
	 * Summary Sales Report.  Called in the view.
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @param string $location_id
	 * @return void
	 */
	public function summary_sales(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void	//TODO: Perhaps these need to be passed as an array?  Too many parameters in the signature.
	{//TODO: Duplicated code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_sales->getData($inputs);
		$summary = $this->summary_sales->getSummaryData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'sale_date' => to_date(strtotime($row['sale_date']) ?? null),
				'sales' => to_quantity_decimals($row['sales']),
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			];
		}

		$data = [
			'title' => lang('Reports.sales_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_sales->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Categories report. Called in the view.
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @param string $location_id
	 * @return void
	 */
	public function summary_categories(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicated code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_categories->getData($inputs);
		$summary = $this->summary_categories->getSummaryData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'category' => $row['category'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			];
		}

		$data = [
			'title' => lang('Reports.categories_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_categories->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Expenses by Categories report.  Called in the view.
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @return void
	 */
	public function summary_expenses_categories(string $start_date, string $end_date, string $sale_type): void
	{
		$this->clearCache();

		$inputs = ['start_date' => $start_date, 'end_date' => $end_date, 'sale_type' => $sale_type];	//TODO: Duplicated Code

		$report_data = $this->summary_expenses_categories->getData($inputs);
		$summary = $this->summary_expenses_categories->getSummaryData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'category_name' => $row['category_name'],
				'count' => $row['count'],
				'total_amount' => to_currency($row['total_amount']),
				'total_tax_amount' => to_currency($row['total_tax_amount'])
			];
		}

		$data = [
			'title' => lang('Reports.expenses_categories_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_expenses_categories->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Customers report. Called in the view
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @param string $location_id
	 * @return void
	 */
	public function summary_customers(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$inputs = [	//TODO: Duplicated Code
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_customers->getData($inputs);
		$summary = $this->summary_customers->getSummaryData($inputs);

		$tabular_data = [];

		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'customer_name' => $row['customer'],
				'sales' => to_quantity_decimals($row['sales']),
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			];
		}

		$data = [
			'title' => lang('Reports.customers_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_customers->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Suppliers report. Called in the view.
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @param string $location_id
	 * @return void
	 */
	public function summary_suppliers(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_suppliers->getData($inputs);
		$summary = $this->summary_suppliers->getSummaryData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'supplier_name' => $row['supplier'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			];
		}

		$data = [
			'title' => lang('Reports.suppliers_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_suppliers->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Items report. Called in the view.
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @param string $location_id
	 * @return void
	 */
	public function summary_items(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_items->getData($inputs);
		$summary = $this->summary_items->getSummaryData($inputs);

		$tabular_data = [];

		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'item_name' => $row['name'],
				'category' => $row['category'],
				'cost_price' => $row['cost_price'],
				'unit_price' => $row['unit_price'],
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax'  => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			];
		}

		$data = [
			'title' => lang('Reports.items_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_items->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Employees report. Called in the view.
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @param string $location_id
	 * @return void
	 */
	public function summary_employees(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_employees->getData($inputs);
		$summary = $this->summary_employees->getSummaryData($inputs);

		$tabular_data = [];

		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'employee_name' => $row['employee'],
				'sales' => to_quantity_decimals($row['sales']),
				'quantity' => to_quantity_decimals($row['quantity_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit'])
			];
		}

		$data = [
			'title' => lang('Reports.employees_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_employees->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Taxes report. Called in the view.
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @param string $location_id
	 * @return void
	 */
	public function summary_taxes(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicate Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_taxes->getData($inputs);
		$summary = $this->summary_taxes->getSummaryData($inputs);

		$tabular_data = [];

		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'tax_name' => $row['name'],
				'tax_percent' => $row['percent'],
				'report_count' => $row['count'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total'])
			];
		}

		$data = [
			'title' => lang('Reports.taxes_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_taxes->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Sales Taxes report
	 */
	public function summary_sales_taxes(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicated code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_sales_taxes->getData($inputs);
		$summary = $this->summary_sales_taxes->getSummaryData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'reporting_authority' => $row['reporting_authority'],
				'jurisdiction_name' => $row['jurisdiction_name'],
				'tax_category' => $row['tax_category'],
				'tax_rate' => $row['tax_rate'],
				'tax' => to_currency_tax($row['tax'])
			];
		}

		$data = [
			'title' => lang('Reports.sales_taxes_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_sales_taxes->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * @return void
	 */
	public function summary_discounts_input(): void
	{
		$this->clearCache();

		$stock_locations = $data = $this->stock_location->get_allowed_locations('sales');
		$stock_locations['all'] = lang('Reports.all');
		$data['stock_locations'] = array_reverse($stock_locations, true);
		$data['mode'] = 'sale';
		$data['discount_type_options'] = ['0' => lang('Reports.discount_percent'), '1'=> lang('Reports.discount_fixed')];
		$data['sale_type_options'] = $this->get_sale_type_options();

		echo view('reports/date_input', $data);
	}

	/**
	 * Summary Discounts report
	 **/
	public function summary_discounts(string $start_date, string $end_date, string $sale_type, string $location_id = 'all', int $discount_type = 0): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id,
			'discount_type' => $discount_type
		];

		$report_data = $this->summary_discounts->getData($inputs);
		$summary = $this->summary_discounts->getSummaryData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'total' => to_currency($row['total']),
				'discount' => $row['discount'],
				'count' => $row['count']
			];
		}

		$data = [
			'title' => lang('Reports.discounts_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_discounts->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Summary Payments report
	 */
	public function summary_payments(string $start_date, string $end_date): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => 'complete',
			'location_id' => 'all'
		];

		$report_data = $this->summary_payments->getData($inputs);
		$summary = $this->summary_payments->getSummaryData($inputs);

		$tabular_data = [];

		foreach($report_data as $row)
		{
			if($row['trans_group'] == '<HR>')
			{
				$tabular_data[] = [
					'trans_group' => '--',
					'trans_type' => '--',
					'trans_sales' => '--',
					'trans_amount' => '--',
					'trans_payments' => '--',
					'trans_refunded' => '--',
					'trans_due' => '--'
				];
			}
			else
			{
				if(empty($row['trans_type']))
				{
					$row['trans_type'] = lang('Reports.trans_nopay_sales');
				}

				$tabular_data[] = [
					'trans_group' => $row['trans_group'],
					'trans_type' => $row['trans_type'],
					'trans_sales' => $row['trans_sales'],
					'trans_amount' => to_currency($row['trans_amount']),
					'trans_payments' => to_currency($row['trans_payments']),
					'trans_refunded' => to_currency($row['trans_refunded']),
					'trans_due' => to_currency($row['trans_due'])
				];
			}
		}

		$data = [
			'title' => lang('Reports.payments_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $this->summary_payments->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $summary
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	 */
	public function date_input(): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$stock_locations = $data = $this->stock_location->get_allowed_locations('sales');
		$stock_locations['all'] = lang('Reports.all');
		$data['stock_locations'] = array_reverse($stock_locations, true);
		$data['mode'] = 'sale';
		$data['sale_type_options'] = $this->get_sale_type_options();

		echo view('reports/date_input', $data);
	}

	/**
	 * Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	 */
	public function date_input_only(): void
	{
		$this->clearCache();

		$data = [];
		echo view('reports/date_input', $data);
	}

	/**
	 * Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	 */
	public function date_input_sales(): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$stock_locations = $data = $this->stock_location->get_allowed_locations('sales');
		$stock_locations['all'] =  lang('Reports.all');
		$data['stock_locations'] = array_reverse($stock_locations, true);
		$data['mode'] = 'sale';
		$data['sale_type_options'] = $this->get_sale_type_options();

		echo view('reports/date_input', $data);
	}

	/**
	 * @return void
	 */
	public function date_input_recv(): void
	{
		$stock_locations = $data = $this->stock_location->get_allowed_locations('receivings');
		$stock_locations['all'] =  lang('Reports.all');
		$data['stock_locations'] = array_reverse($stock_locations, true);
		$data['mode'] = 'receiving';

		echo view('reports/date_input', $data);
	}

	/**
	 * Graphical Expenses by Categories report
	 */
	public function graphical_summary_expenses_categories(string $start_date, string $end_date, string $sale_type): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type
		];

		$report_data = $this->summary_expenses_categories->getData($inputs);
		$summary = $this->summary_expenses_categories->getSummaryData($inputs);

		$labels = [];
		$series = [];
		foreach($report_data as $row)
		{
			$labels[] = $row['category_name'];
			$series[] = [
				'meta' => $row['category_name'] . ' ' . round($row['total_amount'] / $summary['expenses_total_amount'] * 100, 2) . '%',
				'value' => $row['total_amount']
			];
		}

		$data = [
			'title' => lang('Reports.expenses_categories_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary sales report
	 */
	public function graphical_summary_sales(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_sales->getData($inputs);
		$summary = $this->summary_sales->getSummaryData($inputs);

		$labels = [];
		$series = [];
		foreach($report_data as $row)
		{
			$date = to_date(strtotime($row['sale_date'] ?? null));
			$labels[] = $date;
			$series[] = ['meta' => $date, 'value' => $row['total']];
		}

		$data = [
			'title' => lang('Reports.sales_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/line',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'yaxis_title' => lang('Reports.revenue'),
			'xaxis_title' => lang('Reports.date'),
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary items report
	 */
	public function graphical_summary_items(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];


		$report_data = $this->summary_items->getData($inputs);
		$summary = $this->summary_items->getSummaryData($inputs);

		$labels = [];
		$series = [];

		foreach($report_data as $row)
		{
			$labels[] = $row['name'];
			$series[] = $row['total'];
		}

		$data = [
			'title' => lang('Reports.items_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/hbar',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'yaxis_title' => lang('Reports.items'),
			'xaxis_title' => lang('Reports.revenue'),
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary customers report
	 */
	public function graphical_summary_categories(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_categories->getData($inputs);
		$summary = $this->summary_categories->getSummaryData($inputs);

		$labels = [];
		$series = [];
		foreach($report_data as $row)
		{
			$labels[] = $row['category'];
			$series[] = ['meta' => $row['category'] . ' ' . round($row['total'] / $summary['total'] * 100, 2) . '%', 'value' => $row['total']];
		}

		$data = [
			'title' => lang('Reports.categories_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary suppliers report
	 */
	public function graphical_summary_suppliers(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];


		$report_data = $this->summary_suppliers->getData($inputs);
		$summary = $this->summary_suppliers->getSummaryData($inputs);

		$labels = [];
		$series = [];

		foreach($report_data as $row)
		{
			$labels[] = $row['supplier'];
			$series[] = ['meta' => $row['supplier'] . ' ' . round($row['total'] / $summary['total'] * 100, 2) . '%', 'value' => $row['total']];
		}

		$data = [
			'title' => lang('Reports.suppliers_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary employees report
	 */
	public function graphical_summary_employees(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_employees->getData($inputs);
		$summary = $this->summary_employees->getSummaryData($inputs);

		$labels = [];
		$series = [];

		foreach($report_data as $row)
		{
			$labels[] = $row['employee'];
			$series[] = ['meta' => $row['employee'] . ' ' . round($row['total'] / $summary['total'] * 100, 2) . '%', 'value' => $row['total']];
		}

		$data = [
			'title' => lang('Reports.employees_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary taxes report
	 */
	public function graphical_summary_taxes(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_taxes->getData($inputs);
		$summary = $this->summary_taxes->getSummaryData($inputs);

		$labels = [];
		$series = [];

		foreach($report_data as $row)
		{
			$labels[] = $row['percent'];
			$series[] = ['meta' => $row['percent'] . ' ' . round($row['total'] / $summary['total'] * 100, 2) . '%', 'value' => $row['total']];
		}

		$data = [
			'title' => lang('Reports.taxes_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary sales taxes report
	 */
	public function graphical_summary_sales_taxes(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_sales_taxes->getData($inputs);
		$summary = $this->summary_sales_taxes->getSummaryData($inputs);

		$labels = [];
		$series = [];

		foreach($report_data as $row)
		{
			$labels[] = $row['jurisdiction_name'];
			$series[] = ['meta' => $row['tax_rate'] . '%', 'value' => $row['tax']];
		}

		$data = [
			'title' => lang('Reports.sales_taxes_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary customers report
	 */
	public function graphical_summary_customers(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_customers->getData($inputs);
		$summary = $this->summary_customers->getSummaryData($inputs);

		$labels = [];
		$series = [];

		foreach($report_data as $row)
		{
			$labels[] = $row['customer'];
			$series[] = $row['total'];
		}

		$data = [
			'title' => lang('Reports.customers_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/hbar',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'yaxis_title' => lang('Reports.customers'),
			'xaxis_title' => lang('Reports.revenue'),
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary discounts report
	 *
	 * @param string $start_date Start date of the report
	 * @param string $end_date End date of the report
	 * @param string $sale_type
	 * @param string $location_id ID of the location to be reported or 'all' if none is specified
	 * @param int $discount_type
	 */
	public function graphical_summary_discounts(string $start_date, string $end_date, string $sale_type, string $location_id = 'all', int $discount_type = 0): void
	{//TODO: Duplicated Code
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id,
			'discount_type'=>$discount_type
		];

		$report_data = $this->summary_discounts->getData($inputs);
		$summary = $this->summary_discounts->getSummaryData($inputs);

		$labels = [];
		$series = [];

		foreach($report_data as $row)
		{
			$labels[] = $row['discount'];
			$series[] = $row['count'];
		}

		$data = [
			'title' => lang('Reports.discounts_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/bar',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'yaxis_title' => lang('Reports.count'),
			'xaxis_title' => lang('Reports.discount'),
			'show_currency' => false
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * Graphical summary payments report
	 */
	public function graphical_summary_payments(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id
		];

		$report_data = $this->summary_payments->getData($inputs);
		$summary = $this->summary_payments->getSummaryData($inputs);

		$labels = [];
		$series = [];

		foreach($report_data as $row)
		{
			if($row['trans_group'] == lang('Reports.trans_payments') && !empty($row['trans_amount']))
			{
				$labels[] = $row['trans_type'];
				$series[] = ['meta' => $row['trans_type'] . ' ' . round($row['trans_amount'] / $summary['total'] * 100, 2) . '%', 'value' => $row['trans_amount']];
			}
		}

		$data = [
			'title' => lang('Reports.payments_summary_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'chart_type' => 'reports/graphs/pie',
			'labels_1' => $labels,
			'series_data_1' => $series,
			'summary_data_1' => $summary,
			'show_currency' => true
		];

		echo view('reports/graphical', $data);
	}

	/**
	 * @return void
	 */
	public function specific_customer_input(): void
	{
		$this->clearCache();

		$data = [];
		$data['specific_input_name'] = lang('Reports.customer');
		$customers = [];
		foreach($this->customer->get_all()->getResult() as $customer)
		{
			if(isset($customer->company_name))
			{
				$customers[$customer->person_id] = $customer->first_name . ' ' . $customer->last_name. ' ' . ' [ '.$customer->company_name.' ] ';
			}
			else
			{
				$customers[$customer->person_id] = $customer->first_name . ' ' . $customer->last_name;
			}
		}
		$data['specific_input_data'] = $customers;
		$data['sale_type_options'] = $this->get_sale_type_options();

		$data['payment_type'] = $this->get_payment_type();
		echo view('reports/specific_customer_input', $data);
	}

	/**
	 * @return array
	 */
	public function get_payment_type(): array
	{
		return [
			'all' => lang('Common.none_selected_text'),
			'cash' => lang('Sales.cash'),
			'due' => lang('Sales.due'),
			'check' => lang('Sales.check'),
			'credit' => lang('Sales.credit'),
			'debit' => lang('Sales.debit'),
			'invoices' => lang('Sales.invoice')
		];
	}

	/**
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $customer_id
	 * @param string $sale_type
	 * @param string $payment_type
	 * @return void
	 */
	public function specific_customers(string $start_date, string $end_date, string $customer_id, string $sale_type, string $payment_type): void
	{
		$this->clearCache();

		$inputs = ['start_date' => $start_date, 'end_date' => $end_date, 'customer_id' => $customer_id, 'sale_type' => $sale_type, 'payment_type' => $payment_type];

		$specific_customer = model(Specific_customer::class);

		$specific_customer->create($inputs);

		$headers = $specific_customer->getDataColumns();
		$report_data = $specific_customer->getData($inputs);

		$summary_data = [];
		$details_data = [];
		$details_data_rewards = [];

		foreach($report_data['summary'] as $key => $row)
		{
			if($row['sale_status'] == CANCELED)
			{
				$button_key = 'data-btn-restore';
				$button_label = lang('Common.restore');
			}
			else
			{
				$button_key = 'data-btn-delete';
				$button_label = lang('Common.delete');
			}

			$summary_data[] = [
				'id' => $row['sale_id'],
				'type_code' => $row['type_code'],
				'sale_time' => to_datetime(strtotime($row['sale_time'])),
				'sale_date' => to_date(strtotime($row['sale_date'] ?? null)),
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee_name' => $row['employee_name'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment'],
				'edit' => anchor(
					'sales/edit/'. $row['sale_id'],
					'<span class="glyphicon glyphicon-edit"></span>',
					[
						'class' => 'modal-dlg print_hide',
						$button_key => $button_label,
						'data-btn-submit' => lang('Common.submit'),
						'title' => lang('Sales.update')
					])
			];

			foreach($report_data['details'][$key] as $drow)	//TODO: Duplicated Code
			{
				$details_data[$row['sale_id']][] = [
					$drow['name'],
					$drow['category'],
					$drow['item_number'],
					$drow['description'],
					to_quantity_decimals($drow['quantity_purchased']),
					to_currency($drow['subtotal']),
					to_currency_tax($drow['tax']),
					to_currency($drow['total']),
					to_currency($drow['cost']),
					to_currency($drow['profit']),
					($drow['discount_type'] == PERCENT)? $drow['discount'].'%':to_currency($drow['discount'])
				];
			}

			if(isset($report_data['rewards'][$key]))
			{
				foreach($report_data['rewards'][$key] as $drow)
				{
					$details_data_rewards[$row['sale_id']][] = [$drow['used'], $drow['earned']];
				}
			}
		}

		$customer_info = $this->customer->get_info($customer_id);
		$customer_name = !empty($customer_info->company_name)	//TODO: This variable is not used anywhere in the code. Should it be or can it be deleted?
			? "[ $customer_info->company_name ]"
			: $customer_info->company_name;

		//TODO: Duplicated Code
		$data = [
			'title' => $customer_info->first_name . ' ' . $customer_info->last_name . ' ' . lang('Reports.report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $headers,
			'editable' => 'sales',
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $specific_customer->getSummaryData($inputs)
		];

		echo view('reports/tabular_details', $data);
	}

	/**
	 * @return void
	 */
	public function specific_employee_input(): void
	{
		$this->clearCache();

		$data = [];
		$data['specific_input_name'] = lang('Reports.employee');

		$employees = [];
		foreach($this->employee->get_all()->getResult() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}
		$data['specific_input_data'] = $employees;
		$data['sale_type_options'] = $this->get_sale_type_options();

		echo view('reports/specific_input', $data);
	}

	/**
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $employee_id
	 * @param string $sale_type
	 * @return void
	 */
	public function specific_employees(string $start_date, string $end_date, string $employee_id, string $sale_type): void
	{
		$this->clearCache();

		$inputs = ['start_date' => $start_date, 'end_date' => $end_date, 'employee_id' => $employee_id, 'sale_type' => $sale_type];

		$specific_employee = model(Specific_employee::class);

		$specific_employee->create($inputs);

		$headers = $specific_employee->getDataColumns();
		$report_data = $specific_employee->getData($inputs);

		$summary_data = [];
		$details_data = [];
		$details_data_rewards = [];

		foreach($report_data['summary'] as $key => $row)
		{
			if($row['sale_status'] == CANCELED)
			{
				$button_key = 'data-btn-restore';
				$button_label = lang('Common.restore');
			}
			else
			{
				$button_key = 'data-btn-delete';
				$button_label = lang('Common.delete');
			}

			$summary_data[] = [
				'id' => $row['sale_id'],
				'type_code' => $row['type_code'],
				'sale_date' => to_date(strtotime($row['sale_date'] ?? null)),
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'customer_name' => $row['customer_name'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment'],
				'edit' => anchor(
					'sales/edit/'. $row['sale_id'],
					'<span class="glyphicon glyphicon-edit"></span>',
					[
						'class' => 'modal-dlg print_hide',
						$button_key => $button_label,
						'data-btn-submit' => lang('Common.submit'),
						'title' => lang('Sales.update')
					])
			];
			//TODO: Duplicated Code
			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$row['sale_id']][] = [
					$drow['name'],
					$drow['category'],
					$drow['item_number'],
					$drow['description'],
					to_quantity_decimals($drow['quantity_purchased']),
					to_currency($drow['subtotal']),
					to_currency_tax($drow['tax']),
					to_currency($drow['total']),
					to_currency($drow['cost']),
					to_currency($drow['profit']),
					($drow['discount_type'] == PERCENT)? $drow['discount'].'%':to_currency($drow['discount'])
				];
			}

			if(isset($report_data['rewards'][$key]))
			{
				foreach($report_data['rewards'][$key] as $drow)
				{
					$details_data_rewards[$row['sale_id']][] = [$drow['used'], $drow['earned']];
				}
			}
		}

		$employee_info = $this->employee->get_info($employee_id);
		//TODO: Duplicated Code
		$data = [
			'title' => $employee_info->first_name . ' ' . $employee_info->last_name . ' ' . lang('Reports.report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $headers,
			'editable' => 'sales',
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $specific_employee->getSummaryData($inputs)
		];

		echo view('reports/tabular_details', $data);
	}

	/**
	 * @return void
	 */
	public function specific_discount_input(): void
	{
		$this->clearCache();

		$data = [];
		$data['specific_input_name'] = lang('Reports.discount');

		$discounts = [];
		for($i = 0; $i <= 100; $i += 10)
		{
			$discounts[$i] = $i . '%';
		}
		$data['specific_input_data'] = $discounts;
		$data['discount_type_options'] = ['0' => lang('Reports.discount_percent'), '1'=> lang('Reports.discount_fixed')];
		$data['sale_type_options'] = $this->get_sale_type_options();

		echo view('reports/specific_input', $data);
	}

	/**
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $discount
	 * @param string $sale_type
	 * @param string $discount_type
	 * @return void
	 */
	public function specific_discounts(string $start_date, string $end_date, string $discount, string $sale_type, string $discount_type): void
	{
		$this->clearCache();

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'discount' => $discount,
			'sale_type' => $sale_type,
			'discount_type' => $discount_type
		];

		$specific_discount = model(Specific_discount::class);

		$specific_discount->create($inputs);

		$headers = $specific_discount->getDataColumns();
		$report_data = $specific_discount->getData($inputs);

		$summary_data = [];
		$details_data = [];
		$details_data_rewards = [];

		foreach($report_data['summary'] as $key => $row)
		{	//TODO: Duplicated Code
			if($row['sale_status'] == CANCELED)
			{
				$button_key = 'data-btn-restore';
				$button_label = lang('Common.restore');
			}
			else
			{
				$button_key = 'data-btn-delete';
				$button_label = lang('Common.delete');
			}

			$summary_data[] = [
				'id' => $row['sale_id'],
				'type_code' => $row['type_code'],
				'sale_date' => to_date(strtotime($row['sale_date']?? null)),
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee_name' => $row['employee_name'],
				'customer_name' => $row['customer_name'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment'],
				'edit' => anchor(
					'sales/edit/'. $row['sale_id'],
					'<span class="glyphicon glyphicon-edit"></span>',
					[
						'class' => 'modal-dlg print_hide',
						$button_key => $button_label,
						'data-btn-submit' => lang('Common.submit'),
						'title' => lang('Sales.update')
					])
			];
			//TODO: Duplicated Code
			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$row['sale_id']][] = [
					$drow['name'],
					$drow['category'],
					$drow['item_number'],
					$drow['description'],
					to_quantity_decimals($drow['quantity_purchased']),
					to_currency($drow['subtotal']),
					to_currency_tax($drow['tax']),
					to_currency($drow['total']),
					to_currency($drow['cost']),
					to_currency($drow['profit']),
					($drow['discount_type'] == PERCENT)
						? $drow['discount'].'%'
						: to_currency($drow['discount'])
				];
			}

			if(isset($report_data['rewards'][$key]))
			{
				foreach($report_data['rewards'][$key] as $drow)
				{
					$details_data_rewards[$row['sale_id']][] = [$drow['used'], $drow['earned']];
				}
			}
		}

		$data = [
			'title' => $discount . '% ' . lang('Reports.discount') . ' ' . lang('Reports.report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $headers,
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $specific_discount->getSummaryData($inputs)
		];

		echo view('reports/tabular_details', $data);
	}

	/**
	 * @param string $sale_id
	 * @return void
	 */
	public function get_detailed_sales_row(string $sale_id): void
	{
		$this->clearCache();

		$inputs = ['sale_id' => $sale_id];

		$this->detailed_sales->create($inputs);

		$report_data = $this->detailed_sales->getDataBySaleId($sale_id);

		if($report_data['sale_status'] == CANCELED)
		{
			$button_key = 'data-btn-restore';
			$button_label = lang('Common.restore');
		}
		else
		{
			$button_key = 'data-btn-delete';
			$button_label = lang('Common.delete');
		}

		$summary_data = [
			'sale_id' => $report_data['sale_id'],
			'sale_time' => to_datetime(strtotime($report_data['sale_time'])),
			'quantity' => to_quantity_decimals($report_data['items_purchased']),
			'employee_name' => $report_data['employee_name'],
			'customer_name' => $report_data['customer_name'],
			'subtotal' => to_currency($report_data['subtotal']),
			'tax' => to_currency_tax($report_data['tax']),
			'total' => to_currency($report_data['total']),
			'cost' => to_currency($report_data['cost']),
			'profit' => to_currency($report_data['profit']),
			'payment_type' => $report_data['payment_type'],
			'comment' => $report_data['comment'],
			'edit' => anchor(
				'sales/edit/'. $report_data['sale_id'],
				'<span class="glyphicon glyphicon-edit"></span>',
				[
					'class' => 'modal-dlg print_hide',
					$button_key => $button_label,
					'data-btn-submit' => lang('Common.submit'),
					'title' => lang('Sales.update')
				])
		];

		echo json_encode ([$sale_id => $summary_data]);
	}

	/**
	 * @return void
	 */
	public function specific_supplier_input(): void
	{
		$this->clearCache();

		$data = [];
		$data['specific_input_name'] = lang('Reports.supplier');

		$suppliers = [];
		foreach($this->supplier->get_all()->getResult() as $supplier)
		{
			$suppliers[$supplier->person_id] = $supplier->company_name . ' (' . $supplier->first_name . ' ' . $supplier->last_name . ')';
		}
		$data['specific_input_data'] = $suppliers;
		$data['sale_type_options'] = $this->get_sale_type_options();

		echo view('reports/specific_input', $data);
	}

	/**
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $supplier_id
	 * @param string $sale_type
	 * @return void
	 */
	public function specific_suppliers(string $start_date, string $end_date, string $supplier_id, string $sale_type): void
	{
		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'supplier_id' => $supplier_id,
			'sale_type' => $sale_type
		];

		$specific_supplier = model(Specific_supplier::class);

		$specific_supplier->create($inputs);

		$report_data = $specific_supplier->getData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'id' => $row['sale_id'],
				'type_code' => $row['type_code'],
				'sale_date' => to_date(strtotime($row['sale_date'] ?? null)),
				'name' => $row['name'],
				'category' => $row['category'],
				'item_number' => $row['item_number'],
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'discount' => ($row['discount_type'] == PERCENT)? $row['discount'].'%':to_currency($row['discount'])
			];
		}

		$supplier_info = $this->supplier->get_info($supplier_id);
		$data = [
			'title' => $supplier_info->company_name . ' (' . $supplier_info->first_name . ' ' . $supplier_info->last_name . ') ' . lang('Reports.report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $specific_supplier->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $specific_supplier->getSummaryData($inputs)
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * @return array
	 */
	public function get_sale_type_options(): array
	{
		$sale_type_options = [];
		$sale_type_options['complete'] = lang('Reports.complete');
		$sale_type_options['sales'] = lang('Reports.completed_sales');
		if($this->config['invoice_enable'])
		{
			$sale_type_options['quotes'] = lang('Reports.quotes');
			if($this->config['work_order_enable'])
			{
				$sale_type_options['work_orders'] = lang('Reports.work_orders');
			}
		}
		$sale_type_options['canceled'] = lang('Reports.canceled');
		$sale_type_options['returns'] = lang('Reports.returns');
		return $sale_type_options;
	}

	/**
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $sale_type
	 * @param string $location_id
	 * @return void
	 */
	public function detailed_sales(string $start_date, string $end_date, string $sale_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$definition_names = $this->attribute->get_definitions_by_flags(attribute::SHOW_IN_SALES);

		$inputs = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'sale_type' => $sale_type,
			'location_id' => $location_id,
			'definition_ids' => array_keys($definition_names)
		];

		$this->detailed_sales->create($inputs);

		$columns = $this->detailed_sales->getDataColumns();
		$columns['details'] = array_merge($columns['details'], $definition_names);

		$headers = $columns;

		$report_data = $this->detailed_sales->getData($inputs);

		$summary_data = [];
		$details_data = [];
		$details_data_rewards = [];

		$show_locations = $this->stock_location->multiple_locations();

		foreach($report_data['summary'] as $key => $row)
		{	//TODO: Duplicated Code
			if($row['sale_status'] == CANCELED)
			{
				$button_key = 'data-btn-restore';
				$button_label = lang('Common.restore');
			}
			else
			{
				$button_key = 'data-btn-delete';
				$button_label = lang('Common.delete');
			}

			$summary_data[] = [
				'id' => $row['sale_id'],
				'type_code' => $row['type_code'],
				'sale_date' => to_date(strtotime($row['sale_date'] ?? null)),
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee_name' => $row['employee_name'],
				'customer_name' => $row['customer_name'],
				'subtotal' => to_currency($row['subtotal']),
				'tax' => to_currency_tax($row['tax']),
				'total' => to_currency($row['total']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment'],
				'edit' => anchor(
					'sales/edit/'.$row['sale_id'],
					'<span class="glyphicon glyphicon-edit"></span>',
					[
						'class' => 'modal-dlg print_hide',
						$button_key => $button_label,
						'data-btn-submit' => lang('Common.submit'),
						'title' => lang('Sales.update')
					])
			];

			foreach($report_data['details'][$key] as $drow)
			{
				$quantity_purchased = to_quantity_decimals($drow['quantity_purchased']);
				if($show_locations)
				{
					$quantity_purchased .= ' [' . $this->stock_location->get_location_name($drow['item_location']) . ']';
				}

				$attribute_values = expand_attribute_values($definition_names, $drow);

				$details_data[$row['sale_id']][] =
					array_merge ([
						$drow['name'],
						$drow['category'],
						$drow['item_number'],
						$drow['description'],
						$quantity_purchased,
						to_currency($drow['subtotal']),
						to_currency_tax($drow['tax']),
						to_currency($drow['total']),
						to_currency($drow['cost']),
						to_currency($drow['profit']),
						($drow['discount_type'] == PERCENT) ? $drow['discount'].'%' : to_currency($drow['discount'])
					],
					$attribute_values
				);
			}

			if(isset($report_data['rewards'][$key]))
			{
				foreach($report_data['rewards'][$key] as $drow)
				{
					$details_data_rewards[$row['sale_id']][] = [$drow['used'], $drow['earned']];
				}
			}
		}

		$data = [
			'title' => lang('Reports.detailed_sales_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $headers,
			'editable' => 'sales',
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'details_data_rewards' => $details_data_rewards,
			'overall_summary_data' => $this->detailed_sales->getSummaryData($inputs)
		];
		echo view('reports/tabular_details', $data);
	}

	/**
	 * @param string $receiving_id
	 * @return void
	 */
	public function get_detailed_receivings_row(string $receiving_id): void
	{
		$inputs = ['receiving_id' => $receiving_id];

		$this->detailed_receivings->create($inputs);

		$report_data = $this->detailed_receivings->getDataByReceivingId($receiving_id);

		$summary_data = [
			'receiving_id' => $report_data['receiving_id'],
			'receiving_time' => to_datetime(strtotime($report_data['receiving_time'])),
			'quantity' => to_quantity_decimals($report_data['items_purchased']),
			'employee_name' => $report_data['employee_name'],
			'supplier_name' => $report_data['supplier_name'],
			'total' => to_currency($report_data['total']),
			'payment_type' => $report_data['payment_type'],
			'reference' => $report_data['reference'],
			'comment' => $report_data['comment'],
			'edit' => anchor(
				'receivings/edit/'. $report_data['receiving_id'],
				'<span class="glyphicon glyphicon-edit"></span>',
				[
					'class' => 'modal-dlg print_hide',
					'data-btn-submit' => lang('Common.submit'),
					'data-btn-delete' => lang('Common.delete'),
					'title' => lang('Receivings.update')
				])
		];

		echo json_encode ([$receiving_id => $summary_data]);
	}

	/**
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $receiving_type
	 * @param string $location_id
	 * @return void
	 */
	public function detailed_receivings(string $start_date, string $end_date, string $receiving_type, string $location_id = 'all'): void
	{
		$this->clearCache();

		$definition_names = $this->attribute->get_definitions_by_flags(attribute::SHOW_IN_RECEIVINGS);

		$inputs = ['start_date' => $start_date, 'end_date' => $end_date, 'receiving_type' => $receiving_type, 'location_id' => $location_id, 'definition_ids' => array_keys($definition_names)];

		$this->detailed_receivings->create($inputs);

		$columns = $this->detailed_receivings->getDataColumns();
		$columns['details'] = array_merge($columns['details'], $definition_names);

		$headers = $columns;
		$report_data = $this->detailed_receivings->getData($inputs);

		$summary_data = [];
		$details_data = [];

		$show_locations = $this->stock_location->multiple_locations();

		foreach($report_data['summary'] as $key => $row)
		{
			$summary_data[] = [
				'id' => $row['receiving_id'],
				'receiving_date' => to_date(strtotime($row['receiving_date'] ?? null)),
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee_name' => $row['employee_name'],
				'supplier_name' => $row['supplier_name'],
				'total' => to_currency($row['total']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'reference' => $row['reference'],
				'comment' => $row['comment'],
				'edit' => anchor(
					'receivings/edit/' . $row['receiving_id'],
					'<span class="glyphicon glyphicon-edit"></span>',
					[
						'class' => 'modal-dlg print_hide',
						'data-btn-delete' => lang('Common.delete'),
						'data-btn-submit' => lang('Common.submit'),
						'title' => lang('Receivings.update')
					])
			];

			foreach($report_data['details'][$key] as $drow)
			{
				$quantity_purchased = $drow['receiving_quantity'] > 1 ? to_quantity_decimals($drow['quantity_purchased']) . ' x ' . to_quantity_decimals($drow['receiving_quantity']) : to_quantity_decimals($drow['quantity_purchased']);
				if($show_locations)
				{
					$quantity_purchased .= ' [' . $this->stock_location->get_location_name($drow['item_location']) . ']';
				}

				$attribute_values = expand_attribute_values($definition_names, $drow);

				$details_data[$row['receiving_id']][] = array_merge ([
					$drow['item_number'],
					$drow['name'],
					$drow['category'],
					$quantity_purchased,
					to_currency($drow['total']),
					($drow['discount_type'] == PERCENT)? $drow['discount'].'%':to_currency($drow['discount'])], $attribute_values);
			}
		}

		$data = [
			'title' => lang('Reports.detailed_receivings_report'),
			'subtitle' => $this->_get_subtitle_report(['start_date' => $start_date, 'end_date' => $end_date]),
			'headers' => $headers,
			'editable' => 'receivings',
			'summary_data' => $summary_data,
			'details_data' => $details_data,
			'overall_summary_data' => $this->detailed_receivings->getSummaryData($inputs)
		];

		echo view('reports/tabular_details', $data);
	}

	/**
	 * @return void
	 */
	public function inventory_low(): void
	{
		$this->clearCache();

		$inputs = [];

		$inventory_low = model(Inventory_low::class);

		$report_data = $inventory_low->getData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'item_name' => $row['name'],
				'item_number' => $row['item_number'],
				'quantity' => to_quantity_decimals($row['quantity']),
				'reorder_level' => to_quantity_decimals($row['reorder_level']),
				'location_name' => $row['location_name']
			];
		}

		$data = [
			'title' => lang('Reports.inventory_low_report'),
			'subtitle' => '',
			'headers' => $inventory_low->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $inventory_low->getSummaryData($inputs)
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * @return void
	 */
	public function inventory_summary_input(): void
	{
		$this->clearCache();

		$data = [];
		$data['item_count'] = $this->inventory_summary->getItemCountDropdownArray();

		$stock_locations = $this->stock_location->get_allowed_locations();
		$stock_locations['all'] = lang('Reports.all');
		$data['stock_locations'] = array_reverse($stock_locations, true);

		echo view('reports/inventory_summary_input', $data);
	}

	/**
	 * @param string $location_id
	 * @param string $item_count
	 * @return void
	 */
	public function inventory_summary(string $location_id = 'all', string $item_count = 'all'): void
	{
		$this->clearCache();

		$inputs = ['location_id' => $location_id, 'item_count' => $item_count];

		$report_data = $this->inventory_summary->getData($inputs);

		$tabular_data = [];
		foreach($report_data as $row)
		{
			$tabular_data[] = [
				'item_name' => $row['name'],
				'item_number' => $row['item_number'],
				'category' => $row['category'],
				'quantity' => to_quantity_decimals($row['quantity']),
				'low_sell_quantity' => to_quantity_decimals($row['low_sell_quantity']),
				'reorder_level' => to_quantity_decimals($row['reorder_level']),
				'location_name' => $row['location_name'],
				'cost_price' => to_currency($row['cost_price']),
				'unit_price' => to_currency($row['unit_price']),
				'subtotal' => to_currency($row['sub_total_value'])
			];
		}

		$data = [
			'title' => lang('Reports.inventory_summary_report'),
			'subtitle' => '',
			'headers' => $this->inventory_summary->getDataColumns(),
			'data' => $tabular_data,
			'summary_data' => $this->inventory_summary->getSummaryData($report_data)
		];

		echo view('reports/tabular', $data);
	}

	/**
	 * Returns subtitle for the reports
	 */
	private function _get_subtitle_report(array $inputs): string	//TODO: Hungarian Notation
	{
		$subtitle = '';

		if(empty($this->config['date_or_time_format']))
		{
			$subtitle .= date($this->config['dateformat'], strtotime($inputs['start_date'])) . ' - ' . date($this->config['dateformat'], strtotime($inputs['end_date']));
		}
		else
		{
			$subtitle .= date($this->config['dateformat'] . ' ' . $this->config['timeformat'], strtotime(rawurldecode($inputs['start_date']))) . ' - ' . date($this->config['dateformat'] . ' ' . $this->config['timeformat'], strtotime(rawurldecode($inputs['end_date'])));
		}

		return $subtitle;
	}

	/**
	 * @return void
	 */
	private function clearCache(): void
	{
		//Make sure the report is not cached by the browser
		$this->response->setHeader('Pragma', 'no-cache')
			->appendHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
			->appendHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
			->appendHeader('Cache-Control', 'post-check=0, pre-check=0');
	}
}
