<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Customer class
 */

class Customer extends Person
{
	/*
	Determines if a given person_id is a customer
	*/
	public function exists($person_id)
	{
		$builder = $this->db->table('customers');
		$builder->join('people', 'people.person_id = customers.person_id');
		$builder->where('customers.person_id', $person_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/*
	Checks if account number exists
	*/
	public function check_account_number_exists($account_number, $person_id = '')
	{
		$builder = $this->db->table('customers');
		$builder->where('account_number', $account_number);

		if(!empty($person_id))
		{
			$builder->where('person_id !=', $person_id);
		}

		return ($builder->get()->getNumRows() == 1);
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$builder = $this->db->table('customers');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/*
	Returns all the customers
	*/
	public function get_all($rows = 0, $limit_from = 0)
	{
		$builder = $this->db->table('customers');
		$builder->join('people', 'customers.person_id = people.person_id');
		$builder->where('deleted', 0);
		$builder->orderBy('last_name', 'asc');

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/*
	Gets information about a particular customer
	*/
	public function get_info($customer_id)
	{
		$builder = $this->db->table('customers');
		$builder->join('people', 'people.person_id = customers.person_id');
		$builder->where('customers.person_id', $customer_id);
		$query = $builder->get();

		if($query->getNumRows() == 1)
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object, as $customer_id is NOT a customer
			$person_obj = parent::get_info(-1);

			//Get all the fields from customer table
			//append those fields to base parent object, we we have a complete empty object
			foreach($this->db->list_fields('customers') as $field)
			{
				$person_obj->$field = '';
			}

			return $person_obj;
		}
	}

	/*
	Gets stats about a particular customer
	*/
	public function get_stats($customer_id)
	{
		// create a temporary table to contain all the sum and average of items
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_items_temp') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT
					sales.sale_id AS sale_id,
					AVG(sales_items.discount) AS avg_discount,
					SUM(sales_items.quantity_purchased) AS quantity
				FROM ' . $this->db->prefixTable('sales') . ' AS sales
				INNER JOIN ' . $this->db->prefixTable('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales.sale_id
				WHERE sales.customer_id = ' . $this->db->escape($customer_id) . '
				GROUP BY sale_id
			)'
		);

		$totals_decimals = totals_decimals();
		$quantity_decimals = quantity_decimals();

		$builder->select('
						SUM(sales_payments.payment_amount - sales_payments.cash_refund) AS total,
						MIN(sales_payments.payment_amount - sales_payments.cash_refund) AS min,
						MAX(sales_payments.payment_amount - sales_payments.cash_refund) AS max,
						AVG(sales_payments.payment_amount - sales_payments.cash_refund) AS average,
						' . "
						ROUND(AVG(sales_items_temp.avg_discount), $totals_decimals) AS avg_discount,
						ROUND(SUM(sales_items_temp.quantity), $quantity_decimals) AS quantity
						");
		$builder = $this->db->table('sales');
		$builder->join('sales_payments AS sales_payments', 'sales.sale_id = sales_payments.sale_id');
		$builder->join('sales_items_temp AS sales_items_temp', 'sales.sale_id = sales_items_temp.sale_id');
		$builder->where('sales.customer_id', $customer_id);
		$builder->where('sales.sale_status', COMPLETED);
		$this->db->group_by('sales.customer_id');

		$stat = $builder->get()->getRow();

		// drop the temporary table to contain memory consumption as it's no longer required
		$this->db->query('DROP TEMPORARY TABLE IF EXISTS ' . $this->db->prefixTable('sales_items_temp'));

		return $stat;
	}

	/*
	Gets information about multiple customers
	*/
	public function get_multiple_info($customer_ids)
	{
		$builder = $this->db->table('customers');
		$builder->join('people', 'people.person_id = customers.person_id');
		$builder->whereIn('customers.person_id', $customer_ids);
		$builder->orderBy('last_name', 'asc');

		return $builder->get();
	}

	/*
	Checks if customer email exists
	*/
	public function check_email_exists($email, $customer_id = '')
	{
		// if the email is empty return like it is not existing
		if(empty($email))
		{
			return FALSE;
		}

		$builder = $this->db->table('customers');
		$builder->join('people', 'people.person_id = customers.person_id');
		$builder->where('people.email', $email);
		$builder->where('customers.deleted', 0);

		if(!empty($customer_id))
		{
			$builder->where('customers.person_id !=', $customer_id);
		}

		return ($builder->get()->getNumRows() == 1);
	}

	/*
	Inserts or updates a customer
	*/
	public function save_customer(&$person_data, &$customer_data, $customer_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		if(parent::save($person_data, $customer_id))
		{
			if(!$customer_id || !$this->exists($customer_id))
			{
				$customer_data['person_id'] = $person_data['person_id'];
				$success = $builder->insert('customers', $customer_data);
			}
			else
			{
				$builder->where('person_id', $customer_id);
				$success = $builder->update('customers', $customer_data);
			}
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/*
	Updates reward points value
	*/
	public function update_reward_points_value($customer_id, $value)
	{
		$builder->where('person_id', $customer_id);
		$builder->update('customers', array('points' => $value));
	}

	/*
	Deletes one customer
	*/
	public function delete($customer_id)
	{
		$result = TRUE;

		// if privacy enforcement is selected scramble customer data
		if($this->config->item('enforce_privacy'))
		{
			$builder->where('person_id', $customer_id);

			$result &= $builder->update('people', array(
					'first_name'	=> $customer_id,
					'last_name'		=> $customer_id,
					'phone_number'	=> '',
					'email'			=> '',
					'gender'		=> NULL,
					'address_1'		=> '',
					'address_2'		=> '',
					'city'			=> '',
					'state'			=> '',
					'zip'			=> '',
					'country'		=> '',
					'comments'		=> ''
				));

			$builder->where('person_id', $customer_id);

			$result &= $builder->update('customers', array(
					'consent'			=> 0,
					'company_name'		=> NULL,
					'account_number'	=> NULL,
					'tax_id'			=> '',
					'taxable'			=> 0,
					'discount'			=> 0.00,
					'discount_type'		=> 0,
					'package_id'		=> NULL,
					'points'			=> NULL,
					'sales_tax_code_id'	=> NULL,
					'deleted'			=> 1
				));
		}
		else
		{
			$builder->where('person_id', $customer_id);

			$result &= $builder->update('customers', array('deleted' => 1));
		}

		return $result;
	}

	/*
	Deletes a list of customers
	*/
	public function delete_list($customer_ids)
	{
		$builder->whereIn('person_id', $customer_ids);

		return $builder->update('customers', array('deleted' => 1));
 	}

 	/*
	Get search suggestions to find customers
	*/
	public function get_search_suggestions($search, $unique = TRUE, $limit = 25)
	{
		$suggestions = array();

		$builder = $this->db->table('customers');
		$builder->join('people', 'customers.person_id = people.person_id');
		$builder->groupStart();
			$builder->like('first_name', $search);
			$builder->orLike('last_name', $search);
			$builder->orLike('CONCAT(first_name, " ", last_name)', $search);
			if($unique)
			{
				$builder->orLike('email', $search);
				$builder->orLike('phone_number', $search);
				$builder->orLike('company_name', $search);
			}
		$builder->groupEnd();
		$builder->where('deleted', 0);
		$builder->orderBy('last_name', 'asc');
		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->first_name . ' ' . $row->last_name . (!empty($row->company_name) ? ' [' . $row->company_name . ']' : ''). (!empty($row->phone_number) ? ' [' . $row->phone_number . ']' : ''));
		}

		if(!$unique)
		{
			$builder = $this->db->table('customers');
			$builder->join('people', 'customers.person_id = people.person_id');
			$builder->where('deleted', 0);
			$builder->like('email', $search);
			$builder->orderBy('email', 'asc');
			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->email);
			}

			$builder = $this->db->table('customers');
			$builder->join('people', 'customers.person_id = people.person_id');
			$builder->where('deleted', 0);
			$builder->like('phone_number', $search);
			$builder->orderBy('phone_number', 'asc');
			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->phone_number);
			}

