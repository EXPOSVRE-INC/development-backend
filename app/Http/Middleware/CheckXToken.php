<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class CheckXToken
{



    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next, ...$guards) {
//        if ($request->headers->has('X-Token')) {
//            if ($request->headers->get('X-Token') != env('XTOKEN')) {
//                return response()->json('X-Token invalid!');
//            } else {
                return $next($request);
//            }
//        } else {
//            return response()->json('X-Token empty!');
//        }
    }
}
