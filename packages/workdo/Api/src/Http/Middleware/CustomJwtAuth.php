<?php

namespace Workdo\Api\Http\Middleware;

use App\Models\Business;
use App\Models\WorkSpace;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CustomJwtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $module_status = module_is_active('Api',$user->id);
            if($module_status != true)
            {
                return response()->json(['status' => 'error', 'message' => 'Your Add-on Is Not Activated!'], 401);
            }

            $slug = $request->route()->parameter('slug');
            if($slug){
                $business = Business::where('slug',$slug)->first();
                if(!$business){
                    return response()->json(['status'=>"error","message"=>"Slug Not Found!"],404);
                }
                $request['business_id'] = $business->id;
            }
            $request->route()->forgetParameter('slug');

        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token has expired'], 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token is invalid'], 401);
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token is absent'], 401);
        }

        return $next($request);

    }
}
