<?php

namespace App\Http\Controllers;

use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ThemeSetting $themeSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ThemeSetting $themeSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThemeSetting $themeSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ThemeSetting $themeSetting)
    {
        //
    }

    public function themeCustomize(Request $request,$id,$businessID)
    {
        if(Auth::user()->isAbleTo('theme manage'))
        {

            $path           = base_path('packages/workdo/' . $id . '/theme-setting.json');
            $theme_json     = json_decode(file_get_contents($path), true);
            return view('theme_customize.index',compact('id','theme_json','businessID'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customize_theme($id, $slug, $sub_slug, $businessID)
    {
        if(Auth::user()->isAbleTo('theme edit'))
        {
            if (!empty($id)) {
                if ($slug) {
                    $path           = base_path('packages/workdo/' . $id . '/theme-setting.json');
                    $theme_json     = json_decode(file_get_contents($path), true);

                    $themeSetting   = ThemeSetting::where('business_id',$businessID)->where('theme', $id)->pluck('value', 'key');
                    return view('theme_customize.edit', compact('id', 'slug', 'sub_slug', 'theme_json', 'themeSetting','businessID'));
                } else {
                    return abort(404);
                }
            } else {
                return abort(404);
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function customize_theme_update($businessID, $id, Request $request)
    {
        if(Auth::user()->isAbleTo('theme edit'))
        {
            $data = $request->except('_token');
            if (isset($data['banner_repeater'])) {
                foreach ($data['banner_repeater'] as $key => $value) {
                    if(!empty($value['image']) && gettype($value['image']) == 'object')
                    {
                        $filenameWithExt = $value['image']->getClientOriginalName();
                        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension       = $value['image']->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                        $uplaod = upload_file($value,'image',$fileNameToStore,$id);
                        if($uplaod['flag'] == 1)
                        {
                            $url = $uplaod['url'];
                            $data['banner_repeater'][$key]['image'] = $url;
                        }
                        else
                        {
                            return redirect()->back()->with('error', $uplaod['msg']);
                        }
                    }
                }
                $data['banner_repeater'] = json_encode($data['banner_repeater']);
            }


            if (isset($data['brand_carousel_repeater'])) {
                foreach ($data['brand_carousel_repeater'] as $k => $v) {
                    if(!empty($v['image']) && gettype($v['image']) == 'object')
                    {
                        $filenameWithExt = $v['image']->getClientOriginalName();
                        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension       = $v['image']->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                        $uplaod = upload_file($v,'image',$fileNameToStore,$id);
                        if($uplaod['flag'] == 1)
                        {
                            $url = $uplaod['url'];
                            $data['brand_carousel_repeater'][$k]['image'] = $url;
                        }
                        else
                        {
                            return redirect()->back()->with('error', $uplaod['msg']);
                        }
                    }
                }
                $data['brand_carousel_repeater'] = json_encode($data['brand_carousel_repeater']);
            }

            if (isset($data['portfolio_repeater'])) {
                foreach ($data['portfolio_repeater'] as $k => $v) {
                    if(!empty($v['image']) && gettype($v['image']) == 'object')
                    {
                        $filenameWithExt = $v['image']->getClientOriginalName();
                        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension       = $v['image']->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                        $uplaod = upload_file($v,'image',$fileNameToStore,$id);
                        if($uplaod['flag'] == 1)
                        {
                            $url = $uplaod['url'];
                            $data['portfolio_repeater'][$k]['image'] = $url;
                        }
                        else
                        {
                            return redirect()->back()->with('error', $uplaod['msg']);
                        }
                    }
                }
                $data['portfolio_repeater'] = json_encode($data['portfolio_repeater']);
            }

            if (isset($data['gallery_carousel_repeater'])) {
                foreach ($data['gallery_carousel_repeater'] as $k => $v) {
                    if(!empty($v['image']) && gettype($v['image']) == 'object')
                    {
                        $filenameWithExt = $v['image']->getClientOriginalName();
                        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension       = $v['image']->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                        $uplaod = upload_file($v,'image',$fileNameToStore,$id);
                        if($uplaod['flag'] == 1)
                        {
                            $url = $uplaod['url'];
                            $data['gallery_carousel_repeater'][$k]['image'] = $url;
                        }
                        else
                        {
                            return redirect()->back()->with('error', $uplaod['msg']);
                        }
                    }
                }
                $data['gallery_carousel_repeater'] = json_encode($data['gallery_carousel_repeater']);
            }

            if (isset($data['banner-carousel_repeater'])) {
                foreach ($data['banner-carousel_repeater'] as $k => $v) {
                    if (!empty($v['image']) && gettype($v['image']) == 'object') {
                        $filenameWithExt = $v['image']->getClientOriginalName();
                        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension       = $v['image']->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                        $uplaod = upload_file($v, 'image', $fileNameToStore, $id);
                        if ($uplaod['flag'] == 1) {
                            $url = $uplaod['url'];
                            $data['banner-carousel_repeater'][$k]['image'] = $url;
                        } else {
                            return response()->json(['msg' => 'error', 'error' => $uplaod['msg']]);
                        }
                    }
                }
                $data['banner-carousel_repeater'] = json_encode($data['banner-carousel_repeater']);
            }

            if (isset($data['about_repeater'])) {
                foreach ($data['about_repeater'] as $key => $value) {
                    if(!empty($value['image']) && gettype($value['image']) == 'object')
                    {
                        $filenameWithExt = $value['image']->getClientOriginalName();
                        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension       = $value['image']->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                        $uplaod = upload_file($value,'image',$fileNameToStore,$id);
                        if($uplaod['flag'] == 1)
                        {
                            $url = $uplaod['url'];
                            $data['about_repeater'][$key]['image'] = $url;
                        }
                        else
                        {
                            return redirect()->back()->with('error', $uplaod['msg']);
                        }
                    }
                }
                $data['about_repeater'] = json_encode($data['about_repeater']);
            }

            $image_data = $request->slug.'_image' ;
            if(isset($request->$image_data))
            {
                $originalName    = $request->$image_data->getClientOriginalName();
                $filenameWithExt = str_replace(' ', '_', $originalName);
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->$image_data->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request,$image_data,$fileNameToStore,$id);
                if($uplaod['flag'] == 1)
                {
                    $url = $uplaod['url'];
                    $data[$image_data] = $url;
                }
                else
                {
                    return redirect()->back()->with('error', $uplaod['msg']);
                }
            }

            foreach ($data as $key => $value) {
                ThemeSetting::updateOrCreate(['theme' => $id, 'key' => $key, 'business_id' =>$businessID, 'created_by' => creatorId()] , ['value' => $value]);
            }
            return redirect()->back()->with('success', __('Setting Saved Successfully.'));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function imageFileGet(Request $request)
    {
        if ($request->has('imgSrc'))
        {
            $imgSrc = $request->input('imgSrc');
            $imgUrl = get_file($imgSrc);
            return response()->json($imgUrl);
        }
        else
        {
            return response()->json(['error' => 'imgSrc parameter missing'], 400);
        }
    }




}
