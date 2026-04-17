<?php

namespace App\Plugins\MailchimpPlugin\Enums;

enum SubscriptionStatus: int
{
    case SUBSCRIBED = 1;
    case UNSUBSCRIBED = 2;
    case PENDING = 3;
    case CLEANED = 4;
}

