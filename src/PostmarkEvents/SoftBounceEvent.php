<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Carbon\Carbon;
use Spatie\Mailcoach\Domain\Shared\Models\Send;
use Spatie\MailcoachPostmarkFeedback\Enums\BounceType;

class SoftBounceEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'Bounce' &&
            in_array($this->payload['Type'], BounceType::softBounces(), true);
    }

    public function handle(Send $send)
    {
        $bouncedAt = Carbon::parse($this->payload['BouncedAt'] ?? $this->payload['ChangedAt']);

        $send->registerBounce($bouncedAt, softBounce: true);
    }
}
