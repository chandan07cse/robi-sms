<?php

namespace AdaReach\Sms\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SmsEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    public array $data;
    public string $timestamp;

    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
        $this->timestamp = now()->toIso8601String();
    }

    public function broadcastOn(): Channel
    {
        return new Channel('sms-dashboard');
    }

    public function broadcastAs(): string
    {
        return 'sms.' . $this->type;
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => $this->timestamp,
        ];
    }
}
