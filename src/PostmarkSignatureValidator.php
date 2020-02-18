<?php

namespace Spatie\MailcoachPostmarkFeedback;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class PostmarkSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        if (empty($config->signingSecret)) {
            return false;
        }

        [$user, $password] = explode(':', $config->signingSecret);

        return $request->getUser() === $user && $request->getPassword() === $password;
    }
}
