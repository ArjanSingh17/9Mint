<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($req)
    {
        $nft = $this->token?->nft;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'ref' => [
                'amount' => $this->ref_amount,
                'currency' => $this->ref_currency,
            ],
            'token_id' => $this->token_id,
            'nft' => $nft ? [
                'id' => $nft->id,
                'slug' => $nft->slug,
                'name' => $nft->name,
                'image_url' => $nft->image_url,
                'collection_id' => $nft->collection_id,
            ] : null,
        ];
    }
}
