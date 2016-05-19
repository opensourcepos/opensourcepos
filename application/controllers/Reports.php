<?php
require_once ("Secure_area.php");
require_once (APPPATH."libraries/ofc-library/Open-flash-chart.php");

class Reports extends Secure_area
{
	function __construct()
	{
		parent::__construct('reports');
		$method_name = $this->uri->segment(2);
		$exploder = explode('_', $method_name);
		preg_match("/(?:inventory)|([^_.]*)(?:_graph|_row)?$/", $method_name, $matches);
		preg_match("/^(.*?)([sy])?$/", array_pop($matches), $matches);
		$submodule_id = $matches[1] . ((count($matches) > 2) ? $matches[2] : "s");
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		// check access to report submodule
		if (sizeof($exploder) > 1 && !$this->Employee->has_grant('reports_' . $submodule_id,$employee_id))
		{
			redirect('no_access/reports/reports_' . $submodule_id);
		}

		$this->load->helper('report');
	}

	//Initial report listing screen
	function index()
	{
		$data['grants'] = $this->Employee->get_employee_grants($this->session->userdata('person_id'));
		$this->load->view("reports/listing", $data);
	}

 	function get_detailed_sales_row($sale_id)
	{
		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;

		$report_data = $model->getDataBySaleId($sale_id);

		$summary_data = array(
			'sale_id' => $report_data['sale_id'],
			'sale_date' => $report_data['sale_date'],
			'quantity' => to_quantity_decimals($report_data['items_purchased']),
			'employee' => $report_data['employee_name'],
			'customer' => $report_data['customer_name'],
			'subtotal' => to_currency($report_data['subtotal']),
			'total' => to_currency($report_data['total']),
			'tax' => to_currency($report_data['tax']),
			'cost' => to_currency($report_data['cost']),
			'profit' => to_currency($report_data['profit']),
			'payment_type' => $report_data['payment_type'],
			'comment' => $report_data['comment'],
			'edit' => anchor("sales/edit/". $report_data['sale_id'], '<span class="glyphicon glyphicon-edit"></span>',
				array('class'=>"modal-dlg modal-btn-delete modal-btn-submit print_hide", 'title'=>$this->lang->line('sales_update'))
			)
		);

		echo json_encode($summary_data);
	}

	function get_detailed_receivings_row($receiving_id)
	{
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$report_data = $model->getDataByReceivingId($receiving_id);

		$summary_data = array(
			'receiving_id' => $report_data['receiving_id'],
			'receiving_date' => $report_data['receiving_date'],
			'quantity' => to_quantity_decimals($report_data['items_purchased']),
			'invoice_number' => $report_data['invoice_number'],
			'employee' => $report_data['employee_name'],
			'supplier' => $report_data['supplier_name'],
			'total' => to_currency($report_data['total']),
			'comment' => $report_data['comment'],
			'payment_type' => $report_data['payment_type'],
			'edit' => anchor("receivings/edit/". $report_data['receiving_id'], '<span class="glyphicon glyphicon-edit"></span>',
				array('class'=>"modal-dlg modal-btn-delete modal-btn-submit print_hide", 'title'=>$this->lang->line('recvs_update'))
			)
		);

		if($this->config->item('invoice_enable') == TRUE)
		{
			$summary_data[]['invoice_number'] = $report_data['invoice_number'];
		}
		
		$summary_data[] = $report_data['comment'];

		echo json_encode($summary_data);
	}

	function get_summary_data($start_date, $end_date=null, $sale_type=0)
	{
		$end_date = $end_date ? $end_date : $start_date;
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;
		$summary = $model->getSummaryData(array(
				'start_date'=>$start_date,
				'end_date'=>$end_date,
				'sale_type'=>$sale_type));

		echo get_sales_summary_totals($summary, $this);
	}

