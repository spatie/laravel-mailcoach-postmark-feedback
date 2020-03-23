<?php

namespace Spatie\MailcoachPostmarkFeedback;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\ProcessWebhookJob;
use Spatie\Mailcoach\Support\Config;

class ProcessPostmarkWebhookJob extends ProcessWebhookJob
{
    public function __construct(WebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $this->queue = config('mailcoach.perform_on_queue.process_feedback_job');

        $this->connection = $this->connection ?? Config::getQueueConnection();
    }

    public function handle()
    {
        $payload = $this->webhookCall->payload;

        if (!$send = $this->getSend()) {
            return;
        };

        $postmarkEvent = PostmarkEventFactory::createForPayload($payload);

        $postmarkEvent->handle($send);
    }

    protected function getSend(): ?Send
    {
        $metadata = Arr::get($this->webhookCall->payload, 'Metadata');

        if (! isset($metadata['send-uuid'])) {
            return null;
        }

        $messageId = $metadata['send-uuid'];

        return Send::findByUuid($messageId);
    }
}
