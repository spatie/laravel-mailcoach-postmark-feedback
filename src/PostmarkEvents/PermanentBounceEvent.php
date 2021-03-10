<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Carbon\Carbon;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

class PermanentBounceEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        if ($this->event === 'Bounce' && $this->payload['Type'] === 'HardBounce') {
            return true;
        }

        if ($this->event === 'SubscriptionChange' && ($this->payload['SuppressionReason'] ?? null) === 'HardBounce') {
            return true;
        }

        return false;
    }

    public function handle(Send $send)
    {
        $bouncedAt = Carbon::parse($this->payload['BouncedAt'] ?? $this->payload['ChangedAt']);

        $send->registerBounce($bouncedAt);
    }
}
