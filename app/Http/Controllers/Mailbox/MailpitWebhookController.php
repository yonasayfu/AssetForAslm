<?php

namespace App\Http\Controllers\Mailbox;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mailbox\MailpitWebhookRequest;
use App\Jobs\Mailbox\IngestMailpitMessage;
use Illuminate\Http\Response;

class MailpitWebhookController extends Controller
{
    public function __invoke(MailpitWebhookRequest $request)
    {
        $payload = $request->validated();
        $environment = $request->headers->get('X-Mailpit-Label', 'default');

        IngestMailpitMessage::dispatch($payload, $environment)
            ->onQueue(config('mailbox.queue'));

        return response()->noContent(Response::HTTP_NO_CONTENT);
    }
}
