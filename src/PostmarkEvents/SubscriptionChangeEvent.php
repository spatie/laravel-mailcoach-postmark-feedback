<?php

namespace Spatie\MailcoachPostmarkFeedback\PostmarkEvents;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

class SubscriptionChangeEvent extends PostmarkEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'SubscriptionChange';
    }

    public function handle(Send $send)
    {
        $suppressSending = (bool) Arr::get($this->payload, 'SuppressSending');

        /** @var \Spatie\Mailcoach\Domain\Audience\Models\Subscriber $subscriber */
        $subscriber = $send->subscriber;

        if ($suppressSending) {
            $subscriber->unsubscribe($send);
        }
    }
}
