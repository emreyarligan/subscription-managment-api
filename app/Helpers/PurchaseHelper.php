<?php 

namespace App\Helpers;

class PurchaseHelper
{
    public static function checkGoogleMock(string $hashedReceiptId) : array
    {
        return self::isLastDigitOdd($hashedReceiptId);
    }

    public static function checkIOSMock(string $hashedReceiptId) : array
    {
        return self::isLastDigitOdd($hashedReceiptId);
    }

    public static function isLastDigitOdd(string $hashedReceiptId) : array
    {
        $lastChar = substr($hashedReceiptId, -1);
        $isOdd = (is_numeric($lastChar) and (!$lastChar % 2 == 0));

        $responseInf = [
            'status' => $isOdd
        ];

        if ($isOdd) {

            $date = new \DateTime('+1 month', new \DateTimeZone('GMT-6'));

            array_push(
                $responseInf,
                [
                    'expire-date' =>$date->format('Y-m-d H:i:s')
                ]
            );
        }

        return $responseInf;
    }

}
