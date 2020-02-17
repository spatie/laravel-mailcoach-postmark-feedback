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

        $this->validator = new PostmarkSignatureValidator();
    }

    /** @test */
    public function it_requires_signature_data()
    {
        // TO DO: implement test
    }

    /** @test * */
    public function it_fails_if_signature_is_missing()
    {
        // TO DO: implement test
    }

    /** @test * */
    public function it_fails_if_data_is_missing()
    {
        // TO DO: implement test
    }

    /** @test * */
    public function it_fails_if_signature_is_invalid()
    {
        // TO DO: implement test
    }
}
