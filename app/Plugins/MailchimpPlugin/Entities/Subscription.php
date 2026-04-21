<?php

namespace App\Plugins\MailchimpPlugin\Entities;

use CodeIgniter\Entity\Entity;

class Subscription extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'id' => 'integer',
        'customer_id' => 'integer',
        'mailchimp_id' => 'string',
        'status_id' => 'enum[App\Plugins\MailchimpPlugin\Enums\SubscriptionStatus]',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'id'            => null,
        'customer_id'   => null, // ospos_customers.person_id
        'mailchimp_id'  => null, // MD5 hash of the lowercase version of the list member's email address
        'status_id'     => null, // ospos_mailchimpplugin_subscription_status.status_id
        'created_at'    => null, // Timestamp of when the subscription was created
        'updated_at'    => null, // Timestamp of when the subscription was last updated
        'deleted_at'    => null  // Timestamp of when the subscription was deleted
    ];
}
