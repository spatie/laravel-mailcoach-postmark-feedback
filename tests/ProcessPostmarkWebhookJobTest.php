<?php

namespace Spatie\MailcoachPostmarkFeedback\Tests;

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

        $this->send = factory(Send::class)->create([
            'transport_message_id' => '20130503192659.13651.20287@mg.craftremote.com',
        ]);

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
        $this->assertEquals(SendFeedbackType::BOUNCE, SendFeedbackItem::first()->type);
        $this->assertTrue($this->send->is(SendFeedbackItem::first()->send));
    }

    /** @test */
    public function it_processes_a_postmark_complaint_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('complaintWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());
        $this->assertEquals(SendFeedbackType::COMPLAINT, SendFeedbackItem::first()->type);
        $this->assertTrue($this->send->is(SendFeedbackItem::first()->send));
    }

    /** @test */
    public function it_processes_a_postmark_click_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('clickWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, CampaignLink::count());
        $this->assertEquals('http://example.com/signup', CampaignLink::first()->url);
        $this->assertCount(1, CampaignLink::first()->clicks);
    }

    /** @test */
    public function it_can_process_a_postmark_open_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('openWebhookContent')]);
        (new ProcessPostmarkWebhookJob($this->webhookCall))->handle();

        $this->assertCount(1, $this->send->campaign->opens);
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
    public function it_does_nothing_when_it_cannot_find_the_transport_message_id()
    {
        $data = $this->webhookCall->payload;
        $data['event-data']['message']['headers']['message-id'] = 'some-other-id';

        $this->webhookCall->update([
            'payload' => $data,
        ]);

        $job = new ProcessPostmarkWebhookJob($this->webhookCall);

        $job->handle();

        $this->assertEquals(0, SendFeedbackItem::count());
    }
}
