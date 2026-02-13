<?php

namespace App\Controllers;

use App\Models\Giftcard;
use CodeIgniter\HTTP\ResponseInterface;
use Config\OSPOS;
use Config\Services;

class Giftcards extends Secure_Controller
{
    private Giftcard $giftcard;

    public function __construct()
    {
        parent::__construct('giftcards');

        $this->giftcard = model(Giftcard::class);
    }

    /**
     * @return void
     */
    public function getIndex(): ResponseInterface|string
    {
        $data['table_headers'] = get_giftcards_manage_table_headers();

        return view('giftcards/manage', $data);
    }

    /**
     * Returns Giftcards table data rows. This will be called with AJAX.
     */
    public function getSearch(): ResponseInterface
    {
        $search = $this->request->getGet('search');
        $limit  = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort   = $this->sanitizeSortColumn(giftcard_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'giftcard_id');
        $order  = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $giftcards = $this->giftcard->search($search, $limit, $offset, $sort, $order);
        $total_rows = $this->giftcard->get_found_rows($search);

        $data_rows = [];
        foreach ($giftcards->getResult() as $giftcard) {
            $data_rows[] = get_giftcard_data_row($giftcard);
        }

        return $this->response->setJSON(['total' => $total_rows, 'rows' => $data_rows]);
    }

    /**
     * Gets search suggestions for giftcards. Used in app\Views\sales\register.php
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function getSuggest(): ResponseInterface|string
    {
        $search = $this->request->getGet('term');
        $suggestions = $this->giftcard->get_search_suggestions($search, true);

        return $this->response->setJSON($suggestions);
    }

    /**
     * @return void
     */
    public function suggest_search(): ResponseInterface|string
    {
        $search = $this->request->getPost('term');
        $suggestions = $this->giftcard->get_search_suggestions($search);

        return $this->response->setJSON($suggestions);
    }

    /**
     * @param int $row_id
     * @return void
     */
    public function getRow(int $row_id): ResponseInterface|string
    {
        $data_row = get_giftcard_data_row($this->giftcard->get_info($row_id));

        return $this->response->setJSON($data_row);
    }

    /**
     * @param int $giftcard_id
     * @return void
     */
    public function getView(int $giftcard_id = NEW_ENTRY): ResponseInterface|string
    {
        $config = config(OSPOS::class)->settings;
        $giftcard_info = $this->giftcard->get_info($giftcard_id);

        $data['selected_person_name'] = ($giftcard_id > 0 && isset($giftcard_info->person_id)) ? $giftcard_info->first_name . ' ' . $giftcard_info->last_name : '';
        $data['selected_person_id'] = $giftcard_info->person_id;
        if ($config['giftcard_number'] == 'random') {
            $data['giftcard_number'] = $giftcard_id > 0 ? $giftcard_info->giftcard_number : '';
        } else {
            $max_number_obj = $this->giftcard->get_max_number();
            $max_giftnumber = isset($max_number_obj) ? $this->giftcard->get_max_number()->giftcard_number : 0;    // TODO: variable does not follow naming standard.
            $data['giftcard_number'] = $giftcard_id > 0 ? $giftcard_info->giftcard_number : $max_giftnumber + 1;
        }
        $data['giftcard_id'] = $giftcard_id;
        $data['giftcard_value'] = $giftcard_info->value;

        return view("giftcards/form", $data);
    }

    /**
     * @param int $giftcard_id
     * @return void
     */
    public function postSave(int $giftcard_id = NEW_ENTRY): ResponseInterface|string
    {
        $giftcard_number = $this->request->getPost('giftcard_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($giftcard_id == NEW_ENTRY && trim($giftcard_number) == '') {
            $giftcard_number = $this->giftcard->generate_unique_giftcard_name($giftcard_number);
        }

        $giftcard_data = [
            'record_time'     => date('Y-m-d H:i:s'),
            'giftcard_number' => $giftcard_number,
            'value'           => parse_decimals($this->request->getPost('giftcard_amount')),
            'person_id'       => empty($this->request->getPost('person_id')) ? null : $this->request->getPost('person_id', FILTER_SANITIZE_NUMBER_INT)
        ];

        if ($this->giftcard->save_value($giftcard_data, $giftcard_id)) {
            // New giftcard
            if ($giftcard_id == NEW_ENTRY) {    // TODO: Constant needed
                $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Giftcards.successful_adding') . ' ' . $giftcard_data['giftcard_number'],
                    'id'      => $giftcard_data['giftcard_id']
                ]);
            } else { // Existing giftcard
                $this->response->setJSON([
                    'success' => true,
                    'message' => lang('Giftcards.successful_updating') . ' ' . $giftcard_data['giftcard_number'],
                    'id'      => $giftcard_id
                ]);
            }
        } else { // Failure
            $this->response->setJSON([
                'success' => false,
                'message' => lang('Giftcards.error_adding_updating') . ' ' . $giftcard_data['giftcard_number'],
                'id'      => NEW_ENTRY
            ]);
        }
    }

    /**
     * Checks the giftcard number validity. Used in app\Views\giftcards\form.php
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function postCheckNumberGiftcard(): ResponseInterface|string
    {
        $existing_id = $this->request->getPost('giftcard_id', FILTER_SANITIZE_NUMBER_INT);
        $giftcard_number = $this->request->getPost('giftcard_number', FILTER_SANITIZE_NUMBER_INT);
        $giftcard_id = $this->giftcard->get_giftcard_id($giftcard_number);
        $success = ($giftcard_id == (int) $existing_id || !$giftcard_id );

        echo $success ? 'true' : 'false';
    }

    /**
     * @return void
     */
    public function postDelete(): ResponseInterface|string
    {
        $giftcards_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($this->giftcard->delete_list($giftcards_to_delete)) {
            $this->response->setJSON([
                'success' => true,
                'message' => lang('Giftcards.successful_deleted') . ' ' . count($giftcards_to_delete) . ' ' . lang('Giftcards.one_or_multiple')
            ]);
        } else {
            $this->response->setJSON(['success' => false, 'message' => lang('Giftcards.cannot_be_deleted')]);
        }
    }
}
