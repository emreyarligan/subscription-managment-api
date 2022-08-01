<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscriptions;
use App\Models\PurchaseHistory;
use App\Helpers\PurchaseHelper;
use App\Helpers\EventLoggingHelper;


class SubscriptionPolling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $client_token;

    public function __construct($client_token)
    {
        $this->client_token = $client_token;
    }


    public function handle()
    {
        $lastReceiptId = PurchaseHistory::where(['client_token' => $this->client_token])->latest()->first()->receipt_id;
        $checkRateLimits = PurchaseHelper::checkLastTwoDigitsDivisibleBySix($lastReceiptId);

        if ($checkRateLimits) {

            $date = new \DateTime('+1 month', new \DateTimeZone('GMT-6'));
            $expireDate = $date->format('Y-m-d H:i:s');
            Subscriptions::where('client_token',$this->client_token)->update(['expire_date' => $expireDate]);

            EventLoggingHelper::setLog('subscription_updated_by_worker',$this->client_token,$expireDate);

        }

    }
}
