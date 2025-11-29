<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Collection;
use App\Models\Nft;

class GlossyCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Glossy Collection
        $collection = Collection::updateOrCreate(
            ['slug' => 'glossy-collection'],
            [
                'name' => 'Glossy Collection',
                'description' => 'A collection of glossy animal portraits created by artist Vlas',
                'cover_image_url' => '/GlossyDuckNFT.png',
                'creator_name' => 'Vlas'
            ]
        );

        // Create Glossy Duck NFT
        Nft::updateOrCreate(
            ['slug' => 'glossy-duck'],
            [
                'name' => 'Glossy Duck',
                'description' => 'This NFT is a glossy portrait of a duck. This was created by our highly skilled artist Vlas after watching a duck waddle along.',
                'image_url' => '/GlossyDuckNFT.png',
                'currency_code' => 'GBP',
                'price_crypto' => 55.00,
                'editions_total' => 100,
                'editions_remaining' => 100,
                'is_active' => true,
                'collection_id' => $collection->id
            ]
        );

        // Create Glossy Cat NFT
        Nft::updateOrCreate(
            ['slug' => 'glossy-cat'],
            [
                'name' => 'Glossy Cat',
                'description' => 'This NFT is a glossy portrait of a cat. This was created by our highly skilled artist Vlas after witnessing a kitten following its mother.',
                'image_url' => '/GlossyCat.png',
                'currency_code' => 'GBP',
                'price_crypto' => 50.00,
                'editions_total' => 100,
                'editions_remaining' => 100,
                'is_active' => true,
                'collection_id' => $collection->id
            ]
        );

        // Create Glossy Donkey NFT
        Nft::updateOrCreate(
            ['slug' => 'glossy-donkey'],
            [
                'name' => 'Glossy Donkey',
                'description' => 'This NFT is a glossy portrait of a Donkey. This was created by our highly skilled artist Vlas after watching Shrek.',
                'image_url' => '/GlossyDonkeyNFT.png',
                'currency_code' => 'GBP',
                'price_crypto' => 45.00,
                'editions_total' => 100,
                'editions_remaining' => 100,
                'is_active' => true,
                'collection_id' => $collection->id
            ]
        );

        // Create Glossy Giraffe NFT
        Nft::updateOrCreate(
            ['slug' => 'glossy-giraffe'],
            [
                'name' => 'Glossy Giraffe',
                'description' => 'This NFT is a glossy portrait of a giraffe. This was created by our highly skilled artist Vlas after watching Madagascar.',
                'image_url' => '/GlossyGiraffeNFT.png',
                'currency_code' => 'GBP',
                'price_crypto' => 60.00,
                'editions_total' => 100,
                'editions_remaining' => 100,
                'is_active' => true,
                'collection_id' => $collection->id
            ]
        );

        // Create Glossy Lobster NFT
        Nft::updateOrCreate(
            ['slug' => 'glossy-lobster'],
            [
                'name' => 'Glossy Lobster',
                'description' => 'This NFT is a glossy portrait of a lobster. This was created by our highly skilled artist Vlas after seeing pictures on pinterest.',
                'image_url' => '/GlossyLobsterNFT.png',
                'currency_code' => 'GBP',
                'price_crypto' => 70.00,
                'editions_total' => 100,
                'editions_remaining' => 100,
                'is_active' => true,
                'collection_id' => $collection->id
            ]
        );

        // Create Glossy Rooster NFT
        Nft::updateOrCreate(
            ['slug' => 'glossy-rooster'],
            [
                'name' => 'Glossy Rooster',
                'description' => 'This NFT is a glossy portrait of a rooster. This was created by our highly skilled artist Vlas. The inspiration for this piece is unknown.',
                'image_url' => '/GlossyRoosterNFT.png',
                'currency_code' => 'GBP',
                'price_crypto' => 52.00,
                'editions_total' => 100,
                'editions_remaining' => 100,
                'is_active' => true,
                'collection_id' => $collection->id
            ]
        );

        // Create Glossy Squirrel NFT
        Nft::updateOrCreate(
            ['slug' => 'glossy-squirrel'],
            [
                'name' => 'Glossy Squirrel',
                'description' => 'This NFT is a glossy portrait of a squirrel. This was created by our highly skilled artist Vlas after being inspired by the squirrels on campus.',
                'image_url' => '/GlossySquirrelNFT.png',
                'currency_code' => 'GBP',
                'price_crypto' => 48.00,
                'editions_total' => 100,
                'editions_remaining' => 100,
                'is_active' => true,
                'collection_id' => $collection->id
            ]
        );
    }
}
