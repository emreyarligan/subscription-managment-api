<?php

namespace App\Http\Controllers;
use App\Models\Device;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function register(RegisterRequest $request)
    {
        return Device::updateOrCreate(
            [
                'device_uuid' => $request->device_uuid
            ],
            [
                'app_id'        => $request->app_id,
                'language'      => $request->language,
                'os'            => $request->os,
                'client_token'  => Str::uuid()->toString()
            ]
        );
    }
}
