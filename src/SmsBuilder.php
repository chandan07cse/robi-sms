<?php

namespace AdaReach\Sms;

use AdaReach\Sms\Exceptions\AdaReachException;
use AdaReach\Sms\Storage\SmsRepository;
use Illuminate\Support\Facades\Log;

class SmsBuilder
{
    protected AdaReachClient $client;
    protected SmsRepository $repository;
    protected ?string $sender = null;
    protected array $receivers = [];
    protected ?string $content = null;
    protected string $msgType = 'T'; // T = Transactional, P = Promotional
    protected string $requestType = 'S'; // S = Single, B = Bulk
    protected int $contentType = 1; // 1 = Regular, 2 = Unicode

    public function __construct(AdaReachClient $client, SmsRepository $repository = null)
    {
        $this->client = $client;
        $this->repository = $repository ?? app(SmsRepository::class);
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

        $response = $this->client->sendSms($params);

        // Automatically log to Redis for each receiver
        foreach ($this->receivers as $receiver) {
            try {
                $this->repository->store([
                    'phone' => $receiver,
                    'sender' => $this->sender,
                    'message' => $this->content,
                    'status' => isset($response['errorCode']) ? 'failed' : 'sent',
                    'type' => $this->contentType === 2 ? 'unicode' : 'plain',
                    'response' => $response,
                    'response_time' => $response['response_time'] ?? 0,
                    'source' => 'facade'
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the SMS send
                Log::error('Failed to store SMS in Redis', [
                    'phone' => $receiver,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $response;
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
     * Format MSISDN to 11-digit Bangladesh format (01XXXXXXXXX)
     * AdaReach API expects local format, not international
     */
    protected function formatMsisdn(string $msisdn): string
    {
        // Remove any non-numeric characters
        $msisdn = preg_replace('/\D/', '', $msisdn);

        // If starts with 880, remove it and add 0
        if (str_starts_with($msisdn, '880')) {
            return '0' . substr($msisdn, 3);  // 8801712345678 → 01712345678
        }

        // If starts with 01, it's already correct
        if (str_starts_with($msisdn, '01')) {
            return $msisdn;  // 01712345678 → 01712345678
        }

        // If starts with 1 (without 0), prepend 0
        if (str_starts_with($msisdn, '1') && strlen($msisdn) == 10) {
            return '0' . $msisdn;  // 1712345678 → 01712345678
        }

        // Return as-is if already in correct format
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
