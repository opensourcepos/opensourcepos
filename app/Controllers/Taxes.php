<?php

namespace App\Controllers;

use App\Libraries\Tax_lib;
use App\Models\enums\Rounding_mode;
use App\Models\Tax;
use App\Models\Tax_category;
use App\Models\Tax_code;
use App\Models\Tax_jurisdiction;
use Config\OSPOS;
use Config\Services;

class Taxes extends Secure_Controller
{
    private array $config;
    private Tax_lib $tax_lib;
    private Tax $tax;
    private Tax_category $tax_category;
    private Tax_code $tax_code;
    private Tax_jurisdiction $tax_jurisdiction;

    public function __construct()
    {
        parent::__construct('taxes');

        $this->tax = model(Tax::class);
        $this->tax_category = model(Tax_category::class);
        $this->tax_code = model(Tax_code::class);
        $this->tax_jurisdiction = model(Tax_jurisdiction::class);

        $this->tax_lib = new Tax_lib();
        $this->config = config(OSPOS::class)->settings;

        helper('tax_helper');
    }

    /**
     * @return void
     */
    public function getIndex(): void
    {
        $data['tax_codes'] = $this->tax_code->get_all()->getResultArray();
        if (count($data['tax_codes']) == 0) {
            $data['tax_codes'] = $this->tax_code->get_empty_row();
        }

        $data['tax_categories'] = $this->tax_category->get_all()->getResultArray();
        if (count($data['tax_categories']) == 0) {
            $data['tax_categories'] = $this->tax_category->get_empty_row();
        }

        $data['tax_jurisdictions'] = $this->tax_jurisdiction->get_all()->getResultArray();
        if (count($data['tax_jurisdictions']) == 0) {
            $data['tax_jurisdictions'] = $this->tax_jurisdiction->get_empty_row();
        }

        $data['tax_rate_table_headers'] = get_tax_rates_manage_table_headers();
        $data['tax_categories_table_headers'] = get_tax_categories_table_headers();
        $data['tax_types'] = $this->tax_lib->get_tax_types();

        if ($this->config['tax_included']) {
            $data['default_tax_type'] = Tax_lib::TAX_TYPE_INCLUDED;
        } else {
            $data['default_tax_type'] = Tax_lib::TAX_TYPE_EXCLUDED;
        }

        $data['tax_type_options'] = $this->tax_lib->get_tax_type_options($data['default_tax_type']);

        echo view('taxes/manage', $data);
    }

    /**
     * Returns tax_codes table data rows. This will be called with AJAX.
     *
     * @return void
     */
    public function getSearch(): void
    {
        $search = $this->request->getGet('search');
        $limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $tax_rates = $this->tax->search($search, $limit, $offset, $sort, $order);

        $total_rows = $this->tax->get_found_rows($search);

        $data_rows = [];
        foreach ($tax_rates->getResult() as $tax_rate_row) {
            $data_rows[] = get_tax_rates_data_row($tax_rate_row);
        }

        echo json_encode(['total' => $total_rows, 'rows' => $data_rows]);
    }

    /**
     * Gives search suggestions based on what is being searched for
     */
    public function suggest_search(): void
    {
        $search = $this->request->getPost('term');
        $suggestions = $this->tax->get_search_suggestions($search);    // TODO: There is no get_search_suggestions function in the tax model

        echo json_encode($suggestions);
    }

    /**
     * Provides list of tax categories to select from
     *
     * @return void
     */
    public function suggest_tax_categories(): void
    {
        $search = $this->request->getPost('term');
        $suggestions = $this->tax_category->get_tax_category_suggestions($search);

        echo json_encode($suggestions);
    }


    /**
     * @param int $row_id
     * @return void
     */
    public function getRow(int $row_id): void
    {
        $data_row = get_tax_rates_data_row($this->tax->get_info($row_id));

        echo json_encode($data_row);
    }

