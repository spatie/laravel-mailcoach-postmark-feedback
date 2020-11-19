<?php


namespace Spatie\MailcoachPostmarkFeedback;

use Illuminate\Mail\Events\MessageSending;

class AddMessageStreamHeader
{
    public function handle(MessageSending $event)
    {
        $driver = config('mailcoach.mailer') ?? config('mailcoach.campaign_mailer') ?? config('mail.default');

        if ('postmark' !== config("mail.mailers.{$driver}.transport")) {
            return;
        }

        if (! $messageStream = config('mailcoach.postmark.message_stream')) {
            return;
        }

        if (! $event->message->getHeaders()->get('X-MAILCOACH')) {
            return;
        }

        $event->message->getHeaders()->removeAll('X-PM-Message-Stream');
        $event->message->getHeaders()->addTextHeader('X-PM-Message-Stream', $messageStream);
    }
}