	//Summary sales report
	function summary_sales($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['sale_date'],
				to_quantity_decimals($row['quantity_purchased']),
				to_currency($row['subtotal']),
				to_currency($row['total']),
				to_currency($row['tax']),
				to_currency($row['cost']),
				to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Summary categories report
	function summary_categories($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['category'],
				to_quantity_decimals($row['quantity_purchased']),
				to_currency($row['subtotal']),
				to_currency($row['total']),
				to_currency($row['tax']),
				to_currency($row['cost']),
				to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Summary customers report
	function summary_customers($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['customer'],
				to_quantity_decimals($row['quantity_purchased']),
				to_currency($row['subtotal']),
				to_currency($row['total']),
				to_currency($row['tax']),
				to_currency($row['cost']),
				to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_customers_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Summary suppliers report
	function summary_suppliers($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['supplier'],
				to_quantity_decimals($row['quantity_purchased']),
				to_currency($row['subtotal']),
				to_currency($row['total']),
				to_currency($row['tax']),
				to_currency($row['cost']),
				to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Summary items report
	function summary_items($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array(character_limiter($row['name'], 40),
				to_quantity_decimals($row['quantity_purchased']),
				to_currency($row['subtotal']),
				to_currency($row['total']),
				to_currency($row['tax']),
				to_currency($row['cost']),
				to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_items_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Summary employees report
	function summary_employees($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['employee'],
				to_quantity_decimals($row['quantity_purchased']),
				to_currency($row['subtotal']),
				to_currency($row['total']),
				to_currency($row['tax']),
				to_currency($row['cost']),
				to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Summary taxes report
	function summary_taxes($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['percent'], 
				$row['count'], 
				to_currency($row['subtotal']), 
				to_currency($row['total']), 
				to_currency($row['tax']));
		}

		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Summary discounts report
	function summary_discounts($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['discount_percent'],
				$row['count']);
		}

		$data = array(
			"title" => $this->lang->line('reports_discounts_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Summary payments report
	function summary_payments($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['payment_type'],
				$row['count'],
				to_currency($row['payment_amount']));
		}

		$data = array(
			"title" => $this->lang->line('reports_payments_summary_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular", $data);
	}

	//Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	function date_input()
	{
		$data = array();
		$data['mode'] = 'sale';

		$this->load->view("reports/date_input", $data);
	}

	//Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	function date_input_sales()
	{
		$data = array();
		$stock_locations = $this->Stock_location->get_allowed_locations('sales');
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
        $data['mode'] = 'sale';

		$this->load->view("reports/date_input", $data);
	}

    function date_input_recv()
    {
        $data = array();
		$stock_locations = $this->Stock_location->get_allowed_locations('receivings');
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);
 		$data['mode'] = 'receiving';

        $this->load->view("reports/date_input", $data);
    }

	//Graphical summary sales report
	function graphical_summary_sales($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"data_file" => site_url("reports/graphical_summary_sales_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_sales_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[date($this->config->item('dateformat'), strtotime($row['sale_date']))] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"yaxis_label"=>$this->lang->line('reports_revenue'),
			"xaxis_label"=>$this->lang->line('reports_date'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/line", $data);
	}

	//Graphical summary items report
	function graphical_summary_items($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;

		$data = array(
			"title" => $this->lang->line('reports_items_summary_report'),
			"data_file" => site_url("reports/graphical_summary_items_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_items_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['name']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_items_summary_report'),
			"xaxis_label"=>$this->lang->line('reports_revenue'),
			"yaxis_label"=>$this->lang->line('reports_items'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/hbar", $data);
	}

	//Graphical summary customers report
	function graphical_summary_categories($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;

		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"data_file" => site_url("reports/graphical_summary_categories_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_categories_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['category']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie", $data);
	}

	//Graphical summary suppliers report
	function graphical_summary_suppliers($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;

		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"data_file" => site_url("reports/graphical_summary_suppliers_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_suppliers_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['supplier']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie", $data);
	}

	//Graphical summary employees report
	function graphical_summary_employees($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;

		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"data_file" => site_url("reports/graphical_summary_employees_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_employees_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['employee']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie", $data);
	}

	//Graphical summary taxes report
	function graphical_summary_taxes($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;

		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"data_file" => site_url("reports/graphical_summary_taxes_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_taxes_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['percent']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie", $data);
	}

	//Graphical summary customers report
	function graphical_summary_customers($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;

		$data = array(
			"title" => $this->lang->line('reports_customers_summary_report'),
			"data_file" => site_url("reports/graphical_summary_customers_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_customers_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['customer']] = $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_customers_summary_report'),
			"xaxis_label"=>$this->lang->line('reports_revenue'),
			"yaxis_label"=>$this->lang->line('reports_customers'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/hbar", $data);
	}

	//Graphical summary discounts report
	function graphical_summary_discounts($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;

		$data = array(
			"title" => $this->lang->line('reports_discounts_summary_report'),
			"data_file" => site_url("reports/graphical_summary_discounts_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_discounts_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['discount_percent']] = $row['count'];
		}

		$data = array(
			"title" => $this->lang->line('reports_discounts_summary_report'),
			"yaxis_label"=>$this->lang->line('reports_count'),
			"xaxis_label"=>$this->lang->line('reports_discount_percent'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/bar", $data);
	}

	//Graphical summary payments report
	function graphical_summary_payments($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;

		$data = array(
			"title" => $this->lang->line('reports_payments_summary_report'),
			"data_file" => site_url("reports/graphical_summary_payments_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/graphical", $data);
	}

	//The actual graph data
	function graphical_summary_payments_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type));

		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['payment_type']] = $row['payment_amount'];
		}

		$data = array(
			"title" => $this->lang->line('reports_payments_summary_report'),
			"yaxis_label"=>$this->lang->line('reports_revenue'),
			"xaxis_label"=>$this->lang->line('reports_payment_type'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie", $data);
	}

	function specific_customer_input()
	{
		$data = array();
		$data['specific_input_name'] = $this->lang->line('reports_customer');

		$customers = array();
		foreach($this->Customer->get_all()->result() as $customer)
		{
			$customers[$customer->person_id] = $customer->first_name .' '.$customer->last_name;
		}
		$data['specific_input_data'] = $customers;

		$this->load->view("reports/specific_input", $data);
	}

	function specific_customer($start_date, $end_date, $customer_id, $sale_type)
	{
		$this->load->model('reports/Specific_customer');
		$model = $this->Specific_customer;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'customer_id'=>$customer_id, 'sale_type'=>$sale_type));

		$summary_data = array();
		$details_data = array();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/receipt/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target'=>'_blank')), $row['sale_date'], to_quantity_decimals($row['items_purchased']), $row['employee_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']), $row['payment_type'], $row['comment']);

			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$row['sale_id']][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], to_quantity_decimals($drow['quantity_purchased']), to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']), to_currency($drow['cost']), to_currency($drow['profit']), $drow['discount_percent'].'%');
			}
		}

		$customer_info = $this->Customer->get_info($customer_id);
		$data = array(
			"title" => $customer_info->first_name .' '. $customer_info->last_name.' '.$this->lang->line('reports_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'customer_id' =>$customer_id, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular_details", $data);
	}

	function specific_employee_input()
	{
		$data = array();
		$data['specific_input_name'] = $this->lang->line('reports_employee');

		$employees = array();
		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$data['specific_input_data'] = $employees;

		$this->load->view("reports/specific_input", $data);
	}

	function specific_employee($start_date, $end_date, $employee_id, $sale_type)
	{
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'employee_id' =>$employee_id, 'sale_type'=>$sale_type));

		$summary_data = array();
		$details_data = array();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/receipt/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target'=>'_blank')), $row['sale_date'], to_quantity_decimals($row['items_purchased']), $row['customer_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']), to_currency($row['cost']), to_currency($row['profit']), $row['payment_type'], $row['comment']);

			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$row['sale_id']][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], to_quantity_decimals($drow['quantity_purchased']), to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']), to_currency($drow['cost']), to_currency($drow['profit']), $drow['discount_percent'].'%');
			}
		}

		$employee_info = $this->Employee->get_info($employee_id);
		$data = array(
			"title" => $employee_info->first_name .' '. $employee_info->last_name.' '.$this->lang->line('reports_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'employee_id' =>$employee_id, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular_details", $data);
	}

	function specific_discount_input()
	{
		$data = array();
		$data['specific_input_name'] = $this->lang->line('reports_discount');

		$discounts = array();
		for($i = 0; $i <= 100; $i += 10)
		{
			$discounts[$i] = $i . '%';
		}
		$data['specific_input_data'] = $discounts;

		$this->load->view("reports/specific_input", $data);
	}

	function specific_discount($start_date, $end_date, $discount, $sale_type)
	{
		$this->load->model('reports/Specific_discount');
		$model = $this->Specific_discount;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'discount' =>$discount, 'sale_type'=>$sale_type));

		$summary_data = array();
		$details_data = array();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/receipt/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target'=>'_blank')), $row['sale_date'], to_quantity_decimals($row['items_purchased']), $row['customer_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),/*to_currency($row['profit']),*/ $row['payment_type'], $row['comment']);

			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$row['sale_id']][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], to_quantity_decimals($drow['quantity_purchased']), to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']),/*to_currency($drow['profit']),*/ $drow['discount_percent'].'%');
			}
		}

		$data = array(
			"title" => $discount. '% '.$this->lang->line('reports_discount') . ' ' . $this->lang->line('reports_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $headers,
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'discount' =>$discount, 'sale_type'=>$sale_type))
		);

		$this->load->view("reports/tabular_details", $data);
	}

	function detailed_sales($start_date, $end_date, $sale_type, $location_id='all')
	{
		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type, 'location_id'=>$location_id));

