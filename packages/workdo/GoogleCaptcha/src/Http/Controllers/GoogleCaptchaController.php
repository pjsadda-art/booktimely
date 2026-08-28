<?php

namespace Workdo\GoogleCaptcha\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class GoogleCaptchaController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('google-captcha::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('google-captcha::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('google-captcha::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('google-captcha::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function setting(Request $request)
    {
        if(Auth::user()->isAbleTo('recaptcha manage'))
        {

            $getActiveBusiness = getActiveBusiness();
            $creatorId = creatorId();
            if($request->has('google_recaptcha_is_on'))
            {
                $validator = \Validator::make($request->all(), [
                    'google_recaptcha_key' => 'required|string',
                    'google_recaptcha_secret' => 'required|string',
                    'google_recaptcha_version' => 'required'
                ]);
                if($validator->fails()){
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $post = $request->all();
                unset($post['_token']);
                foreach ($post as $key => $value) {
                    // Define the data to be updated or inserted
                    $data = [
                        'key' => $key,
                        'business' => $getActiveBusiness,
                        'created_by' => $creatorId,
                    ];

                    // Check if the record exists, and update or insert accordingly
                    Setting::updateOrInsert($data, ['value' => $value]);
                }
            }else{
                $data = [
                    'key' => 'google_recaptcha_is_on',
                    'business' => $getActiveBusiness,
                    'created_by' => $creatorId,
                ];
                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => 'off']);
            }
            // Settings Cache forget
            AdminSettingCacheForget();
            comapnySettingCacheForget();
            return redirect()->back()->with('success','ReCaptcha Setting save sucessfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
