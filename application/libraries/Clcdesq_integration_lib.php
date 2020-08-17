<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CLCdesq API REST client Connector
 *
 * Interface for communicating with the CLCdesq Product Push API
 */

class Clcdesq_integration_lib
{
	private $CI;
	private $api_key;
	private $api_url;

	/**
	 * Constructor
	 */
	public function __construct($api_key = '')
	{
		$this->CI =& get_instance();

		$this->api_key	= $this->CI->encryption->decrypt($this->CI->Appconfig->get('clcdesq_api_key'));
		$this->api_url	= $this->CI->encryption->decrypt($this->CI->Appconfig->get('clcdesq_api_url'));
	}

	public function new_product_push($data)
	{
		if(!$this->is_enabled())
		{
			return NULL;
		}

		$push_data		= $this->populate_api_data($data);
		$api_responses	= $this->send_data($this->api_url, $this->api_key, $push_data);

		$push_data = NULL;

		$this->process_api_responses($api_responses);

		return NULL;
	}

	/**
	 * Send API request to update the item. Since CLCdesq does not have a partial update function, it sends the item with all the same information as before, but also including the GUID.
	 *
	 * @param	array	$data	Partial data needed to
	 * @return 	boolean			TRUE is returned if the push was successful or FALSE if there was some error.
	 */
	public function update_product_push($data)
	{
		if(!$this->is_enabled())
		{
			return NULL;
		}

		$push_data		= $this->populate_api_data($data);
		$api_responses	= $this->send_data($this->api_url, $this->api_key, $push_data);

		$push_data = NULL;

		$this->process_api_responses($api_responses);

		return NULL;
	}

	/**
	 * Send API request to delete the item. Since CLCdesq does not have a true delete function, it sends the item with Published and ShowOnWebsite set to FALSE.
	 *
	 * @param	array	$data
	 * @return 	boolean			TRUE is returned if the push was successful or FALSE if there was some error.
	 */
	public function delete_product_push($item_ids_to_delete)
	{
		if(!$this->is_enabled())
		{
			return NULL;
		}

		foreach($item_ids_to_delete as $item_id)
		{
			$item_data[] = get_object_vars($this->CI->Item->get_info($item_id));
		}

		$push_data	= $this->populate_api_data($item_data);

		foreach($push_data as &$product)
		{
			$product['Published'] 		= FALSE;
			$product['ShowOnWebsite']	= FALSE;
		}

		$api_responses = $this->send_data($this->api_url, $this->api_key, $push_data);

		$push_data = NULL;

		$this->process_api_responses($api_responses);

		return NULL;	//No errors
	}

	public function items_upload()
	{
		set_time_limit(180);
		ini_set('memory_limit','512M');

		$all_items						= $this->CI->Item->get_all()->result_array();
		$show_on_website_definition_id	= $this->CI->Appconfig->get('clcdesq_showonwebsite');

		foreach($all_items as $key => &$item)
		{
			$show_on_website = $this->CI->Attribute->get_attribute_value($item['item_id'], (int)$show_on_website_definition_id)->attribute_value;

			if($this->get_total_quantity($item['item_id']) < 1 || $show_on_website === '0')
			{
				unset($all_items[$key]);
			}
		}

		$push_data		= $this->populate_api_data($all_items);
		$api_responses	= $this->send_data($this->api_url, $this->api_key, $push_data);

		$push_data = NULL;

		$this->process_api_responses($api_responses);
	}

	private function is_enabled()
	{
		return ($this->CI->Appconfig->get('clcdesq_enable') === '1');
	}

	/**
	 * In versions greater than 7.1 json_encode is causing decimals to be displayed with long precision even if the data fed has low precision.
	 * TODO: this code needs a better home or maybe a better solution
	 */
	private function set_php_json_precision()
	{

		if (version_compare(phpversion(), '7.1', '>='))
		{
			ini_set( 'precision', 17 );
			ini_set( 'serialize_precision', -1 );
		}
	}

