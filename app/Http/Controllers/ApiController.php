<?php

namespace App\Http\Controllers;
use App\Models\Device;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function register(Request $request)
    {
        Device::updateOrCreate(
            [
                'device_uuid' => $request->device_uuid
            ],
            [
                'app_id'    => $request->app_id,
                'language'  => $request->language,
                'os'        => $request->os
            ]
        );
    }
}
