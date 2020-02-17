<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;

class PermanentBounceEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event == 'Bounce';
    }

    public function handle(Send $send)
    {
        $send->registerBounce();
    }
}