	/**
	 * Sends the POST JSON request via cURL
	 *
	 * @param	string	$url		The API URL to call.
	 * @param	string	$key		The API key to use in the request.
	 * @param 	string	$all_items	The array to turn into a JSON string
	 * @return	array				Returns the result array from the API
	 */
	private function send_data($url, $api_key, $all_items)
	{
		$chunks = array_chunk($all_items, 100);
		$result = [];
$items_sent = 0;
		foreach($chunks as $key => $chunk)
		{
			$this->set_php_json_precision();

			$json 			= json_encode(array('Products' => $chunk), JSON_UNESCAPED_UNICODE);
			$curl_resource	= curl_init($url);

			curl_setopt($curl_resource, CURLOPT_HTTPHEADER, array('Content-type: application/json',"APIKEY: $api_key"));
			curl_setopt($curl_resource, CURLOPT_POST, TRUE);
			curl_setopt($curl_resource, CURLOPT_POSTFIELDS, $json);
			curl_setopt($curl_resource, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl_resource, CURLOPT_SSL_VERIFYPEER, FALSE);

			$result[] = curl_exec($curl_resource);

			curl_close($curl_resource);
$items_sent += count($chunk);
log_message('Error', "Chunk $key sent. Total items sent: $items_sent");
log_message('Error', $json);
			unset($chunks[$key]);
		}

		$json	= NULL;
		$chunks = NULL;

		return $result;
	}

