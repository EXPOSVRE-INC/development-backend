<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Iman\Streamer\VideoStreamer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VideoController extends Controller
{
    public function streamVideo() {
        $video_path = storage_path('video/file_example.mp4');
        VideoStreamer::streamFile($video_path);
    }

    public function streamVideoByUuid($uuid, Request $request) {
        $file = Media::where('uuid', $uuid)->first();
        return $file->toInlineResponse($request);
    }

    public function testText()
    {
        $response = Http::get('https://api1.webpurify.com/services/rest/?api_key=fd46f1bb39d810b27b35accee2ad83da&method=webpurify.live.return&text=Hi%20there!%20How%20are%20you%20doing%3F%20What%20do%20you%20want%20fucking%20nigga%3F&lang=en&format=json');

        return response()->json(['data' => $response->json()]);
    }

    public function testImage()
    {
        $response = Http::get('https://im-api1.webpurify.com/services/rest/?method=webpurify.aim.imgcheck&api_key=dc6438a2025d9f6c9d0ba7384c23937f&format=json&cats=nudity,wad,offensive,gore,celebrities,text,faces,ocr,scam&imgurl=https://revelation-dev.xyz/storage/gore.png');

        return response()->json(['data' => $response->json()]);

    }

    public function testImage1()
    {
        $response = Http::get('https://im-api1.webpurify.com/services/rest/?method=webpurify.aim.imgcheck&api_key=dc6438a2025d9f6c9d0ba7384c23937f&format=json&cats=nudity,wad,offensive,gore,celebrities,text,faces,ocr,scam&imgurl=https://revelation-dev.xyz/storage/VIOLENCE.jpeg');

        return response()->json(['data' => $response->json()]);

    }

    public function testImage2()
    {
        $params = array(
            'url' =>  'https://revelation-dev.xyz/storage/VIOLENCE.jpeg',
            'models' => 'nudity-2.0,wad,scam,gore,text,tobacco,gambling',
            'api_user' => '543845028',
            'api_secret' => 'CS8yHYPqG4AiqBXDcbUR',
        );
        $response = Http::get('https://api.sightengine.com/1.0/check.json?'.http_build_query($params));

        return response()->json(['data' => $response->json()]);

    }
}
