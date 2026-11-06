<?php

namespace App\Plugins\MailchimpPlugin\Controllers;

use App\Controllers\Secure_Controller;
use app\Plugins\MailchimpPlugin\Libraries\MailchimpLibrary;
use CodeIgniter\HTTP\ResponseInterface;

class MailchimpController extends Secure_Controller
{
    /**
     * Gets Mailchimp lists when a valid API key is inserted. Used in App/Plugins/MailchimpPlugin/Views/config.php
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postCheckMailchimpApiKey(): ResponseInterface
    {
        $lists = $this->getAllMailchimpLists($this->request->getPost('api_key'));
        $success = count($lists) > 0;

        return $this->response->setJSON([
            'success'   => $success,
            'message'   => lang('MailchimpPlugin.key_' . ($success ? '' : 'un') . 'successfully'),
            'lists'     => $lists
        ]);
    }

    /**
     * This function fetches all the available lists from Mailchimp for the given API key
     */
    private function getAllMailchimpLists(string $api_key = ''): array
    {
        $mailchimpLibrary = new MailchimpLibrary(['api_key' => $api_key]);

        $result = [];

        $lists = $mailchimpLibrary->getLists();
        if ($lists !== false) {
            if (is_array($lists) && !empty($lists['lists']) && is_array($lists['lists'])) {
                foreach ($lists['lists'] as $list) {
                    $result[$list['id']] = $list['name'] . ' [' . $list['stats']['member_count'] . ']';
                }
            }
        }

        return $result;
    }
}
