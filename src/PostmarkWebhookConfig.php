<?php

namespace Spatie\MailcoachPostmarkFeedback;

use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class PostmarkWebhookConfig
{
    public static function get(): WebhookConfig
    {
        $config = config('mailcoach.postmark_feedback');

        return new WebhookConfig([
            'name' => 'postmark-feedback',
            'signing_secret' => $config['signing_secret'] ?? '',
            'header_name' => '',
            'signature_validator' => $config['signature_validator'] ?? PostmarkSignatureValidator::class,
            'webhook_profile' =>  $config['webhook_profile'] ?? ProcessEverythingWebhookProfile::class,
            'webhook_model' => $config['webhook_model'] ?? WebhookCall::class,
            'process_webhook_job' => $config['process_webhook_job'] ?? ProcessPostmarkWebhookJob::class,
        ]);
    }
}