			$builder = $this->db->table('customers');
			$builder->join('people', 'customers.person_id = people.person_id');
			$builder->where('deleted', 0);
			$builder->like('account_number', $search);
			$builder->orderBy('account_number', 'asc');
			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->account_number);
			}
			$builder = $this->db->table('customers');
			$builder->join('people', 'customers.person_id = people.person_id');
			$builder->where('deleted', 0);
			$builder->like('company_name', $search);
			$builder->orderBy('company_name', 'asc');
			foreach($builder->get()->getResult() as $row)
			{
				$suggestions[] = array('value' => $row->person_id, 'label' => $row->company_name);
			}
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

 	/*
	Gets rows
	*/
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'last_name', 'asc', TRUE);
	}

	/*
	Performs a search on customers
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'last_name', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$builder->select('COUNT(customers.person_id) as count');
		}

		$builder = $this->db->table('customers AS customers');
		$builder->join('people', 'customers.person_id = people.person_id');
		$builder->groupStart();
			$builder->like('first_name', $search);
			$builder->orLike('last_name', $search);
			$builder->orLike('email', $search);
			$builder->orLike('phone_number', $search);
			$builder->orLike('account_number', $search);
			$builder->orLike('company_name', $search);
			$builder->orLike('CONCAT(first_name, " ", last_name)', $search);
		$builder->groupEnd();
		$builder->where('deleted', 0);

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $builder->get()->getRow()->count;
		}

		$builder->orderBy($sort, $order);

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}
}
?>
