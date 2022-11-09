<?php

namespace Spatie\MailcoachPostmarkFeedback;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Campaign\Events\WebhookCallProcessedEvent;
use Spatie\Mailcoach\Domain\Shared\Models\Send;
use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
use Spatie\Mailcoach\Mailcoach;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;

class ProcessPostmarkWebhookJob extends ProcessWebhookJob
{
    use UsesMailcoachModels;

    public function __construct(WebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $this->queue = config('mailcoach.campaigns.perform_on_queue.process_feedback_job');

        $this->connection = $this->connection ?? Mailcoach::getQueueConnection();
    }

    public function handle()
    {
        $payload = $this->webhookCall->payload;

        if ($send = $this->getSend()) {
            $postmarkEvent = PostmarkEventFactory::createForPayload($payload);
            $postmarkEvent->handle($send);
        }

        event(new WebhookCallProcessedEvent($this->webhookCall));
    }

    protected function getSend(): ?Send
    {
        $send = $this->getSendByMetaData();

        if (! $send) {
            $send = $this->getSendByMessageId();
        }

        return $send;
    }

    protected function getSendByMetaData(): ?Send
    {
        $metadata = Arr::get($this->webhookCall->payload, 'Metadata');

        if (! isset($metadata['send-uuid'])) {
            return null;
        }

        $messageId = $metadata['send-uuid'];

        $sendClass = self::getSendClass();

        return $sendClass::findByUuid($messageId);
    }

    protected function getSendByMessageId(): ?Send
    {
        $payload = $this->webhookCall->payload;

        if (! isset($payload['MessageID'])) {
            return null;
        }

        $messageId = $payload['MessageID'];

        $sendClass = self::getSendClass();

        return $sendClass::findByTransportMessageId($messageId);
    }
}
