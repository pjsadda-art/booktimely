<?php

namespace Workdo\ServiceTax\Http\Controllers\Company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::where('business', getActiveBusiness())
            ->where('created_by', creatorId())
            ->whereIn('key', ['service_tax_option'])
            ->first();
            
        return view('service-tax::servicetax.company.settings.index', compact('settings'));
    }

    public function settingSave(Request $request)
    {
        if ($request->has('service_tax_option')) {
            $settings = [
                'service_tax_option' => $request->service_tax_option,
            ];
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    [
                        'business' => getActiveBusiness(),
                        'created_by' => creatorId(),
                        'key' => $key
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Service Tax settings saved successfully.'));
    }

}