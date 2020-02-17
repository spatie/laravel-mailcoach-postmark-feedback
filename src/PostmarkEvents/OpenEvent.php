<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Spatie\Mailcoach\Models\Send;

class OpenEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        // TO DO: implement
    }

    public function handle(Send $send)
    {
        return $send->registerOpen();
    }
}
