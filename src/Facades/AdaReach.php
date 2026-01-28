<?php

namespace AdaReach\Sms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \AdaReach\Sms\SmsBuilder from(string $sender)
 * @method static \AdaReach\Sms\SmsBuilder to(string $receiver)
 * @method static \AdaReach\Sms\SmsBuilder toMany(array $receivers)
 * @method static \AdaReach\Sms\SmsBuilder content(string $content)
 * @method static \AdaReach\Sms\SmsBuilder transactional()
 * @method static \AdaReach\Sms\SmsBuilder promotional()
 * @method static array generateToken()
 * @method static array refreshToken()
 * @method static array sendSms(array $params)
 * @method static array checkStatus(string $sender, string $messageId, string $receiver)
 * @method static array checkBalance()
 * @method static void clearTokenCache()
 *
 * @see \AdaReach\Sms\AdaReachClient
 */
class AdaReach extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'adarearch';
    }

    /**
     * Create a new SMS builder instance
     */
    public static function message(): \AdaReach\Sms\SmsBuilder
    {
        return new \AdaReach\Sms\SmsBuilder(static::getFacadeRoot());
    }
}
