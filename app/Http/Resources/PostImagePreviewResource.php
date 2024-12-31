<?php

namespace App\Http\Resources;
use FFMpeg\FFMpeg;
use Spatie\MediaLibrary\Support\ImageFactory;
use Illuminate\Http\Resources\Json\JsonResource;

class PostImagePreviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Initialize data array
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->file_name,
            'size' => $this->size,
            'link' => $this->getUrl('original'),
        ];

        if (str_starts_with($this->mime_type, 'image/')) {
            $imagePath = $this->getPath('original');
            $image = ImageFactory::load($imagePath);
            $data['image_height'] = $image->getHeight();
            $data['image_width'] = $image->getWidth();
        }
        elseif (str_contains($this->mime_type, 'video')) {
            $videoPath = $this->getPath('original');

            if (!empty($videoPath) && file_exists($videoPath)) {
                $ffmpeg = FFMpeg::create();
                $video = $ffmpeg->open($videoPath);

                $dimension = $video
                    ->getStreams()
                    ->videos()
                    ->first()
                    ->getDimensions();

                $data['image_height'] = $dimension->getHeight();
                $data['image_width'] = $dimension->getWidth();
            } else {
                $data['image_height'] = 0;
                $data['image_width'] = 0;
            }
        }
        if ($this->type == 'video') {
            $data['isVideo'] = true;
        }
        return $data;
    }

}
