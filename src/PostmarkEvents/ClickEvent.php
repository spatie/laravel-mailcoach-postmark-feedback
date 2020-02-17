<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;

class ClickEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'Click';
    }

    public function handle(Send $send)
    {
        $url = Arr::get($this->payload, 'OriginalLink');

        $send->registerClick($url);
    }
}
