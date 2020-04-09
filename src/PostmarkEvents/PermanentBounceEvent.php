<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;

class PermanentBounceEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        if ($this->event !== 'Bounce') {
            return false;
        }

        if ($this->payload['Type'] !== 'HardBounce') {
            return false;
        }

        return true;
    }

    public function handle(Send $send)
    {
        $bouncedAt = Carbon::parse($this->payload['BouncedAt']);

        $send->registerBounce($bouncedAt);
    }
}
