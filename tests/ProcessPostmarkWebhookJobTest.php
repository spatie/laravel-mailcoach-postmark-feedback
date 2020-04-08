<?php

namespace Spatie\MailcoachPostmarkFeedback\Tests;

use Carbon\Carbon;
use Spatie\Mailcoach\Enums\SendFeedbackType;
use Spatie\Mailcoach\Models\CampaignLink;
use Spatie\Mailcoach\Models\CampaignOpen;
use Spatie\Mailcoach\Models\Send;
use Spatie\Mailcoach\Models\SendFeedbackItem;
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

        $this->send = factory(Send::class)->create();

        $this->send->update(['uuid' => 'my-uuid']);

        $this->send->campaign->update([
            'track_opens' => true,
            'track_clicks' => true,
        ]);
    }

    /** @test */
    public function it_processes_a_postmark_bounce_webhook_call()
    {
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        tap(SendFeedbackItem::first(), function (SendFeedbackItem $sendFeedbackItem) {
            $this->assertEquals(SendFeedbackType::BOUNCE, $sendFeedbackItem->type);
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
            $this->assertEquals(SendFeedbackType::COMPLAINT, $sendFeedbackItem->type);
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
