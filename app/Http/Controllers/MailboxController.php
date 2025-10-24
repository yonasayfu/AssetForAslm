<?php

namespace App\Http\Controllers;

use App\Models\Mailbox\MailboxMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MailboxController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only('status', 'environment', 'email', 'date_from', 'date_to');

        $messages = MailboxMessage::with('recipients')
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->input('environment'), function ($query, $env) {
                $query->where('environment', $env);
            })
            ->when($request->input('email'), function ($query, $email) {
                $query->whereHas('recipients', function ($q) use ($email) {
                    $q->where('address', 'like', "%{$email}%");
                });
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Mailbox/Index', [
            'messages' => $messages,
            'filters' => $filters,
        ]);
    }

    public function show(MailboxMessage $message): Response
    {
        $message->load('recipients', 'attachments', 'events.user');

        // Log the 'viewed' event if it's the first time for this user
        $viewedEventExists = $message->events()
            ->where('user_id', auth()->id())
            ->where('event_type', 'viewed')
            ->exists();

        if (!$viewedEventExists) {
            $message->events()->create([
                'user_id' => auth()->id(),
                'event_type' => 'viewed',
            ]);
            // Reload the events relationship to show the new event immediately
            $message->load('events.user');
        }

        return Inertia::render('Mailbox/Show', [
            'message' => $message,
        ]);
    }

    public function process(Request $request, MailboxMessage $message)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $message->update([
            'status' => 'processed',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        $message->events()->create([
            'user_id' => auth()->id(),
            'event_type' => 'processed',
            'context' => ['note' => $request->input('note')],
        ]);

        return back()->with('success', 'Message marked as processed.');
    }
}