    /**
     * @param int $tax_code
     * @return void
     */
    public function getView_tax_codes(int $tax_code = NEW_ENTRY): void
    {
        $tax_code_info = $this->tax->get_info($tax_code);

        $default_tax_category_id = 1; // Tax category id is always the default tax category    // TODO: Replace 1 with constant
        $default_tax_category = $this->tax->get_tax_category($default_tax_category_id);    // TODO: this variable is never used in the code.

        $tax_rate_info = $this->tax->get_rate_info($tax_code, $default_tax_category_id);

        if ($this->config['tax_included']) {
            $data['default_tax_type'] = Tax_lib::TAX_TYPE_INCLUDED;
        } else {
            $data['default_tax_type'] = Tax_lib::TAX_TYPE_EXCLUDED;
        }

        $data['rounding_options'] = rounding_mode::get_rounding_options();
        $data['html_rounding_options'] = $this->get_html_rounding_options();

        if ($tax_code == NEW_ENTRY) {   // TODO: Duplicated code
            $data['tax_code'] = '';
            $data['tax_code_name'] = '';
            $data['tax_code_type'] = '0';
            $data['city'] = '';
            $data['state'] = '';
            $data['tax_rate'] = '0.0000';
            $data['rate_tax_code'] = '';
            $data['rate_tax_category_id'] = 1;
            $data['tax_category'] = '';
            $data['add_tax_category'] = '';
            $data['rounding_code'] = '0';
        } else {
            $data['tax_code'] = $tax_code;
            $data['tax_code_name'] = $tax_code_info->tax_code_name;
            $data['tax_code_type'] = $tax_code_info->tax_code_type;
            $data['city'] = $tax_code_info->city;
            $data['state'] = $tax_code_info->state;
            $data['rate_tax_code'] = $tax_code_info->rate_tax_code;
            $data['rate_tax_category_id'] = $tax_code_info->rate_tax_category_id;
            $data['tax_category'] = $tax_code_info->tax_category;
            $data['add_tax_category'] = '';
            $data['tax_rate'] = $tax_rate_info->tax_rate;
            $data['rounding_code'] = $tax_rate_info->rounding_code;
        }

        $tax_rates = [];
        foreach ($this->tax->get_tax_code_rate_exceptions($tax_code) as $tax_code_rate) {    // TODO: get_tax_code_rate_exceptions doesn't exist.  This was deleted by @steveireland in https://github.com/opensourcepos/opensourcepos/commit/32204698379c230f2a6756655f40334308023de9#diff-e746bab6720cf5dbf855de6cda68f7aca9ecea7ddd5a39bb852e9b9047a7a838L435 but it's unclear if that was on purpose or accidental.
            $tax_rate_row = [];
            $tax_rate_row['rate_tax_category_id'] = $tax_code_rate['rate_tax_category_id'];
            $tax_rate_row['tax_category'] = $tax_code_rate['tax_category'];
            $tax_rate_row['tax_rate'] = $tax_code_rate['tax_rate'];
            $tax_rate_row['rounding_code'] = $tax_code_rate['rounding_code'];

            $tax_rates[] = $tax_rate_row;
        }

        $data['tax_rates'] = $tax_rates;

        echo view('taxes/tax_code_form', $data);
    }


    /**
     * @param int $tax_rate_id
     * @return void
     */
    public function getView(int $tax_rate_id = NEW_ENTRY): void
    {
        $tax_rate_info = $this->tax->get_info($tax_rate_id);

        $data['tax_rate_id'] = $tax_rate_id;
        $data['rounding_options'] = rounding_mode::get_rounding_options();

        $data['tax_code_options'] = $this->tax_lib->get_tax_code_options();
        $data['tax_category_options'] = $this->tax_lib->get_tax_category_options();
        $data['tax_jurisdiction_options'] = $this->tax_lib->get_tax_jurisdiction_options();

        if ($tax_rate_id == NEW_ENTRY) {
            $data['rate_tax_code_id'] = $this->config['default_tax_code'];
            $data['rate_tax_category_id'] = $this->config['default_tax_category'];
            $data['rate_jurisdiction_id'] = $this->config['default_tax_jurisdiction'];
            $data['tax_rounding_code'] = rounding_mode::HALF_UP;
            $data['tax_rate'] = '0.0000';
        } else {
            $data['rate_tax_code_id'] = $tax_rate_info->rate_tax_code_id;
            $data['rate_tax_code'] = $tax_rate_info->tax_code;
            $data['rate_tax_category_id'] = $tax_rate_info->rate_tax_category_id;
            $data['rate_jurisdiction_id'] = $tax_rate_info->rate_jurisdiction_id;
            $data['tax_rounding_code'] = $tax_rate_info->tax_rounding_code;
            $data['tax_rate'] = $tax_rate_info->tax_rate;
        }

        echo view('taxes/tax_rates_form', $data);
    }

