<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Carbon\Carbon;
use Spatie\Mailcoach\Models\Send;

class ComplaintEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'SpamComplaint';
    }

    public function handle(Send $send)
    {
        $complainedAt = Carbon::parse($this->payload['BouncedAt']);

        $send->registerComplaint($complainedAt);
    }
}
