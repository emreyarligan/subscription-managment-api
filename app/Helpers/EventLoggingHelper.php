<?php 

namespace App\Helpers;
use App\Models\Logs;

class EventLoggingHelper
{
    public static function setLog(string $eventType, string $clientToken, string $expireDate = null) : void
    {
        Logs::create(
            [
                'event_type' => $eventType,
                'client_token' => $clientToken,
                'expire_date' => $expireDate
            ]
        );
    }
}