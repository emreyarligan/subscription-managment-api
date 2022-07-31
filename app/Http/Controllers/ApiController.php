<?php

namespace App\Http\Controllers;
use App\Models\Device;
use App\Models\PurchaseHistory;
use App\Models\Subscriptions;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\CheckSubscriptionStatusRequest;
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

        $mockResponse = self::mockValidation($request->clientToken,$request->receiptId);
        $checkPurchaseHistory =  PurchaseHistory::where(['client_token' => $request->clientToken,'receipt_id' => $request->receiptId])->first();

        // if client_token and receipt_id are used, the subscription will not be updated. receipt_id must be different...
        if (!$checkPurchaseHistory) { 
            Subscriptions::updateOrCreate(
                [
                    'client_token' => $request->clientToken,
                ],
                [
                    'status'        => true,
                    'expire_date'   => $mockResponse['expire-date'],
                ],
            );
        }

        return PurchaseHistory::firstOrCreate(
            [
                'client_token' => $request->clientToken,
                'receipt_id' => $request->receiptId,
            ],
            [
                'expire_date' => $mockResponse['expire-date'],
                'api_result' => json_encode($mockResponse)
            ],
        );

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

    private function mockValidation($clientToken,$receiptId)
    {
        $providerMatch = [
            'ios'       => 'checkIOSMock',
            'android'   => 'checkGoogleMock'
        ];

        $deviceOS = Device::where('client_token',$clientToken)->first()->os;

        $mockFunctionName = $providerMatch[$deviceOS];

        $mockResponse = PurchaseHelper::{$mockFunctionName}(md5($receiptId));

        if(!$mockResponse['status']) {
            throw new HttpResponseException(response()->json([
                'success'   => false,
                'message'   => 'Last digit of hashed data must be odd',
                'data'      => [
                    'receiptId'         => $receiptId,
                    'hashedReceiptId'   => md5($receiptId)
                ]
            ]));
        }

        return $mockResponse;

    }

    public function checkSubscriptionStatus(CheckSubscriptionStatusRequest $request)
    {
        $json = [];

        $subscription = Subscriptions::where('client_token',$request->clientToken)->first();

        if ($subscription) { // if the subscription is not cancelled, check the expire date

            if ($subscription->status) {

                $active = strtotime($subscription->expire_date) > time();

                $json = [
                    'subscriptionStatus'    => $active,
                    'message'               => (($active) ? 'Subscription is active until ' : 'subscription expired on ').$subscription->expire_date,
                    'expire_date'           => $subscription->expire_date,
                    'expire_date_unixtime'  => strtotime($subscription->expire_date),
                ];

            } else { // if the subscription is cancelled

                $json = [
                    'subscriptionStatus'            => false,
                    'message'                       => 'Subscription is cancelled on '.$subscription->expire_date,
                    'cancellation_date'             => $subscription->cancellation_date,
                    'cancellation_date_unixtime'    => strtotime($subscription->cancellation_date),
                ];

            }
            
        } else {
            $json = [
                'subscriptionStatus'    => false,
                'message'               => 'No subscription found with this Client Token',
            ];
        }

        throw new HttpResponseException(response()->json($json));
    }

}
