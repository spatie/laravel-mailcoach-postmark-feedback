<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;

class ClickEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        // TO DO: implement
    }

    public function handle(Send $send)
    {
        $url = Arr::get($this->payload, 'event-data.url');

        $send->registerClick($url);
    }
}
