<?php

namespace Workdo\CompoundService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CompoundServiceController extends Controller
{

    public function index()
    {
        return view('compound-service::index');
    }


    public function create()
    {
        return view('compound-service::create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return view('compound-service::show');
    }

    public function edit($id)
    {
        return view('compound-service::edit');
    }


    public function update(Request $request, $id)
    {
        //
    }

 
    public function destroy($id)
    {
        //
    }
}
