<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestimonialController extends Controller
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
        if(Auth::user()->isAbleTo('testimonial create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'customer_id' => 'required',
                    'description' => 'required',
                    'theme' => 'required',
                    'businessID' => 'required',
                    'testimonial_image' => 'required',
                    ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $testimonial                          = new Testimonial();
                $testimonial->customer_id             = $request->customer_id;
                $testimonial->description             = $request->description;
                $testimonial->theme                   = $request->theme;
                $testimonial->business_id             = $request->businessID;
                $testimonial->created_by              = creatorId();

                if ($request->hasFile('testimonial_image'))
                {
                    $filenameWithExt = $request->file('testimonial_image')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('testimonial_image')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $uplaod = upload_file($request,'testimonial_image',$fileNameToStore,$request->theme);
                    if($uplaod['flag'] == 1)
                    {
                        $url = $uplaod['url'];
                    }
                    else
                    {
                        return redirect()->back()->with('error',$uplaod['msg']);
                    }
                }
                $testimonial->image                   = !empty($request->testimonial_image) ? $url : '';
                $testimonial->save();

                return redirect()->back()->with('success', __('Testimonial successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimonial $testimonial)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testimonial $testimonial)
    {
        if(Auth::user()->isAbleTo('testimonial edit'))
        {
            $customers = Customer::where('business_id',getActiveBusiness())->where('created_by',creatorId())->pluck('name','id')->prepend('Select Customer', '');

            return view('testimonial.edit',compact('testimonial','customers'));
        }
        else
        {
           return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        if(Auth::user()->isAbleTo('testimonial edit'))
        {

            $validator = \Validator::make(
                $request->all(), [
                    'customer_id' => 'required',
                    'description' => 'required',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $testimonial->customer_id = $request->customer_id;
            $testimonial->description = $request->description;

            if ($request->hasFile('testimonial_image'))
            {
                $filenameWithExt = $request->file('testimonial_image')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('testimonial_image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request,'testimonial_image',$fileNameToStore,$testimonial->theme);

                if($uplaod['flag'] == 1)
                {
                    if (!empty($testimonial->image)) {
                        delete_file($testimonial->image);
                    }
                    $url = $uplaod['url'];
                }
                else
                {
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
                $testimonial->image  = !empty($request->testimonial_image) ? $url : '';
            }

            $testimonial->save();

            return redirect()->back()->with('success', __('Testimonial updated successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        if(Auth::user()->isAbleTo('testimonial delete'))
        {
            if(!empty($testimonial->image))
            {
                delete_file($testimonial->image);
            }
            $testimonial->delete();
            return redirect()->back()->with('error', __('Testimonial successfully delete.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function testimonialManage(Request $request,$id, $businessID)
    {
        if(Auth::user()->isAbleTo('testimonial manage'))
        {
            $testimonials = Testimonial::where('business_id',$businessID)->where('created_by',creatorId())->where('theme',$id)->get();
            return view('testimonial.index',compact('id','testimonials','businessID'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function testimonialCreate(Request $request,$id, $businessID)
    {
        $customers = Customer::where('business_id',$businessID)->where('created_by',creatorId())->pluck('name','id')->prepend('Select Customer', '');

        return view('testimonial.create',compact('id','customers','businessID'));
    }


}

