<?php

namespace App\Http\Controllers;
use App\Models\Device;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function register(RegisterRequest $request)
    {
        Device::upsert(
            [
                    'device_uuid'   => $request->device_uuid,
                    'app_id'        => $request->app_id,
                    'language'      => $request->language,
                    'os'            => $request->os,
                    'client_token'  => Str::uuid()->toString()
            ],
            ['device_uuid'],
            ['app_id','language','os'] // don't update client_token if there is a record with this device_uuid...
        );

        // if we used updateOrCreate method for upsert we wouldn't use this where query because updateOrCreate gives the row...
        // But upsert method returns boolean and we have to use it because we don't want update client_token...

        return Device::where('device_uuid', $request->device_uuid)->first();

    }
}
