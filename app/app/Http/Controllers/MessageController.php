<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $messages = Message::with('sender')
            ->where('recipient_id', $user->id)
            ->latest()
            ->paginate(12);

        return view('messages.index', ['messages' => $messages, 'box' => 'inbox']);
    }

    public function sent(Request $request): View
    {
        $messages = Message::with('recipient')
            ->where('sender_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('messages.sent', ['messages' => $messages, 'box' => 'sent']);
    }

    public function create(Request $request): View
    {
        $users = User::where('id', '!=', $request->user()->id)
            ->with('role')->orderBy('name')->get();

        $reply = null;
        if ($request->reply_to && $original = Message::find($request->reply_to)) {
            $reply = $original;
        }

        return view('messages.create', compact('users', 'reply'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
        ]);

        $data['sender_id'] = $request->user()->id;
        Message::create($data);

        return redirect()->route('messages.index')->with('success', 'Mensaje enviado.');
    }

    public function show(Request $request, Message $message): View
    {
        // Solo remitente o destinatario pueden ver el mensaje
        abort_unless(in_array($request->user()->id, [$message->sender_id, $message->recipient_id]), 403);

        // Marcar como leído si soy el destinatario
        if ($message->recipient_id === $request->user()->id && $message->isUnread()) {
            $message->update(['read_at' => now()]);
        }

        $message->load(['sender', 'recipient']);

        return view('messages.show', compact('message'));
    }

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        abort_unless(in_array($request->user()->id, [$message->sender_id, $message->recipient_id]), 403);

        $message->delete();

        return redirect()->route('messages.index')->with('success', 'Mensaje eliminado.');
    }
}
