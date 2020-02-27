<?php

namespace Spatie\MailcoachPostmarkFeedback;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class PostmarkSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        if (empty($config->signingSecret)) {
            return false;
        }

        return $request->header('mailcoach_signature') === $config->signingSecret;
    }
}
