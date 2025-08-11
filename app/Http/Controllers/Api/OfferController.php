<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use Illuminate\Support\Facades\Auth;
use PhpMqtt\Client\Facades\MQTT;

class OfferController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|integer',
            'buyer_id' => 'required|integer',
            'seller_id' => 'required|integer',
            'message' => 'nullable|string',
            'offered_price' => 'nullable|numeric',
            'made_by' => 'required|in:buyer,seller',
            'source' => 'in:chat,direct'
        ]);

        $offer = Offer::create([
            'post_id'       => $validated['post_id'],
            'buyer_id'      => $validated['buyer_id'],
            'seller_id'     => $validated['seller_id'],
            'message'       => $validated['message'] ?? null,
            'offered_price' => $validated['offered_price'] ?? null,
            'status'        => 'pending',
            'made_by'       => $validated['made_by'],
            'source'        => $validated['source'] ?? 'chat'
        ]);

        // Prepare MQTT payload
        $payload = json_encode([
            'id'            => $offer->id,
            'post_id'       => $offer->post_id,
            'buyer_id'      => $offer->buyer_id,
            'seller_id'     => $offer->seller_id,
            'message'       => $offer->message,
            'offered_price' => $offer->offered_price,
            'status'        => $offer->status,
            'made_by'       => $offer->made_by,
            'source'        => $offer->source,
            'created_at'    => $offer->created_at->toDateTimeString(),
        ]);

        // Publish to MQTT
        $topic = "offers/{$offer->post_id}";
        $mqtt = MQTT::connection();
        $mqtt->publish($topic, $payload, 0, false);

        return response()->json(['data' => $offer], 201);
    }

    public function respond(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accept,reject,counter',
            'offered_price' => 'nullable|numeric|min:1'
        ]);

        $offer = Offer::findOrFail($id);

        if (Auth::id() !== $offer->seller_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $mqtt = MQTT::connection();
        $topicBase = "offers/{$offer->post_id}";

        switch ($request->action) {
            case 'accept':
                $offer->update(['status' => 'accepted']);
                $payload = json_encode([
                    'event' => 'offer.accepted',
                    'offer' => $offer
                ]);
                $mqtt->publish("$topicBase/accepted", $payload);
                break;

            case 'reject':
                $offer->update(['status' => 'rejected']);
                $payload = json_encode([
                    'event' => 'offer.rejected',
                    'offer' => $offer
                ]);
                $mqtt->publish("$topicBase/rejected", $payload);
                break;

            case 'counter':
                // Keep the old offer as rejected
                $offer->update(['status' => 'rejected']);

                // Create counter offer
                $counterOffer = Offer::create([
                    'buyer_id'      => $offer->buyer_id,
                    'seller_id'     => $offer->seller_id,
                    'post_id'       => $offer->post_id,
                    'offered_price' => $request->offered_price,
                    'status'        => 'pending',
                    'made_by'       => 'seller',
                    'source'        => 'chat',
                ]);

                $payload = json_encode([
                    'event'        => 'offer.countered',
                    'original_id'  => $offer->id,
                    'counter_offer' => $counterOffer
                ]);
                $mqtt->publish("$topicBase/counter", $payload);

                return response()->json([
                    'message' => 'Counter offer created',
                    'offer'   => $counterOffer
                ]);
        }

        return response()->json([
            'message' => 'Offer response saved',
            'offer'   => $offer
        ]);
    }


    public function listByPost($post_id)
    {
        $offers = Offer::where('post_id', $post_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'post_id' => $post_id,
            'offers'  => $offers
        ]);
    }
}
