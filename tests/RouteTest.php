<?php

namespace Spatie\MailcoachPostmarkFeedback\Tests;

use Illuminate\Support\Facades\Route;
use Spatie\MailcoachPostmarkFeedback\PostmarkWebhookConfig;

class RouteTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('mailcoach.postmark_feedback.signing_secret', 'user:pw');

        Route::postmarkFeedback('postmark-feedback');
    }

    /** @test */
    public function it_provides_a_route_macro_to_handle_webhooks()
    {
        $payload = $this->getStub('complaintWebhookContent');

        $this
            ->post('postmark-feedback', $payload, ['PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'pw'])
            ->assertSuccessful();
    }

    /** @test */
    public function it_fails_when_using_an_invalid_payload()
    {
        $payload = $this->getStub('complaintWebhookContent');

        $this
            ->post('postmark-feedback', $payload)
            ->assertStatus(500);
    }
}
