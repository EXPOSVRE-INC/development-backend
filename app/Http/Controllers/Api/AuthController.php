<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RegisterProfileRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\PaymentCardResource;
use App\Http\Resources\UserAddressResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Http\Service\StripeService;
use App\Mail\ResetPassword;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserShippingAddress;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Stripe\Customer;
use Stripe\StripeClient;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    private $stripeService;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
        $this->middleware('auth:api', [
            'except' => [
                'login',
                'register',
                'sendRecoveryPassword',
                'resetPassword',
                'confirmResetPassword',
                'publishMessage',
                'twoFaVerifyPhoneCode',
            ],
        ]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        $user = User::where(['email' => $credentials['email']])->with('profile')->first();

        if (!($token = auth('api')->attempt($credentials))) {
            return response()->json(
                [
                    'error' => 'Unauthorized',
                    'message' => 'Wrong email or password',
                ],
                401
            );
        }

        if ($user->status == 'deleted') {
            return response()->json(
                [
                    'error' => 'Account Deactivated',
                    'message' =>
                        'Your account has been deleted. Please contact support for assistance.',
                ],
                403
            );
        }

        if ($user->status == 'ban') {
            return response()->json(
                [
                    'error' => 'Account Banned',
                    'message' =>
                        "Your account has been banned for violating EXPOSVRE's terms and conditions!",
                ],
                403
            );
        }

        if ($user->twoFactorEnabled == 1) {
            if ($user->profile && $user->profile->phone) {
                $this->sendOtp($user->profile->phone);
                return response()->json([
                    'phone' => $user->profile->phone,
                ]);
            }

            return response()->json(
                [
                    'error' => 'Unauthorized',
                    'message' => 'Your phone number is not registered.',
                ],
                401
            );
        }
        if (
            $user->profile != null ||
            $user->profile->phone != null ||
            $user->phoneIsActivated == 1
        ) {
            return $this->respondWithToken($token);
        }
        
        //  else {
        //     return response()->json(
        //         [
        //             'error' => 'Unauthorized',
        //             'message' => 'Your phone number unverified',
        //         ],
        //         401
        //     );
        // }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function register(Request $request)
    {
        $findUser = User::where(['email' => $request->get('email')])->first();
        if ($findUser != null) {
            $findUser->username = $request->get('username');
            $findUser->email = $request->get('email');
            $findUser->password = $request->get('password');
            $findUser->save();
            $user = $findUser;
            $exposureUser = User::where(['id' => 1])->first();
            $user->subscribe($exposureUser);
        } else {
            $user = new User();
            $user->username = $request->get('username');
            $user->email = $request->get('email');
            $user->password = $request->get('password');
            $user->save();
            $exposureUser = User::where(['id' => 1])->first();
            $user->subscribe($exposureUser);
        }

        return new UserResource($user);
    }

    public function updateUser(Request $request)
    {
        $user = User::where(['id' => $request->get('id')])->first();

        $user->username = $request->get('username');
        $user->email = $request->get('email');
        $user->password = $request->get('password');
        $user->save();

        return new UserResource($user);
    }

    public function registerProfile(Request $request)
    {
        $profile = UserProfile::firstOrNew([
            'user_id' => auth('api')->user()->id,
        ]);
        $user = auth('api')->user();

        if (!$profile->firstName && !$request->has('firstName')) {
            return response()->json(['error' => 'First name is required'], 422);
        }

        if ($request->has('username')) {
            $user->username = $request->get('username');
        }

        $user->twoFactorEnabled  = $request->get('twoFactorEnabled') ?? false;
        $user->save();
        $profile->firstName = $request->get('firstName');
        $profile->lastName = $request->get('lastName');
        $profile->birthDate = Carbon::createFromTimestamp(
            $request->get('birthDate')
        );
        if (
            $profile->phone == null ||
            $profile->phone == '' ||
            $request->has('phone')
        ) {
            $profile->phone = $request->get('phone');
        }
        $profile->jobTitle = $request->get('jobTitle');
        $profile->jobDescription = $request->get('jobDescription');

        $profile->website = $request->get('website');
        $profile->instagram = $request->get('instagram');
        $profile->twitter = $request->get('twitter');
        $profile->user_id = $user->id;

        $profile->save();

        return response()->json(['data' => new UserResource($user)]);
    }

    public function verifyPhone(Request $request)
    {
        $accountSid = env('TWILIOACCOUNTID');
        $authToken = env('TWILIOTOKENID');
        $appSid = env('TWILIOAPPSID');
        $twilio = new Client($accountSid, $authToken);

        //        $request->validate(['phone' => 'unique:user_profile']);
        try {
            $result = $twilio->verify->v2
                ->services($appSid)
                ->verifications->create('+' . $request->get('phone'), 'sms');

            $user = auth('api')->user();

            if ($user->profile == null) {
                $newProfile = new UserProfile();
                $newProfile->user_id = $user->id;
                $newProfile->phone = $request->get('phone');
                $user->profile = $newProfile;
                $newProfile->save();
                $profile = $newProfile;
            } else {
                $profile = auth('api')->user()->profile;
            }
            $profile->phone = $request->get('phone');
            $profile->save();

            return response()->json(['data' => true]);

        } catch (\Twilio\Exceptions\RestException $e) {
            return response()->json([
                'data' => false,
                'error' =>  $e->getMessage(),
                'message'=>"The phone number is unverified"
            ], 403);
        }
    }

    public function verifyPhoneCode(Request $request)
    {
        $accountSid = env('TWILIOACCOUNTID');
        $authToken = env('TWILIOTOKENID');
        $appSid = env('TWILIOAPPSID');
        $twilio = new Client($accountSid, $authToken);

        try {
            // Check if Service SID is set
            if (empty($appSid)) {
                return response()->json([
                    'data' => false,
                    'error' => 'Twilio Service SID is missing or invalid.'
                ], 400);
            }

            // Get the phone number from the user's profile
            $phoneNumber = auth('api')->user()->profile->phone;
            if (empty($phoneNumber)) {
                $phoneNumber = $request->get('phone');
                $userProfile = auth('api')->user()->profile;
                $userProfile->phone = $phoneNumber;
                $userProfile->save();
            }

            // Verify the code using Twilio
            $verification = $twilio->verify->v2
                ->services($appSid)
                ->verificationChecks->create([
                    'to' => '+' . $phoneNumber,
                    'code' => $request->get('code'),
                ]);

            // If verification is valid, activate the phone number
            if ($verification->valid) {
                $user = auth('api')->user();
                $user->phoneIsActivated = true;
                $user->save();
                return response()->json(['data' => true]);
            } else {
                // Handle invalid OTP
                return response()->json([
                    'data' => false,
                    'error' => 'Invalid verification code.'
                ], 400);
            }

        } catch (\Twilio\Exceptions\RestException $e) {
            // Handle specific Twilio errors (like wrong Service SID)
            return response()->json([
                'data' => false,
                'error' => 'Twilio Error: ' . $e->getMessage()
            ], $e->getStatusCode() ?? 403);
        } catch (\Exception $e) {
            // Handle general errors
            return response()->json([
                'data' => false,
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyEmail(Request $request)
    {
    }

    public function verifyEmailCode(Request $request)
    {
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            //            'expires_in' => -1
            //            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function changePassword(Request $request)
    {
        #Match The Old Password
        if (
            !Hash::check($request->old_password, auth('api')->user()->password)
        ) {
            return response()->json([
                'error' => true,
                'status' => 'error',
                'message' => "Old Password Doesn't match!",
            ]);
        }

        #Update the new Password
        if ($request->new_password == $request->confirm_password) {
            User::whereId(auth('api')->user()->id)->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json(['data' => []]);
        } else {
            return response()->json([
                'error' => true,
                'status' => 'error',
                'message' => "New password and Confirm password Doesn't match!",
            ]);
        }
    }

    public function setAddress(Request $request)
    {
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

        return response()->json(['data' => new UserAddressResource($address)]);
        //        $address->lat = $request->get('lat');
        //        $address->lon = $request->get('lon');
    }

    public function getAddress()
    {
        $user = auth('api')->user();
        if ($user->address) {
            return response()->json([
                'data' => new UserAddressResource($user->address),
            ]);
        } else {
            return response()->json([
                'data' => [
                    'country' => '',
                    'state' => '',
                    'city' => '',
                    'zip' => '',
                    'address' => '',
                ],
            ]);
        }
    }

    public function addPaymentData(Request $request)
    {
        $user = auth('api')->user();
        if (!$user->stripeCustomerId) {
            $customer = $this->stripeService->createCustomer($request, $user);
            $customerId = $customer->id;
        } else {
            //            $client = new StripeClient(env('STRIPE_SECRET'));
            //
            //            $client->paymentMethods->attach($request->stripePaymentMethod, [
            //                'customer' => $user->stripeCustomerId
            //            ]);

            $card = $this->stripeService->createCard($request->all());
            //            $customer = Customer::createSource($user->stripeCustomerId, ['paymentMethod' => $card->id]);
            //            $customerId = $user->stripeCustomerId;
        }

        return response()->json([
            'data' => PaymentCardResource::collection(
                auth('api')->user()->paymentCards
            ),
        ]);
    }

    public function getCardList()
    {
        return response()->json([
            'data' => PaymentCardResource::collection(
                auth('api')->user()->paymentCards
            ),
        ]);
    }

    public function getFinishRegistration()
    {
        $user = auth('api')->user();
        $user->isConfirmed = true;
        $user->save();

        return response()->json(['data' => new UserResource($user)]);
    }

    public function sendRecoveryPassword(Request $request)
    {
        if (!$request->has('email')) {
            return response()->json(['error' => 'Email is required'], 400);
        }
    
        $user = User::where('email', $request->get('email'))->first();
    
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    
        $token = Password::createToken($user);
    
        Mail::to($user->email)->send(new ResetPassword($user, $token));
    
        return response()->json(['data' => 'Reset link sent']);
    }

    public function resetPassword($token, Request $request)
    {
        $user = User::where(['email' => $request->get('email')])->first();
        $checkToken = Password::tokenExists($user, $token);
        if ($checkToken == true) {
            return redirect()->to(
                'EXPOSVRE://passwordreset/' . $user->email . '/' . $token
            );
        } else {
            return response()->json(['data' => 'Wrong token!']);
        }
    }

    public function confirmResetPassword(Request $request)
    {
        $request->merge(['password_confirmation' => $request->get('password')]);
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {
                $user
                    ->forceFill([
                        'password' => $password,
                    ])
                    ->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json(['data' => $status]);
    }
    public function publishMessage(){
    }

    protected function sendOtp($phoneNumber)
    {
        //send otp
        $accountSid = env('TWILIOACCOUNTID');
        $authToken = env('TWILIOTOKENID');
        $appSid = env('TWILIOAPPSID');
        $twilio = new \Twilio\Rest\Client($accountSid, $authToken);

        try {
            $twilio->verify->v2
                ->services($appSid)
                ->verifications
                ->create('+' . $phoneNumber, 'sms');
        } catch (\Twilio\Exceptions\RestException $e) {
            // Handle Twilio errors
            throw new \Exception('Failed to send OTP: ' . $e->getMessage());
        }
    }

    public function twoFaVerifyPhoneCode(Request $request)
    {
        $accountSid = env('TWILIOACCOUNTID');
        $authToken = env('TWILIOTOKENID');
        $appSid = env('TWILIOAPPSID');
        $twilio = new Client($accountSid, $authToken);


        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation errors occurred.',
                    'errors' => $validator->errors(),
                ],
                422
            );
        }

        try {
            if (empty($appSid)) {
                return response()->json([
                    'data' => false,
                    'error' => 'Twilio Service SID is missing or invalid.'
                ], 400);
            }

            $phoneNumber = $request->get('phone');

            $verification = $twilio->verify->v2
                ->services($appSid)
                ->verificationChecks->create([
                    'to' => '+' . $phoneNumber,
                    'code' => $request->get('code'),
                ]);

            if ($verification->valid) {
                $profile = UserProfile::where('phone', $phoneNumber)->first();

                if ($profile && $profile->user) {
                    $user = $profile->user;
                    $user->phoneIsActivated = true;
                    $user->save();

                    $token = auth('api')->login($user);

                    return response()->json([
                        'data' => true,
                        'access_token' => $token,
                        'token_type' => 'bearer',
                    ]);
                } else {
                    return response()->json([
                        'data' => false,
                        'error' => 'User not found for the provided phone number.'
                    ], 404);
                }
            } else {
                return response()->json([
                    'data' => false,
                    'error' => 'Invalid verification code.'
                ], 400);
            }

        } catch (\Twilio\Exceptions\RestException $e) {
            return response()->json([
                'data' => false,
                'error' => 'Twilio Error: ' . $e->getMessage()
            ], $e->getStatusCode() ?? 403);
        } catch (\Exception $e) {
            return response()->json([
                'data' => false,
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

}
