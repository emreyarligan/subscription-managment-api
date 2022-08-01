<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriptions;
use App\Jobs\SubscriptionPolling;
use Illuminate\Http\Exceptions\HttpResponseException;

class WorkerController extends Controller
{
    public function prepareQueue()
    {
        $subscriptions = Subscriptions::where(
            'status', true,
        )->where(
            'expire_date', '<=', date('Y-m-d H:i:s',time())
        )->get()->toArray();

        if ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                SubscriptionPolling::dispatch($subscription['client_token'])->onQueue('subscriptionPolling');
            }

            return response()->json([
                'success'   => true,
                'message'   => count($subscriptions).' data added to the subscriptionPolling quene',
            ]);
        }


        return response()->json([
            'success'   => false,
            'message'   => 'There is no active and expired subscription',
        ]);
        
    }
}
