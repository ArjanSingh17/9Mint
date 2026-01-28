<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NftResource extends JsonResource
{
    public function toArray($req)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'price' => ['amount'=>$this->price_crypto, 'currency'=>$this->currency_code],
            'prices_gbp' => [
                'small' => $this->price_small_gbp,
                'medium' => $this->price_medium_gbp,
                'large' => $this->price_large_gbp,
            ],
            'editions' => ['total'=>$this->editions_total, 'remaining'=>$this->editions_remaining],
            'collection' => ['id'=>$this->collection_id],
        ];
    }
}
