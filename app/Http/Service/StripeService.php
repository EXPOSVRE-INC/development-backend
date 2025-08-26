<?php

namespace App\Http\Service;

use App\Http\Requests\SearchPostRequest;
use App\Http\Resources\HybridPurchasesResource;
use App\Http\Resources\HybridSalesResource;
use App\Http\Resources\PurchasesResource;
use App\Http\Resources\SalesResource;
use App\Models\Order;
use App\Models\PaymentAccount;
use App\Models\PaymentCard;
use App\Models\Post;
use App\Models\User;
use App\Notifications\SaleNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\BankAccount;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Token;

class StripeService
{

    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function createCustomer(Request $request, User $user)
    {
        //        try {
        if ($user->profile != null) {
            $data['phone'] = $user->profile->phone;
        }
        $data = [
            //            'payment_method' => $request->stripePaymentMethod,
            'email' => $user->email,
        ];
        $result = Customer::create($data);
        $user->stripeCustomerId = $result->id;
        $user->save();

        //        dump($result);
        $card = $this->createCard($request->all());


        return $result;
        //        } catch (\Exception $e) {
        //            return $e->getMessage();
        //        }
    }

    public function createCard($data)
    {
        //    $card = Token::create([
        //        'card' => [
        //            'name' => $data['holder'],
        //            'number' => $data['number'],
        //            'exp_month' => sprintf("%02d", $data['expMonth']),
        //            'exp_year' => $data['expYear'],
        //            'cvc' => $data['cvv']
        //        ],
        //    ]);
        $client = new StripeClient(env('STRIPE_SECRET'));
        $client->paymentMethods->attach($data['stripePaymentMethod'], [
            'customer' => auth('api')->user()->stripeCustomerId
        ]);
        $newCard = new PaymentCard();
        $newCard->holder = $data['holder'];
        $newCard->last4 = $data['last4'];
        $newCard->stripeToken = $data['stripePaymentMethod'];
        $newCard->isActive = auth('api')->user()->paymentCards()->count() == 0 ? true : false;
        $newCard->user_id = auth('api')->user()->id;
        $newCard->save();

        return $newCard;
    }

    public function createBankAccount($request)
    {
        $client = new StripeClient(env('STRIPE_SECRET'));


        $user = auth()->user();
        $bankAccount = Token::create([
            'bank_account' => [
                'country' => 'US',
                'currency' => 'usd',
                'account_holder_name' => $user->profile->firstName . ' ' . $user->profile->lastName,
                'account_holder_type' => 'individual',
                'bank_name' => $request->get('nameOfBank'),
                'routing_number' => $request->get('routingNumber'),
                'account_number' => $request->get('accountNumber'),
            ]
        ]);

        if ($user->stripeAccountId != null) {
            $account = Account::retrieve($user->stripeAccountId);
        } else {
            $account = $this->createAccount($request);
        }

        $externalAccount = $client->accounts->createExternalAccount($account->id, [
            'external_account' => $bankAccount->id
        ]);

        if ($externalAccount) {
            $request->merge(['user_id' => $user->id]);
            $request->merge(['stripeId' => $externalAccount->id]);
            $request->merge(['nameOfBank' => $request->get('bankName')]);
            $request->merge(['zipCode' => $request->get('zip')]);
            $request->merge(['addressOfBank' => $request->get('bankAddress')]);
            $newAccount = PaymentAccount::create($request->all());
        }

        return $newAccount;
    }


    public function getStateTax($post_id)
    {
        $user = auth('api')->user();
        $client = new StripeClient(env('STRIPE_SECRET'));

        $post = Post::where(['id' => $post_id])->first();

        $taxObject = $client->taxRates->create([
            'currency' => 'usd',
            'line_items' => [
                [
                    'amount' => $post->fixed_price + $post->shippingPrice,
                    'reference' => 'L1',
                ],
            ],
            'customer_details' => [
                'address' => [
                    'line1' => '920 5th Ave',
                    'city' => 'Seattle',
                    'state' => 'WA',
                    'postal_code' => '98104',
                    'country' => 'US',
                ],
                'address_source' => 'shipping',
            ],
        ]);
        dump($taxObject);
    }


