<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientMessageMail;

class ClientMessageController extends Controller
{
    // Show send message page with list of users
    public function index()
    {
        $users = \App\Models\User::where('role', '!=', 'admin')->get();
        return view('messaging.sendMessage', compact('users'));
    }


   public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        // ✅ Works for both user and admin
        $sender = auth()->user() ?? auth('admin_guard')->user();

        if (!$sender) {
            return redirect()->route('login')->with('error', 'You must be logged in to send a message.');
        }

        // Get receiver
        $receiver = User::find($request->receiver_id);
        if (!$receiver) {
            return back()->with('error', 'Receiver not found.');
        }

        // ✅ Insert message into chattbl
        Chat::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'subject' => $request->subject,
            'message' => $request->message,
            'sender_role' => $sender->role ?? 'admin',
        ]);

        // ✅ Send email
        try {
            Mail::to($receiver->email)->send(new ClientMessageMail($sender, $request->subject, $request->message));
            return back()->with('success', 'Message sent and email delivered successfully!');
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: ' . $e->getMessage());
            return back()->with('error', 'Message saved but email could not be sent.');
        }
    }

}
