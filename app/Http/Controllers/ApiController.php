<?php

namespace App\Http\Controllers;
use App\Models\Device;
use App\Models\Subscription;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\PurchaseRequest;
use App\Helpers\PurchaseHelper;
use Illuminate\Http\Exceptions\HttpResponseException;
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

    public function purchase(PurchaseRequest $request)
    {
        self::checkClientTokenExists($request->clientToken);

        $mockResponse = PurchaseHelper::checkGoogleMock(md5($request->receiptId));

        if ($mockResponse['status']) {
            
            return Subscription::updateOrCreate(
                ['receipt_id' => $request->receiptId, 'client_token' => $request->clientToken],
                ['expire_date' => $mockResponse['expire-date'],'api_result' => json_encode($mockResponse)]
            );

        } else {

            throw new HttpResponseException(response()->json([
                'success'   => false,
                'message'   => 'Last digit of hashed data must be odd',
                'data'      => [
                    'receiptId'         => $request->receiptId,
                    'hashedReceiptId'   => md5($request->receiptId)
                ]
            ]));

        }

    }

    private function checkClientTokenExists($clientToken)
    {
        $checkClientTokenExist = (Device::where('client_token',$clientToken)->first());

        if (!$checkClientTokenExist) {
            throw new HttpResponseException(response()->json([
                'success'   => false,
                'message'   => 'This Client Token does not exists in Devices table',
                'data'      => [
                    'clientToken'  => $clientToken,
                ]
            ]));
        }

        return true;

    }

}