		$summary_data = array();
		$details_data = array();

		$show_locations = $this->Stock_location->multiple_locations();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(
				'id' => $row['sale_id'],
				'sale_date' => $row['sale_date'],
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee' => $row['employee_name'],
				'customer' => $row['customer_name'],
				'subtotal' => to_currency($row['subtotal']),
				'total' => to_currency($row['total']),
				'tax' => to_currency($row['tax']),
				'cost' => to_currency($row['cost']),
				'profit' => to_currency($row['profit']),
				'payment_type' => $row['payment_type'],
				'comment' => $row['comment'],
				'edit' => anchor("sales/edit/".$row['sale_id'], '<span class="glyphicon glyphicon-edit"></span>',
					array('class' => "modal-dlg modal-btn-delete modal-btn-submit print_hide", 'title' => $this->lang->line('sales_update'))
				)
			);

			foreach($report_data['details'][$key] as $drow)
			{
				$quantity_purchased = to_quantity_decimals($drow['quantity_purchased']);
				if ($show_locations)
				{
					$quantity_purchased .= ' [' . $this->Stock_location->get_location_name($drow['item_location']) . ']';
				}
				$details_data[$row['sale_id']][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $quantity_purchased, to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']), to_currency($drow['cost']), to_currency($drow['profit']), $drow['discount_percent'].'%');
			}
		}

