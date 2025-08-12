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
use App\Http\Resources\UserInfoResource;
use App\Notifications\MessageNewNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChatController extends Controller
{
    public function index()
    {
        $userId = auth()->user()->id;
        $conversations = Conversation::where(function ($query) use ($userId) {
            $query->where('sender', $userId)
                ->orWhere('receiver', $userId);
        })
            ->where('status', 'active')
            ->withCount([
                'chat as unread_count' => function ($query) use ($userId) {
                    $query->where('read', false)
                        ->where('from', '!=', $userId)
                        ->where('removed', false);
                }
            ])
            ->with([
                'chat' => function ($query) {
                    $query->where('removed', false)
                        ->latest('created_at');
                }
            ])
            ->whereHas('chat', function ($query) {
                $query->where('removed', false);
            })
            ->get();

        return response()->json([
            'data' => ConversationResource::collection($conversations),
        ]);
    }

    public function getMessage(Request $request)
    {
        $userId = auth()->user()->id;
        $conversation = null;
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
                ->get();
        } elseif ($request->has('participantId')) {
            $otherUserId = $request->query('participantId');
            $chats = Chat::where(function ($query) use ($userId, $otherUserId) {
                $query->where('from', $userId)->where('to', $otherUserId);
            })->where('removed', false)
                ->orWhere(function ($query) use ($userId, $otherUserId) {
                    $query->where('from', $otherUserId)->where('to', $userId);
                })->where('removed', false)
                ->get();
        } else {
            return response()->json(
                ['error' => 'Missing conversationId or participantId'],
                400
            );
        }
        if ($chats->isNotEmpty()) {
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
            if ($postData) {
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

            $newConversation = false;
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
                $newConversation = true;
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

            $unreadCount = Chat::where('conversation_id', $conversation->id)
                ->where('read', false)
                ->where('removed', false)
                ->count();

            $senderResource = new UserInfoResource(User::find($userFrom));
            $receiverResource = new UserInfoResource(User::find($userTo));

            if ($newConversation) {
                $latestChat = $conversation->chat()->where('removed', false)->latest()->first();
                $conversationTopic = "chat/newConversation/{$receiverId}/{$conversation->id}";
                $mqtt = MQTT::connection();
                $mqtt->publish(
                    $conversationTopic,
                    json_encode([
                        'id' => $conversation->id,
                        'sender' => $senderResource,
                        'receiver' => $receiverResource,
                        'unread_count' => $unreadCount ?? 0,
                        'chat' => $latestChat ? [
                            'id' => $latestChat->id,
                            'conversation_id' => $latestChat->conversation_id,
                            'from' => $latestChat->from,
                            'to' => $latestChat->to,
                            'message' => $latestChat->message,
                            'received' => (bool) $latestChat->received,
                            'removed' => (bool) $latestChat->removed,
                            'datetime' => $latestChat->datetime,
                            'read' => (bool) $latestChat->read,
                            'message_id' => $latestChat->message_id,
                        ] : null,
                    ]),
                    0,
                    false
                );
            }
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
        $userId = auth()->user()->id;

        $chats = Chat::where('created_at', '<=', $chat->created_at)->where('conversation_id', $chat->conversation_id)->where('to', $userId)->get();

        if ($chats) {
            $chats->each(function ($chat) {
                $chat->update([
                    'read' => true,
                    'received' => true,
                ]);
            });
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
            'payload' => 'nullable', // Allow payload updates
        ]);

        $chat = Chat::find($chatId);

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        $chat->update([
            'message' => $request->input('message'),
            'payload' => $request->has('payload') ? $request->input('payload') : $chat->payload,
        ]);

        $receiverId = $chat->to;
        $topic = "chat/updateMessage/{$receiverId}/{$chat->id}";

        $payloadArray = [
            'message'    => $chat->message,
            'from'       => $chat->from,
            'to'         => $chat->to,
            'read'       => (bool) $chat->read,
            'received'   => (bool) $chat->received,
            'id'         => (int) $chat->id,
            'removed'    => (bool) $chat->removed,
            'message_id' => $chat->message_id,
            'datetime'   => $chat->datetime,
            'payload'    => !empty($chat->payload) && $chat->payload != 'null' ? $chat->payload : null,
        ];

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

    public function deleteMessage($chatId)
    {
        $chat = Chat::find($chatId);

        if ($chat) {
            $receiverId = $chat->to;
            $chat->update([
                'removed' => true,
                'message' => '<REMOVED>',
            ]);
            $topic = "message/deleteMessage/{$receiverId}/{$chat->id}";

            $payload = json_encode([
                'id' => (int) $chatId,
                'message' => '<REMOVED>',
                'from' => $chat->from,
                'to' => $chat->to,
                'read' => (bool) $chat->read,
                'removed' => true,
                'received' => (bool) $chat->received,
                'message_id' => $chat->message_id,
                'datetime' => $chat->datetime,
            ]);

            $mqtt = MQTT::connection();
            $mqtt->publish($topic, $payload, 0, false);

            $conversationId = $chat->conversation_id;
            $remainingMessages = Chat::where('conversation_id', $conversationId)->where('removed', false)->count();

            if ($remainingMessages == 0) {
                Chat::where('conversation_id', $conversationId)->delete();
                Conversation::where('id', $conversationId)->delete();
            }
            return response()->json(
                [
                    'data' => new ChatResource($chat),
                ],
                200
            );
        }

        return response()->json(['message' => 'Chat not found'], 404);
    }
}
