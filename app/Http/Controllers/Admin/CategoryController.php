<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterestsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index() {
//        $categories = InterestsCategory::defaultOrder()->withDepth()->get();

        $interests = InterestsCategory::orderBy('slug')->get()->toTree();

        $interestsPrepared = [];

        foreach ($interests as $key => $interest) {
            $button = "<div class='btn-group btn-group-sm float-right'> <a href='" . route('category-edit', ['id' => $interest->id]) . "' id='" . $interest->id . "'  class='btn btn-primary float-right'>" .
                "<i class='glyphicon glyphicon-edit'></i> Edit" .
                "</a>";
            $buttonDelete = " <a href='" . route('category-delete', ['id' => $interest->id]) . "' id='" . $interest->id . "'  class='btn btn-danger float-right'>" .
                "<i class='fa fa-trash'></i> Delete" .
                "</a></div>";
            $interestsPrepared[$key]['text'] = $interest->name . $button . $buttonDelete;
            $interestsPrepared[$key]['isExpanded'] = false;
            $nodes = [];
            foreach ($interest->children as $child) {
                $nodeButton = "<div class='btn-group btn-group-sm float-right'> <a href='" . route('category-edit', ['id' => $child->id]) . "' id='" . $child->id . "'  class='btn btn-primary float-right'>" .
                    "<i class='glyphicon glyphicon-edit'></i> Edit" .
                    "</a>";
                $buttonNodeDelete = " <a href='" . route('category-delete', ['id' => $child->id]) . "' id='" . $child->id . "'  class='btn btn-danger float-right'>" .
                    "<i class='fa fa-trash'></i> Delete" .
                    "</a></div>";
                $nodes[]['text'] = $child->name . $nodeButton . $buttonNodeDelete;
            }
            $interestsPrepared[$key]['nodes'] = $nodes;
        }

        return view('admin.categories.index', [
//            'categories' => $categories,
            'interests' => json_encode($interestsPrepared)
        ]);
    }

    public function createForm(Request $request) {

        $categoriesParent = InterestsCategory::where(['parent_id' => null])->get();

        return view('admin.categories.create', ['parents' => $categoriesParent]);
    }
    public function create(Request $request) {
        $category = InterestsCategory::create([
            'name' => $request->get('name'),
            'slug' => $request->get('name'),
            'parent_id' => $request->get('parent_id'),
        ]);
        return redirect()->route('category-index');
    }

    public function editForm($category_id) {
        $category = InterestsCategory::where(['id' => $category_id])->first();
        $categoriesParent = InterestsCategory::where(['parent_id' => null])->get();

        return view('admin.categories.edit', ['category' => $category, 'parents' => $categoriesParent]);
    }
    public function edit($category_id, Request $request) {
        $category = InterestsCategory::where(['id' => $category_id])->first();
        $category->update([
            'name' => $request->get('name'),
            'slug' => $request->get('name'),
            'parent_id' => $request->get('parent_id'),
        ]);

        return redirect()->route('category-index');
    }

    public function delete($category_id) {
        $category = InterestsCategory::where(['id' => $category_id])->first();
        $category->delete();
        return redirect()->route('category-index');
    }
}
