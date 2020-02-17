<?php

namespace Spatie\MailcoachPostmarkFeedback;

use Illuminate\Mail\Events\MessageSent;

class StoreTransportMessageId
{
    public function handle(MessageSent $event)
    {
        if (! isset($event->data['send'])) {
            return;
        }

        if (! $event->message->getHeaders()->has('X-Postmark-Message-ID')) {
            return;
        }

        /** @var \Spatie\Mailcoach\Models\Send $send */
        $send = $event->data['send'];

        $transportMessageId = $event->message->getHeaders()->get('X-Postmark-Message-ID')->getFieldBody();

        $transportMessageId = ltrim($transportMessageId, '<');
        $transportMessageId = rtrim($transportMessageId, '>');

        $send->storeTransportMessageId($transportMessageId);
    }
}
