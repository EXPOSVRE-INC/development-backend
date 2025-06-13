<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MusicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::get('publish', [AuthController::class, 'publishMessage']);
// Route::get('publish', 'AuthController@publishMessage');


Route::group([
    'middleware' => ['api', 'x-token'],
    'prefix' => 'v.1.0',
    'namespace' => '\App\Http\Controllers\Api'
], function ($router) {
    Route::group([
        'prefix' => 'mobile'
    ], function ($router) {
        Route::group([
            'prefix' => 'auth'
        ], function ($router) {
            Route::post('login', 'AuthController@login');
            Route::post('register', 'AuthController@register');
            Route::post('user-register', 'AuthController@userRegistration');
            Route::post('update-user', 'AuthController@updateUser');

            Route::post('recovery-password', 'AuthController@sendRecoveryPassword');

            Route::get('/reset-password/{token}', 'AuthController@resetPassword')->name('password.reset');
            Route::post('/confirm-password', 'AuthController@confirmResetPassword');

            Route::post('register/profile', 'AuthController@registerProfile');

            Route::post('phone-send-code', 'AuthController@verifyPhone');
            Route::post('verify-phone-code', 'AuthController@verifyPhoneCode');
            Route::post('two-fa-verify-phone-code', 'AuthController@twoFaVerifyPhoneCode');


            Route::post('email-send-code', 'AuthController@verifyEmail');
            Route::post('verify-email-code', 'AuthController@verifyEmailCode');

            Route::post('logout', 'AuthController@logout');
            Route::post('change-password', 'AuthController@changePassword');
            Route::post('set-address', 'AuthController@setAddress');
            Route::get('get-my-address', 'AuthController@getAddress');

            Route::post('set-payment-card', 'AuthController@addPaymentData');
            Route::get('get-card-list', 'AuthController@getCardList');

            Route::get('finish', 'AuthController@getFinishRegistration');
            Route::get('/user-me', 'AuthController@getUserDetail');

            Route::post('refresh', 'AuthController@refresh');
            Route::get('/test-push', 'UserController@test');
        });

        //        Route::post('address-autocomplete', 'UserController@getAddress');

        Route::group([
            'prefix' => 'search',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::post('/', 'SearchController@index');
        });

        Route::group([
            'prefix' => 'order',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::post('request-refund', 'OrderController@requestRefund');
            Route::post('provide-refund', 'OrderController@responseRefund');
        });

        Route::group([
            'prefix' => 'payment',
            'middleware' => ['auth:api']
        ], function ($router) {

            Route::post('set-payment-card', 'AuthController@addPaymentData');
            Route::get('get-card-list', 'AuthController@getCardList');

            Route::get('get-buyer-by-order-id/{order}', 'PaymentController@getBuyerByOrderId');

            Route::post('/remove-card', 'PaymentController@removeCard');
            Route::post('/remove-payout-account', 'PaymentController@removePaymentAccount');

            Route::post('/set-default-card', 'PaymentController@setDefaultCard');
            Route::post('/set-default-payout-method', 'PaymentController@setDefaultAccount');

            Route::post('/account', 'PaymentController@addStripeAccount');
            Route::post('/create-bank-account', 'PaymentController@createStripeBankAccount');
            Route::get('/list-payouts', 'PaymentController@listAccountPayoutMethods');

            Route::post('/buy', 'PaymentController@buyPost');

            Route::post('/set-shipping-address-by-order-id/{id}', 'PaymentController@setShippingAddressByOrderId');

            Route::post('/set-order-status/{id}', 'PaymentController@setOrderStatus');

            Route::get('/list-purchases', 'PaymentController@listPurchases');
            Route::get('/list-sales', 'PaymentController@listSales');
            Route::get('/list-transactions', 'PaymentController@listTransactions');
        });

        Route::group([
            'prefix' => 'settings',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::get('/milestones', 'UserController@milestones');
            Route::post('/setup', 'UserController@setupSettings');
        });

        Route::group([
            'prefix' => 'address',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::post('/add', 'UserController@addShippingAddress');
        });

        Route::group([
            'prefix' => 'collections',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::post('upload', 'PostController@fileUploaderForCollection');
            Route::get('drop-files', 'PostController@dropCollectionFiles');

            Route::get('list-collections-by-user-id/{id}', 'PostController@listCollectionsByUser');
            Route::get('list-collection-posts-ids/{id}', 'PostController@listPostsByCollectionId');

            Route::get('get/{id}', 'PostController@getCollection');

            Route::get('like/{id}', 'PostController@likeCollection');
            Route::get('unlike/{id}', 'PostController@unlikeCollection');

            Route::post('set-collection-by-post-ids-array/{id}', 'PostController@setCollectionByPostIdsArray');

            Route::post('create', 'PostController@createCollection');
            Route::post('update/{id}', 'PostController@updateCollection');

            Route::get('remove/{id}', 'PostController@removeCollection');

            Route::post('comment/{id}', 'PostController@commentCollection');
            Route::get('comments-list/{id}', 'PostController@collectionListComments');
        });

        Route::group([
            'prefix' => 'posts',
            'middleware' => ['auth:api']
        ], function () {

            // 🔹 Basic Retrieval
            Route::get('/saved-posts', 'PostController@getSavedPosts');
            Route::get('/archived-posts', 'PostController@getArchivedPosts');
            Route::get('/most-crowned', 'PostController@mostCrowned');
            Route::get('/most-viewed', 'PostController@mostViewed');

            Route::get('/', 'PostController@index');
            Route::get('/view/{id}', 'PostController@viewPost');

            // 🔹 Post Creation & Updates
            Route::post('/create', 'PostController@createPost');
            Route::post('/{post}', 'PostController@updatePost');

            Route::get('/{post}', 'PostController@getPost');
            // 🔹 Deletion
            Route::get('/delete/{post}', 'PostController@deletePost');

            // 🔹 Media Uploads
            Route::post('/upload', 'PostController@fileUploader');
            Route::post('/multiple-file-upload', 'PostController@multipleFileUploader');
            Route::get('/get-files', 'PostController@getAllPostImages');
            Route::post('/drop-file-by-uuid', 'PostController@dropFileByUuid');
            Route::get('/drop-files', 'PostController@dropFiles');

            // 🔹 Search & Filters
            Route::post('/search', 'PostController@search');
            Route::post('/search-tag', 'PostController@searchPostsByTag');
            Route::post('/search-interest', 'PostController@searchPostsByInterest');

            // 🔹 Interactions: Likes, Favorites, Comments
            Route::get('/like/{post}', 'PostController@likePost');
            Route::get('/unlike/{post}', 'PostController@unlikePost');

            Route::get('/add-favorite/{post}', 'PostController@favoritePost');
            Route::get('/remove-favorite/{post}', 'PostController@unfavoritePost');

            Route::get('/{post}/comments', 'PostController@getComments');
            Route::post('/{post}/comment', 'PostController@setComment');

            // 🔹 Post State: Archive, Repost, Saved
            Route::get('/archive/{post}', 'PostController@addToArchive');
            Route::get('/remove-archive/{post}', 'PostController@removeFromArchive');
            Route::get('/repost/{post}', 'PostController@repost');


            // 🔹 Price Request Handling
            Route::get('/send-price-request/{id}', 'PaymentController@sendPriceRequest');
            Route::get('/accept-price-request/{id}', 'PaymentController@acceptRequest');
            Route::get('/decline-price-request/{id}', 'PaymentController@declineRequest');

            Route::group([
                'prefix' => 'interestsCategory',
                'middleware' => ['auth:api']
            ], function ($router) {
                Route::get('/index', 'CategoryInterestsController@index');
            });
        });

        Route::group([
            'prefix' => 'notifications',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::get('/', 'UserController@getNotificationsList');
            Route::post('action', 'UserController@notificationAction');
        });

        Route::get('subscriptions', 'UserController@subscriptions');
        Route::get('subscribers', 'UserController@subscribers');

        Route::get('subscriptions-and-subscribers-by-user-id/{id}', 'UserController@subscriptionsAndSubscribersByUserId');

        Route::get('remove-user', 'UserController@delete')->middleware(['auth:api']);

        Route::group([
            'prefix' => 'user',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::get('/{id}', 'UserController@userInfo');
            Route::get('/by-username/{username}', 'UserController@userInfoByUsername');

            Route::get('/market-like/{id}', 'UserController@marketLike');

            Route::get('/market-unlike/{id}', 'UserController@marketUnlike');

            Route::get('/{id}/subscribe', 'UserController@subscribe');
            Route::get('/{id}/unsubscribe', 'UserController@unsubscribe');


            Route::get('/block/{id}', 'UserController@blockUser');
            Route::get('/blocked/list', 'UserController@blockedList');
            Route::get('/unblock/{id}', 'UserController@unblockUser');

            Route::post('/feed', 'UserController@feed');
            Route::get('/feeds/main', 'UserController@mainFeed');
            Route::get('/feeds/editorial', 'UserController@editorial');
            Route::get('/feeds/market', 'UserController@market');

            Route::post('/set-push-token', 'UserController@setToken');

            Route::post('/avatar-upload', 'UserController@avatarUpload');

            Route::get('/{id}/posts', 'UserController@userPosts');

            Route::post('/tag/assign', 'UserController@assignTag');

            Route::post('/tag/remove', 'UserController@removeTag');

            Route::post('/sort-posts', 'PostController@sortPostsOrderForDashboard');
            Route::group([
                'prefix' => 'interests',
            ], function ($router) {
                Route::get('/', 'UserController@index');
                Route::post('/assign', 'UserController@assignInterest');
                Route::post('/assign/array', 'UserController@assignInterestArray');
            });
            Route::group([
                'prefix' => 'not-interests',
            ], function ($router) {
                Route::post('/assign', 'UserController@assignNotInterest');
                Route::post('/assign/array', 'UserController@assignNotInterestArray');
            });
        });

        Route::get('/test-video-stream', 'VideoController@streamVideo');

        Route::get('/test-text', 'VideoController@testText');

        Route::get('/test-image', 'VideoController@testImage');

        Route::get('/test-image-1', 'VideoController@testImage1');

        Route::get('/test-image-2', 'VideoController@testImage2');

        Route::get('/get-video-stream/{uuid}', 'VideoController@streamVideoByUuid')->name('video-stream');

        Route::group([
            'prefix' => 'interests',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::get('/', 'CategoryInterestsController@index');
        });

        Route::group([
            'prefix' => 'conversations',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::get('/', [ChatController::class, 'index']); // List all conversations
            Route::get('/chat/fetch-message', [ChatController::class, 'getMessage']);
            Route::post('/chat/send-message', [ChatController::class, 'sendMessage']);
            Route::put('/chat/read-message/{chatId}', [ChatController::class, 'readMessage']);
            Route::put('/chat/edit-message/{chatId}', [ChatController::class, 'editMessage']);
            Route::delete('/chat/delete-message/{chatId}', [ChatController::class, 'deleteMessage']);
        });


        Route::group([
            'prefix' => 'songs',
            'middleware' => ['auth:api']
        ], function ($router) {
            Route::get('/genres', [MusicController::class, 'getGenres']); // List all conversations
            Route::get('/moods', [MusicController::class, 'getMoods']);
            Route::get('/list', [MusicController::class, 'songList']);
            Route::get('/unlike/{id}', [MusicController::class, 'unlikeSong']);
            Route::get('/detail/{id}', [MusicController::class, 'songDetail']);
            Route::get('/like/{id}', [MusicController::class, 'likeSong']);
            Route::get('/view/{id}', [MusicController::class, 'viewSong']);
            Route::get('/add-favorite/{song}', [MusicController::class, 'favoriteSong']);
            Route::get('/remove-favorite/{song}', [MusicController::class, 'unfavoriteSong']);
            Route::post('comment/{id}', [MusicController::class, 'commentSong']);
            Route::get('comments-list/{id}', [MusicController::class, 'songListComments']);

            Route::get('/download/{id}', [MusicController::class, 'download']);
        });
    });
});