	/**
	 * Populates the API data needed for the push.  This is used by all three product_push member functions.
	 *
	 * @param 	array	data	Complete data array needed to build the Array of products.
	 * @return	array			Array to be used in the product push.
	 */
	private function populate_api_data($data)
	{
		$config_data		= [];
		$api_data			= [];
		$stock_locations	= $this->CI->Stock_location->get_all()->result_array();

		foreach($this->CI->Appconfig->get_all()->result() as $app_config)
		{
			$config_data[$app_config->key] = $app_config->value;
		}

		foreach($data as $key => $product)
		{
			$item_id					= $product['item_id'];
			$attribute_values			= $this->CI->Attribute->get_attribute_values($item_id);
			$number_of_discs			= $attribute_values[$config_data['clcdesq_numberofdiscs']]['attribute_decimal'];
			$number_of_pages			= $attribute_values[$config_data['clcdesq_numberofpages']]['attribute_decimal'];
			$running_time				= $attribute_values[$config_data['clcdesq_runningtime']]['attribute_decimal'];
			$stock_on_order				= $attribute_values[$config_data['clcdesq_stockonorder']]['attribute_decimal'];
			$unique_id					= $attribute_values[$config_data['clcdesq_uniqueid']]['attribute_value'];
			$dimension_unit				= $attribute_values[$config_data['clcdesq_depth']]['attribute_decimal'];
			$weight_unit				= $attribute_values[$config_data['clcdesq_weight']]['attribute_decimal'];
			$category_ao_array			= array($this->get_category_ao_array($item_id, $this->CI->Item->get_info($item_id)->category, 0));
			$quantity 					= empty($product['stock_count']) ? (int)$this->get_total_quantity($item_id, $stock_locations) : $product['stock_count'];

			if($this->data_error_check($product, $config_data, $category_ao_array))
			{
				$api_data[]					= array(
					'AspectRatio' 			=> $attribute_values[$config_data['clcdesq_aspectratio']]['attribute_value'],
					'AudienceRating' 		=> $attribute_values[$config_data['clcdesq_audiencerating']]['attribute_value'],
					'AudioFormat' 			=> $attribute_values[$config_data['clcdesq_audioformat']]['attribute_value'],
					'AudioTrackListing' 	=> $attribute_values[$config_data['clcdesq_audiotracklisting']]['attribute_value'],
					'AuthorsText' 			=> $attribute_values[$config_data['clcdesq_authorstext']]['attribute_value'],
					'Barcode' 				=> $product['item_number'],
					'Binding' 				=> $attribute_values[$config_data['clcdesq_binding']]['attribute_value'],
					'BookForeword' 			=> $attribute_values[$config_data['clcdesq_bookforeword']]['attribute_value'],
					'BookIndex' 			=> $attribute_values[$config_data['clcdesq_bookindex']]['attribute_value'],
					'BookSampleChapter' 	=> $attribute_values[$config_data['clcdesq_booksamplechapter']]['attribute_value'],
					'Contributors' 			=> $this->get_contributor_ao_array($attribute_values[$config_data['clcdesq_authorstext']]['attribute_value']),
					'Condition'				=> $this->get_condition_ao_array($attribute_values[$config_data['clcdesq_condition']]['attribute_value']),
					'DateAdded'	 			=> $this->get_date_added($item_id),
					'Depth'		 			=> (float)$dimension_unit,
					'Description' 			=> $product['description'],
					'DimensionUnit' 		=> $dimension_unit !== NULL ? $this->CI->Attribute->get_info($config_data['clcdesq_depth'])->definition_unit : NULL,
					'DiscountGroup' 		=> $this->get_product_discount_group_ao_array($item_id),
					'EAN' 					=> $this->get_ean($this->get_isbn($product['item_number'])),
					'Format' 				=> $attribute_values[$config_data['clcdesq_format']]['attribute_value'],
					'Height'		 		=> (float)$attribute_values[$config_data['clcdesq_height']]['attribute_decimal'],
					'Image'					=> $this->get_image_array($product['pic_filename']),
					'InternalCode' 			=> (string)$item_id,
					'ISBN'		 			=> $this->get_isbn($product['item_number']),
					'KindId'		 		=> $product['category'] === 'Books' ? 1 : NULL,		/* Regular Book*///TODO: this should not be hardcoded.
					'Language'	 			=> $this->get_language_ao_array($attribute_values[$config_data['clcdesq_language']]['attribute_value']),
					'MediaType'	 			=> $this->get_media_type_ao_array($product['category']),
					'NumberOfDiscs' 		=> $number_of_discs ? (int)$number_of_discs : NULL,
					'NumberOfPages' 		=> $number_of_pages ? (int)$number_of_pages : NULL,
					'OriginalTitle' 		=> $attribute_values[$config_data['clcdesq_originaltitle']]['attribute_value'],
					'Price' 				=> (float)$product['unit_price'],
					'PriceWithoutVAT'		=> (float)$this->get_price_without_VAT($product['unit_price']),
					'PriceNote'				=> $attribute_values[$config_data['clcdesq_pricenote']]['attribute_value'],
					'Producer'				=> $this->get_producer_user_ao_array($attribute_values[$config_data['clcdesq_producer']]['attribute_value']),
					'ProductStatusProducer' => $this->get_product_status_producer_ao_array($product['deleted'], $quantity),
					'PriceCurrency'			=> $config_data['currency_code'] !== '' ? $config_data['currency_code'] : NULL,
					'Published' 			=> !$product['deleted'],
					'PublisherRRP'			=> (float)$attribute_values[$config_data['clcdesq_publisherrrp']]['attribute_decimal'],
					'ReducedPrice'			=> (float)$attribute_values[$config_data['clcdesq_reducedprice']]['attribute_decimal'],
					'ReducedPriceStartDate'	=> $attribute_values[$config_data['clcdesq_reducedpricestartdate']]['attribute_date'],
					'ReducedPriceEndDate'	=> $attribute_values[$config_data['clcdesq_reducedpriceenddate']]['attribute_date'],
					'ReleaseDate' 			=> $this->get_release_date($attribute_values[$config_data['clcdesq_releasedate']]['attribute_date']),
					'RunningTime'			=> $running_time ? (int)$running_time : NULL,
					'Series'				=> $this->get_product_series_ao_array($attribute_values[$config_data['clcdesq_series']]['attribute_value'], $item_id),
					'ShowOnWebsite'			=> $this->get_show_on_website($attribute_values[$config_data['clcdesq_showonwebsite']]['attribute_value']),
					'StockCount'			=> $quantity,
					'StockOnOrder'			=> $stock_on_order ? (int)$stock_on_order : NULL,
					'Supplier'				=> $this->get_supplier_user_ao_array($product['supplier_id']),
					'Subtitle'				=> $attribute_values[$config_data['clcdesq_subtitle']]['attribute_value'],
					'Subtitles'				=> $attribute_values[$config_data['clcdesq_subtitles']]['attribute_value'],
					'TeaserDescription'		=> $attribute_values[$config_data['clcdesq_teaserdescription']]['attribute_value'],
					'Title' 				=> $product['name'],
					'UniqueId'				=> $unique_id ? $unique_id : $this->generate_and_save_uniqueid($item_id, $config_data['clcdesq_uniqueid']),
					'UPC' 					=> $attribute_values[$config_data['clcdesq_upc']]['attribute_value'],
					'VatPercent'			=> (float)$this->CI->Item_taxes->get_info($item_id)[0]['percent'],
					'VideoTrailerEmbedCode'	=> $product['videotrailerembedcode'],
					'Weight'				=> (float)$weight_unit,
					'WeightForShipping'		=> (float)$attribute_values[$config_data['clcdesq_weightforshipping']]['attribute_decimal'],
					'WeightUnit'			=> $weight_unit !== NULL ? $this->CI->Attribute->get_info($config_data['clcdesq_weight'])->definition_unit : NULL,
					'Width'					=> (float)$attribute_values[$config_data['clcdesq_width']]['attribute_decimal'],
					'Categories' 			=> $category_ao_array);
			}
			else
			{
				log_message('Error',"Item ID: $item_id skipped.  Minimum CLCdesq data requirements were not met.");
			}

			unset($data[$key]);
		}

		$data = NULL;

		return $this->array_filter_recursive($api_data);
	}

