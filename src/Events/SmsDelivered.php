<?php

namespace AdaReach\Sms\Events;

class SmsDelivered extends SmsEvent
{
    public function __construct(array $smsData)
    {
        parent::__construct('delivered', $smsData);
    }
}
