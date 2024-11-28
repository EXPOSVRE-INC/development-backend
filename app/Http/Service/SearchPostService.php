<?php

namespace App\Http\Service;

use App\Http\Requests\SearchPostRequest;
use App\Models\Post;

class SearchPostService {

    private $status = [
        'all' => "all",
        'new' => "new",
        'buy now' => "buy-now",
        'collectible' => "collectible",
        'not-collectible' => "not-collectible"
    ];

    public function newFilterPosts(SearchPostRequest $request) {
        $query = $request->get('query');
        $posts = Post::where(function($q) use ($query) {
            $q->where('title', 'LIKE', '%'.$query.'%')
              ->orWhere('description', 'LIKE', '%'.$query.'%');
        })
        ->where(function($q) {
            $q->where('status', '!=', 'archive')
              ->orWhereNull('status');
        });

        if($request->get('status') && $request->get('status') != '') {
//                    $statuses = json_decode($request->get('status'));
            $statuses = $request->get('status');
            if (count($statuses) < 2) {
                if (in_array('collectible', $statuses)) {
                    $posts->where(['collection_post' => 1]);
                }
                if (in_array('not collectible', $statuses)) {
                    $posts->where(['collection_post' => 0]);
                }
            }
        }
        if ($request->get('types') && $request->get('types') != '') {
//                    $post_types = json_decode($request->get('post_type'));
            $post_types = $request->get('types');
            if (in_array('image', $post_types)) {
                $posts->where(['type' => 'image']);
            }
        }
        if ($request->get('currency') && $request->get('currency') != '') {
            $posts->where(['currency' => $request->get('currency')]);
        }
        if ($request->get('interests') && $request->get('interests') != '') {
//                    $interests = json_decode($request->get('interests'));
            $interests = $request->get('interests');
            $interests = array_filter($interests, function($value) {
                return !is_null($value) && $value !== '';
            } );
            if (count($interests) > 0 && !empty($interests)) {
                $posts->whereHas('interests', function ($query) use ($interests) {
                    $query->whereIn('slug', $interests);
                });
            }
        }
        $posts->where('fixed_price', '>=', $request->input('price_min'));
        $posts->where('fixed_price', '<=', $request->input('price_max'));
        $posts = $posts->limit(100)->get();
        return $posts;
    }

    public function filterPosts(SearchPostRequest $request) {
        $posts = Post::query();

        $posts->where(function($q) {
            $q->where('status', '!=', 'archive')
              ->orWhereNull('status');
        });


        if ($request->has('query') && $request->input('query') != '' && $request->input('query') != null) {
            $posts->where('title', 'LIKE', '%'.$request->input('query').'%');
        }


        if ($request->has('currency') && $request->has('price_min') && $request->has('price_max')) {
            $posts->where('currency', $request->input('currency'));
            $posts->where('fixed_price', '>=', $request->input('price_min'));
            $posts->where('fixed_price', '<=', $request->input('price_max'));
        }

        if ($request->has('interests')) {
//            $posts->with('tags')->where('tags.name', 'IN', $request->input('interests'));
        }
        if ($request->has('status')) {
//            $posts->where('status', 'IN', $this->status[$request->input('status')]);
        }

        if ($request->has('types')) {
            if (!in_array('all', $request->input('types'))) {
                $posts->whereIn('type', $request->input('types'));
            }
        }

        if (count($request->all()) > 0) {
            $posts = $posts->limit(100)->get();
//            dd(\DB::getQueryLog());
            return $posts;
        } else {
            return [];
        }
    }

}