    /**
     * @param int $tax_code
     * @return void
     */
    public function getView_tax_categories(int $tax_code = NEW_ENTRY): void    // TODO: This appears to be called no where in the code.
    {
        $tax_code_info = $this->tax->get_info($tax_code);    // TODO: Duplicated Code

        $default_tax_category_id = 1; // Tax category id is always the default tax category    // TODO: replace with a constant.
        $default_tax_category = $this->tax->get_tax_category($default_tax_category_id);

        $tax_rate_info = $this->tax->get_rate_info($tax_code, $default_tax_category_id);

        $data['rounding_options'] = rounding_mode::get_rounding_options();
        $data['html_rounding_options'] = $this->get_html_rounding_options();

        if ($this->config['tax_included']) {
            $data['default_tax_type'] = Tax_lib::TAX_TYPE_INCLUDED;
        } else {
            $data['default_tax_type'] = Tax_lib::TAX_TYPE_EXCLUDED;
        }

        if ($tax_code == NEW_ENTRY) {
            $data['tax_code'] = '';
            $data['tax_code_name'] = '';
            $data['tax_code_type'] = '0';
            $data['city'] = '';
            $data['state'] = '';
            $data['tax_rate'] = '0.0000';
            $data['rate_tax_code'] = '';
            $data['rate_tax_category_id'] = 1;
            $data['tax_category'] = '';
            $data['add_tax_category'] = '';
            $data['rounding_code'] = '0';
        } else {
            $data['tax_code'] = $tax_code;
            $data['tax_code_name'] = $tax_code_info->tax_code_name;
            $data['tax_code_type'] = $tax_code_info->tax_code_type;
            $data['city'] = $tax_code_info->city;
            $data['state'] = $tax_code_info->state;
            $data['rate_tax_code'] = $tax_code_info->rate_tax_code;
            $data['rate_tax_category_id'] = $tax_code_info->rate_tax_category_id;
            $data['tax_category'] = $tax_code_info->tax_category;
            $data['add_tax_category'] = '';
            $data['tax_rate'] = $tax_rate_info->tax_rate;
            $data['rounding_code'] = $tax_rate_info->rounding_code;
        }

        $tax_rates = [];
        foreach ($this->tax->get_tax_code_rate_exceptions($tax_code) as $tax_code_rate) {    // TODO: get_tax_code_rate_exceptions doesn't exist in the tax model
            $tax_rate_row = [];
            $tax_rate_row['rate_tax_category_id'] = $tax_code_rate['rate_tax_category_id'];
            $tax_rate_row['tax_category'] = $tax_code_rate['tax_category'];
            $tax_rate_row['tax_rate'] = $tax_code_rate['tax_rate'];
            $tax_rate_row['rounding_code'] = $tax_code_rate['rounding_code'];

            $tax_rates[] = $tax_rate_row;
        }

        $data['tax_rates'] = $tax_rates;

        echo view('taxes/tax_category_form', $data);
    }

    /**
     * @param int $tax_code
     * @return void
     */
    public function getView_tax_jurisdictions(int $tax_code = NEW_ENTRY): void // TODO: This appears to be called no where in the code.
    {
        $tax_code_info = $this->tax->get_info($tax_code);    // TODO: Duplicated code

        $default_tax_category_id = 1; // Tax category id is always the default tax category
        $default_tax_category = $this->tax->get_tax_category($default_tax_category_id);    // TODO: This variable is not used anywhere in the code

        $tax_rate_info = $this->tax->get_rate_info($tax_code, $default_tax_category_id);

        $data['rounding_options'] = rounding_mode::get_rounding_options();
        $data['html_rounding_options'] = $this->get_html_rounding_options();

        if ($this->config['tax_included']) {
            $data['default_tax_type'] = Tax_lib::TAX_TYPE_INCLUDED;
        } else {
            $data['default_tax_type'] = Tax_lib::TAX_TYPE_EXCLUDED;
        }

        if ($tax_code == NEW_ENTRY) {
            $data['tax_code'] = '';
            $data['tax_code_name'] = '';
            $data['tax_code_type'] = '0';
            $data['city'] = '';
            $data['state'] = '';
            $data['tax_rate'] = '0.0000';
            $data['rate_tax_code'] = '';
            $data['rate_tax_category_id'] = 1;
            $data['tax_category'] = '';
            $data['add_tax_category'] = '';
            $data['rounding_code'] = '0';
        } else {
            $data['tax_code'] = $tax_code;
            $data['tax_code_name'] = $tax_code_info->tax_code_name;
            $data['tax_code_type'] = $tax_code_info->tax_code_type;
            $data['city'] = $tax_code_info->city;
            $data['state'] = $tax_code_info->state;
            $data['rate_tax_code'] = $tax_code_info->rate_tax_code;
            $data['rate_tax_category_id'] = $tax_code_info->rate_tax_category_id;
            $data['tax_category'] = $tax_code_info->tax_category;
            $data['add_tax_category'] = '';
            $data['tax_rate'] = $tax_rate_info->tax_rate;
            $data['rounding_code'] = $tax_rate_info->rounding_code;
        }

        $tax_rates = [];
        foreach ($this->tax->get_tax_code_rate_exceptions($tax_code) as $tax_code_rate) {    // TODO: get_tax_code_rate_exceptions doesn't exist in the tax model
            $tax_rate_row = [];
            $tax_rate_row['rate_tax_category_id'] = $tax_code_rate['rate_tax_category_id'];
            $tax_rate_row['tax_category'] = $tax_code_rate['tax_category'];
            $tax_rate_row['tax_rate'] = $tax_code_rate['tax_rate'];
            $tax_rate_row['rounding_code'] = $tax_code_rate['rounding_code'];

            $tax_rates[] = $tax_rate_row;
        }

        $data['tax_rates'] = $tax_rates;

        echo view('taxes/tax_jurisdiction_form', $data);
    }