		$data = array(
			"title" => $this->lang->line('reports_detailed_sales_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"editable" => "sales",
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type'=>$sale_type, 'location_id'=>$location_id))
		);

		$this->load->view("reports/tabular_details", $data);
	}

	function detailed_receivings($start_date, $end_date, $receiving_type, $location_id='all')
	{
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;

		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'receiving_type'=>$receiving_type, 'location_id'=>$location_id));

		$summary_data = array();
		$details_data = array();

		$show_locations = $this->Stock_location->multiple_locations();

		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(
				'id' => $row['receiving_id'],
				'receiving_date' => $row['receiving_date'],
				'quantity' => to_quantity_decimals($row['items_purchased']),
				'employee' => $row['employee_name'], $row['supplier_name'],
				'total' => to_currency($row['total']),
				'payment_type' => $row['payment_type'],
				'invoice_number' => $row['invoice_number'],
				'comment' => $row['comment'],
				'edit' => anchor("receivings/edit/" . $row['receiving_id'], '<span class="glyphicon glyphicon-edit"></span>',
					array('class' => "modal-dlg modal-btn-delete modal-btn-submit print_hide", 'title' => $this->lang->line('receivings_update'))
				)
			);
			if(!$this->config->item('invoice_enable'))
			{
				unset($summary_data['invoice_number']);
			}

			foreach($report_data['details'][$key] as $drow)
			{
				$quantity_purchased = $drow['receiving_quantity'] > 1 ? to_quantity_decimals($drow['quantity_purchased']) . ' x ' . to_quantity_decimals($drow['receiving_quantity']) : to_quantity_decimals($drow['quantity_purchased']);
				if ($show_locations)
				{
					$quantity_purchased .= ' [' . $this->Stock_location->get_location_name($drow['item_location']) . ']';
				}
				$details_data[$row['receiving_id']][] = array($drow['item_number'], $drow['name'], $drow['category'], $quantity_purchased, to_currency($drow['total']), $drow['discount_percent'].'%');
			}
		}

		$data = array(
			"title" => $this->lang->line('reports_detailed_receivings_report'),
			"subtitle" => date($this->config->item('dateformat'), strtotime($start_date)) . '-' . date($this->config->item('dateformat'), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"editable" => "receivings",
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'receiving_type'=>$receiving_type, 'location_id'=>$location_id))
		);

		$this->load->view("reports/tabular_details", $data);
	}

	function inventory_low()
	{
		$this->load->model('reports/Inventory_low');
		$model = $this->Inventory_low;
		$tabular_data = array();
		$report_data = $model->getData(array());
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['name'], 
								$row['item_number'], 
								$row['description'], 
								to_quantity_decimals($row['quantity']), 
								to_quantity_decimals($row['reorder_level']), 
								$row['location_name']);
		}

		$data = array(
			"title" => $this->lang->line('reports_inventory_low_report'),
			"subtitle" => '',
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array())
		);

		$this->load->view("reports/tabular", $data);
	}

	function inventory_summary_input()
	{
		$data = array();

		$this->load->model('reports/Inventory_summary');
		$model = $this->Inventory_summary;
		$data['item_count'] = $model->getItemCountDropdownArray();

		$stock_locations = $this->Stock_location->get_allowed_locations();
		$stock_locations['all'] =  $this->lang->line('reports_all');
		$data['stock_locations'] = array_reverse($stock_locations, TRUE);

		$this->load->view("reports/inventory_summary_input", $data);
	}

	function inventory_summary($location_id='all', $item_count='all')
	{
		$this->load->model('reports/Inventory_summary');
		$model = $this->Inventory_summary;
		$tabular_data = array();
		$report_data = $model->getData(array('location_id'=>$location_id, 'item_count'=>$item_count));
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['name'],
								$row['item_number'],
								$row['description'],
								to_quantity_decimals($row['quantity']),
								to_quantity_decimals($row['reorder_level']),
								$row['location_name'],
								to_currency($row['cost_price']),
								to_currency($row['unit_price']),
								to_currency($row['sub_total_value']));
		}

		$data = array(
			"title" => $this->lang->line('reports_inventory_summary_report'),
			"subtitle" => '',
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData($report_data)
		);

		$this->load->view("reports/tabular", $data);
	}
}
?>