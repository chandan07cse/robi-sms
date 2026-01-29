<?php

namespace AdaReach\Sms;

use AdaReach\Sms\Exceptions\AdaReachException;

class SmsBuilder
{
    protected AdaReachClient $client;
    protected ?string $sender = null;
    protected array $receivers = [];
    protected ?string $content = null;
    protected string $msgType = 'T'; // T = Transactional, P = Promotional
    protected string $requestType = 'S'; // S = Single, B = Bulk
    protected int $contentType = 1; // 1 = Regular, 2 = Unicode

    public function __construct(AdaReachClient $client)
    {
        $this->client = $client;
    }

    /**
     * Set the sender ID
     */
    public function from(string $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * Set single receiver
     */
    public function to(string $receiver): self
    {
        $this->receivers = [$this->formatMsisdn($receiver)];
        $this->requestType = 'S';
        return $this;
    }

    /**
     * Set multiple receivers (bulk)
     */
    public function toMany(array $receivers): self
    {
        if (count($receivers) > 400) {
            throw new AdaReachException('Bulk request cannot exceed 400 MSISDNs', 1514);
        }

        $this->receivers = array_map([$this, 'formatMsisdn'], $receivers);
        $this->requestType = 'B';
        return $this;
    }

    /**
     * Set message content
     */
    public function content(string $content): self
    {
        $this->content = $content;
        
        // Auto-detect if content needs Unicode
        if ($this->needsUnicode($content)) {
            $this->contentType = 2;
        }
        
        return $this;
    }

    /**
     * Set as transactional message
     */
    public function transactional(): self
    {
        $this->msgType = 'T';
        return $this;
    }

    /**
     * Set as promotional message
     */
    public function promotional(): self
    {
        $this->msgType = 'P';
        $this->contentType = 2; // Promotional messages require Unicode
        return $this;
    }

    /**
     * Set content type explicitly
     */
    public function contentType(int $type): self
    {
        if (!in_array($type, [1, 2])) {
            throw new AdaReachException('Content type must be 1 (Regular) or 2 (Unicode)');
        }
        
        $this->contentType = $type;
        return $this;
    }

    /**
     * Send the SMS
     */
    public function send(): array
    {
        $this->validate();

        $params = [
            'sender' => $this->sender,
            'receiver' => $this->receivers,
            'content' => $this->content,
            'msgType' => $this->msgType,
            'requestType' => $this->requestType,
            'contentType' => $this->contentType,
        ];

        return $this->client->sendSms($params);
    }

    /**
     * Validate the message parameters
     */
    protected function validate(): void
    {
        if (!$this->sender) {
            throw new AdaReachException('Sender ID is required');
        }

        if (empty($this->receivers)) {
            throw new AdaReachException('At least one receiver is required');
        }

        if (!$this->content) {
            throw new AdaReachException('Message content is required');
        }

        // Check promotional message timing (9 AM - 8 PM)
        if ($this->msgType === 'P') {
            $hour = now()->hour;
            if ($hour < 9 || $hour >= 20) {
                throw new AdaReachException(
                    'Promotional messages must be sent between 9 AM and 8 PM'
                );
            }
        }
    }

    /**
     * Format MSISDN to 13-digit format
     */
    protected function formatMsisdn(string $msisdn): string
    {
        // Remove any non-numeric characters
        $msisdn = preg_replace('/\D/', '', $msisdn);

        // If starts with 880, ensure it's 13 digits
        if (str_starts_with($msisdn, '880')) {
            return $msisdn;
        }

        // If starts with 01, remove the 0 and prepend 880
        if (str_starts_with($msisdn, '01')) {
            return '880' . substr($msisdn, 1);  // Remove leading 0
        }

        // If starts with 1 (without 0), prepend 880
        if (str_starts_with($msisdn, '1') && strlen($msisdn) == 10) {
            return '880' . $msisdn;
        }

        return $msisdn;
    }

    /**
     * Check if content needs Unicode encoding
     */
    protected function needsUnicode(string $content): bool
    {
        // Check for non-ASCII characters
        return !mb_check_encoding($content, 'ASCII');
    }
}
