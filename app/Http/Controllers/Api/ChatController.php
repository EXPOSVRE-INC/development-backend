<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Chat;
use App\Models\User;
use PhpMqtt\Client\Facades\MQTT;
use App\Http\Resources\ConversationResource;
use App\Notifications\MessageNewNotification;


class ChatController extends Controller
{
    public function index()
    {
        $userId = auth()->user()->id;
        $conversations = Conversation::where('sender', $userId)
            ->with('chats')
            ->get();
        return response()->json([
            'data' => ConversationResource::collection($conversations),
        ]);
    }

    public function fetchMessage(Request $request)
    {
        $conversation = Conversation::where('id', $request->id)
            ->with('chats')
            ->get();

        return response()->json([
            'data' => ConversationResource::collection($conversation),
        ]);
    }

    public function getMessage($receiverId)
    {
        $userId = auth()->user()->id;
        $chats = Chat::where(function ($query) use ($userId, $receiverId) {
            $query->where('from', $userId)->where('to', $receiverId);
        })
            ->orWhere(function ($query) use ($userId, $receiverId) {
                $query->where('from', $receiverId)->where('to', $userId);
            })
            ->get(['message', 'from', 'to']);

        return response()->json($chats);
    }

    public function sendMessage(Request $request)
    {
        try{
            $userFrom = auth()->user()->id;
            $userTo = $request->input('to');
            $messageContent = $request->input('message');
            $read = $request->input('read') ?? false;
            $datetime = $request->input('datetime') ?? time();
            $received = $request->input('received') ?? false;
            $removed = $request->input('removed') ?? false;
            $id = $request->input('id');

            $senderId = $userFrom;
            $receiverId = $userTo;

            $conversation = Conversation::where(function ($query) use (
                $senderId,
                $receiverId
            ) {
                $query->where('sender', $senderId)->where('receiver', $receiverId);
            })
                ->orWhere(function ($query) use ($senderId, $receiverId) {
                    $query
                        ->where('sender', $receiverId)
                        ->where('receiver', $senderId);
                })
                ->first();

            // If not, create a new one
            if (!$conversation) {
                $conversation = Conversation::create([
                    'sender' => $senderId,
                    'receiver' => $receiverId,
                ]);
            }

           $chat =  Chat::create([
                'conversation_id' => $conversation->id,
                'from' => $userFrom,
                'to' => $userTo,
                'message' => $messageContent,
                'received' => false,
                'removed' => false,
            ]);

            $mqtt = MQTT::connection();

            $topic = "newMessage/{$userTo}/{$chat->id}";

            $payload = json_encode([
                'message' => $messageContent,
                'datetime' => $datetime,
                'read' => $read,
                'received' => $received,
                'from' => $userFrom,
                'to' => $userTo,
                'id' => $chat->id,
                'removed' => $removed,
            ]);

            $mqtt->publish($topic, $payload , 0 , true);

            if ($received == false) {
                $userFromData = User::find($userFrom);
                $userToData = User::find($userTo);

                $deepLink = 'EXPOSVRE://user/' . $userFromData->id;

                $notification = new \App\Models\Notification();
                $notification->title = 'You have new message from, ' . $userFromData->username;
                $notification->description = $userFromData->username . ': ' . $chat->message;
                $notification->type = 'newmessage';
                $notification->user_id = $userToData->id;
                $notification->sender_id = $userFromData->id;
                $notification->deep_link = $deepLink;
                $notification->save();

                $userToData->notify(new MessageNewNotification($userFromData, $userToData, $chat->message));
            }

            return response()->json(['status' => 'Message sent']);
        }

        catch (\Exception $e) {
            // Handle other errors
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while sending the message',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
