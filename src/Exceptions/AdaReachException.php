<?php

namespace AdaReach\Sms\Exceptions;

use Exception;

class AdaReachException extends Exception
{
    protected array $errorCodes = [
        1501 => 'Invalid Token - Authorization token is invalid or missing',
        1502 => 'Exceed Capacity - TPS (transaction per second) limit exceeded',
        1503 => 'Sender Not Allowed - Masking not configured for your account',
        1504 => 'Invalid Parameter - One or more API parameters are invalid',
        1505 => 'Missing Parameter - Required field is missing',
        1506 => 'Insufficient Balance - Not enough balance to send SMS',
        1508 => 'API Auth Fail - Invalid authentication credentials',
        1509 => 'New API Not Allowed - Account not allowed to use the new adaReach API',
        1510 => 'New API Other Error - General error not covered by other codes',
        1512 => 'System Error - Remote node down',
        1513 => 'Wrong Content Type - Unicode message sent with wrong contentType',
        1514 => 'MSISDN Limit Exceeded - Bulk request exceeded max 400 MSISDNs',
        1201 => 'Data API Not Allowed - Data API access not enabled',
        1202 => 'Receiver Number Not Valid - Wrong Receiver Number',
        1203 => 'Provision Pack Not Allowed - No provision pack available',
        1204 => 'Provision Pack Invalid - Invalid provision pack',
        1001 => 'International Not Allowed - International SMS not allowed',
        1002 => 'SMS Other Error - SMSC submission error',
    ];

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        if ($code && isset($this->errorCodes[$code])) {
            $message = $this->errorCodes[$code] . ($message ? " - {$message}" : '');
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get user-friendly error message
     */
    public function getUserMessage(): string
    {
        return $this->errorCodes[$this->code] ?? $this->message;
    }
}