    public function createAccount($request)
    {
        $client = new StripeClient(env('STRIPE_SECRET'));

        $user = auth()->user();

        if ($user->stripeAccountId) {
            $account = Account::retrieve($user->stripeAccountId);
        } else {
            $account = Account::create(
                [
                    'type' => 'standard',
                    'country' => 'US',
                    'email' => auth()->user()->email,
                    //                'capabilities' => [
                    //                    'card_payments' => ['requested' => true],
                    //                    'transfers' => ['requested' => true],
                    //                ],
                ]
            );
        }


        //        $account = Account::create(
        //            [
        //                'type' => 'standard',
        //                'country' => 'US',
        //                'email' => auth()->user()->email,
        //                'business_type' => 'individual',
        //                'capabilities' => [
        //                    'card_payments' => ['requested' => true],
        //                    'transfers' => ['requested' => true],
        //                ],
        //                'business_profile' => [
        //                    'url' => 'app.exposvre.com',
        //                    'mcc' => '7221'
        //                ],
        //                'company' => [
        //                    'address' => [
        //                        'city' => 'Bishop',
        //                        "country" => "US",
        //                        "line1" => "350 Short St",
        //                        "postal_code" => "93514",
        //                        "state" => "CA"
        //                    ],
        //                    'name' => $user->profile->firstName . " " . $user->profile->lastName,
        //                    'tax_id' => '000000000',
        //                    'phone' => '+14844608139',
        //                ],
        //                'individual' => [
        //                    'email' => $user->email,
        //                    'first_name' => $user->profile->firstName,
        //                    'last_name' => $user->profile->lastName,
        //                    'phone' => '+14844731459',
        //                    'ssn_last_4' => '0000',
        ////                    'id_number' => '123456789',
        //                    'address' => [
        //                        'city' => 'Bishop',
        //                        "country" => "US",
        //                        "line1" => "350 Short St",
        //                        "postal_code" => "93514",
        //                        "state" => "CA"
        //                    ],
        //                    'dob' => [
        //                        'day' => 20,
        //                        'month' => 7,
        //                        'year' => 1990
        //                    ]
        //                ]
        //            ]
        //        );

        $user->stripeAccountId = $account->id;
        $user->save();


        //        $client->accounts->update($account->id, [
        //            'tos_acceptance' => [
        //                'date' => Carbon::now()->timestamp,
        //                'ip' => $request->ip(),
        //            ],
        //        ]);
        $accountLink = $client->accountLinks->create([
            'account' => $account->id,
            'refresh_url' => 'https://app.exposvre.com/stripe-redirect',
            'return_url' => 'https://app.exposvre.com/stripe-redirect',
            'type' => 'account_onboarding',
        ]);
        //        dump($accountLink->url);

        return $accountLink->url;
    }

    public function buy(Request $request)
    {
        $post = Post::where(['id' => $request->get('id')])->first();
        $user = auth('api')->user();
        $priceRequest = \App\Models\PriceRequest::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->where('status', 'offer_accepted') // adjust if you store status
            ->first();

        $price = $priceRequest ? $priceRequest->offered_price : $post->fixed_price;

        if ($post->shippingIncluded == 0) {
            $price = $price + $post->shippingPrice + $post->fixed_price * 0.059 + $post->fixed_price * 0.0825;
        }
        $payment_method = $user->paymentCards->filter(function ($card) {
            return $card->isActive == 1;
        })->first();
        if ($price > 0) {
            $payment_intent = \Stripe\PaymentIntent::create([
                'payment_method_types' => ['card'],
                'payment_method' => $payment_method->stripeToken,
                //            'automatic_payment_methods' => ['enabled' => true],
                'amount' => round($price * 100),
                'customer' => $user->stripeCustomerId,
                'currency' => 'usd',
                'description' => $post->title,
                'application_fee_amount' => round($post->fixed_price * 0.1 * 100), // also in cents!
                'transfer_data' => [
                    //                        'amount' => round($request->amount * $space->applicationFee/100),
                    'destination' => $post->owner->stripeAccountId,
                ],
                'receipt_email' => $user->email,
                "confirmation_method" => "automatic",
                "capture_method" => "automatic",
                'setup_future_usage' => 'off_session',
                'metadata' => [
                    'post_id' => $post->id
                ]
            ])->confirm();
        }


        $order = new Order();
        $order->buyer_id = $user->id;
        $order->seller_id = $post->owner->id;
        $order->post_id = $post->id;
        $order->price = $price;
        $order->status = 'completed';
        if ($price > 0) {
            $order->payment_intent_id = $payment_intent->id;
        } else {
            $order->payment_intent_id = 'free';
        }
        if ($user->address) {
            $order->shipping_address_id = $user->address->id;
        }

        $notification = new \App\Models\Notification();
        $deepLink = 'EXPOSVRE://sale/' . $post->id;
        $notification->deep_link = $deepLink;
        $notification->title = $user->profile->firstName . ' ' . $user->profile->lastName . ' buy your post';
        $notification->description = 'buy your post';
        $notification->type = 'sale';
        $notification->user_id = $post->owner_id;
        $notification->sender_id = $user->id;
        $notification->post_id = $post->id;
        $notification->save();

        $post->owner->notify(new SaleNotification($post, $user));

        $order->save();

        return $order;
    }

