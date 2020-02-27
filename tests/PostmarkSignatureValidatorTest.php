<?php

namespace Spatie\MailcoachPostmarkFeedback\Tests;

use Illuminate\Http\Request;
use Spatie\MailcoachPostmarkFeedback\PostmarkSignatureValidator;
use Spatie\MailcoachPostmarkFeedback\PostmarkWebhookConfig;
use Spatie\WebhookClient\WebhookConfig;

class PostmarkSignatureValidatorTest extends TestCase
{
    private WebhookConfig $config;

    private PostmarkSignatureValidator $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = PostmarkWebhookConfig::get();

        $this->config->signingSecret = 'my-secret';

        $this->validator = new PostmarkSignatureValidator();
    }

    /** @test */
    public function it_requires_signature_data()
    {
        $request = new Request();

        $request->headers->set('mailcoach_signature', 'my-secret');

        $this->assertTrue($this->validator->isValid($request, $this->config));
    }

    /** @test * */
    public function it_fails_if_signature_is_missing()
    {
        $request = new Request();

        $this->assertFalse($this->validator->isValid($request, $this->config));
    }

    /** @test * */
    public function it_fails_if_signature_is_invalid()
    {
        $request = new Request();

        $request->headers->set('mailcoach_signature', 'incorrect-secret');

        $this->assertFalse($this->validator->isValid($request, $this->config));
    }
}
