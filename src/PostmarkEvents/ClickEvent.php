<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

class ClickEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'Click';
    }

    public function handle(Send $send)
    {
        $url = Arr::get($this->payload, 'OriginalLink');
        $clickedAt = Carbon::parse($this->payload['ReceivedAt']);

        $send->registerClick($url, $clickedAt);
    }
}