    public function listCustomerPaymentMethods()
    {
        $user = auth()->user();

        $client = new StripeClient(env('STRIPE_SECRET'));

        $paymentMethods = $client->customers->allSources(
            $user->stripeCustomerId,
            [
                'object' => 'card',
            ]
        );

        return $paymentMethods;
    }

    public function removeCustomerCard(Request $request)
    {
        $user = auth()->user();
        $client = new StripeClient(env('STRIPE_SECRET'));

        $findCard = PaymentCard::where(['id' => $request->get('id')])->first();

        $delete = $client->paymentMethods->detach($findCard->stripeToken);
        $findCard->delete();

        return $user->paymentCards;
    }

    public function setDefaultCard(Request $request)
    {

        $user = auth()->user();

        $findCard = PaymentCard::where(['id' => $request->get('id')])->first();

        //        $customer = Customer::retrieve($user->stripeCustomerId);

        $customer = Customer::update($user->stripeCustomerId, [
            'invoice_settings' => [
                'default_payment_method' => $findCard->stripeToken
            ]
        ]);

        foreach ($user->paymentCards as $paymentCard) {
            if ($paymentCard->isActive) {
                $paymentCard->isActive = false;
                $paymentCard->save();
            }
        }

        $findCard->isActive = true;
        $findCard->save();

        $user->refresh();

        return $user->paymentCards;
    }

    public function setDefaultBankAccount(Request $request)
    {

        $user = auth()->user();

        $BA = PaymentAccount::where(['id' => $request->get('id')])->first();

        $client = new StripeClient(env('STRIPE_SECRET'));

        $client->accounts->updateExternalAccount(
            $user->stripeAccountId,
            $BA->stripeId,
            [
                'default_for_currency' => true
            ]
        );

        foreach ($user->paymentAccounts as $paymentAccount) {
            if ($paymentAccount->isActive) {
                $paymentAccount->isActive = false;
                $paymentAccount->save();
            }
        }

        $BA->isActive = true;
        $BA->save();

        $user->refresh();
        return $user->paymentAccounts;
    }

    public function removePaymentAccount(Request $request)
    {

        $user = auth()->user();
        $client = new StripeClient(env('STRIPE_SECRET'));

        $BA = PaymentAccount::where(['id' => $request->get('id')])->first();

        $delete = $client->accounts->deleteExternalAccount(
            $user->stripeAccountId,
            $BA->stripeId,
            []
        );

        if ($delete->deleted == true) {
            $BA->delete();
        }
        return $user->paymentAccounts;
    }

    public function listPurchases()
    {
        $ordersForBuyer = auth()->user()->ordersForBuyer;

        return PurchasesResource::collection($ordersForBuyer);
    }

    public function listSales()
    {
        $ordersForSeller = auth()->user()->ordersForSeller;

        return SalesResource::collection($ordersForSeller);
    }

    public function hybridlListPurchases()
    {
        $ordersForBuyer = auth()->user()->ordersForBuyer;

        return HybridPurchasesResource::collection($ordersForBuyer);
    }

    public function hybridListSales()
    {
        $ordersForSeller = auth()->user()->ordersForSeller;

        return HybridSalesResource::collection($ordersForSeller);
    }
}
