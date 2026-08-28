<?php

namespace Workdo\CollaborativeServices\Http\Controllers;

use App\Models\Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\CollaborativeServices\Entities\CollaborativeServiceUtility;

class CollaborativeServicesController extends Controller
{

    public function checkCollaborativeService(Request $request)
    {
        $service = Service::find($request->service);
        
        $collaborative_service = 0;

        if ($service) {
            if ($service->service_type != null && $service->collaborative_service_id != null && $service->service_type == 'collaborative') {
                $collaborative_service = 1;
                return response()->json(['collaborative_service' => $collaborative_service, 'price' => $service->price]);
            } 
        }

        return response()->json(['collaborative_service' => $collaborative_service]);
    }

}