    /**
     * @return string
     */
    public static function get_html_rounding_options(): string
    {
        return rounding_mode::get_html_rounding_options();
    }

    /**
     * @param int $tax_rate_id
     * @return void
     */
    public function postSave(int $tax_rate_id = NEW_ENTRY): void
    {
        $tax_category_id = $this->request->getPost('rate_tax_category_id', FILTER_SANITIZE_NUMBER_INT);
        $tax_rate = parse_tax($this->request->getPost('tax_rate'));

        if ($tax_rate == 0) {    // TODO: Replace 0 with constant?
            $tax_category_info = $this->tax_category->get_info($tax_category_id);    // TODO: this variable is not used anywhere in the code
        }

        $tax_rate_data = [
            'rate_tax_code_id'     => $this->request->getPost('rate_tax_code_id', FILTER_SANITIZE_NUMBER_INT),
            'rate_tax_category_id' => $tax_category_id,
            'rate_jurisdiction_id' => $this->request->getPost('rate_jurisdiction_id', FILTER_SANITIZE_NUMBER_INT),
            'tax_rate'             => $tax_rate,
            'tax_rounding_code'    => $this->request->getPost('tax_rounding_code', FILTER_SANITIZE_NUMBER_INT)
        ];

        if ($this->tax->save_value($tax_rate_data, $tax_rate_id)) {
            if ($tax_rate_id == NEW_ENTRY) {    // TODO: this needs to be replaced with ternary notation
                echo json_encode(['success' => true, 'message' => lang('Taxes.tax_rate_successfully_added')]);
            } else { // Existing tax_code
                echo json_encode(['success' => true, 'message' => lang('Taxes.tax_rate_successful_updated')]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => lang('Taxes.tax_rate_error_adding_updating')]);
        }
    }

    /**
     * @return void
     */
    public function postDelete(): void
    {
        $tax_codes_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT);

