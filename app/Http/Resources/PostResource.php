<?php

namespace App\Http\Resources;

use App\Http\Service\StripeService;
use App\Models\PriceRequest;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Support\ImageFactory;

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
                ? Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $this->time_sale_from_date
                )->timestamp
                : 0,
            'time_sale_to_date' => $this->time_sale_from_date
                ? Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $this->time_sale_to_date
                )->timestamp
                : 0,
            'fixed_price' => (int) $this->fixed_price / 100,
            'totalPrice' => $this->isFree
                ? (int) $this->shippingPrice / 100
                : (int) ($this->fixed_price / 100 +
                    $this->shippingPrice / 100 +
                    ($this->fixed_price / 100) * 0.059 +
                    ($this->fixed_price / 100) * 0.0825),
            'tax' => $this->isFree ? 0 : ($this->fixed_price / 100) * 0.0825,
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
            'shippingPrice' => (int) $this->shippingPrice / 100,
            'transactionFees' => ((int) $this->fixed_price / 100) * 0.059,
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

        // Check if there is any media in 'files' collection
        if ($firstMedia = $this->getFirstMedia('files')) {
            if (str_contains($firstMedia->mime_type, 'image')) {
                $data['image'] = $this->getFirstMediaUrl('files');

                if (str_contains($firstMedia->mime_type, 'webp')) {
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
            // No media, set default values
            $data['image'] = null;
            $data['image_height'] = 0;
            $data['image_width'] = 0;
        }

        // $thumbUrl = $this->getFirstMediaUrl('thumb');
        // $data['thumb'] = !empty($thumbUrl) ? $thumbUrl : $this->getFirstMediaUrl('files', 'original');

        $files = $this->getMedia('files');

        if (count($files) === 1) {
            $data['thumb'] = $files[0]->getUrl('original');
        } elseif (count($files) > 1) {
            $data['thumb'] = $files[0]->getUrl();
        } else {
            $data['thumb'] = $this->getFirstMediaUrl('thumb');
        }

        // Handle video media if 'ad' is set to 1
        if ($this->ad == 1) {
            $data['video'] = $this->getFirstMediaUrl('video');
            $data['video_preview'] = $this->getFirstMediaUrl('video', 'original') ?? '';
        }


        return $data;
    }
}
