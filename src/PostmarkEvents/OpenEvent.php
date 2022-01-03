<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

class OpenEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'Open';
    }

    public function handle(Send $send)
    {
        if (! Arr::get($this->payload, 'FirstOpen')) {
            return;
        }

        $openedAt = Carbon::parse($this->payload['ReceivedAt']);

        return $send->registerOpen($openedAt);
    }
}
