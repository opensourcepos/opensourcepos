<?php

namespace App\Plugins\MailchimpPlugin\Enums;

enum SubscriptionStatus: int
{
    case SUBSCRIBED = 1;
    case UNSUBSCRIBED = 2;
    case PENDING = 3;
    case CLEANED = 4;

    public static function fromApiString(string $status): ?self
    {
        foreach (self::cases() as $case) {
            if (strtolower($case->name) === strtolower($status)) {
                return $case;
            }
        }

        return null;
    }
}

