<?php

/**
 * Simple seeding script for local/dev use.
 *
 * Usage (from project root):
 *   php tools/seed-collections-and-nfts.php
 *
 * It will create (or update) the following:
 * - collections:
 *     - glossy-collection
 *     - superhero-collection
 * - nfts belonging to those collections, matching your hard-coded views.
 */

use App\Models\Collection;
use App\Models\Nft;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Seeding collections and NFTs...\n";

// --- Collections ---
$collections = [
    'glossy-collection' => [
        'name'         => 'Glossy Collection',
        'description'  => 'Glossy animal NFTs created by Vlas.',
        'cover_image_url' => '/images/nfts/glossy/GlossyDuckNFT.png',
        'creator_name' => 'Vlas',
    ],
    'superhero-collection' => [
        'name'         => 'Superhero Collection',
        'description'  => 'Iconic superhero NFTs.',
        'cover_image_url' => '/images/nfts/superhero/Superman.png',
        'creator_name' => 'Team 9Mint',
    ],
    'geotennis-collection' => [
        'name'            => 'Geo Tennis',
        'description'     => 'Playing tennis with squares',
        'cover_image_url' => '/images/nfts/geotennis/t1.png',
        'creator_name'    => 'Team 9Mint',
    ],
    'characters-collection' => [
        'name'            => 'Characters',
        'description'     => 'movie stars',
        'cover_image_url' => '/images/nfts/characters/carl.png',
        'creator_name'    => 'Team 9Mint',
    ]

];

foreach ($collections as $slug => $data) {
    $collection = Collection::updateOrCreate(
        ['slug' => $slug],
        $data
    );

    echo "Collection seeded: {$collection->slug}\n";
}

$glossy = Collection::where('slug', 'glossy-collection')->first();
$superhero = Collection::where('slug', 'superhero-collection')->first();
$geotennis = Collection::where('slug', 'geotennis-collection')->first();
$characters = Collection::where('slug', 'characters-collection')->first();


if (!$glossy || !$superhero) {
    echo "Error: collections not found after seeding. Aborting NFT creation.\n";
    exit(1);
}

// Common defaults
$defaultCurrency = 'GBP';
$defaultPrice    = 0.00;    // crypto price (you can adjust later)
$editionsTotal   = 5;

/**
 * Generate deterministic per-NFT size prices in GBP.
 * This keeps local/dev data stable across runs while ensuring prices differ per NFT.
 */
function sizePricesGbp(string $slug): array
{
    $seed = abs(crc32($slug));
    // Base between 18.00 and 58.00 (GBP)
    $base = 18 + (($seed % 4000) / 100);
    $medium = round($base, 2);
    $small = round($medium * 0.75, 2);
    $large = round($medium * 1.25, 2);
    return [$small, $medium, $large];
}

// --- Glossy NFTs (matching Glossy-collection.blade.php) ---
$glossyNfts = [
    [
        'slug'        => 'glossy-duck',
        'name'        => 'Glossy Duck',
        'description' => 'This NFT is a glossy portrait of a duck created by our highly skilled artist Vlas.',
        'image_url'   => '/images/nfts/glossy/GlossyDuckNFT.png',
    ],
    [
        'slug'        => 'glossy-cat',
        'name'        => 'Glossy Cat',
        'description' => 'A glossy portrait of a cat created by Vlas.',
        'image_url'   => '/images/nfts/glossy/GlossyCatNFT.png',
    ],
    [
        'slug'        => 'glossy-donkey',
        'name'        => 'Glossy Donkey',
        'description' => 'Inspired by Shrek, this glossy donkey portrait was created by Vlas.',
        'image_url'   => '/images/nfts/glossy/GlossyDonkeyNFT.png',
    ],
    [
        'slug'        => 'glossy-giraffe',
        'name'        => 'Glossy Giraffe',
        'description' => 'A glossy giraffe portrait inspired by Madagascar.',
        'image_url'   => '/images/nfts/glossy/GlossyGiraffeNFT.png',
    ],
    [
        'slug'        => 'glossy-lobster',
        'name'        => 'Glossy Lobster',
        'description' => 'A glossy lobster portrait inspired by Pinterest.',
        'image_url'   => '/images/nfts/glossy/GlossyLobsterNFT.png',
    ],
    [
        'slug'        => 'glossy-rooster',
        'name'        => 'Glossy Rooster',
        'description' => 'A glossy rooster portrait created by Vlas.',
        'image_url'   => '/images/nfts/glossy/GlossyRoosterNFT.png',
    ],
    [
        'slug'        => 'glossy-squirrel',
        'name'        => 'Glossy Squirrel',
        'description' => 'Inspired by playful squirrels on campus.',
        'image_url'   => '/images/nfts/glossy/GlossySquirrelNFT.png',
    ],
];

foreach ($glossyNfts as $data) {
    [$small, $medium, $large] = sizePricesGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $glossy->id,
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'currency_code'      => $defaultCurrency,
            'price_crypto'       => $defaultPrice,
            'price_small_gbp'    => $small,
            'price_medium_gbp'   => $medium,
            'price_large_gbp'    => $large,
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    echo "NFT seeded (glossy): {$nft->slug}\n";
}

