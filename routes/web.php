<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserReportController;
use App\Http\Controllers\Admin\PostReportController;
use App\Http\Controllers\Admin\SongController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\MoodController;
use App\Http\Controllers\Admin\CommentReportController;
use App\Http\Controllers\Admin\ArtistController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//\Illuminate\Support\Facades\Auth::routes();

Route::get('/', function () {
    return view('welcome');
});

Route::get('/link/post/{id}', function ($id) {
//    return redirect()->to('EXPOSVRE://post/'.$id);
//    return redirect()->to('https://apps.apple.com/us/app/exposvre/id1630178424');
    return view('universal-link-page');
});

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::get('login', [AuthController::class, 'index'])->name('login');
    Route::post('login', [AuthController::class, 'postLogin'])->name('login.post');
});

Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'admin'],
], function ($router) {
  
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('reports', [UserReportController::class, 'userReports'])->name('reports');

    Route::post('clear-accounts', [UserReportController::class, 'clearAccounts'])->name('clear-accounts');
    Route::post('issue-accounts', [UserReportController::class, 'issueAccounts'])->name('issue-accounts');
    Route::post('ban-accounts', [UserReportController::class, 'banAccounts'])->name('ban-accounts');


    Route::post('clear-post-accounts', [PostReportController::class, 'clearAccounts'])->name('clear-post-accounts');
    Route::post('issue-post-accounts', [PostReportController::class, 'issueAccounts'])->name('issue-post-accounts');
    Route::post('ban-post-accounts', [PostReportController::class, 'banAccounts'])->name('ban-post-accounts');

    Route::post('clear-comment-accounts', [CommentReportController::class, 'clearAccounts'])->name('clear-comment-accounts');
    Route::post('issue-comment-accounts', [CommentReportController::class, 'issueAccounts'])->name('issue-comment-accounts');
    Route::post('ban-comment-accounts', [CommentReportController::class, 'banAccounts'])->name('ban-comment-accounts');



    Route::get('post-reports', [PostReportController::class, 'postReports'])->name('post-reports');
    Route::get('posts-warnings', [PostReportController::class, 'warnings'])->name('posts-warnings');
    Route::get('posts-banned', [PostReportController::class, 'banned'])->name('posts-banned');

    Route::get('create-ad', [AdminController::class, 'getAdForm'])->name('admin-ad');

    Route::post('post-ad', [AdminController::class, 'postAdForm'])->name('admin-ad-post');

    Route::group([
        'prefix' => 'accounts',
    ], function ($router) {
        Route::get('flagged', [\App\Http\Controllers\Admin\AccountController::class, 'flagged'])->name('accounts-flagged');
        Route::get('warnings', [\App\Http\Controllers\Admin\AccountController::class, 'warnings'])->name('accounts-warnings');
//        Route::get('suspend', [\App\Http\Controllers\Admin\AccountController::class, 'scheduled'])->name('accounts-suspend');
        Route::get('banned', [\App\Http\Controllers\Admin\AccountController::class, 'banned'])->name('accounts-banned');

    });
    Route::group([
        'prefix' => 'articles',
    ], function ($router) {
        Route::get('index', [\App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('articles-index');
        Route::get('scheduled', [\App\Http\Controllers\Admin\ArticleController::class, 'scheduled'])->name('articles-scheduled');
        Route::get('published', [\App\Http\Controllers\Admin\ArticleController::class, 'published'])->name('articles-published');
        Route::get('drafts', [\App\Http\Controllers\Admin\ArticleController::class, 'drafts'])->name('articles-drafts');
        Route::get('archive', [\App\Http\Controllers\Admin\ArticleController::class, 'archive'])->name('articles-archive');
        Route::get('to-archive/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'moveToArchive'])->name('articles-to-archive');
        Route::get('from-archive/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'moveFromArchive'])->name('articles-from-archive');
        Route::get('create', [\App\Http\Controllers\Admin\ArticleController::class, 'getAdForm'])->name('articles-create');
        Route::post('create-post', [\App\Http\Controllers\Admin\ArticleController::class, 'postAdForm'])->name('article-post');
        Route::get('edit/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'editForm'])->name('articles-edit');
        Route::post('edit/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'editFormPost'])->name('articles-edit-post');
        Route::get('delete', [\App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('articles-delete');
    });

    Route::group([
        'prefix' => 'ads',
    ], function ($router) {
        Route::get('index', [\App\Http\Controllers\Admin\AdController::class, 'index'])->name('ads-index');
        Route::get('scheduled', [\App\Http\Controllers\Admin\AdController::class, 'scheduled'])->name('ads-scheduled');
        Route::get('published', [\App\Http\Controllers\Admin\AdController::class, 'published'])->name('ads-published');
        Route::get('drafts', [\App\Http\Controllers\Admin\AdController::class, 'drafts'])->name('ads-drafts');
        Route::get('archive', [\App\Http\Controllers\Admin\AdController::class, 'archive'])->name('ads-archive');
        Route::get('to-archive/{id}', [\App\Http\Controllers\Admin\AdController::class, 'moveToArchive'])->name('ads-to-archive');
        Route::get('prioritise/{id}', [\App\Http\Controllers\Admin\AdController::class, 'highestPriority'])->name('prioritise-post');
        Route::get('from-archive/{id}', [\App\Http\Controllers\Admin\AdController::class, 'moveFromArchive'])->name('ads-from-archive');
        Route::get('create', [\App\Http\Controllers\Admin\AdController::class, 'getAdForm'])->name('ads-create');
        Route::post('create-post', [\App\Http\Controllers\Admin\AdController::class, 'postAdForm'])->name('ads-post');
        Route::get('edit/{id}', [\App\Http\Controllers\Admin\AdController::class, 'editAddForm'])->name('ads-edit');
        Route::post('edit/{id}', [\App\Http\Controllers\Admin\AdController::class, 'editAddFormPost'])->name('ads-edit-post');
        Route::get('delete/{id}', [\App\Http\Controllers\Admin\AdController::class, 'deletePost'])->name('post-delete');
        Route::post('remove-image', function () {
            return response()->json('Image removed');
        })->name('image-delete');
    });

    Route::group([
        'prefix' => 'categories',
    ], function ($router) {
        Route::get('/', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('category-index');
        Route::get('create', [\App\Http\Controllers\Admin\CategoryController::class, 'createForm'])->name('category-create');
        Route::post('create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('category-create-post');
        Route::get('edit/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'editForm'])->name('category-edit');
        Route::post('edit/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('category-edit-post');
        Route::get('delete/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'delete'])->name('category-delete');

    });

    Route::group([
        'prefix' => 'songs',
    ], function ($router) {
        Route::get('/', [SongController::class, 'index'])->name('song-index');
        Route::get('create', [SongController::class, 'createForm'])->name('song-create');
        Route::post('create', [SongController::class, 'create'])->name('create-song');
        Route::get('edit/{id}', [SongController::class, 'editSongForm'])->name('song-edit-form');
        Route::post('edit/{id}', [SongController::class, 'edit'])->name('song-edit');
        Route::get('delete/{id}', [SongController::class, 'delete'])->name('song-delete');

    });

    Route::group([
        'prefix' => 'genres',
    ], function ($router) {
        Route::get('/', [GenreController::class, 'getGenre'])->name('genre-index');
        Route::get('create', [GenreController::class, 'createGenreForm'])->name('song-genre-form');
        Route::post('create', [GenreController::class, 'createGenre'])->name('song-genre-create');
        Route::get('edit/{id}', [GenreController::class, 'editGenreForm'])->name('genre-edit-form');
        Route::post('edit/{id}', [GenreController::class, 'editGenre'])->name('genre-edit');
        Route::get('delete/{id}', [GenreController::class, 'deleteGenre'])->name('genre-delete');
    });


    Route::group([
        'prefix' => 'moods',
    ], function ($router) {
        Route::get('/', [MoodController::class, 'getMoods'])->name('mood-index');
        Route::get('create', [MoodController::class, 'createMoodForm'])->name('mood-form');
        Route::post('create', [MoodController::class, 'createMood'])->name('mood-create');
        Route::get('edit/{id}', [MoodController::class, 'editMoodForm'])->name('mood-edit-form');
        Route::post('edit/{id}', [MoodController::class, 'editMood'])->name('mood-edit');
        Route::get('delete/{id}', [MoodController::class, 'deleteMood'])->name('mood-delete');
    });

    Route::group([
        'prefix' => 'artists',
    ], function ($router) {
        Route::get('/', [ArtistController::class, 'index'])->name('artist-index');
        Route::get('create', [ArtistController::class, 'createForm'])->name('artist-form');
        Route::post('create', [ArtistController::class, 'create'])->name('artist-create');
        Route::get('edit/{id}', [ArtistController::class, 'editArtistForm'])->name('artist-edit-form');
        Route::post('edit/{id}', [ArtistController::class, 'editArtist'])->name('artist-edit');
        Route::get('delete/{id}', [ArtistController::class, 'delete'])->name('artist-delete');
    });

    Route::group([
        'prefix' => 'users',
    ], function ($router) {
        Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users-index');
        Route::get('view/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users-show');
        Route::post('verify', [\App\Http\Controllers\Admin\UserController::class, 'verify'])->name('users-verify');
    });

    Route::group([
        'prefix' => 'comments-reports',
    ], function ($router) {
        Route::get('/', [\App\Http\Controllers\Admin\CommentReportController::class, 'commentReports'])->name('comment-reports-index');
    });
});

Route::get('/home', function() {
    return view('adminlte::dashboard');
})->middleware('auth');

Route::get('/stripe-redirect', function () {
    return redirect()->away('EXPOSVRE://');
});
