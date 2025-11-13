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
            'editions' => ['total'=>$this->editions_total, 'remaining'=>$this->editions_remaining],
            'collection' => ['id'=>$this->collection_id],
        ];
    }
}