        if ($this->tax->delete_list($tax_codes_to_delete)) {    // TODO: this needs to be replaced with ternary notation
            echo json_encode(['success' => true, 'message' => lang('Taxes.tax_code_successful_deleted')]);
        } else {
            echo json_encode(['success' => false, 'message' => lang('Taxes.tax_code_cannot_be_deleted')]);
        }
    }

    /**
     * Get search suggestions for tax codes. Used in app/Views/customers/form.php
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function getSuggestTaxCodes(): void
    {
        $search = $this->request->getPostGet('term');
        $suggestions = $this->tax_code->get_tax_codes_search_suggestions($search);

        echo json_encode($suggestions);
    }

    /**
     * Saves Tax Codes. Used in app/Views/taxes/tax_codes.php
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function save_tax_codes(): void
    {
        $tax_code_id = $this->request->getPost('tax_code_id', FILTER_SANITIZE_NUMBER_INT);
        $tax_code = $this->request->getPost('tax_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tax_code_name = $this->request->getPost('tax_code_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $city = $this->request->getPost('city', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $state = $this->request->getPost('state', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $array_save = [];    // TODO: the naming of this variable is not good.
        foreach ($tax_code_id as $key => $val) {
            $array_save[] = [
                'tax_code_id'   => $val,
                'tax_code'      => $tax_code[$key],
                'tax_code_name' => $tax_code_name[$key],
                'city'          => $city[$key],
                'state'         => $state[$key]
            ];
        }

        $success = $this->tax_code->save_tax_codes($array_save);

        echo json_encode([
            'success' => $success,
            'message' => lang('Taxes.tax_codes_saved_' . ($success ? '' : 'un') . 'successfully')
        ]);
    }

    /**
     * Saves given tax jurisdiction. Used in app/Views/taxes/tax_jurisdictions.php.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function save_tax_jurisdictions(): void
    {
        $jurisdiction_id = $this->request->getPost('jurisdiction_id', FILTER_SANITIZE_NUMBER_INT);
        $jurisdiction_name = $this->request->getPost('jurisdiction_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tax_group = $this->request->getPost('tax_group', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tax_type = $this->request->getPost('tax_type', FILTER_SANITIZE_NUMBER_INT);
        $reporting_authority = $this->request->getPost('reporting_authority', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tax_group_sequence = $this->request->getPost('tax_group_sequence', FILTER_SANITIZE_NUMBER_INT);
        $cascade_sequence = $this->request->getPost('cascade_sequence', FILTER_SANITIZE_NUMBER_INT);

        $array_save = [];
        $unique_tax_groups = [];

        foreach ($jurisdiction_id as $key => $val) {
            $array_save[] = [
                'jurisdiction_id'     => $val,
                'jurisdiction_name'   => $jurisdiction_name[$key],
                'tax_group'           => $tax_group[$key],
                'tax_type'            => $tax_type[$key],
                'reporting_authority' => $reporting_authority[$key],
                'tax_group_sequence'  => $tax_group_sequence[$key],
                'cascade_sequence'    => $cascade_sequence[$key]
            ];

            if (in_array($tax_group[$key], $unique_tax_groups)) {    // TODO: This can be replaced with `in_array($tax_group[$key], $unique_tax_groups)`
                echo json_encode([
                    'success' => false,
                    'message' => lang('Taxes.tax_group_not_unique', [$tax_group[$key]])
                ]);
                return;
            } else {
                $unique_tax_groups[] = $tax_group[$key];
            }
        }

        $success = $this->tax_jurisdiction->save_jurisdictions($array_save);

        echo json_encode([
            'success' => $success,
            'message' => lang('Taxes.tax_jurisdictions_saved_' . ($success ? '' : 'un') . 'successfully')
        ]);
    }

    /**
     * Saves tax categories. Used in app/Views/taxes/tax_categories.php
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function save_tax_categories(): void
    {
        $tax_category_id = $this->request->getPost('tax_category_id', FILTER_SANITIZE_NUMBER_INT);
        $tax_category = $this->request->getPost('tax_category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tax_group_sequence = $this->request->getPost('tax_group_sequence', FILTER_SANITIZE_NUMBER_INT);

        $array_save = [];

        foreach ($tax_category_id as $key => $val) {
            $array_save[] = [
                'tax_category_id'    => $val,
                'tax_category'       => $tax_category[$key],
                'tax_group_sequence' => $tax_group_sequence[$key]
            ];
        }

        $success = $this->tax_category->save_categories($array_save);

        echo json_encode([
            'success' => $success,
            'message' => lang('Taxes.tax_categories_saved_' . ($success ? '' : 'un') . 'successfully')
        ]);
    }

    /**
     * Gets tax codes partial view. Used in app/Views/taxes/tax_codes.php.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function ajax_tax_codes(): void
    {
        $tax_codes = $this->tax_code->get_all()->getResultArray();

        echo view('partial/tax_codes', ['tax_codes' => $tax_codes]);
    }

    /**
     * Gets current tax categories. Used in app/Views/taxes/tax_categories.php
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function ajax_tax_categories(): void
    {
        $tax_categories = $this->tax_category->get_all()->getResultArray();

        echo view('partial/tax_categories', ['tax_categories' => $tax_categories]);
    }

    /**
     * Gets the tax jurisdiction partial view.  Used in app/Views/taxes/tax_jurisdictions.php.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function ajax_tax_jurisdictions(): void
    {
        $tax_jurisdictions = $this->tax_jurisdiction->get_all()->getResultArray();

        if ($this->config['tax_included']) {    // TODO: ternary notation
            $default_tax_type = Tax_lib::TAX_TYPE_INCLUDED;
        } else {
            $default_tax_type = Tax_lib::TAX_TYPE_EXCLUDED;
        }

        $tax_types = $this->tax_lib->get_tax_types();

        echo view('partial/tax_jurisdictions', [
            'tax_jurisdictions' => $tax_jurisdictions,
            'tax_types'         => $tax_types,
            'default_tax_type'  => $default_tax_type
        ]);
    }
}