	private function data_error_check($product, $config_data, $category_ao_array)
	{
		$results = TRUE;

		if($product['category'] !== 'Books' || $config_data['currency_code'] === '' || empty($product['name']) || $product['price'] === '' || empty($category_ao_array))
		{
			$results = FALSE;
		}

		return $results;
	}

	/**
	 *
	 * @param	int $item_id
	 * @return	NULL|string[]
	 */
	private function get_contributor_ao_array($contributor)
	{
		$author = $this->parse_author($contributor);

		if(!$author)
		{
			return NULL;
		}
		else
		{
			$contributor_ao	= array(array(
				'Id'			=> null,
				'Guid'			=> null,
				'FirstName'		=> $author['first_name'],
				'LastName'		=> $author['last_name'],
				'DisplayName'	=> $author['display_name'],
				'Description'	=> null,
				'Role'			=> 'A01',	//Only authors are submitted at this time
				'Published'		=> TRUE
			));

			return $contributor_ao;
		}
	}

	/**
	 * Parses out the First Name, Last Name and Display Name of a Given input
	 *
	 * @param	string		$input	Text to parse for author details
	 * @return	array|bool			An array containing First Name, Last Name and Display Name Strings or FALSE if an empty $author_candidate is given
	 */
	private function parse_author($author_candidate)
	{
		if(empty($author_candidate))
		{
			return FALSE;
		}

		//Not Last, First or First Last format
		if(strpos($author_candidate,',') === FALSE && strpos($author_candidate,' ') === FALSE)
		{
			$author 	= array('display_name' => trim($author_candidate));
		}
		//Last, First format
		else if(strpos($author_candidate,',') !== FALSE)
		{
			$author		= array(
				'last_name'		=> trim(strtok($author_candidate,',')),
				'first_name'	=> trim(substr($author_candidate, strpos($author_candidate, ',') + 1)),
				'display_name'	=> trim($author_candidate));
		}
		//First Last format
		else
		{
			$author		= array(
				'last_name'		=> trim(substr($author_candidate, strrpos($author_candidate, ' ') + 1)),
				'first_name'	=> trim(substr($author_candidate, 0, strrpos($author_candidate, ' ')))
			);

			$author +=	['display_name'	=> trim($author['last_name'] . ', ' .$author['first_name'])];
		}

		return $author;
	}

