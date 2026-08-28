<?php

namespace App\Traits;

use App\Events\AdditionalServicePayment;
use App\Models\Business;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\AppointmentPayment;
use App\Models\Customer;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Events\CreateAppoinment;
use App\Models\EmailTemplate;
use App\Events\AppointmentPaymentData;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Workdo\BulkAppointments\Events\CreateBulkAppointment;
use Workdo\CollaborativeServices\Events\StoreCollaborativeServices;
use Workdo\CompoundService\Events\CreateCompoundBooking;
use Workdo\FlexibleHours\Entities\FlexibleHour;
use Workdo\RepeatAppointments\Events\RepeatAppointement;
use Workdo\SequentialAppointment\Events\SequentialAppointment;
use Workdo\ShoppingCart\Events\ShoppinCartData;
use Workdo\TeamBooking\Events\CreateTeamBooking;



trait PaymentTrait
{

    protected $adminSetting;
    protected $companySetting;

    // Store Setting
    public function paymentSetting($request, $permission, $enable_key, $validation)
    {
        if (Auth::user()->isAbleTo($permission)) {
            if ($request->has($enable_key)) {
                $validator = Validator::make($request->all(), $validation);
                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->getMessageBag()->first());
                }
            }
            $getActiveBusiness  = getActiveBusiness();
            $creatorId          = creatorId();
            if ($request->has($enable_key)) {
                $post = $request->all();
                unset($post['_token']);
                unset($post['_method']);
                foreach ($post as $key => $value) {
                    // Define the data to be updated or inserted
                    $data = [
                        'key'        => $key,
                        'business'   => $getActiveBusiness,
                        'created_by' => $creatorId,
                    ];

                    // Check if the record exists, and update or insert accordingly
                    Setting::updateOrInsert($data, ['value' => $value]);
                }
            } else {
                $data = [
                    'key'        => $enable_key,
                    'business'   => $getActiveBusiness,
                    'created_by' => $creatorId,
                ];
                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => 'off']);
            }

            // Settings Cache forget
            AdminSettingCacheForget();
            comapnySettingCacheForget();
            return redirect()->back()->with('success', __('payment setting has been saved successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // Plan Payment
    public function payThisPlan($request, $package_name, $Session  = false)
    {
        $user               =   Auth::user();
        $plan               =   Plan::find($request->plan_id);
        $this->adminSetting =   getAdminAllSetting();
        $admin_settings     =   $this->adminSetting;
        $currency           =   !empty($admin_settings['defult_currancy']) ? $admin_settings['defult_currancy'] : '';
        $user_counter       =   !empty($request->user_counter_input) ? $request->user_counter_input : 0;
        $business_counter   =   !empty($request->business_counter_input) ? $request->business_counter_input : 0;
        $user_module        =   !empty($request->user_module_input) ? $request->user_module_input : '';
        $duration           =   !empty($request->time_period) ? $request->time_period : 'Month';
        $order_id           =   strtoupper(str_replace('.', '', uniqid('', true)));
        $other_order_id     =   strtoupper(str_replace('.', '', uniqid('', true)));


        $user_module_price  =   0;
        if (!empty($user_module) && $plan->custom_plan == 1) {
            $user_module_array = explode(',', $user_module);
            foreach ($user_module_array as $key => $value) {
                $temp               = ($duration == 'Year') ? ModulePriceByName($value)['yearly_price'] : ModulePriceByName($value)['monthly_price'];
                $user_module_price  = $user_module_price + $temp;
            }
        }

        $user_price = 0;
        if ($user_counter > 0) {
            $temp           = ($duration == 'Year') ? $plan->price_per_user_yearly : $plan->price_per_user_monthly;
            $user_price     = $user_counter * $temp;
        }

        $business_price = 0;
        if ($business_counter > 0) {
            $temp               = ($duration == 'Year') ? $plan->price_per_business_yearly : $plan->price_per_business_monthly;
            $business_price     = $business_counter * $temp;
        }

        $plan_price = ($duration == 'Year') ? $plan->package_price_yearly : $plan->package_price_monthly;

        $counter = [
            'user_counter'      => $user_counter,
            'business_counter'  => $business_counter,
        ];

        if ($plan) {
            /* Check for code usage */
            $plan->discounted_price     = false;
            if ($request->coupon_code) {
                $plan_price = CheckCoupon($request->coupon_code, $plan_price);
            }

            $price = $plan_price + $user_module_price + $user_price + $business_price;

            if ($price <= 0) {
                $assignPlan = DirectAssignPlan($plan->id, $duration, $user_module, $counter, $package_name, $request->coupon_code);
                $plan_type   = 'free';
                if ($assignPlan['is_success']) {
                    $status  = 'success';
                    $message = __('Your plan has been successfully activated.');
                } else {
                    $status  = 'error';
                    $message = __('Plan activation failed. Please try again.');
                }
            }

            unset($request['time_period'], $request['user_counter_input'], $request['business_counter_input'], $request['user_module_input']);
            $request_data = $request->merge([
                'status'    => !empty($status) ? $status : 'success',
                'message'   => !empty($message) ? $message : __('Your plan is in progress.'),
                'plan_type' => $plan_type ?? 'paid',
                'order_id'  => $order_id,
                'currency'  => $currency,
                'plan'      => $plan,
                'counter'   => $counter,
                'duration'  => $duration,
                'price'     => $price,
                'user'      => $user,
                'settings'  => $admin_settings,
                'user_module'    => $user_module,
                'other_order_id' => $other_order_id,
                'Order' => [
                    'order_id'         => $order_id,
                    'name'             => $user->name,
                    'email'            => $user->email,
                    'card_number'      => null,
                    'card_exp_month'   => null,
                    'card_exp_year'    => null,
                    'plan_name'        => !empty($plan->name) ? $plan->name : 'Basic Package',
                    'plan_id'          => $plan->id,
                    'price'            => $price,
                    'price_currency'   =>  $currency,
                    'txn_id'           => '',
                    'payment_type'     => $package_name,
                    'payment_status'   => 'pending',
                    'receipt'          => '',
                    'user_id'          => $user->id,
                ],
            ]);

            $cache_session_data = Arr::except($request_data->toArray(), ['settings', 'Order', 'status', 'message', '_token',]);
            if ($Session  == false) {
                Cache::put($order_id, $cache_session_data);
            } else {
                Session::put($order_id, $cache_session_data);
            }
            return $request_data;
        } else {
            return (object) [
                'order_id'  => $order_id,
                'status'    => 'error',
                'currency'  => $currency,
                'message'   =>  __('Plan not found. Please check the plan details and try again.'),
            ];
        }
    }

    public function statusThisPlan($request, $paymentStatus, $Session  = false, $receipt_url = null)
    {

        try {
            if (!isset($request->order_id) && $request->order_id == null) {
                return (object) ['status' => 'error', 'message' => __('The order id not found.')];
            }

            if ($Session  == false) {
                $Cache = (object) Cache::get($request->order_id);
                Cache::forget($request->order_id);
            } else {
                $Cache = (object) Session::get($request->order_id);
                Session::forget($request->order_id);
            }

            if ($paymentStatus == 'success' || $paymentStatus == 'paid' || $paymentStatus == 'true') {
                $Order = Order::where('order_id', $Cache->order_id)->first();

                if (!isset($Order) && $Order == null) {
                    return (object) ['status' => 'error', 'message' => __('Please generate your Payment Order ID')];
                }

                $Order->payment_status  = 'succeeded';
                $Order->receipt = $receipt_url;
                $Order->save();

                $user       = Auth::user();
                $plan       = Plan::find($Cache->plan_id);
                $assignPlan = $user->assignPlan($plan->id, $Cache->duration, $Cache->user_module, $Cache->counter);

                if ($Cache->coupon_code) {
                    UserCoupon($Cache->coupon_code, $Cache->order_id);
                }

                if ($assignPlan['is_success']) {
                    return (object) ['status' => 'success', 'message'   =>  __('Your plan has been successfully activated.')];
                } else {
                    return (object) ['status' => 'error', 'message'   =>  __($assignPlan['error'])];
                }
            } else {
                return (object) ['status'    => 'error', 'message'   =>  __('The transaction has been failed.')];
            }
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return (object) ['status' => 'error', 'message'   =>  $e->getMessage()];
        }
    }


    // Appointment Payment
    public function payThisAppointment($request, $Session  = false)
    {
        $business               = Business::find($request->business_id);
        $service                = Service::find($request->service);
        $service_price          = $service->price;

        $this->companySetting   = getCompanyAllSetting($business->created_by, $business->id);
        $companySetting         = $this->companySetting;
        $currency               = !empty($companySetting['defult_currancy']) ? $companySetting['defult_currancy'] : '';
        $order_id               = strtoupper(str_replace('.', '', uniqid('', true)));
        $other_order_id         = strtoupper(str_replace('.', '', uniqid('', true)));

        //For Single Modules
        if (module_is_active('ServiceTax', $business->created_by) && $service->tax_id != 0) {
            $service_price = $request['final_amount'];
        }
        if (module_is_active('Discount', $business->created_by) && $request->has('discount_amount')) {
            $service_price = $service->price - $request->discount_amount;
        }
        if (module_is_active('PromoCodes', $business->created_by) && $request->after_promo_price !== null) {
            $service_price = $request->after_promo_price;
        }
        if (module_is_active('TeamBooking', $business->created_by)) {
            // $service_price = $service_price * $request->person;
            $clean_service_price = (float)str_replace(',', '', $service_price);
            $person_count = (int)$request->person;
            $service_price = $clean_service_price * $person_count;
            if ($request->type == 'guest-user') {
                $service_price = $clean_service_price;
            }
        }
        if (module_is_active('FlexibleHours', $business->created_by) && $request->flexible_id !== null) {
            $flexible = FlexibleHour::find($request->flexible_id);
            $service_price = $flexible->price;
        }
        if (module_is_active('BulkAppointments', $business->created_by) && isset($data['quantity']) && $data['quantity'] != 0) {
            if (is_numeric($service_price) && is_numeric($request->quantity)) {
                $service_price = $service_price * $request->quantity;
            }
        }
        if (module_is_active('FlexibleDuration', $business->created_by) && $request->has('flexible_duration_amount')) {
            $service_price = $request->flexible_duration_amount;
        }
        if (module_is_active('ShoppingCart', $business->created_by)) {
            $service_price = $request['all_service_price'];
        }
        if (((module_is_active('RepeatAppointments', $business->created_by)) && ($request->has('date')) && ($request->has('booked_slot')))) {
            $company_settings = getCompanyAllSetting($service->created_by, $service->business_id);
            if (isset($request['date']) && isset($request['booked_slot'])) {
                if ($company_settings['repeat_payment_type'] == 1) {
                    $countTotalSlot = count($request['date']);
                    $service_price = $countTotalSlot * $service->price;
                } else {
                    $service_price =  $service->price;
                }
            }
        }
        if ((module_is_active('SequentialAppointment', $business->created_by) && $request->sequential_services !== null)) {
            $serviceIds = [$request->service];

            foreach ($request->sequential_services as $sequentialService) {
                $serviceIds[] = $sequentialService['sequential_service'];
            }
            $totalPrice = 0;

            foreach ($serviceIds as $serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    $totalPrice += $service->price;
                }
            }
            $service_price = $totalPrice;
        }
        if (module_is_active('AdditionalServices', $business->created_by) && $request->has('additional_service_price') && $request->has('additional_service')) {
            $service_price = $request->additional_service_price;
        }
        if (module_is_active('EasyDepositPayments', $business->created_by)) {
            $service = Service::find($request->service);
            $admin_settings = getCompanyAllSetting($business->created_by, $business->id);

            if (!isset($admin_settings['deposit_payment_setting'])) {
                $admin_settings['deposit_payment_setting'] = 'only deposit payment';
            }

            if (!empty($service->deposit_amount) && $service->deposit_amount != null && $service->deposit_amount != 0) {
                $depositAmount = $service->deposit_amount;
                $deposit_amount = (($service->price * $depositAmount) / 100) - $service->amount;
                if ($admin_settings['deposit_payment_setting'] == 'default deposit payment') {
                    if ($request->deposit_payment_type == 'full_payment') {
                        $service_price = $service->price;
                    } else {
                        $service_price = $deposit_amount;
                    }
                } else {
                    $service_price = $deposit_amount;
                }
            }
        }
        // Double Module Start
        if (module_is_active('FlexibleHours', $business->created_by) && $request->flexible_id !== null && module_is_active('ServiceTax', $business->created_by) && isset($request['service_tax'])) {
            $service_price = $request->final_amount;
        }
        if (module_is_active('PromoCodes', $business->created_by) && $request->promocode != null && module_is_active('FlexibleHours', $business->created_by) && $request->flexible_id !== null) {
            $service_price = $request->service_after_promo;
        }
        if (module_is_active('Discount', $business->created_by) && $request->has('discount_amount') && module_is_active('FlexibleHours', $business->created_by) && $request->flexible_id !== null) {
            $service_price = $request->final_amount;
        }
        if ((module_is_active('SequentialAppointment', $business->created_by) && $request->sequential_services !== null)
            && (module_is_active('PromoCodes', $business->created_by) && $request->promocode != null)
        ) {
            $service_price = $request->service_after_promo;
        }
        if ((module_is_active('SequentialAppointment', $business->created_by) && $request->sequential_services !== null)
            && (module_is_active('ServiceTax', $business->created_by) && isset($request['service_tax']))
        ) {
            $service_price = $request->final_amount;
        }
        if ((module_is_active('SequentialAppointment', $business->created_by) && $request->sequential_services !== null) && module_is_active('BulkAppointments', $business->created_by)) {
            $firstService = Service::find($request->service);
            if ($firstService) {
                $service_price = $firstService->price * $request->quantity;
            }

            $totalPrice = $service_price;
            $serviceIds = [$request->service];

            foreach ($request->sequential_services as $sequentialService) {
                $serviceIds[] = $sequentialService['sequential_service'];
            }
            foreach ($serviceIds as $index => $serviceId) {
                if ($index === 0) continue;
                $service = Service::find($serviceId);
                if ($service) {
                    $totalPrice += $service->price;
                }
            }
            $service_price = $totalPrice;
        }
        if ((module_is_active('SequentialAppointment', $business->created_by) && $request->sequential_services !== null) && module_is_active('TeamBooking', $business->created_by)) {

            $firstService = Service::find($request->service);
            if ($firstService) {
                $service_price = $firstService->price * $request->person;
            }

            $totalPrice = $service_price;
            $serviceIds = [$request->service];

            foreach ($request->sequential_services as $sequentialService) {
                $serviceIds[] = $sequentialService['sequential_service'];
            }
            foreach ($serviceIds as $index => $serviceId) {
                if ($index === 0) continue;
                $service = Service::find($serviceId);
                if ($service) {
                    $totalPrice += $service->price;
                }
            }

            $service_price = $totalPrice;
        }
        if (module_is_active('ServiceTax', $business->created_by) && module_is_active('ShoppingCart', $business->created_by)) {
            $service_price = $request->final_amount;
        }
        if ((module_is_active('PromoCodes', $business->created_by) && $request->promocode != null) && module_is_active('BulkAppointments', $business->created_by)) {
            $service_price = $request['service_after_promo'];
        }
        if (module_is_active('PromoCodes', $business->created_by) && $request->promocode != null && module_is_active('ServiceTax', $business->created_by)) {
            $service_price = $request->after_promo_price;
        }
        if (module_is_active('PromoCodes', $business->created_by) && module_is_active('Discount', $business->created_by)) {
            $service_price = $request['after_promo_discount'];
        }
        if (module_is_active('PromoCodes', $business->created_by) && module_is_active('ShoppingCart', $business->created_by)) {
            $service_price = $request['cart_promo_discount'];
        }
        if (module_is_active('PromoCodes', $business->created_by) && module_is_active('EasyDepositPayments') && isset($request['deposit_price']) && $request['promocode'] != null && isset($request['promocode'])) {
            if ($request->deposit_payment_type == 'full_payment') {
                $service_price = $request['service_after_promo'];
            } else {
                $service_price = $request->deposit_price;
            }
        }
        if ((module_is_active('PromoCodes', $business->created_by) && $request->promocode != null) && module_is_active('TeamBooking', $business->created_by)) {
            $service_price = $request['service_after_promo'];
        }
        if (module_is_active('ServiceTax', $business->created_by) && module_is_active('TeamBooking', $business->created_by)) {
            $service_price = $request->final_amount;
        }
        if (module_is_active('ServiceTax', $business->created_by) && module_is_active('BulkAppointments', $business->created_by)) {
            $service_price = $request->final_amount;
        }
        if ((module_is_active('AdditionalServices', $business->created_by) &&  $request->has('additional_service_price') && $request->has('additional_service')) && module_is_active('ServiceTax', $business->created_by) && isset($request['service_tax'])) {
            $service_price = $request->final_amount;
        }
        if ((module_is_active('AdditionalServices', $business->created_by) &&  $request->has('additional_service_price') && $request->has('additional_service')) && (module_is_active('PromoCodes', $business->created_by) && $request['promocode'] != null && isset($request['promocode']))) {
            $service_price = $request['service_after_promo'];
        }
        if (module_is_active('PromoCodes', $business->created_by) && module_is_active('ShoppingCart', $business->created_by)) {
            $service_price = $request['cart_promo_discount'];
        }
        if (module_is_active('PromoCodes', $business->created_by) && module_is_active('EasyDepositPayments') && isset($request['deposit_price']) && $request['promocode'] != null && isset($request['promocode'])) {
            if ($request->deposit_payment_type == 'full_payment') {
                $service_price = $request['service_after_promo'];
            } else {
                $service_price = $request->deposit_price;
            }
        }
        if (module_is_active('ShoppingCart', $business->created_by) && module_is_active('Discount', $business->created_by)) {
            $service_price = $request['final_amount'];
        }
        if (module_is_active('Discount', $business->created_by) && $request->has('discount_amount') && module_is_active('BulkAppointments', $business->created_by)) {
            $service_price = $request['final_amount'];
        }
        if (module_is_active('Discount', $business->created_by) && $request->has('discount_amount') && module_is_active('TeamBooking', $business->created_by)) {
            $service_price = $request['final_amount'];
        }
        if (((module_is_active('RepeatAppointments', $business->created_by)) && ($request->has('date')) && ($request->has('booked_slot'))) && ((module_is_active('AdditionalServices', $business->created_by)) && ($request->has('additional_service_price')) && ($request->has('additional_service')))) {
            $company_settings = getCompanyAllSetting($service->created_by, $service->business_id);
            if (isset($request['date']) && isset($request['booked_slot'])) {
                if ($company_settings['repeat_payment_type'] == 1) {
                    $countTotalSlot             = count($request['date']);
                    $recurringAppointmentPrice  = $countTotalSlot * $service->price;
                    $additionalServicePrice     =  $request->additional_total_price;
                    $additionalServicePriceForAllAppointment =  $countTotalSlot * $additionalServicePrice;
                    $service_price              = $recurringAppointmentPrice + $additionalServicePriceForAllAppointment;
                } else {
                    $recurringAppointmentPrice  = $service->price;
                    $additionalServicePrice     =  $request->additional_total_price;
                    $service_price              =  $recurringAppointmentPrice + $additionalServicePrice;
                }
            }
        }

        if (module_is_active('Discount', $business->created_by) && $request->has('discount_amount') && module_is_active('AdditionalServices', $business->created_by) && $request->has('additional_service')) {
            $service_price = $request['final_amount'];
        }

        if (module_is_active('Discount', $business->created_by) && $request->has('discount_amount') && module_is_active('SequentialAppointment', $business->created_by) && $request->sequential_services !== null) {
            $service_price = $request['final_amount'];
        }

        // Repeat Appointment With Service Tax
        if (((module_is_active('RepeatAppointments', $business->created_by)) && ($request->has('date')) && ($request->has('booked_slot'))) && (module_is_active('ServiceTax', $business->created_by) && $request->has('service_tax'))) {
            $service_price = $request['final_amount'];
        }

        // Repeat Appointment With Discount
        if (((module_is_active('RepeatAppointments', $business->created_by)) && ($request->has('date')) && ($request->has('booked_slot'))) && (module_is_active('Discount', $business->created_by) && $request->has('discount_amount'))) {
            $service_price = $request['final_amount'];
        }

        // Repeat Appointment With Promo Code

        if (((module_is_active('RepeatAppointments', $business->created_by)) && ($request->has('date')) && ($request->has('booked_slot'))) && (module_is_active('PromoCodes', $business->created_by) && $request->has('service_after_promo'))) {
            $service_price = $request['service_after_promo'];
        }

        // Repeat Appointment With Discount & Service Tax

        if (((module_is_active('RepeatAppointments', $business->created_by)) && ($request->has('date')) && ($request->has('booked_slot')))  && (module_is_active('Discount', $business->created_by) && $request->has('discount_amount'))  && (module_is_active('ServiceTax', $business->created_by) && $request->has('service_tax'))) {
            $service_price = $request['total_amount'];
        }

        $price  = floatval(str_replace(',', '', $service_price));
        $url    = route('appointments.form', ['slug' => $business->slug, 'appointment' => 'failed']) . '#appointment';

        $request_data = $request->merge([
            'order_id'  => $order_id,
            'other_order_id' => $other_order_id,
            'business'  => (array) $business->toArray(),
            'service'   => (array) $service->toArray(),
            'price'     => (int) $price,
            'currency'  => $currency,
            'settings'  => (array) $companySetting,
            'fail_url'  => $url,
            'status'    => 'success',
            'message'   =>   __('Your appointment in progress.'),
        ])->all();

        if ($request->hasFile('attachment')) {
            $filenameWithExt    = $request['attachment']->getClientOriginalName();
            $filename           = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension          = $request['attachment']->getClientOriginalExtension();
            $fileNameToStore    = $filename . '_' . time() . '.' . $extension;
            $upload             = upload_file($request, 'attachment', $fileNameToStore, 'Appointment');
            if ($upload['flag'] == 1) {
                $request_data['attachment'] = $upload['url'];
            } else {
                $request_data['status'] = 'error';
                $request_data['message'] = $upload['msg'];
            }
        }
        $cache_session_data = Arr::except($request_data, ['settings', 'status', 'message', '_token']);

        if ($Session  == false) {
            Cache::put($order_id, $cache_session_data);
        } else {
            Session::put($order_id, $cache_session_data);
        }
        return (object) $request_data;
    }

    public function statusThisAppointment($request, $slug, $payment_status, $Session  = false, $receipt_url = null)
    {
        if ($payment_status == 'success' || $payment_status == 'paid' || $payment_status == 'true') {

            if (!isset($request->order_id) && $request->order_id == null) {
                return (object) ['status' => 'error', 'message' => __('The order id not found.')];
            }

            if ($Session  == false) {
                $Cache = (object) Cache::get($request->order_id);
                Cache::forget($request->order_id);
            } else {
                $Cache = (object) Session::get($request->order_id);
                Session::forget($request->order_id);
            }

            if (is_null($Cache)) {
                return (object) ['appointment'  => 'failed', 'slug' => $slug, 'status'  => 'error', 'message' =>  __('The require data not found'),];
            }

            $business = (object) $Cache->business;
            $service  = (object) $Cache->service;

            if (!empty($business)) {
                try {
                    $request_data   = (array) $Cache;
                    $promo_code_id  = 0;
                    $final_amount   = $service->price;

                    if (module_is_active('PromoCodes') && array_key_exists('after_promo_price', $request_data) && array_key_exists('promo_code_id', $request_data)) {
                        $final_amount = $request_data['after_promo_price'];
                        $promo_code_id = $request_data['promo_code_id'];
                    }
                    if (module_is_active('ServiceTax') && array_key_exists('final_amount', $request_data)) {
                        $final_amount = $request_data['final_amount'];
                    }
                    if (module_is_active('FlexibleHours', $business->created_by) && isset($request_data['flexible_id'])) {
                        $flexible_hour = FlexibleHour::find($request_data['flexible_id']);
                        $final_amount = $flexible_hour->price;
                    }
                    if (module_is_active('PromoCodes', $business->created_by) && module_is_active('FlexibleHours') && isset($request_data['flexible_id'])) {
                        $final_amount = $flexible_hour->price;
                    }
                    if (module_is_active('FlexibleDuration', $business->created_by)) {
                        $service_duration_amount = isset($request_data['flexible_duration_amount']) ? $request_data['flexible_duration_amount'] : 0;
                    }

                    if (module_is_active('EasyDepositPayments', $business->created_by)) {
                        $admin_settings = getCompanyAllSetting($business->created_by, $business->id);
                        if (!isset($admin_settings['deposit_payment_setting'])) {
                            $admin_settings['deposit_payment_setting'] = 'only deposit payment';
                        }
                        if (!empty($service->deposit_amount) && $service->deposit_amount != null && $service->deposit_amount != 0) {
                            $depositAmount = $service->deposit_amount;
                            $deposit_amount = (($service->price * $depositAmount) / 100) - $service->price;
                            if ($admin_settings['deposit_payment_setting'] == 'default deposit payment') {
                                if ($request->request_data['deposit_payment_type'] == 'full_payment') {
                                    $final_amount = $service->price;
                                } else {
                                    $final_amount = $deposit_amount;
                                }
                            } else {
                                $final_amount = $deposit_amount;
                            }
                        }
                    }

                    if (module_is_active('Discount', $business->created_by) && module_is_active('PromoCodes', $business->created_by)) {
                        $final_amount = isset($request_data['after_promo_discount'])? $request_data['after_promo_discount'] : 0;
                    }

                    $appointment = $this->appointmentEntry([
                        'attachment'                => $request_data['attachment'] ?? '',
                        'service_price'             => module_is_active('FlexibleHours', $business->created_by) && !empty($flexible_hour) ? $flexible_hour->price : (module_is_active('EasyDepositPayments', $business->created_by) ? $final_amount : $service->price),
                        'service'                   => $service->id,
                        'staff'                     => $request_data['staff'] ?? '',
                        'location'                  => $request_data['location'] ?? '',
                        'appointment_date'          => $request_data['appointment_date'],
                        'duration'                  => $request_data['duration'],
                        'name'                      => $request_data['name'] ?? '',
                        'contact'                   => $request_data['contact'] ?? '',
                        'email'                     => $request_data['email'] ?? '',
                        'password'                  => $request_data['password'] ?? '',
                        'final_amount'              => $final_amount,
                        'tax_amount'                => $request_data['service_tax'] ?? 0,
                        'service_tax'               => $request_data['service_tax'] ?? 0,
                        'type'                      => $request_data['type'],
                        'payment_type'              => $request_data['payment'] ?? 'manually',
                        'promo_code_id'             => $promo_code_id ?? '',
                        'promocode'                 => $request_data['promocode'] ?? '',
                        'price_after_promo'         => $request_data['after_promo_price'] ?? 0,
                        'date'                      => $request_data['date'] ?? '',
                        'booked_slot'               => $request_data['booked_slot'] ?? '',
                        'sequential_services'       => $request_data['sequential_services'] ?? '',
                        'selectedServiceIds'        => $request_data['selectedServiceIds'] ?? '',
                        'selectedCartIds'           => $request_data['selectedCartIds'] ?? '',
                        'appointment_type'          => isset($request_data['selectedCartIds']) ? 'multiple' : 'single',
                        'person'                    => isset($request_data['person']) ?  $request_data['person'] : null,
                        'quantity'                  => isset($request_data['quantity']) ?  $request_data['quantity'] : 0,
                        'additional_service'        => isset($request_data['additional_service']) ? $request_data['additional_service'] : [],
                        'extra'                     => isset($request_data['extra']) ? $request_data['extra'] : [],
                        'additional_total_price'    => isset($request_data['additional_total_price']) ? $request_data['additional_total_price'] : 0,
                        'additional_service_price'  => isset($request_data['additional_service_price']) ? $request_data['additional_service_price'] : 0,
                        'business_id'               => $business->id ?? '',
                        'online_meeting'            => isset($request_data['online_meeting']) ?  $request_data['online_meeting'] : '',
                        'flexible_duration_amount'  => (module_is_active('FlexibleDuration', $business->created_by) && isset($request_data['flexible_duration_amount'])) ? $service_duration_amount : $service->price,
                        'deposit_payment_type'      => isset($request_data['deposit_payment_type']) ? $request_data['deposit_payment_type'] : '',
                        'service_after_promo'       => isset($request_data['service_after_promo']) ? $request_data['service_after_promo'] : 0,
                        'deposit_price'             => isset($request_data['deposit_price']) ? $request_data['deposit_price'] : '',
                        'after_promo_discount'      => isset($request_data['after_promo_discount']) ? $request_data['after_promo_discount'] : '',
                        'coupon_price'              => isset($request_data['coupon_price']) ? $request_data['coupon_price'] : '',
                        'flexible_id'               => isset($request_data['flexible_id']) ? $request_data['flexible_id'] : null,
                        'after_promo_price'         => isset($request_data['after_promo_price']) ? $request_data['after_promo_price'] : null,
                        'tax_amount_after_promo'    => isset($request_data['tax_amount_after_promo']) ? $request_data['tax_amount_after_promo'] : null,
                        'total_amount'              => isset($request_data['total_amount']) ? $request_data['total_amount'] : 0,
                        'discount_amount'           => isset($request_data['discount_amount']) ? $request_data['discount_amount'] : 0,
                        'total_appointment_count'   => isset($request_data['total_appointment_count']) ? $request_data['total_appointment_count'] : 0,
                    ], $slug);
                    return (object) ['appointment'  => $appointment, 'slug' => $slug, 'status'  => 'success', 'message' =>  __('The Payment has been added successfully.'),];
                } catch (\Exception $e) {
                    Log::debug($e->getMessage());
                    return (object) ['appointment'  => 'failed', 'slug' => $slug, 'status'  => 'error', 'message' =>  __('The transaction has been failed'), 'exception' => $e->getMessage()];
                }
            } else {
                return (object) ['appointment'  => 'failed', 'slug' => $slug, 'status'  => 'error', 'message' =>  __('The business not found'),];
            }
        } else {
            return (object) ['appointment'  => 'failed', 'slug' => $slug, 'status'  => 'error', 'message' =>  __('The transaction has been failed'),];
        }
    }

    public function appointmentEntry($data, $slug)
    {
        $business       = Business::where('slug', $slug)->first();
        $service        = Service::find($data['service']);
        $defaults       = resolveDefaultAppointmentStatus('online', getActiveBusiness(), $business->created_by);
        $default_status = $defaults['status_id'];

        if (module_is_active('CollaborativeServices', $business->created_by) && $service->service_type == 'collaborative') {
            $event = event(new StoreCollaborativeServices($data, 'appointmentpayment', $slug));
            return $event[0];
        }

        if (module_is_active('ShoppingCart', $business->created_by) && (isset($data['appointment_type']) && isset($data['appointment_type']) == 'multiple')) {
            $event = event(new ShoppinCartData($data, 'appointmentpayment', $slug));
            return $event[0];
        }
        if (module_is_active('CompoundService', $business->created_by) && $service->service_type == 'compound') {
            $event = event(new CreateCompoundBooking($data, 'appointmentpayment', $slug));
            return $event[0];
        }

        if (module_is_active('TeamBooking', $business->created_by) && isset($data['person'])) {
            $event = event(new CreateTeamBooking($data, $business));
            return $event[0];
        }

        if (module_is_active('BulkAppointments', $business->created_by) && isset($data['quantity']) && $data['quantity'] != 0) {
            $event = event(new CreateBulkAppointment($data, $business));
            return $event[0];
        }

        if (module_is_active('RepeatAppointments', $business->created_by)) {
            if (!empty($data['date']) && !empty($data['booked_slot'])) {
                $event = event(new RepeatAppointement($data, 'appointmentpayment', $slug));
                return $event[0];
            }
        }
        if (module_is_active('SequentialAppointment', $business->created_by) && $data['sequential_services']) {
            $event = event(new SequentialAppointment($data, 'appointmentpayment', $slug));
            return $event[0];
        }

        if ($data['type'] == 'new-user') {
            $roles = Role::where('name', 'customer')->where('created_by', $business->created_by)->first();
            if ($roles) {
                $user = User::create([
                    'name'              => !empty($data['name']) ? $data['name'] : null,
                    'email'             => !empty($data['email']) ? $data['email'] : null,
                    'mobile_no'         => !empty($data['contact']) ? $data['contact'] : null,
                    'email_verified_at' => date('Y-m-d h:i:s'),
                    'password'          => !empty($data['password']) ? Hash::make($data['password']) : null,
                    'avatar'            => 'uploads/users-avatar/avatar.png',
                    'type'              => 'customer',
                    'lang'              => 'en',
                    'business_id'       => $business->id,
                    'created_by'        => $business->created_by,
                ]);
                $user->addRole($roles);

                $customer               = new Customer();
                $customer->name         = $data['name'];
                $customer->user_id      = $user->id;
                $customer->gender       = !empty($data['gender']) ? $data['gender'] : '';
                $customer->dob          = !empty($data['dob']) ? $data['dob'] : '';
                $customer->description  = !empty($data['description']) ? $data['description'] : '';
                $customer->business_id  = $user->business_id;
                $customer->created_by   = $user->created_by;
                $customer->save();
            }
        }

        if ($data['type'] == 'existing-user') {
            $email  = $data['email'];
            $user   = User::where('email', $email)->where('type', 'customer')->first();

            if (!empty($data['password']) && !empty($user)) {
                $check_password = Hash::check($data['password'], $user->password);
                if ($check_password) {
                    $customer = Customer::where('user_id', $user->id)->first();
                } else {
                    return 'failed';
                }
            } else {
                return 'failed';
            }
        }


        $Appointment  = new Appointment();
        if ($data['type'] == 'new-user' || $data['type'] == 'existing-user') {
            $Appointment->customer_id      = !empty($customer) ? $customer->user_id : null;
        } else {
            $Appointment->customer_id      = !empty($data['customer']) ? $data['customer'] : null;
        }

        if ($data['type'] == 'guest-user') {
            $Appointment->name       = $data['name'];
            $Appointment->email      = $data['email'];
            $Appointment->contact    = $data['contact'];
        }


        $Appointment->location_id           = $data['location'];
        $Appointment->service_id            = $data['service'];
        $Appointment->staff_id              = $data['staff'];
        $Appointment->date                  = !empty($data['appointment_date']) ? $data['appointment_date'] : '';
        $Appointment->time                  = !empty($data['duration']) ? $data['duration'] : '';

        $windowError = validateAppointmentWindow($business->id, $business->created_by, $Appointment->staff_id, $Appointment->date, $Appointment->time);
        if (!empty($windowError)) {
            return 'failed';
        }

        $Appointment->notes                 = !empty($data['notes']) ? $data['notes'] : '';
        $Appointment->payment_type          = !empty($data['payment_type']) ? $data['payment_type'] : 'Manually';
        $Appointment->appointment_status    = !empty($default_status) ? $default_status : 'Pending';
        $Appointment->attachment            = !empty($data['attachment']) ? $data['attachment'] : null;
        $Appointment->custom_field          = !empty($data['values']) ? json_encode($data['values']) : null;
        $Appointment->business_id           = $business->id;
        $Appointment->created_by            = $business->created_by;
        $Appointment->save();

        $service = Service::find($data['service']);

        $payment = AppointmentPayment::create([
            'appointment_id'    => $Appointment->id,
            'payment_type'      => $Appointment->payment_type,
            'amount'            => $data['service_price'],
            'payment_date'      => now(),
            'business_id'       => $business->id,
            'created_by'        => $business->created_by,
        ]);

        event(new AppointmentPaymentData($data, $payment, $service));
        event(new CreateAppoinment($Appointment, $data));

        if (module_is_active('AdditionalServices', $business->created_by) && isset($data['additional_service']) && $data['additional_service'] !== [] && isset($data['additional_service_price'])) {
            event(new AdditionalServicePayment($data, $payment, $service));
        }

        $appointment_number = Appointment::appointmentNumberFormat($Appointment->id, $business->created_by, $business->id);

        $company_settings   = getCompanyAllSetting($Appointment->created_by, $Appointment->business_id);
        $customCss          = isset($company_settings['custom_css']) ? $company_settings['custom_css'] : null;
        $customJs           = isset($company_settings['custom_js']) ? $company_settings['custom_js'] : null;
        //Email notification
        $emailTemplate = $defaults['template_name'];
        if (!empty($emailTemplate) && (!empty($company_settings[$emailTemplate]) && $company_settings[$emailTemplate]  == true)) {
            $trackingUrl = route('find.appointment', ['businessSlug' => $business->slug]);
            $uArr = [
                'business_name'         => $business->name,
                'service'               => $Appointment->ServiceData ? $Appointment->ServiceData->name : '-',
                'location'              => $Appointment->LocationData ? $Appointment->LocationData->name : '-',
                'staff'                 => $Appointment->StaffData->user ? $Appointment->StaffData->user->name : '-',
                'appointment_date'      => $Appointment->date,
                'appointment_time'      => $Appointment->time,
                'appointment_number'    => $appointment_number,
                'tracking_url'          => $trackingUrl,
            ];
            $resp = EmailTemplate::sendEmailTemplate($emailTemplate, [$Appointment->CustomerData ? $Appointment->CustomerData->customer->email : $Appointment->email], $uArr, $Appointment->created_by, $business->id);
        }

        return $Appointment->id;
    }
}
