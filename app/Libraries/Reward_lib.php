<?php

namespace App\Libraries;

use App\Enums\RewardOperation;
use App\Models\Customer;

/**
 * Reward library
 *
 * Handles customer reward points business logic for sales transactions.
 * Extracted from Sale model to provide centralized reward management.
 */
class Reward_lib
{
    private Customer $customer;

    public function __construct()
    {
        $this->customer = model(Customer::class);
    }

    /**
     * Calculates reward points earned for a purchase.
     *
     * @param float $totalAmount Total sale amount
     * @param float $pointsPercent Points percentage from customer reward package
     * @return float Points earned
     */
    public function calculatePointsEarned(float $totalAmount, float $pointsPercent): float
    {
        return $totalAmount * $pointsPercent / 100;
    }

    /**
     * Adjusts customer reward points for a sale transaction.
     * Handles new sales, sale updates, and sale cancellations.
     * Prevents negative balances by validating sufficient points before deduction.
     *
     * @param int|null $customerId Customer ID (null for walk-in customers)
     * @param float $rewardAmount Amount to deduct from points (for new/updated sales)
     * @param RewardOperation $operation Operation type (Deduct, Restore, or Adjust)
     * @return bool Success status (false if insufficient points for deduct/adjust)
     */
    public function adjustRewardPoints(?int $customerId, float $rewardAmount, RewardOperation $operation): bool
    {
        if (empty($customerId) || $rewardAmount == 0) {
            return false;
        }

        $currentPoints = $this->customer->get_info($customerId)->points ?? 0;

        switch ($operation) {
            case RewardOperation::Deduct:
            case RewardOperation::Adjust:
                if ($currentPoints < $rewardAmount) {
                    log_message(
                        'warning',
                        'Reward_lib::adjustRewardPoints insufficient points customer_id=' . $customerId
                        . ' current=' . $currentPoints . ' requested=' . $rewardAmount
                    );
                    return false;
                }
                $this->customer->update_reward_points_value($customerId, $currentPoints - $rewardAmount);
                return true;
            case RewardOperation::Restore:
                $this->customer->update_reward_points_value($customerId, $currentPoints + $rewardAmount);
                return true;
            default:
                return false;
        }
    }

    /**
     * Handles reward point adjustment when customer changes on a sale.
     * Restores points to previous customer, deducts from new customer.
     * Prevents negative balances by capping deduction at available points.
     *
     * @param int|null $previousCustomerId Previous customer ID
     * @param int|null $newCustomerId New customer ID
     * @param float $previousRewardUsed Reward points used by previous customer
     * @param float $newRewardUsed Reward points to be used by new customer
     * @return array ['restored' => float, 'charged' => float, 'insufficient' => bool] Amounts restored/charged
     */
    public function handleCustomerChange(?int $previousCustomerId, ?int $newCustomerId, float $previousRewardUsed, float $newRewardUsed): array
    {
        $result = ['restored' => 0.0, 'charged' => 0.0, 'insufficient' => false];

        if ($previousCustomerId === $newCustomerId) {
            return $result;
        }

        if (!empty($previousCustomerId) && $previousRewardUsed != 0) {
            $previousPoints = $this->customer->get_info($previousCustomerId)->points ?? 0;
            $this->customer->update_reward_points_value($previousCustomerId, $previousPoints + $previousRewardUsed);
            $result['restored'] = $previousRewardUsed;
        }

        if (!empty($newCustomerId) && $newRewardUsed != 0) {
            $newPoints = $this->customer->get_info($newCustomerId)->points ?? 0;
            
            if ($newPoints < $newRewardUsed) {
                log_message(
                    'warning',
                    'Reward_lib::handleCustomerChange insufficient points new_customer_id=' . $newCustomerId
                    . ' available=' . $newPoints . ' requested=' . $newRewardUsed
                );
                $result['insufficient'] = true;
            }
            
            $actualCharged = min($newPoints, $newRewardUsed);
            $this->customer->update_reward_points_value($newCustomerId, max(0, $newPoints - $newRewardUsed));
            $result['charged'] = $actualCharged;
        }

        return $result;
    }

    /**
     * Adjusts reward points delta for same customer (e.g., payment amount changed).
     * Prevents negative balances by validating sufficient points before adjustment.
     *
     * @param int|null $customerId Customer ID
     * @param float $rewardAdjustment Difference between new and previous reward usage (positive = more used)
     * @return bool Success status (false if insufficient points)
     */
    public function adjustRewardDelta(?int $customerId, float $rewardAdjustment): bool
    {
        if (empty($customerId) || $rewardAdjustment == 0) {
            return false;
        }

        $currentPoints = $this->customer->get_info($customerId)->points ?? 0;
        
        if ($rewardAdjustment > 0 && $currentPoints < $rewardAdjustment) {
            log_message(
                'warning',
                'Reward_lib::adjustRewardDelta insufficient points customer_id=' . $customerId
                . ' current=' . $currentPoints . ' adjustment=' . $rewardAdjustment
            );
            return false;
        }
        
        $this->customer->update_reward_points_value($customerId, $currentPoints - $rewardAdjustment);
        return true;
    }

    /**
     * Validates if a customer has sufficient reward points for a purchase.
     *
     * @param int $customerId Customer ID
     * @param float $requiredPoints Points required for purchase
     * @return bool True if customer has sufficient points
     */
    public function hasSufficientPoints(int $customerId, float $requiredPoints): bool
    {
        $currentPoints = $this->customer->get_info($customerId)->points ?? 0;
        return $currentPoints >= $requiredPoints;
    }

    /**
     * Gets current reward points for a customer.
     *
     * @param int $customerId Customer ID
     * @return float Current points balance
     */
    public function getPointsBalance(int $customerId): float
    {
        return $this->customer->get_info($customerId)->points ?? 0;
    }

    /**
     * Calculates reward payment amount from a payments array.
     *
     * @param array $payments Array of payment data
     * @param array $rewardLabels Array of valid reward payment labels (localized)
     * @return float Total reward payment amount
     */
    public function calculateRewardPaymentAmount(array $payments, array $rewardLabels): float
    {
        $totalRewardAmount = 0;

        foreach ($payments as $payment) {
            if (in_array($payment['payment_type'] ?? '', $rewardLabels, true)) {
                $totalRewardAmount += floatval($payment['payment_amount'] ?? 0);
            }
        }

        return $totalRewardAmount;
    }
}