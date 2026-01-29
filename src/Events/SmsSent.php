<?php

namespace AdaReach\Sms\Events;

class SmsSent extends SmsEvent
{
    public function __construct(array $smsData)
    {
        parent::__construct('sent', $smsData);
    }
}
