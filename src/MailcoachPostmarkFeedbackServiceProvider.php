<?php

namespace Spatie\MailcoachPostmarkFeedback;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MailcoachPostmarkFeedbackServiceProvider extends ServiceProvider
{
    public function register()
    {
        Route::macro(
            'postmarkFeedback',
            fn (string $url) => Route::post("{$url}/{mailerConfigKey?}", '\\' . PostmarkWebhookController::class)
        );

        Event::listen(MessageSending::class, AddMessageStreamHeader::class);
    }
}
