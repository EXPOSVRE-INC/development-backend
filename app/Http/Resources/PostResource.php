<?php

namespace App\Http\Resources;

use App\Http\Service\StripeService;
use App\Models\PriceRequest;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Support\ImageFactory;
use Imagick;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        $isMarket = false;
        $user = auth('api')->user();

        if ($this->post_for_sale == 1) {
            if ($user->isSubscription($this->owner_id)) {
                $isMarket = true;
            } else {
                $isMarket = true;
            }
        }
        $priceRequest = PriceRequest::where([
            'user_id' => $user->id,
            'post_id' => $this->id,
        ])
            ->latest()
            ->first();
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle == null ? '' : $this->subtitle,
            'description' =>
            $this->description == null ? '' : $this->description,
            'author' => $this->author == null ? '' : $this->author,
            'link' => $this->link,
            'post_for_sale' => (bool) $this->post_for_sale,
            'collection' => $this->collection,
            'song' =>  new SongResource($this->song),
            'unlimited_edition' => (bool) $this->unlimited_edition,
            'limited_addition_number' => (int) $this->limited_addition_number,
            'physical_item' => (bool) $this->physical_item,
            'time_sale_from_date' => $this->time_sale_from_date
                ? Carbon::createFromFormat('Y-m-d H:i:s', $this->time_sale_from_date)->timestamp
                : Carbon::now()->timestamp,

            'time_sale_to_date' => $this->time_sale_to_date
                ? Carbon::createFromFormat('Y-m-d H:i:s', $this->time_sale_to_date)->timestamp
                : Carbon::now()->timestamp,
            'fixed_price' => (int) $this->fixed_price,
            'totalPrice' => $this->isFree
                ? (int) $this->shippingPrice
                : (int) ($this->fixed_price +
                    $this->shippingPrice +
                    ($this->fixed_price) * 0.059 +
                    ($this->fixed_price) * 0.0825),
            'tax' => $this->isFree ? 0 : ($this->fixed_price) * 0.0825,
            'royalties_percentage' => (int) $this->royalties_percentage,
            'allow_to_comment' => (bool) $this->allow_to_comment,
            'allow_views' => (bool) $this->allow_views,
            'exclusive_content' => (bool) $this->exclusive_content,
            'user' => UserResource::make($this->owner),
            'tags' => TagsResource::collection($this->tags),
            'views_count' => (int) $this->views_count,
            'likes_count' => (int) $this->likers()->count(),
            'likersByLastDay' => count($this->likersByLastDay),
            'viewsByLastDay' => $this->views_by_last_day,
            'liked' => (bool) $this->isLikedBy(auth('api')->user()),
            'favorited' => (bool) $this->hasBeenFavoritedBy(
                auth('api')->user()
            ),
            'archived' => (bool) $this->is_archived,
            'order_priority' =>
            $this->order_priority != null ? $this->order_priority : 0,
            'isMarket' => (bool) $isMarket,
            'type' => $this->type,
            'currency' => $this->currency,
            'interests' => PostInterestsResource::collection($this->interests),
            'nudity' => (bool) $this->nudity,
            'typeOfPrice' => (string) $this->typeOfPrice,
            'isFree' => (bool) $this->isFree,
            'isPriceRequested' => $priceRequest != null ? true : false,
            'priceRequestStatus' =>
            $priceRequest != null ? $priceRequest->status : 'none',
            'shippingIncluded' => (bool) $this->isFree,
            'shippingPrice' => (int) $this->shippingPrice,
            'transactionFees' => ((int) $this->fixed_price) * 0.059,
            'ad' => (bool) $this->ad,
            'publish_date' => $this->publish_date,
            'files' => ImageResource::collection($this->getMedia('files')),

            'liveExperiences' =>
            count($this->intervals) > 0
                ? LiveExperienceResource::collection($this->intervals)
                : [],

            //                'creator' => ($this->parent) ? UserResource::make($this->parent->owner) : UserResource::make($this->owner),
        ];
        //        dd($this->getMedia('files'));

        if ($firstMedia = $this->getFirstMedia('files')) {
            $data['image'] = $this->getFirstMediaUrl('files');

            if (str_contains($firstMedia->mime_type, 'image')) {

                $thumbUrl = $this->getFirstMediaUrl('thumb');
                $thumbPath = $this->getFirstMediaPath('thumb');
                if ($thumbPath && file_exists($thumbPath)) {
                    $image = new Imagick($thumbPath);
                    $data['image'] = $this->getFirstMediaUrl('thumb');
                    $data['image_width'] = $image->getImageWidth();;
                    $data['image_height'] = $image->getImageHeight();
                } elseif (str_contains($firstMedia->mime_type, 'webp')) {
                    $data['image_height'] = 160;
                    $data['image_width'] = 160;
                } else {
                    $mediaPath = $firstMedia->getPath('original');
                    if (!empty($mediaPath) && file_exists($mediaPath)) {
                        $image = ImageFactory::load($mediaPath);
                        $data['image_height'] = $image->getHeight();
                        $data['image_width'] = $image->getWidth();
                    } else {
                        $data['image_height'] = 0;
                        $data['image_width'] = 0;
                    }
                }
            } elseif (str_contains($firstMedia->mime_type, 'video')) {
                $data['image'] = $this->getFirstMediaUrl('files', 'original');
                $mediaPath = $firstMedia->getPath('original');

                if (!empty($mediaPath) && file_exists($mediaPath)) {
                    $ffmpeg = FFMpeg::create();
                    $video = $ffmpeg->open($mediaPath);

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
        } else {
            $data['image'] = null;
            $data['image_height'] = 0;
            $data['image_width'] = 0;
        }

        // $thumbUrl = $this->getFirstMediaUrl('thumb');
        // $data['thumb'] = !empty($thumbUrl) ? $thumbUrl : $this->getFirstMediaUrl('files', 'original');

        $files = $this->getMedia('files');

        if (count($files) === 1) {
            $data['thumb'] = $files[0]->getUrl('original');
        } elseif (count($files) > 1 && (bool)$this->ad == false) {
            $data['thumb'] = $files[0]->getUrl();
        } elseif (count($files) > 1 && (bool)$this->ad == true) {
            $data['thumb'] = $this->getFirstMediaUrl('thumb');
        } else {
            $data['thumb'] = $this->getFirstMediaUrl('thumb');
        }
        // Handle video media if 'ad' is set to 1
        if ($this->ad == 1) {
            $data['video'] = $this->getFirstMediaUrl('video');
            $data['video_preview'] = $this->getFirstMediaUrl('video', 'original') ?? '';
            $data['video_thumb'] = $this->getFirstMediaUrl('video_thumb');
        }
        return $data;
    }
}
