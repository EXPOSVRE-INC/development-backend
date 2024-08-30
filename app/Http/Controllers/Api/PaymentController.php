<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentAccountResource;
use App\Http\Resources\PaymentCardResource;
use App\Http\Resources\PurchasesResource;
use App\Http\Resources\SalesResource;
use App\Http\Resources\UserWithShippingAddressResource;
use App\Http\Service\StripeService;
use App\Models\Order;
use App\Models\PaymentAccount;
use App\Models\Post;
use App\Models\PriceRequest;
use App\Models\UserShippingAddress;
use App\Notifications\PriceRequestAcceptedNotification;
use App\Notifications\PriceRequestDeclinedNotification;
use App\Notifications\PriceRequestNotification;
use Illuminate\Http\Request;
use Stripe\Account;

class PaymentController extends Controller
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function addStripeAccount(Request $request) {
        $account = $this->stripeService->createAccount($request);

        return response()->json(['data' => $account]);
    }

    public function buyPost(Request $request) {
        $order = $this->stripeService->buy($request);
        return response()->json(['data' => new PurchasesResource($order)]);
    }

    public function listAccountPayoutMethods(Request $request) {
        $user = auth('api')->user();
        $account = Account::retrieve($user->stripeAccountId, []);

        if (auth('api')->user()->paymentAccounts->count() < 1 && $account->external_accounts != null ){
            $externalAccount = new PaymentAccount();
            $externalAccount->stripeId = $account->external_accounts->data[0]->id;
            $externalAccount->accountNumber = $account->external_accounts->data[0]->last4;
            $externalAccount->nameOfBank = $account->external_accounts->data[0]->bank_name;
            $externalAccount->user_id = $user->id;
            $externalAccount->isActive = 1;
            $externalAccount->save();
        }
        auth('api')->user()->refresh();

        return response()->json(['data' => PaymentAccountResource::collection(auth('api')->user()->paymentAccounts)]);
    }

    public function removeCard(Request $request) {
        $cards = $this->stripeService->removeCustomerCard($request);

        return response()->json(['data' => PaymentCardResource::collection($cards)]);
    }

    public function setDefaultCard(Request $request) {
        $cards = $this->stripeService->setDefaultCard($request);

        return response()->json(['data' => PaymentCardResource::collection($cards)]);
    }

    public function removePaymentAccount(Request $request) {
        $accounts = $this->stripeService->removePaymentAccount($request);

        return response()->json(['data' => PaymentAccountResource::collection($accounts)]);
    }

    public function setDefaultAccount(Request $request) {
        $accounts = $this->stripeService->setDefaultBankAccount($request);

        return response()->json(['data' => PaymentAccountResource::collection($accounts)]);
    }

    public function listPurchases() {
        return response()->json(['data' => $this->stripeService->listPurchases()]);
    }

    public function listSales() {
        return response()->json(['data' => $this->stripeService->listSales()]);
    }

    public function listTransactions(Request $request) {
        $sales = $this->stripeService->listSales()->toArray($request);
        $purchases = $this->stripeService->listPurchases()->toArray($request);

        return response()->json(['data' => array_merge($sales, $purchases)]);
    }

    public function setShippingAddressByOrderId($id, Request $request) {
        $order = Order::where(['id' => $id])->first();

        $user = auth('api')->user();
        if ($user->address) {
            $user->address->delete();
        }

        $address = new UserShippingAddress();
        $address->country = $request->get('country');
        $address->state = $request->get('state');
        $address->city = $request->get('city');
        $address->zip = $request->get('zip');
        $address->address = $request->get('address');
        $address->user_id = auth('api')->user()->id;

        $address->save();

        $order->shipping_address_id = $address->id;
        $order->save();

        return response()->json(['data' => new PurchasesResource($order)]);
    }

    public function setOrderStatus($id, Request $request)
    {
        $order = Order::where(['id' => $id])->first();

        $order->status = $request->get('status');
        if ($request->has('shipping') && $request->get('shipping') != '') {
            $order->shippingMethod = $request->get('shipping');
        }
        if ($request->has('number') && $request->get('number') != '') {
            $order->trackingNumber = $request->get('number');
        }
        $order->save();

        return response()->json(['data' => SalesResource::make($order)]);
    }

    public function getBuyerByOrderId(Order $order) {
        if ($order->seller->id == auth('api')->user()->id) {
            $buyer = $order->buyer;
            return response()->json(['data' => ['buyer' => UserWithShippingAddressResource::make($buyer)]]);
        } else {
            return response()->json(['error' => 'No access'], 401);
        }

    }

    public function sendPriceRequest($postId)
    {
        $post = Post::where(['id' => $postId])->first();

        $request = new PriceRequest();
        $request->post_id = $post->id;
        $request->user_id = auth('api')->user()->id;
        $request->status = 'new';
        $request->save();

        $user = auth('api')->user();

        $notification = new \App\Models\Notification();
        $notification->title = $user->profile->firstName . ' ' . $user->profile->lastName . ' is interested in item';
        $notification->description = 'interested in item';
        $notification->type = 'priceRequest';
        $notification->user_id = $post->owner_id;
        $notification->sender_id = $user->id;
        $notification->post_id = $post->id;
        $notification->deep_link = '';
        $notification->save();

        $deepLink = 'EXPOSVRE://request/' . $post->id . '/' .$request->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        $post->owner->notify(new PriceRequestNotification($post, $user, $request));

        return response()->json(['data' => $request]);
    }

    public function acceptRequest($requestId)
    {
        $user = auth('api')->user();
        $request = PriceRequest::where(['id' => $requestId])->first();
        $request->status = 'accepted';
        $request->save();

        $deepLink = 'EXPOSVRE://post/' . $request->post->id;
        $notification = new \App\Models\Notification();
        $notification->title = 'Hi! The price of post is ' . round($request->post->fixed_price/100, 2) . '$';
        $notification->description = $request->id;
        $notification->type = 'priceRespondedApprove';
        $notification->user_id = $request->requestor->id;
        $notification->sender_id = $request->post->owner_id;
        $notification->post_id = $request->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();


        $deepLink = 'EXPOSVRE://post/' . $request->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        $request->requestor->notify(new PriceRequestAcceptedNotification($request->post, auth('api')->user(), $request));

//        $priceRequests = PriceRequest::where(['post_id' => $request->porst_id, 'user_id' => $request->user_id])->get();
//        foreach ($priceRequests as $priceRequest) {
//            $priceRequest->delete();
//        }

        return response()->json(['data' => $request]);
    }

    public function declineRequest($requestId)
    {
        $request = PriceRequest::where(['id' => $requestId])->first();
        $request->status = 'declined';
        $request->save();
        $user = auth('api')->user();

        $deepLink = 'EXPOSVRE://post/' . $request->post->id;
        $notification = new \App\Models\Notification();
        $notification->title = 'Price request declined';
        $notification->description = $request->id;
        $notification->type = 'priceRespondedDecline';
        $notification->user_id = $request->requestor->id;
        $notification->sender_id = $request->post->owner_id;
        $notification->post_id = $request->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        $request->requestor->notify(new PriceRequestDeclinedNotification($request->post, $user, $request));


        return response()->json(['data' => $request]);
    }
}