	private function get_condition_ao_array($short_name)
	{
		if($short_name)
		{
			return array(
						'Id' 			=> 0,
						'ShortName'		=> $short_name,
						'Explanation'	=> NULL,
						'Published'		=> TRUE
					);
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Retrieve the date that the item was first added.
	 *
	 * @param	int		$item_id	The ID of the item to retrieve date added.
	 * @return	string				The Date this item was first added.
	 */
	private function get_date_added($item_id)
	{
		$date_added = $this->CI->Inventory->get_inventory_data_for_item($item_id)->result_array();

		return date('Y-m-d\TH:i:s',strtotime($date_added[0]['trans_date']));
	}

	/**
	 * Prepares the ProductDiscountGroup API Object for inclusion in the API data
	 *
	 * @param	int	$item_id	Item ID for which to add discounts for.
	 */

//TODO: Currently the item_id is not used since this information is not kept in OSPOS
	private function get_product_discount_group_ao_array($item_id)
	{
		$product_discount_group_ao	= array('DiscountGroup' => array(
			'UID'			=> NULL,
			'Name'			=> NULL,
			'Description'	=> NULL
		));

		return $product_discount_group_ao;
	}

	/**
	 * Generate EAN code from ISBN
	 *
	 * @param	string|NULL	$isbn		The ISBN-13 or ISBN-10 of the item
	 * @return 	string|NULL				The EAN code of the item or NULL if there is no ISBN
	 */
	private function get_ean($isbn)
	{
		if($isbn !== NULL)
		{
			return preg_replace('/[^0-9]/', '', $isbn);
		}

		return NULL;
	}

	/**
	 * Generate ISBN from Barcode if the Barcode is properly formatted
	 *
	 * @param	NULL|string		$barcode	The barcode of the item.
	 * @return 	NULL|string					Returns the ISBN-10, ISBN-13 or NULL if no ISBN is in the barcode.
	 */
	private function get_isbn($barcode)
	{
		if(!empty($barcode))
		{
			$isbn_candidate = preg_replace('/[^0-9xX]/', '', $barcode);

			if(strlen($isbn_candidate) !== 10 && strlen($isbn_candidate) !== 13)
			{
				return NULL;
			}
			else
			{
				return $isbn_candidate;
			}
		}
		return NULL;
	}

	private function get_image_array($image_file)
	{
		if($image_file !== NULL)
		{
			return array('UrlOriginal' => base_url() . "/uploads/item_pics/$image_file");
		}

		return NULL;
	}

	/**
	 * Prepares a LanguageAO array to be sent in the API.
	 *
	 * @param	int		$item_id	The unique identifier for which to get
	 * @return	array				An associative array containing the LanguageAO information.
	 */
	private function get_language_ao_array($language_shortname)
	{
		$language_ao = array(
			'ShortName'			=> $language_shortname,
			'OnixLanguageCode'	=> NULL
		);

		return $language_ao;
	}

	/**
	 * Prepares a MediaTypeAO array to be sent in the API.
	 *
	 * @param	NULL|string	$category	The category translates specifically to the MediaTypeAO Title.
	 * @return	array					An associative array containing the MediaTypeAO information
	 */
	private function get_media_type_ao_array($category)
	{
		if(!empty($category))
		{
			$mediatype_ao	= array(
				'Id'				=> NULL,
				'Title'				=> $category,
				'Description'		=> NULL,
				'DefaultWeight'		=> NULL,
				'Published'			=> TRUE,
				'ShortName'			=> NULL,
				'DefaultVatPercent'	=> NULL
			);
		}

		return $mediatype_ao;
	}

	/**
	 * Given the price of the item determines the price without VAT included.
	 *
	 * @param	NULL|float	$price	Price of the item.
	 * @return 	float|NULL			Returns the price of the item without VAT included. If VAT is not included in the price, then it returns the given price.
	 */
	private function get_price_without_vat($price)
	{
		if($price === NULL)
		{
			return NULL;
		}

		$tax_rate		= (float)$this->CI->Appconfig->get('default_tax_1_rate', NULL);
		$tax_included	= (bool)$this->CI->Appconfig->get('tax_included');

		if($tax_rate !== NULL && $tax_included)
		{
			$tax_percent = $tax_rate/100;

			return round($price - ceil(($price * $tax_percent)*100)/100, 2);
		}
		else if($tax_rate !== NULL)
		{
			return $price;
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Prepares a ProducerUserAO array to be sent in the API.
	 *
	 * @param	int		$item_id	The unique identifier of the item to generate the ProducerUserAO for.
	 * @return	array				An associative array containing the ProducerUserAO information
	 */
	private function get_producer_user_ao_array($company_name)
	{
		if(empty($company_name))
		{
			return NULL;
		}
		else
		{
			$producer_user_ao	= array(
				'UniqueId'				=> NULL,
				'FirstName'				=> NULL,
				'LastName'				=> NULL,
				'Email'					=> NULL,
				'DateAdded'				=> NULL,
				'AllowPaymentOnAccount'	=> NULL,
				'ActivePublic'			=> FALSE,
				'ActiveAdmin'			=> FALSE,
				'IsSupplier'			=> FALSE,
				'IsProducer'			=> TRUE,
				'PasswordHash'			=> NULL,
				'PasswordSalt'			=> NULL,
				'Username'				=> NULL,
				'CompanyName'			=> $company_name,
				'CompanyRegistration'	=> NULL,
				'CompanyVatCode'		=> NULL,
				'DiscountGroup'			=> NULL
			);

			return $producer_user_ao;
		}
	}

	/**
	 * Prepares a ProductStatusProducerAO array to be sent in the API.
	 *
	 * @param	int		$item_id	The unique identifier of the item to generate the ProductStatusProducerAO for.
	 * @return	array				An associative array containing the ProductStatusProducerAO information.
	 */
	private function get_product_status_producer_ao_array($deleted, $quantity)
	{
		$product_status_producer_ao = NULL;


		if(!$deleted)
		{
			$product_status_producer_ao	= array(
				'Id'							=> NULL,
				'Name'							=> "Available or Order",
				'DisplayStatus'					=> TRUE,
				'DisplayProduct'				=> ($quantity > 0),
				'AllowOrdering'					=> TRUE,
				'AllowOrderingMinStockAmount'	=> 0,
				'EnforceActualStock'			=> TRUE,
				'DisplayStock'					=> TRUE,
				'StatusExplanation'				=> NULL,
				'StatusColorHex'				=> NULL
			);
		}

		return $product_status_producer_ao;
	}

	/**
	 * Returns the release date of the item in YYYY-MM-DDTHH:MM:SS format or NULL if no release date is specified.
	 *
	 * @param	int 		$item_id		Unique item identifier of the item in question.
	 * @param	object		$config_data	Configuration data storing the attribute bindings for the API
	 * @return	date|NULL					The datetime in YYYY-MM-DDTHH:MM:SS format or NULL if no release date is specified.
	 */
	private function get_release_date($release_date)
	{
		if(!empty($release_date))
		{
			return date('Y-m-d\TH:i:s',strtotime($release_date));
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Prepares a ProductSeriesAO array to be sent in the API
	 *
	 * @param	int		$item_id	The unique identifier of the item to generate the ProductSeriesAO for.
	 * @return	array				An associative array containing the ProductSeriesAO information.
	 */
	private function get_product_series_ao_array($title, $item_id)
	{
		if(empty($title))
		{
			return NULL;
		}
		else
		{
			$product_series_ao	= array(
				'Id'			=> NULL,
				'UID'			=> $this->generate_and_save_uniqueid(),
				'Title'			=> $title,
				'Description'	=> NULL,
				'DateAdded'		=> $this->get_date_added($item_id),
				'Published'		=> TRUE
			);

			return $product_series_ao;
		}
	}

	/**
	 * Generates and Saves Microsoft GUID v4.
	 *
	 * @param	int		$item_id
	 * @param	array	$config_data
	 * @return	string	Microsoft GUID v4
	 */
	private function generate_and_save_uniqueid($item_id = NULL, $unique_id_definition_id = NULL)
	{
		if(!empty($item_id))
		{
			$data = openssl_random_pseudo_bytes(16);
			$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
			$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
			$unique_id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

			if(!$this->CI->Attribute->save_value($unique_id, $unique_id_definition_id, $item_id, FALSE, 'TEXT'))
			{
				return NULL;
			}

			return $unique_id;
		}
		else
		{
			return '00000000-0000-0000-0000-000000000000';
		}
	}

	/**
	 * Returns the boolean value of the show on website attribute or TRUE if the attribute does not exist
	 *
	 * @param	int		$item_id
	 * @param 	array	$config_data	Array containing the values of the data in con
	 * @return	boolean					Value of Show on website attribute or TRUE
	 */
	private function get_show_on_website($show_on_website)
	{
		if($show_on_website === NULL)
		{
			return TRUE;
		}

		return $show_on_website ? TRUE : FALSE;
	}

//TODO: We may want to move this to the Item_quantity model
	/**
	 * Returns the total quantity available from all suppliers.
	 *
	 * @param	int	$item_id	The unique identifier of the item to get the total quantity for.
	 * @return	int				The total quantity between all stock locations.
	 */
	private function get_total_quantity($item_id, $stock_locations)
	{
		$total_quantity = 0;

		foreach($stock_locations as $location_data)
		{
			$total_quantity += $this->CI->Item_quantity->get_item_quantity($item_id, $location_data['location_id'])->quantity;
		}

		return $total_quantity;
	}

	/**
	 * Prepares a SupplierUserAO array to be sent in the API
	 *
	 * @param	int|NULL		$item_id	The unique identifier of the item to generate the SupplierUserAO for.
	 * @return	array				An associative array containing the SupplierUserAO information.
	 */
	private function get_supplier_user_ao_array($supplier_id)
	{
		if(!empty($supplier_id))
		{
			$supplier_info = $this->CI->Supplier->get_info($supplier_id);

			$supplier_user_ao	= array(
				'UniqueId'				=> NULL,
				'FirstName'				=> $supplier_info->first_name,
				'LastName'				=> $supplier_info->last_name,
				'Email'					=> $supplier_info->email,
				'DateAdded'				=> NULL,
				'AllowPaymentOnAccount'	=> NULL,
				'ActivePublic'			=> FALSE,
				'ActiveAdmin'			=> FALSE,
				'IsSupplier'			=> TRUE,
				'IsProducer'			=> FALSE,
				'PasswordHash'			=> NULL,
				'PasswordSalt'			=> NULL,
				'Username'				=> NULL,
				'CompanyName'			=> $supplier_info->company_name,
				'CompanyRegistration'	=> NULL,
				'CompanyVatCode'		=> $supplier_info->tax_id,
				'DiscountGroup'			=> NULL);

			return $supplier_user_ao;
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Prepares a CategoryAO array to be sent in the API
	 *
	 * @param	int			$item_id	The unique identifier of the item to generate the ProductSeriesAO for.
	 * @return	array|NULL				An associative array containing the ProductSeriesAO information.
	 */
	private function get_category_ao_array($item_id, $title = NULL, $level)
	{
		if(empty($title))
		{
			return NULL;
		}
		else
		{
			//$data['category']->$attribute['location']->$attribute->['category']
			switch($level)
			{
				case 0: //Location Attribute(Gift and Travel, Reference, Azerbaijani, etc.)
					$next_title = $this->CI->Attribute->get_attribute_value($item_id, (int)$this->CI->Appconfig->get('clcdesq_location'))->attribute_value;
					break;

				case 1: //Category (Dictionaries, Local Interests, etc)
					$next_title = $this->CI->Attribute->get_attribute_value($item_id, (int)$this->CI->Appconfig->get('clcdesq_category'))->attribute_value;
					break;

				default:
					$next_title = NULL;
					break;
			}

			$category_ao	= array(
				'Id'		=> NULL,
				'Title'		=> $title,
				'Children'	=> array($this->get_category_ao_array($item_id, $next_title, $level+1)));
		}

		return $category_ao;
	}

	/**
	 * Recursively filters out unacceptable values (NULL and '') from Array
	 *
	 * @param	array|string	$input	The array or array value to analize
	 * @return	array|string			The resulting array element or array
	 */
	private function array_filter_recursive($input)
	{
		foreach($input as $key => &$value)
		{
			if(is_array($value))
			{
				$value = $this->array_filter_recursive($value);
			}

			if(in_array($value, array(NULL, '')) && $value !== 0)
			{
				unset($input[$key]);
			}
		}

		return $input;
	}

	private function process_api_responses($api_responses)
	{
		foreach($api_responses as $response)
		{
			$response = json_decode($response, TRUE);

			foreach($response['Results'] as $result)
			{
				if($result['Status'] !== 'SUCCESS')
				{
					log_message('ERROR', 'Product Push API Error with Status "' . $result['Status'] . '": "' . $result['Error'] . "\"\nProductUID: " . $result['ProductUID'] . "\nProductId: " . $result['ProductId']);
				}
			}
		}
	}

}