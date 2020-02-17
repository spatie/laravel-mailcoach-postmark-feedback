<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Spatie\Mailcoach\Models\Send;

class ComplaintEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'SpamComplaint';
    }

    public function handle(Send $send)
    {
        $send->registerComplaint();
    }
}
