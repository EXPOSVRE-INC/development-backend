<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InterestsCategory;
use Illuminate\Http\Request;

class CategoryInterestsController extends Controller
{
    public function index()
    {

        $interests = InterestsCategory::orderBy('slug')->get()->toTree();

        $responseArray = [];

        foreach ($interests as $k => $interest) {
            $responseArray[$k][] = [$interest->id => $interest->name];

            foreach ($interest->children as $child) {
                $responseArray[$k][] =  [$child->id => $child->name];
            }
        }

//        dd($responseArray);

        return response()->json(['data' => $responseArray]);
    }
}
