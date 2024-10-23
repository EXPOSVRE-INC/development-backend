<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Chat;
use App\Models\User;
use PhpMqtt\Client\Facades\MQTT;
use App\Http\Resources\ConversationResource;
use App\Notifications\MessageNewNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class ChatController extends Controller
{
    public function index()
    {
        $userId = auth()->user()->id;
        $conversations = Conversation::where(function ($query) use ($userId) {
            $query->where('sender', $userId)->orWhere('receiver', $userId);
        })
        ->where('status', 'active')
        ->withCount([
            'chat as unread_count' => function ($query) {
                $query->where('read', false);
            },
        ])
        ->with([
            'chat' => function ($query) {
                $query->where('removed', false)->latest();
            },
        ])
        ->get();

    if ($conversations->isEmpty()) {
        return response()->json(
            [
                'data' => [],
            ],
            404
        );
    }

    // Check for conversations with no chats
    $hasChats = $conversations->filter(function ($conversation) {
        return $conversation->chat !== null; // Ensure there's a chat associated
    });

    if ($hasChats->isEmpty()) {
        return response()->json(
            [
                'data' => [],
            ],
            404
        );
    }

    return response()->json([
        'data' => ConversationResource::collection($conversations),
    ]);
    }

    public function fetchConversationDetail($sender, $receiver)
    {
        $conversation = Conversation::where('sender', $sender)
            ->where('receiver', $receiver)
            ->where('status', 'active')
            ->with('chats') // Load related chats
            ->get();
        $mqtt = MQTT::connection();

        $topic = "chat/newConversation/{$receiver}/#";
        $mqtt->publish($topic, 0, true);

        return response()->json([
            'data' => ConversationResource::collection($conversation),
        ]);
    }

    public function getMessage(Request $request)
    {
        $userId = auth()->user()->id;

        // Check if a conversationId is provided
        if ($request->has('conversationId')) {
            $conversationId = $request->query('conversationId');
            try {
                $conversation = Conversation::findOrFail($conversationId);
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Conversation not found'], 404);
            }

            if (
                $conversation->sender !== $userId &&
                $conversation->receiver !== $userId
            ) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $chats = Chat::where('conversation_id', $conversationId)
                ->where('removed', false)
                ->latest()
                ->get();
        }
        // Check if a participantId is provided
        elseif ($request->has('participantId')) {
            $otherUserId = $request->query('participantId');
            $chats = Chat::where(function ($query) use ($userId, $otherUserId) {
                $query->where('from', $userId)->where('to', $otherUserId);
            })->where('removed',false)
                ->orWhere(function ($query) use ($userId, $otherUserId) {
                    $query->where('from', $otherUserId)->where('to', $userId);
                })->where('removed',false)
                ->latest()
                ->get();
        } else {
            return response()->json(
                ['error' => 'Missing conversationId or participantId'],
                400
            );
        }

        if ($chats->isNotEmpty()) {
            $chats->each(function ($chat) {
                $chat->update([
                    'read' => true,
                    'received' => true,
                ]);
            });
            return response()->json([
                'data' => ChatResource::collection($chats),
            ]);
        }

        return response()->json(['data' => []], 404);
    }

    public function sendMessage(Request $request)
    {
        try {
            $userFrom = auth()->user()->id;
            $userTo = $request->input('to');

            if ($userFrom === $userTo) {
                return response()->json(
                    ['error' => 'You cannot send a message to yourself!'],
                    400
                );
            }
            if (!User::where('id', $userTo)->exists()) {
                return response()->json(
                    ['error' => 'Participant does not exist!'],
                    404
                );
            }
            $postData = $request->input('payload');
            if($postData)
            {
                foreach ($postData as $key => $value) {
                    if (empty($value)) {
                        $postData[$key] = '';
                    }
                }
            }

            // Print the updated array
            $messageContent = $request->input('message') ?? '';
            $read = $request->input('read') ?? false;
            $datetime = $request->input('datetime') ?? now()->timestamp;
            $received = $request->input('received') ?? false;
            $removed = $request->input('removed') ?? false;
            $messageId = $request->input('message_id');
            $payload = $postData;
            $senderId = $userFrom;
            $receiverId = $userTo;

            $conversation = Conversation::where(function ($query) use (
                $senderId,
                $receiverId
            ) {
                $query
                    ->where('sender', $senderId)
                    ->where('receiver', $receiverId);
            })
                ->orWhere(function ($query) use ($senderId, $receiverId) {
                    $query
                        ->where('sender', $receiverId)
                        ->where('receiver', $senderId);
                })
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'sender' => $senderId,
                    'receiver' => $receiverId,
                    'status' => 'active',
                ]);

                $conversationTopic = "chat/newConversation/{$receiverId}/{$conversation->id}";
                $mqtt = MQTT::connection();
                $mqtt->publish(
                    $conversationTopic,
                    json_encode([
                        'conversation_id' => $conversation->id,
                        'sender' => $senderId,
                        'receiver' => $receiverId,
                    ]),
                    0,
                    false
                );
            }
            $chat = Chat::create([
                'conversation_id' => $conversation->id,
                'from' => $userFrom,
                'to' => $userTo,
                'message' => $messageContent,
                'received' => $received,
                'removed' => $removed,
                'read' => $read,
                'message_id' => $messageId ?? '',
                'datetime' => $datetime,
                'payload' => json_encode($payload),
            ]);

            $mqtt = MQTT::connection();
            $topic = "chat/newMessage/{$userTo}/{$chat->id}";

            $payloadData = json_encode([
                'message' => $messageContent,
                'datetime' => $datetime,
                'read' => $read,
                'received' => $received,
                'from' => $userFrom,
                'to' => $userTo,
                'id' => $chat->id,
                'removed' => $removed,
                'message_id' => $chat->message_id,
                'payload' => $payload,
            ]);

            $mqtt->publish($topic, $payloadData, 0, false);

            if (!$received) {
                $userFromData = User::find($userFrom);
                $userToData = User::find($userTo);

                $userToData->notify(
                    new MessageNewNotification(
                        $userFromData,
                        $userToData,
                        $chat->message
                    )
                );
            }
            return response()->json(
                [
                    'data' => new ChatResource($chat),
                ],
                201
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while sending the message',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function readMessage($chatId)
    {
        $chat = Chat::find($chatId);

        if ($chat) {
            $chat->update([
                'read' => true,
                'received' => true,
            ]);
            return response()->json(
                [
                    'data' => new ChatResource($chat),
                ],
                200
            );
        }

        return response()->json(['message' => 'Chat not found'], 404);
    }

    public function editMessage(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required',
        ]);

        $chat = Chat::find($chatId);


        if ($chat) {
            $chat->update([
                'message' => $request->input('message'),
            ]);

            $receiverId = $chat->to; // Assuming 'to' is the receiver's ID
            $topic = "chat/updateMessage/{$receiverId}/{$chat->id}";

            $payloadArray = [
                'message' => $chat->message,
                'from' => $chat->from,
                'to' => $chat->to,
                'read' => (bool) $chat->read,
                'received' => (bool) $chat->received,
                'id' => (int) $chat->id,
                'removed' => (bool) $chat->removed,
                'message_id' => $chat->message_id,
                'datetime' => $chat->datetime,
            ];

            if (!empty($chat->payload) && $chat->payload != 'null') {
                $payloadArray['payload'] = $chat->payload;
            } else {
                $payloadArray['payload'] = null; // Empty object if no payload
            }

            $payload = json_encode($payloadArray);
            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 0, false);

            return response()->json(
                [
                    'data' => new ChatResource($chat),
                ],
                200
            );
        }

        // If the chat message does not exist, return a 404 response
        return response()->json(['message' => 'Chat not found'], 404);
    }

    public function deleteMessage($chatId)
    {
        $chat = Chat::find($chatId);

        if ($chat) {
            $receiverId = $chat->to;

            $chat->update([
                'removed' => true,
                'message' => '<REMOVED>',
            ]);
            $topic = "chat/deleteMessage/{$receiverId}/{$chatId}"; // Create the topic

            $payload = json_encode([
                'id' => (int) $chatId,
                'message' => '<REMOVED>',
                'from' => $chat->from,
                'to' => $chat->to,
                'read'=> (bool) $chat->read,
                'removed' => true,
                'received' => (bool) $chat->received,
                'message_id' => $chat->message_id,
                'datetime' => $chat->datetime,
            ]);

            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 0, false);
            // Return a success response
            return response()->json(
                [
                    'data' => new ChatResource($chat),
                ],
                200
            );
        }

        // If the chat message does not exist, return a 404 response
        return response()->json(['message' => 'Chat not found'], 404);
    }
}
