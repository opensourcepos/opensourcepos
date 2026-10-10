<?php

namespace App\Enums;

/**
 * Reward operation types for customer points adjustments.
 * 
 * Used by Reward_lib to perform type-safe reward point operations.
 */
enum RewardOperation: string
{
    case Deduct = 'deduct';
    case Restore = 'restore';
    case Adjust = 'adjust';
}