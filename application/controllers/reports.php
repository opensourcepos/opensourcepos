<?php
require_once ("secure_area.php");
require_once (APPPATH."libraries/ofc-library/open-flash-chart.php");
class Reports extends Secure_area 
{	
	function __construct()
	{
		parent::__construct('reports');
		$this->load->helper('report');		
	}
	
	//Initial report listing screen
	function index()
	{
		$this->load->view("reports/listing",array());	
	}
	
	function _get_common_report_data()
	{
		$data = array();
		$data['report_date_range_simple'] = get_simple_date_ranges();
		$data['months'] = get_months();
		$data['days'] = get_days();
		$data['years'] = get_years();
		$data['selected_month']=date('n');
		$data['selected_day']=date('d');
		$data['selected_year']=date('Y');	
	
		return $data;
	}
	
	//Input for reports that require only a date range and an export to excel. (see routes.php to see that all summary reports route here)
	function date_input_excel_export()
	{
		$data = $this->_get_common_report_data();
		$this->load->view("reports/date_input_excel_export",$data);	
	}
	
	//Summary sales report
	function summary_sales($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['sale_date'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}
	
	//Summary categories report
	function summary_categories($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['category'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}
	
	//Summary customers report
	function summary_customers($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['customer'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_customers_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}
	
	//Summary suppliers report
	function summary_suppliers($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['supplier'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}
	
	//Summary items report
	function summary_items($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array(character_limiter($row['name'], 16), $row['quantity_purchased'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_items_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);
		
		$this->load->view("reports/tabular",$data);
	}
	
	//Summary employees report
	function summary_employees($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['employee'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']));
		}

		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}
	
	//Summary taxes report
	function summary_taxes($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['percent'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']));
		}

		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}
	
	//Summary discounts report
	function summary_discounts($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['discount_percent'],$row['count']);
		}

		$data = array(
			"title" => $this->lang->line('reports_discounts_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}
	
	function summary_payments($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;
		$tabular_data = array();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['payment_type'],to_currency($row['payment_amount']));
		}

		$data = array(
			"title" => $this->lang->line('reports_payments_summary_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);
	}
	
	//Input for reports that require only a date range. (see routes.php to see that all graphical summary reports route here)
	function date_input()
	{
		$data = $this->_get_common_report_data();
		$this->load->view("reports/date_input",$data);	
	}
	
	//Graphical summary sales report
	function graphical_summary_sales($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"data_file" => site_url("reports/graphical_summary_sales_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_sales_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_sales');
		$model = $this->Summary_sales;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[date('m/d/Y', strtotime($row['sale_date']))]= $row['total'];
		}

		$data = array(
			"title" => $this->lang->line('reports_sales_summary_report'),
			"yaxis_label"=>$this->lang->line('reports_revenue'),
			"xaxis_label"=>$this->lang->line('reports_date'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/line",$data);

	}
	
	//Graphical summary items report
	function graphical_summary_items($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;

		$data = array(
			"title" => $this->lang->line('reports_items_summary_report'),
			"data_file" => site_url("reports/graphical_summary_items_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_items_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_items');
		$model = $this->Summary_items;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
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

		$this->load->view("reports/graphs/hbar",$data);
	}
	
	//Graphical summary customers report
	function graphical_summary_categories($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;

		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"data_file" => site_url("reports/graphical_summary_categories_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_categories_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_categories');
		$model = $this->Summary_categories;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['category']] = $row['total'];
		}
		
		$data = array(
			"title" => $this->lang->line('reports_categories_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}
	
	function graphical_summary_suppliers($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;

		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"data_file" => site_url("reports/graphical_summary_suppliers_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_suppliers_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_suppliers');
		$model = $this->Summary_suppliers;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['supplier']] = $row['total'];
		}
		
		$data = array(
			"title" => $this->lang->line('reports_suppliers_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}
	
	function graphical_summary_employees($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;

		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"data_file" => site_url("reports/graphical_summary_employees_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_employees_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_employees');
		$model = $this->Summary_employees;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['employee']] = $row['total'];
		}
		
		$data = array(
			"title" => $this->lang->line('reports_employees_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}
	
	function graphical_summary_taxes($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;

		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"data_file" => site_url("reports/graphical_summary_taxes_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_taxes_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_taxes');
		$model = $this->Summary_taxes;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		$graph_data = array();
		foreach($report_data as $row)
		{
			$graph_data[$row['percent']] = $row['total'];
		}
		
		$data = array(
			"title" => $this->lang->line('reports_taxes_summary_report'),
			"data" => $graph_data
		);

		$this->load->view("reports/graphs/pie",$data);
	}
	
	//Graphical summary customers report
	function graphical_summary_customers($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;

		$data = array(
			"title" => $this->lang->line('reports_customers_summary_report'),
			"data_file" => site_url("reports/graphical_summary_customers_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_customers_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_customers');
		$model = $this->Summary_customers;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
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

		$this->load->view("reports/graphs/hbar",$data);
	}
	
	//Graphical summary discounts report
	function graphical_summary_discounts($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;

		$data = array(
			"title" => $this->lang->line('reports_discounts_summary_report'),
			"data_file" => site_url("reports/graphical_summary_discounts_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_discounts_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_discounts');
		$model = $this->Summary_discounts;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
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

		$this->load->view("reports/graphs/bar",$data);
	}
	
	function graphical_summary_payments($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;

		$data = array(
			"title" => $this->lang->line('reports_payments_summary_report'),
			"data_file" => site_url("reports/graphical_summary_payments_graph/$start_date/$end_date/$sale_type"),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type))
		);

		$this->load->view("reports/graphical",$data);
	}
	
	//The actual graph data
	function graphical_summary_payments_graph($start_date, $end_date, $sale_type)
	{
		$this->load->model('reports/Summary_payments');
		$model = $this->Summary_payments;
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
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

		$this->load->view("reports/graphs/pie",$data);
	}
	function specific_customer_input()
	{
		$data = $this->_get_common_report_data();
		$data['specific_input_name'] = $this->lang->line('reports_customer');
		
		$customers = array();
		foreach($this->Customer->get_all()->result() as $customer)
		{
			$customers[$customer->person_id] = $customer->first_name .' '.$customer->last_name;
		}
		$data['specific_input_data'] = $customers;
		$this->load->view("reports/specific_input",$data);	
	}

	function specific_customer($start_date, $end_date, $customer_id, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Specific_customer');
		$model = $this->Specific_customer;
		
		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'customer_id' =>$customer_id, 'sale_type' => $sale_type));
		
		$summary_data = array();
		$details_data = array();
		
		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/edit/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target' => '_blank')), $row['sale_date'], $row['items_purchased'], $row['employee_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']), $row['payment_type'], $row['comment']);
			
			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$key][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $drow['quantity_purchased'], to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']),to_currency($drow['profit']), $drow['discount_percent'].'%');
			}
		}

		$customer_info = $this->Customer->get_info($customer_id);
		$data = array(
			"title" => $customer_info->first_name .' '. $customer_info->last_name.' '.$this->lang->line('reports_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'customer_id' =>$customer_id, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);
	}
	
	function specific_employee_input()
	{
		$data = $this->_get_common_report_data();
		$data['specific_input_name'] = $this->lang->line('reports_employee');
		
		$employees = array();
		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$data['specific_input_data'] = $employees;
		$this->load->view("reports/specific_input",$data);	
	}

	function specific_employee($start_date, $end_date, $employee_id, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Specific_employee');
		$model = $this->Specific_employee;
		
		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'employee_id' =>$employee_id, 'sale_type' => $sale_type));
		
		$summary_data = array();
		$details_data = array();
		
		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/edit/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target' => '_blank')), $row['sale_date'], $row['items_purchased'], $row['customer_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']), $row['payment_type'], $row['comment']);
			
			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$key][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $drow['quantity_purchased'], to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']),to_currency($drow['profit']), $drow['discount_percent'].'%');
			}
		}

		$employee_info = $this->Employee->get_info($employee_id);
		$data = array(
			"title" => $employee_info->first_name .' '. $employee_info->last_name.' '.$this->lang->line('reports_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date,'employee_id' =>$employee_id, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);
	}
	
	function detailed_sales($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Detailed_sales');
		$model = $this->Detailed_sales;
		
		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		$summary_data = array();
		$details_data = array();
		
		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('sales/edit/'.$row['sale_id'], 'POS '.$row['sale_id'], array('target' => '_blank')), $row['sale_date'], $row['items_purchased'], $row['employee_name'], $row['customer_name'], to_currency($row['subtotal']), to_currency($row['total']), to_currency($row['tax']),to_currency($row['profit']), $row['payment_type'], $row['comment']);
			
			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$key][] = array($drow['name'], $drow['category'], $drow['serialnumber'], $drow['description'], $drow['quantity_purchased'], to_currency($drow['subtotal']), to_currency($drow['total']), to_currency($drow['tax']),to_currency($drow['profit']), $drow['discount_percent'].'%');
			}
		}

		$data = array(
			"title" =>$this->lang->line('reports_detailed_sales_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);
	}
	
	function detailed_receivings($start_date, $end_date, $sale_type, $export_excel=0)
	{
		$this->load->model('reports/Detailed_receivings');
		$model = $this->Detailed_receivings;
		
		$headers = $model->getDataColumns();
		$report_data = $model->getData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type));
		
		$summary_data = array();
		$details_data = array();
		
		foreach($report_data['summary'] as $key=>$row)
		{
			$summary_data[] = array(anchor('receivings/receipt/'.$row['receiving_id'], 'RECV '.$row['receiving_id'], array('target' => '_blank')), $row['receiving_date'], $row['items_purchased'], $row['employee_name'], $row['supplier_name'], to_currency($row['total']), $row['payment_type'], $row['comment']);
			
			foreach($report_data['details'][$key] as $drow)
			{
				$details_data[$key][] = array($drow['name'], $drow['category'], $drow['quantity_purchased'], to_currency($drow['total']), $drow['discount_percent'].'%');
			}
		}

		$data = array(
			"title" =>$this->lang->line('reports_detailed_receivings_report'),
			"subtitle" => date('m/d/Y', strtotime($start_date)) .'-'.date('m/d/Y', strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $model->getSummaryData(array('start_date'=>$start_date, 'end_date'=>$end_date, 'sale_type' => $sale_type)),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular_details",$data);
	}
			
	function excel_export()
	{
		$this->load->view("reports/excel_export",array());		
	}
	
	function inventory_low($export_excel=0)
	{
		$this->load->model('reports/Inventory_low');
		$model = $this->Inventory_low;
		$tabular_data = array();
		$report_data = $model->getData(array());
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['name'], $row['item_number'], $row['description'], $row['quantity'], $row['reorder_level']);
		}

		$data = array(
			"title" => $this->lang->line('reports_low_inventory_report'),
			"subtitle" => '',
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array()),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);	
	}
	
	function inventory_summary($export_excel=0)
	{
		$this->load->model('reports/Inventory_summary');
		$model = $this->Inventory_summary;
		$tabular_data = array();
		$report_data = $model->getData(array());
		foreach($report_data as $row)
		{
			$tabular_data[] = array($row['name'], $row['item_number'], $row['description'], $row['quantity'], $row['reorder_level']);
		}

		$data = array(
			"title" => $this->lang->line('reports_inventory_summary_report'),
			"subtitle" => '',
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(array()),
			"export_excel" => $export_excel
		);

		$this->load->view("reports/tabular",$data);	
	}
	
}
?>