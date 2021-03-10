<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Spatie\Mailcoach\Domain\Shared\Models\Send;

class OtherEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return true;
    }

    public function handle(Send $send)
    {
    }
}
