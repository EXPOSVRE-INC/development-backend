<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Song;
use Closure;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
        public function handle($request, Closure $next)
        {
            if (session()->has('previous_url')) {
                session(['two_steps_back' => session('previous_url')]);
            }

            session(['previous_url' => url()->previous()]);

            return $next($request);
        }
    public function index() {
        $artists = Artist::where('status', 'active')->latest()->get();
        return view('admin.artists.index', [
            'artists' => $artists
        ]);
    }

    public function createForm($id) {
        if($id == 'new')
        {
          $artist = array();
        }
        else
        {
           $artist = Artist::where(['id' => $id])->first();
        }
        return view('admin.artists.create',compact('id','artist'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:artists,name',
        ]);
        $arr = $request->all();
        if($request->id == 'new')
          {
            $obj = new Artist();
          }
        else
          {
            $obj = Artist::where('id',$request->id)->first();
          }

        $obj->fill($arr);
        $obj->save();

        return redirect()->to($request->input('previous_url'));
    }

    public function delete($artist_id) {
        $artist = Artist::where(['id' => $artist_id])->first();
        if ($artist) {
            $artist->status = 'inactive';
            $artist->save();

            Song::where('artist_id', $artist_id)->update(['status' => 'deleted']);
        }
        return redirect()->route('artist-index');
    }
}
