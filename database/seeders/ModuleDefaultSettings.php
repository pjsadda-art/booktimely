<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\CustomStatus;
use App\Models\Setting;
use App\Services\CustomerNotifier;
use Illuminate\Database\Seeder;

/**
 * Default settings for the customer, deposit and inventory modules, plus the
 * standard status spine every business needs.
 *
 * Every notification toggle key is *derived* from the event catalogue via
 * CustomerNotifier::settingsKey(), the same call the reader uses. That is the
 * whole point: a per-event switch written under one key name and read under
 * another is a feature that can never fire, and it fails silently.
 */
class ModuleDefaultSettings extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // --- Deposits ---------------------------------------------------
            'deposit_auto_enabled' => 'off',
            'deposit_default_percentage' => '15',
            'deposit_service_price_threshold' => '0',
            'deposit_min_cancellations' => '0',
            'deposit_min_cancellations_months' => '6',
            'deposit_min_noshows' => '0',
            'deposit_min_noshows_months' => '6',
            // A cancellation this close to the start forfeits a paid deposit.
            // 0 means only a no-show ever forfeits.
            'deposit_forfeit_window_hours' => '24',

            // --- Customers --------------------------------------------------
            // Shared by the reliability badge and the deposit engine so the two
            // always agree about the same person. 0 means count lifetime.
            'reliability_window_months' => '6',

            // --- Loyalty ----------------------------------------------------
            'loyalty_program' => 'off',
            'loyalty_program_type' => 'service',
            'loyalty_visits_required' => '6',

            // --- Inventory --------------------------------------------------
            'purchase_prefix' => '#PUR',
        ];

        foreach (CustomerNotifier::EVENTS as $event => $definition) {
            foreach (['sms', 'email'] as $channel) {
                $defaults[CustomerNotifier::settingsKey($event, $channel)] = 'on';
            }
        }

        foreach (Business::all() as $business) {
            foreach ($defaults as $key => $value) {
                // updateOrInsert with the value in the second argument would
                // overwrite a business's own choice on every deploy, so only
                // absent keys are written.
                $exists = Setting::where('key', $key)
                    ->where('business', $business->id)
                    ->where('created_by', $business->created_by)
                    ->exists();

                if (!$exists) {
                    Setting::create([
                        'key' => $key,
                        'value' => $value,
                        'business' => $business->id,
                        'created_by' => $business->created_by,
                    ]);
                }
            }

            // Deposits resolve "Deposit Pending" and "Confirmed" by slug, so
            // those rows have to exist before the first deposit is raised.
            CustomStatus::ensureStandard($business->id, $business->created_by);
        }
    }
}
