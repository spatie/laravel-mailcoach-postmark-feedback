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
        $validator = Validator::make($request->all(), [
            // add fields to be validated
        ]);

        if ($validator->fails()) {
            return false;
        }

        return $this->hasValidSignature($request, $config);
    }

    public function hasValidSignature(Request $request, WebhookConfig $config): bool
    {
        // implement method
    }
}
