<?php

namespace App\Services;

use App\Models\Expert;
use App\Models\CommissionPolicy;
use App\Models\ExpertCommission;
use App\Models\Booking;

class CommissionService
{
    /**
     * Calculate commission breakdown for a given amount and expert.
     * Hierarchy: Override (handled in Booking) > Expert > Category > Global.
     */
    public function calculateStrict(Expert $expert, float $amount, ?Booking $bookingOverride = null)
    {
        // 1. Booking Level Override (Admin Manual Intervention)
        if ($bookingOverride && $bookingOverride->commission_override_type) {
            return $this->compute($amount, $bookingOverride->commission_override_type, $bookingOverride->commission_override_value, 'override');
        }

        // 2. Expert Specific Commission
        $expertCommission = ExpertCommission::where('expert_id', $expert->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($expertCommission) {
            return $this->compute($amount, $expertCommission->commission_type, $expertCommission->commission_value, 'expert');
        }

        // 3. Category Specific Commission
        // REFACTOR: Use expert_category_id relation
        $categoryPolicy = null;
        if ($expert->expert_category_id) {
            $categoryPolicy = CommissionPolicy::where('policy_type', 'category')
                ->where('expert_category_id', $expert->expert_category_id)
                ->where('is_active', true)
                ->first();
        } 
        
        // Fallback to legacy string match if strictly needed during transition, 
        // but since we migrated data, we should prefer ID.
        // Keeping legacy logic as backup? No, let's switch to new logic as primary.
        
        if ($categoryPolicy) {
             // Pass the category name for logging purposes from relationship or legacy
             $catName = $categoryPolicy->expertCategory->name ?? 'Unknown Category';
            return $this->compute($amount, $categoryPolicy->commission_type, $categoryPolicy->commission_value, 'category', $categoryPolicy);
        }

        // 4. Global Platform Default
        $globalPolicy = CommissionPolicy::where('policy_type', 'global')
            ->where('is_active', true)
            ->first();

        if ($globalPolicy) {
            return $this->compute($amount, $globalPolicy->commission_type, $globalPolicy->commission_value, 'global', $globalPolicy);
        }

        // Fallback if no policy found (Safe Default: 10%)
        return $this->compute($amount, 'percentage', 10, 'fallback');
    }

    private function compute($totalAmount, $type, $value, $source, $policy = null)
    {
        $platformFee = 0;

        if ($type === 'percentage') {
            $platformFee = ($totalAmount * $value) / 100;
        } else {
            $platformFee = $value;
        }

        // Cap fee at total amount
        if ($platformFee > $totalAmount) {
            $platformFee = $totalAmount;
        }

        // Tax Logic (GST on Platform Fee)
        // Default: 18% GST on service fee
        $gstRate = $policy && !$policy->gst_applicable ? 0 : 18; 
        $gstAmount = ($platformFee * $gstRate) / 100;

        // TDS Logic (Deducted from Expert Earning)
        // Default: 10% TDS (if applicable)
        $tdsRate = $policy && !$policy->tds_applicable ? 0 : 10;
        // TDS is calculated on the Gross Earning of Expert before TDS deduction?
        // Gross Expert Earning = Total Amount - Platform Fee (incl GST)?
        // Or is TDS on the whole payout? Standard: TDS is on the income.
        
        // Let's define: 
        // Gross Expert Share = Total Amount - Platform Fee - GST on Platform Fee
        // TDS = Gross Expert Share * TDS Rate
        // Net Payable to Expert = Gross Expert Share - TDS

        $grossExpertShare = $totalAmount - ($platformFee + $gstAmount);
        
        // Prevent negative
        if ($grossExpertShare < 0) $grossExpertShare = 0;

        $tdsAmount = ($grossExpertShare * $tdsRate) / 100;
        $netExpertEarning = $grossExpertShare - $tdsAmount;

        return [
            'total_amount' => $totalAmount,
            'platform_fee_base' => round($platformFee, 2),
            'gst_on_fee' => round($gstAmount, 2),
            'platform_total_deduction' => round($platformFee + $gstAmount, 2),
            'gross_expert_share' => round($grossExpertShare, 2),
            'tds_deduction' => round($tdsAmount, 2),
            'net_expert_earning' => round($netExpertEarning, 2),
            'applied_type' => $source, // global, category, etc
            'applied_rate' => $value,
            'applied_gst_rate' => $gstRate,
            'applied_tds_rate' => $tdsRate,
        ];
    }
}
