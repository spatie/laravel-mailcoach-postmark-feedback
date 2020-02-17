<?php

namespace Spatie\MailcoachPostmarkFeedback;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProcessor;

class PostmarkWebhookController
{
    public function __invoke(Request $request)
    {
        $webhookConfig = PostmarkWebhookConfig::get();

        (new WebhookProcessor($request, $webhookConfig))->process();

        return response()->json(['message' => 'ok']);
    }
}
