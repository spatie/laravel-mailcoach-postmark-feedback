<?php

namespace Spatie\MailcoachPostmarkFeedback\Tests\PostmarkEvents;

use Generator;
use Illuminate\Support\Facades\Event;
use Spatie\Mailcoach\Database\Factories\SendFactory;
use Spatie\Mailcoach\Domain\Campaign\Events\SoftBounceRegisteredEvent;
use Spatie\MailcoachPostmarkFeedback\PostmarkEvents\SoftBounceEvent;
use Spatie\MailcoachPostmarkFeedback\Tests\TestCase;

class SoftBounceEventTest extends TestCase
{
    /** @test */
    public function it_can_handle_a_soft_bounce_event()
    {
        Event::fake();

        $event = new SoftBounceEvent([
            'RecordType' => 'Bounce',
            'Type' => 'DnsError',
            'BouncedAt' => 1610000000,
        ]);

        $this->assertTrue($event->canHandlePayload());

        $event->handle(SendFactory::new()->create());

        Event::assertDispatched(SoftBounceRegisteredEvent::class);
    }

    /**
     * @test
     * @dataProvider failures
     */
    public function it_cannot_handle_soft_bounces(array $payload)
    {
        Event::fake();

        $event = new SoftBounceEvent($payload);

        $this->assertFalse($event->canHandlePayload());
    }

    public function failures(): Generator
    {
        yield 'different event' => [
            [
                'RecordType' => 'Something else',
                'Type' => 'DnsError',
            ],
        ];

        yield 'different type' => [
            [
                'RecordType' => 'Bounce',
                'Type' => 'something else',
            ],
        ];
    }
}
