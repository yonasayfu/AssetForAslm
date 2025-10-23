<?php

namespace App\Http\Requests\Mailbox;

use Illuminate\Foundation\Http\FormRequest;

class MailpitWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'created' => ['required', 'date'],
            'subject' => ['nullable', 'string'],
            'from' => ['required', 'array'],
            'from.address' => ['required', 'email'],
            'from.name' => ['nullable', 'string'],
            'to' => ['nullable', 'array'],
            'to.*.address' => ['required_with:to', 'email'],
            'to.*.name' => ['nullable', 'string'],
            'cc' => ['nullable', 'array'],
            'cc.*.address' => ['required_with:cc', 'email'],
            'cc.*.name' => ['nullable', 'string'],
            'bcc' => ['nullable', 'array'],
            'bcc.*.address' => ['required_with:bcc', 'email'],
            'bcc.*.name' => ['nullable', 'string'],
            'html' => ['nullable', 'string'],
            'text' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
            'headers' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.id' => ['required_with:attachments', 'string'],
            'attachments.*.filename' => ['nullable', 'string'],
            'attachments.*.content_type' => ['nullable', 'string'],
            'attachments.*.size' => ['nullable', 'integer'],
            'attachments.*.inline' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
            'mailbox' => ['nullable', 'array'], // Mailpit metadata
        ];
    }
}
