<?php

namespace Spatie\MailcoachPostmarkFeedback\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Spatie\Mailcoach\Domain\Campaign\Enums\SendFeedbackType;
use Spatie\Mailcoach\Domain\Campaign\Events\WebhookCallProcessedEvent;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignLink;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignOpen;
use Spatie\Mailcoach\Domain\Shared\Models\Send;
use Spatie\Mailcoach\Domain\Shared\Models\SendFeedbackItem;
use Spatie\MailcoachPostmarkFeedback\ProcessPostmarkWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;

class ProcessPostmarkWebhookJobTest extends TestCase
{
    private WebhookCall $webhookCall;

    private Send $send;

    public function setUp(): void
    {
        parent::setUp();

        $this->webhookCall = WebhookCall::create([
            'name' => 'postmark',
            'payload' => $this->getStub('bounceWebhookContent'),
        ]);

        $this->send = Send::factory()->create();

        $this->send->update(['uuid' => 'my-uuid']);
    }

    /** @test */
    public function it_processes_a_postmark_bounce_webhook_call()
    {
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        tap(SendFeedbackItem::first(), function (SendFeedbackItem $sendFeedbackItem) {
            $this->assertEquals(SendFeedbackType::Bounce, $sendFeedbackItem->type);
            $this->assertEquals(Carbon::parse('2019-11-05T16:33:54.0Z'), $sendFeedbackItem->created_at);
            $this->assertTrue($this->send->is($sendFeedbackItem->send));
        });
    }

    /** @test */
    public function it_processes_a_postmark_bounce_via_subscription_change_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('bounceViaSubscriptionChangeWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        tap(SendFeedbackItem::first(), function (SendFeedbackItem $sendFeedbackItem) {
            $this->assertEquals(SendFeedbackType::Bounce, $sendFeedbackItem->type);
            $this->assertEquals(Carbon::parse('2019-11-05T16:33:54.0Z'), $sendFeedbackItem->created_at);
            $this->assertTrue($this->send->is($sendFeedbackItem->send));
        });
    }

    /** @test */
    public function it_wil_not_process_a_postmark_soft_bounce_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('softBounceWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(0, SendFeedbackItem::count());
    }

    /** @test */
    public function it_processes_a_postmark_complaint_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('complaintWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        tap(SendFeedbackItem::first(), function (SendFeedbackItem $sendFeedbackItem) {
            $this->assertEquals(SendFeedbackType::Complaint, $sendFeedbackItem->type);
            $this->assertEquals(Carbon::parse('2019-11-05T16:33:54.0Z'), $sendFeedbackItem->created_at);
            $this->assertTrue($this->send->is($sendFeedbackItem->send));
        });
    }

    /** @test */
    public function it_processes_a_postmark_complaint_via_subscription_change_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('complaintViaSubscriptionChangeWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        tap(SendFeedbackItem::first(), function (SendFeedbackItem $sendFeedbackItem) {
            $this->assertEquals(SendFeedbackType::Complaint, $sendFeedbackItem->type);
            $this->assertEquals(Carbon::parse('2019-11-05T16:33:54.0Z'), $sendFeedbackItem->created_at);
            $this->assertTrue($this->send->is($sendFeedbackItem->send));
        });
    }

    /** @test */
    public function it_processes_a_postmark_click_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('clickWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, CampaignLink::count());
        $this->assertEquals('http://example.com/signup', CampaignLink::first()->url);
        $this->assertCount(1, CampaignLink::first()->clicks);
        $this->assertEquals(Carbon::parse('2017-10-25T15:21:11.0Z'), CampaignLink::first()->clicks->first()->created_at);
    }

    /** @test */
    public function it_can_process_a_postmark_open_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('openWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertCount(1, $this->send->campaign->opens);
        $this->assertEquals(Carbon::parse('2019-11-05T16:33:54.0Z'), $this->send->campaign->opens->first()->created_at);
    }

    /** @test */
    public function it_can_process_a_postmark_open_webhook_call_by_message_id()
    {
        $this->send->update(['transport_message_id' => 'some-message-id']);
        $payload = $this->getStub('openWebhookContent');
        $payload['MessageID'] = 'some-message-id';
        unset($payload['Metadata']);

        $this->webhookCall->update(['payload' => $payload]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertCount(1, $this->send->campaign->opens);
        $this->assertEquals(Carbon::parse('2019-11-05T16:33:54.0Z'), $this->send->campaign->opens->first()->created_at);
    }

    /** @test */
    public function it_fires_an_event_after_processing_the_webhook_call()
    {
        Event::fake(WebhookCallProcessedEvent::class);

        $this->webhookCall->update(['payload' => $this->getStub('openWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        Event::assertDispatched(WebhookCallProcessedEvent::class);
    }

    /** @test */
    public function it_will_not_handle_unrelated_events()
    {
        $this->webhookCall->update(['payload' => $this->getStub('otherWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(0, CampaignLink::count());
        $this->assertEquals(0, CampaignOpen::count());
        $this->assertEquals(0, SendFeedbackItem::count());
    }

    /** @test */
    public function it_does_nothing_when_it_cannot_find_a_send_for_the_uuid_in_the_webhook()
    {
        $data = $this->webhookCall->payload;
        $data['Metadata']['send-uuid'] = 'some-other-uuid';

        $this->webhookCall->update([
            'payload' => $data,
        ]);

        $job = new ProcessPostmarkWebhookJob($this->webhookCall);

        $job->handle();

        $this->assertEquals(0, SendFeedbackItem::count());
    }

    /** @test */
    public function it_will_not_fail_if_RecordType_is_not_set()
    {
        $payload = $this->getStub('clickWebhookContent');

        unset($payload['RecordType']);

        $this->webhookCall->update(['payload' => $payload]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(0, CampaignLink::count());
    }
}
