<?php


namespace Spatie\MailcoachPostmarkFeedback\Tests;


use Illuminate\Mail\Events\MessageSending;
use Swift_Message;

class AddMessageStreamHeaderTest extends TestCase
{
    /** @test **/
    public function it_adds_a_message_stream_header_if_the_message_is_sent_by_mailcoach()
    {
        $message = new Swift_Message('Test', 'body');
        $message->getHeaders()->addTextHeader('X-MAILCOACH', true);

        config()->set('mailcoach.postmark.message_stream', 'hello');
        config()->set('mail.default', 'postmark');
        config()->set('mail.mailers.ses.transport', 'postmark');

        event(new MessageSending($message));

        $this->assertEquals('hello', $message->getHeaders()->get('X-PM-Message-Stream')->getFieldBody());
    }
}
