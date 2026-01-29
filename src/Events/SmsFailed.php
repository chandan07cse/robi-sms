<?php

namespace AdaReach\Sms\Events;

class SmsFailed extends SmsEvent
{
    public function __construct(array $smsData)
    {
        parent::__construct('failed', $smsData);
    }
}
