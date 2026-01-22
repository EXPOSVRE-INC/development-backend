<?php

namespace App\Http\Resources;

use App\Models\PriceRequest;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use Illuminate\Http\Resources\Json\JsonResource;
use Imagick;

class PostWithoutAdResource extends JsonResource
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

        $fixedPrice = (float) $this->fixed_price;
        $shippingPrice = (float) $this->shippingPrice;

        $tax = $this->isFree ? 0 : round($fixedPrice * 0.0825, 2);
        $transactionFees = $this->isFree ? 0 : round($fixedPrice * 0.059, 2);

        $totalPrice = $this->isFree
            ? $shippingPrice
            : round($fixedPrice + $shippingPrice + $tax + $transactionFees, 2);
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
            'fixed_price' => $fixedPrice,
            'totalPrice' => $totalPrice,
            'tax' => $tax,
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
            'priceRequestId' => $priceRequest != null ? $priceRequest->id : null,
            'priceRequestStatus' =>
            $priceRequest != null ? $priceRequest->status : 'none',
            'offeredPrice' => $priceRequest != null ? $priceRequest->offered_price : 0,
            'shippingIncluded' => (bool) $this->isFree,
            'shippingPrice' => $shippingPrice,
            'transactionFees' => $transactionFees,
            'ad' => (bool) $this->ad,
            'publish_date' => $this->publish_date,
            'files' => ImageResource::collection($this->getMedia('files')),

            'liveExperiences' =>
            count($this->intervals) > 0
                ? LiveExperienceResource::collection($this->intervals)
                : [],

        ];

        $files = $this->getMedia('files');
        $headerVideoMedia = $this->getFirstMedia('header_video');
        $thumbMedia = null;

        if ((bool) $this->ad === false) {
            // ✅ ad = 0 → always first file
            if ($files->count() > 0) {
                $thumbMedia = $files[0];
                $data['thumb'] = $thumbMedia->getUrl('original');
            } else {
                $data['thumb'] = null;
            }
        } else {
            // ✅ ad = 1
            if ($headerVideoMedia) {
                $data['thumb'] = $headerVideoMedia->getUrl('original');
            } elseif ($files->count() > 1) {
                $thumbMedia = $files[$files->count() - 1];
                $data['thumb'] = $thumbMedia->getUrl('original');
            } elseif ($files->count() === 1) {
                $thumbMedia = $files[0];
                $data['thumb'] = $thumbMedia->getUrl('original');
            } else {
                $thumbMedia = $this->getFirstMedia('thumb');
                $data['thumb'] = $thumbMedia ? $thumbMedia->getUrl('original') : null;
            }
        }
        if ($thumbMedia) {
            $thumbPath = $thumbMedia->getPath('original');
            $data['image'] = $thumbMedia->getUrl('original');

            if (!empty($thumbPath) && file_exists($thumbPath)) {
                try {
                    if (str_contains($thumbMedia->mime_type, 'image')) {
                        $image = new Imagick($thumbPath);
                        $data['image_width'] = $image->getImageWidth();
                        $data['image_height'] = $image->getImageHeight();
                    } elseif (str_contains($thumbMedia->mime_type, 'video')) {
                        $ffmpeg = \FFMpeg\FFMpeg::create();
                        $video = $ffmpeg->open($thumbPath);
                        $dimension = $video->getStreams()->videos()->first()->getDimensions();
                        $data['image_width'] = $dimension->getWidth();
                        $data['image_height'] = $dimension->getHeight();
                    } else {
                        $data['image_width'] = 0;
                        $data['image_height'] = 0;
                    }
                } catch (\Exception $e) {
                    $data['image_width'] = 0;
                    $data['image_height'] = 0;
                }
            } else {
                $data['image_width'] = 0;
                $data['image_height'] = 0;
            }
        } else {
            $data['image'] = null;
            $data['image_width'] = 0;
            $data['image_height'] = 0;
        }
        if ($this->ad == 1) {
            $data['video'] = $this->getFirstMediaUrl('video');
            $data['video_preview'] = $this->getFirstMediaUrl('video', 'original') ?? '';
            $data['video_thumb'] = $this->getFirstMediaUrl('video_thumb');
        }
        return $data;
    }
}