// --- Superhero NFTs (matching SuperheroCollection.blade.php) ---
$superheroNfts = [
    [
        'slug'        => 'aquaman',
        'name'        => 'Aquaman',
        'description' => 'A powerful underwater hero with unmatched strength and courage.',
        'image_url'   => '/images/nfts/superhero/Aquaman.png',
    ],
    [
        'slug'        => 'batman',
        'name'        => 'Batman',
        'description' => 'The Dark Knight who protects Gotham City.',
        'image_url'   => '/images/nfts/superhero/Batman.png',
    ],
    [
        'slug'        => 'cyborg',
        'name'        => 'Cyborg',
        'description' => 'Half human, half machine — fully powerful.',
        'image_url'   => '/images/nfts/superhero/Cyborg.png',
    ],
    [
        'slug'        => 'flash',
        'name'        => 'Flash',
        'description' => 'The fastest hero alive.',
        'image_url'   => '/images/nfts/superhero/Flash.png',
    ],
    [
        'slug'        => 'ironman',
        'name'        => 'Iron Man',
        'description' => 'Genius. Billionaire. Philanthropist.',
        'image_url'   => '/images/nfts/superhero/IronMan.png',
    ],
    [
        'slug'        => 'spiderman',
        'name'        => 'Spiderman',
        'description' => 'Friendly neighbourhood protector.',
        'image_url'   => '/images/nfts/superhero/Spiderman.png',
    ],
    [
        'slug'        => 'superman',
        'name'        => 'Superman',
        'description' => 'The Man of Steel.',
        'image_url'   => '/images/nfts/superhero/Superman.png',
    ],
    [
        'slug'        => 'wonder-woman',
        'name'        => 'Wonder Woman',
        'description' => 'A fearless warrior of justice.',
        'image_url'   => '/images/nfts/superhero/WonderWomen.png',
    ],
];

foreach ($superheroNfts as $data) {
    [$small, $medium, $large] = sizePricesGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $superhero->id,
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'currency_code'      => $defaultCurrency,
            'price_crypto'       => $defaultPrice,
            'price_small_gbp'    => $small,
            'price_medium_gbp'   => $medium,
            'price_large_gbp'    => $large,
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    echo "NFT seeded (superhero): {$nft->slug}\n";
}


// --- geotennis NFTs ---
$geotennisNfts = [
    [
        'slug'        => 't1',
        'name'        => 't1',
        'description' => 'tennis court square',
        'image_url'   => '/images/nfts/geotennis/t1.png',
    ],
    [
        'slug'        => 't2',
        'name'        => 't2',
        'description' => 'light tennis court square',
        'image_url'   => '/images/nfts/geotennis/t2.png',
    ],
        [
        'slug'        => 't3',
        'name'        => 't3',
        'description' => 'nuclear tennis court square',
        'image_url'   => '/images/nfts/geotennis/t3.png',
    ],
        [
        'slug'        => 't4',
        'name'        => 't4',
        'description' => 'grass tennis court square',
        'image_url'   => '/images/nfts/geotennis/t4.png',
    ],
        [
        'slug'        => 't5',
        'name'        => 't5',
        'description' => 'white square',
        'image_url'   => '/images/nfts/geotennis/t5.png',
    ],
        [
        'slug'        => 't6',
        'name'        => 't6',
        'description' => 'sandpaper square',
        'image_url'   => '/images/nfts/geotennis/t6.png',
    ],
        [
        'slug'        => 't7',
        'name'        => 't7',
        'description' => 'covered square',
        'image_url'   => '/images/nfts/geotennis/t7.png',
    ],
];

foreach ($geotennisNfts as $data) {
    [$small, $medium, $large] = sizePricesGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $geotennis->id, // Use the new collection ID
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'currency_code'      => $defaultCurrency,
            'price_crypto'       => $defaultPrice,
            'price_small_gbp'    => $small,
            'price_medium_gbp'   => $medium,
            'price_large_gbp'    => $large,
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    echo "NFT seeded (geotennis): {$nft->slug}\n";
}

// --- Characters NFTs ---
$charactersNfts = [
    [
        'slug'        => 'carl',
        'name'        => 'carlos',
        'description' => 'movie star and icons',
        'image_url'   => '/images/nfts/characters/carl.png',
    ],
    [
        'slug'        => 'him',
        'name'        => 'Him',
        'description' => 'Green G',
        'image_url'   => '/images/nfts/characters/him.png',
    ],
    [
        'slug'        => 'lee',
        'name'        => 'Lee',
        'description' => 'martial arts master',
        'image_url'   => '/images/nfts/characters/lee.png',
    ],
    [
        'slug'        => 'mads',
        'name'        => 'Mads',
        'description' => 'Has some distinctive appearance',
        'image_url'   => '/images/nfts/characters/mads.png',
    ],
    [
        'slug'        => 'mike',
        'name'        => 'Mike',
        'description' => 'peekaboo',
        'image_url'   => '/images/nfts/characters/mike.png',
    ],
    [
        'slug'        => 'qqq',
        'name'        => 'QQQ',
        'description' => 'faceless',
        'image_url'   => '/images/nfts/characters/qqq.png',
    ],
    [
        'slug'        => 'box',
        'name'        => 'Box',
        'description' => 'genetically mog',
        'image_url'   => '/images/nfts/characters/box.png',
    ],
    
];

foreach ($charactersNfts as $data) {
    [$small, $medium, $large] = sizePricesGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $characters->id, // Use the new collection ID
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'currency_code'      => $defaultCurrency,
            'price_crypto'       => $defaultPrice,
            'price_small_gbp'    => $small,
            'price_medium_gbp'   => $medium,
            'price_large_gbp'    => $large,
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    echo "NFT seeded (characters): {$nft->slug}\n";
}

echo "Done.\n";

