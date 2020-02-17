<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;

class OpenEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'Open';
    }

    public function handle(Send $send)
    {
        if (!Arr::get($this->payload, 'FirstOpen')) {
            return;
        }

        return $send->registerOpen();
    }
}
