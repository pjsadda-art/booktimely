<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
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
        if(Auth::user()->isAbleTo('blog create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'date' => 'required',
                    'description' => 'required',
                    'theme' => 'required',
                    'businessID' => 'required',
                    'blog_image' => 'required',
                    ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $blog                          = new Blog();
                $blog->title                   = $request->title;
                $blog->description             = $request->description;
                $blog->date                    = $request->date;
                $blog->theme                   = $request->theme;
                $blog->business_id             = $request->businessID;
                $blog->created_by              = creatorId();

                if ($request->hasFile('blog_image'))
                {
                    $filenameWithExt = $request->file('blog_image')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('blog_image')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $uplaod = upload_file($request,'blog_image',$fileNameToStore,$request->theme);
                    if($uplaod['flag'] == 1)
                    {
                        $url = $uplaod['url'];
                    }
                    else
                    {
                        return redirect()->back()->with('error',$uplaod['msg']);
                    }
                }
                $blog->image                   = !empty($request->blog_image) ? $url : '';
                $blog->save();

                return redirect()->back()->with('success', __('Blog successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        if(Auth::user()->isAbleTo('blog edit'))
        {
            return view('blog.edit',compact('blog'));
        }
        else
        {
           return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        if(Auth::user()->isAbleTo('blog edit'))
        {

            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'date' => 'required',
                    'description' => 'required',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $blog->title = $request->title;
            $blog->date = $request->date;
            $blog->description = $request->description;

            if ($request->hasFile('blog_image'))
            {
                $filenameWithExt = $request->file('blog_image')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('blog_image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request,'blog_image',$fileNameToStore,$blog->theme);

                if($uplaod['flag'] == 1)
                {
                    $url = $uplaod['url'];
                    if (!empty($blog->image)) {
                        delete_file($blog->image);
                    }
                }
                else
                {
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
                $blog->image  = !empty($request->blog_image) ? $url : '';
            }

            $blog->save();

            return redirect()->back()->with('success', __('Blog updated successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        if(Auth::user()->isAbleTo('blog delete'))
        {
            if(!empty($blog->image))
            {
                delete_file($blog->image);
            }
            $blog->delete();
            return redirect()->back()->with('error', __('Blog successfully delete.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function blogManage(Request $request,$id, $businessID)
    {
        if(Auth::user()->isAbleTo('blog manage'))
        {
            $blogs = Blog::where('business_id',$businessID)->where('created_by',creatorId())->where('theme',$id)->get();
            return view('blog.index',compact('id','blogs', 'businessID'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function blogCreate(Request $request,$id, $businessID)
    {
        return view('blog.create',compact('id','businessID'));
    }


}
