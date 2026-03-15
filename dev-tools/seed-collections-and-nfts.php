<?php

/**
 * Simple seeding script for local/dev use.
 *
 * Usage (from project root):
 * php tools/seed-collections-and-nfts.php
 *
 * It will create (or update) the following:
 * - collections:
 * - glossy-collection
 * - superhero-collection
 * - geotennis-collection
 * - characters-collection
 * - fish-collection
 * - faces-collection
 * - nfts belonging to those collections, matching your hard-coded views.
 */

use App\Models\Collection;
use App\Models\Listing;
use App\Models\Nft;
use App\Models\NftToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Seeding collections and NFTs...\n";

DB::listen(function ($query) {
    $sql = $query->sql;
    foreach ($query->bindings as $binding) {
        $bindingValue = is_numeric($binding) ? $binding : "'".str_replace("'", "''", (string) $binding)."'";
        $sql = preg_replace('/\?/', $bindingValue, $sql, 1);
    }
    echo "[DB] {$sql}\n";
});

$nineMintUser = User::updateOrCreate(
    ['id' => 1],
    ['name' => '9Mint', 'email' => null, 'password' => null, 'role' => 'admin']
);

$vlasUser = User::updateOrCreate(
    ['id' => 2],
    [
        'name' => 'Vlas',
        'email' => null,
        'password' => null,
        'role' => 'user',
        'badges' => [[
            'key' => 'trusted_seller',
            'label' => 'Trusted Seller',
            'description' => 'Recognized for consistent verified NFT sales and marketplace reliability.',
        ]],
    ]
);

try {
    $nineMintUser->assignRole('admin');
} catch (\Throwable $e) {
    // admin
}

$sellerUserId = $vlasUser->id;


// --- Collections ---
$collections = [
    'glossy-collection' => [
        'name'            => 'Glossy Collection',
        'description'     => 'Glossy animal NFTs created by Vlas.',
        'cover_image_url' => '/images/nfts/glossy/GlossyDuckNFT.png',
        'creator_name'    => 'Vlas',
    ],
    'superhero-collection' => [
        'name'            => 'Superhero Collection',
        'description'     => 'Iconic superhero NFTs.',
        'cover_image_url' => '/images/nfts/superhero/Superman.png',
        'creator_name'    => 'Vlas',
    ],
    'geotennis-collection' => [
        'name'            => 'Geo Tennis',
        'description'     => 'Playing tennis with squares',
        'cover_image_url' => '/images/nfts/geotennis/t1.png',
        'creator_name'    => 'Vlas',
    ],
    'characters-collection' => [
        'name'            => 'Characters',
        'description'     => 'movie stars',
        'cover_image_url' => '/images/nfts/characters/carl.png',
        'creator_name'    => 'Vlas',
    ],
    'fish-collection' => [
        'name'            => 'Fish Collection',
        'description'     => 'A collection of aquatic fish.',
        'cover_image_url' => '/images/nfts/fish/fish1.png',
        'creator_name'    => 'Vlas',
    ],
    'faces-collection' => [
        'name'            => 'Faces Collection',
        'description'     => 'A collection of distinct faces.',
        'cover_image_url' => '/images/nfts/faces/face1.png',
        'creator_name'    => 'Vlas',
    ],
];

foreach ($collections as $slug => $data) {
    $collection = Collection::updateOrCreate(
        ['slug' => $slug],
        $data
    );
}

$glossy = Collection::where('slug', 'glossy-collection')->first();
$superhero = Collection::where('slug', 'superhero-collection')->first();
$geotennis = Collection::where('slug', 'geotennis-collection')->first();
$characters = Collection::where('slug', 'characters-collection')->first();
$fish = Collection::where('slug', 'fish-collection')->first();
$faces = Collection::where('slug', 'faces-collection')->first();

if (!$glossy || !$superhero || !$geotennis || !$characters || !$fish || !$faces) {
    echo "Error: collections not found after seeding. Aborting NFT creation.\n";
    exit(1);
}

// Common defaults
$defaultCurrency = 'GBP';
$editionsTotal   = 5;

/**
 * Generate deterministic per-NFT reference price in GBP.
 * This keeps local/dev data stable across runs while ensuring prices differ per NFT.
 */
function refPriceGbp(string $slug): float
{
    $seed = abs(crc32($slug));
    // Base between 18.00 and 58.00 (GBP)
    $base = 18 + (($seed % 4000) / 100);
    return round($base, 2);
}

// --- Glossy NFTs ---
$glossyNfts = [
    [
        'slug'        => 'glossy-duck',
        'name'        => 'Glossy Duck',
        'description' => 'A pixelated, 3D-rendered duck shimmering with a high-gloss mosaic finish.',
        'image_url'   => '/images/nfts/glossy/GlossyDuckNFT.png',
    ],
    [
        'slug'        => 'glossy-cat',
        'name'        => 'Glossy Cat',
        'description' => 'A fragmented, glassy feline calmly observing the digital realm.',
        'image_url'   => '/images/nfts/glossy/GlossyCatNFT.png',
    ],
    [
        'slug'        => 'glossy-donkey',
        'name'        => 'Glossy Donkey',
        'description' => 'A shiny, blocky reimagining of a beloved animated sidekick.',
        'image_url'   => '/images/nfts/glossy/GlossyDonkeyNFT.png',
    ],
    [
        'slug'        => 'glossy-giraffe',
        'name'        => 'Glossy Giraffe',
        'description' => 'Reaching new heights with a tiled, highly reflective geometric coat.',
        'image_url'   => '/images/nfts/glossy/GlossyGiraffeNFT.png',
    ],
    [
        'slug'        => 'glossy-lobster',
        'name'        => 'Glossy Lobster',
        'description' => 'A vibrant red crustacean constructed from glossy, translucent digital scales.',
        'image_url'   => '/images/nfts/glossy/GlossyLobsterNFT.png',
    ],
    [
        'slug'        => 'glossy-rooster',
        'name'        => 'Glossy Rooster',
        'description' => 'Strutting its pixelated plumage with an undeniable, eye-catching sheen.',
        'image_url'   => '/images/nfts/glossy/GlossyRoosterNFT.png',
    ],
    [
        'slug'        => 'glossy-squirrel',
        'name'        => 'Glossy Squirrel',
        'description' => 'A glassy-eyed woodland creature rendered in polished mosaic tiles.',
        'image_url'   => '/images/nfts/glossy/GlossySquirrelNFT.png',
    ],
];

foreach ($glossyNfts as $data) {
    $refPrice = refPriceGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $glossy->id,
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    $existingTokens = NftToken::where('nft_id', $nft->id)->count();
    for ($i = $existingTokens + 1; $i <= $editionsTotal; $i++) {
        $token = NftToken::create([
            'nft_id' => $nft->id,
            'serial_number' => $i,
            'owner_user_id' => $vlasUser->id,
            'status' => 'listed',
        ]);

        Listing::create([
            'token_id' => $token->id,
            'seller_user_id' => $sellerUserId,
            'status' => 'active',
            'ref_amount' => $refPrice,
            'ref_currency' => $defaultCurrency,
        ]);
    }
}

// --- Superhero NFTs ---
$superheroNfts = [
    [
        'slug'        => 'aquaman',
        'name'        => 'Aquaman',
        'description' => 'A uniquely drawn king of the sea featuring a rather questionable MS-Paint beard.',
        'image_url'   => '/images/nfts/superhero/Aquaman.png',
    ],
    [
        'slug'        => 'batman',
        'name'        => 'Batman',
        'description' => 'A hilariously simplified Dark Knight sporting a very smooth cowl.',
        'image_url'   => '/images/nfts/superhero/Batman.png',
    ],
    [
        'slug'        => 'cyborg',
        'name'        => 'Cyborg',
        'description' => 'Half-machine, half-derp, fully ready to save the day in vivid digital ink.',
        'image_url'   => '/images/nfts/superhero/Cyborg.png',
    ],
    [
        'slug'        => 'flash',
        'name'        => 'Flash',
        'description' => 'The fastest man alive, looking appropriately startled by his own speed.',
        'image_url'   => '/images/nfts/superhero/Flash.png',
    ],
    [
        'slug'        => 'ironman',
        'name'        => 'Iron Man',
        'description' => 'A wonderfully wobbly take on the iconic billionaire armored avenger.',
        'image_url'   => '/images/nfts/superhero/IronMan.png',
    ],
    [
        'slug'        => 'spiderman',
        'name'        => 'Spiderman',
        'description' => 'A spectacularly stretchy and thoroughly confused-looking web-slinger.',
        'image_url'   => '/images/nfts/superhero/Spiderman.png',
    ],
    [
        'slug'        => 'superman',
        'name'        => 'Superman',
        'description' => 'The Man of Steel, featuring an impressively elongated neck.',
        'image_url'   => '/images/nfts/superhero/Superman.png',
    ],
    [
        'slug'        => 'wonder-woman',
        'name'        => 'Wonder Woman',
        'description' => 'A fierce warrior princess rendered with charmingly off-kilter proportions.',
        'image_url'   => '/images/nfts/superhero/WonderWomen.png',
    ],
];

foreach ($superheroNfts as $data) {
    $refPrice = refPriceGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $superhero->id,
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    $existingTokens = NftToken::where('nft_id', $nft->id)->count();
    for ($i = $existingTokens + 1; $i <= $editionsTotal; $i++) {
        $token = NftToken::create([
            'nft_id' => $nft->id,
            'serial_number' => $i,
            'owner_user_id' => $vlasUser->id,
            'status' => 'listed',
        ]);

        Listing::create([
            'token_id' => $token->id,
            'seller_user_id' => $sellerUserId,
            'status' => 'active',
            'ref_amount' => $refPrice,
            'ref_currency' => $defaultCurrency,
        ]);
    }
}

// --- Geo Tennis NFTs ---
$geotennisNfts = [
    [
        'slug'        => 't1',
        'name'        => 't1',
        'description' => 'A dynamic tennis serve playfully interrupted by a bold, pale-yellow geometric block.',
        'image_url'   => '/images/nfts/geotennis/t1.png',
    ],
    [
        'slug'        => 't2',
        'name'        => 't2',
        'description' => 'Mid-stride on the hard court, heavily obscured by a mint-green censorship square.',
        'image_url'   => '/images/nfts/geotennis/t2.png',
    ],
    [
        'slug'        => 't3',
        'name'        => 't3',
        'description' => 'A powerful backhand stance partially hidden by a striking lime-green rectangle.',
        'image_url'   => '/images/nfts/geotennis/t3.png',
    ],
    [
        'slug'        => 't4',
        'name'        => 't4',
        'description' => 'Sprinting across the grass court, interrupted by an earthy olive-green box.',
        'image_url'   => '/images/nfts/geotennis/t4.png',
    ],
    [
        'slug'        => 't5',
        'name'        => 't5',
        'description' => 'A focused return shot blocked out by a stark, crisp white square over the torso.',
        'image_url'   => '/images/nfts/geotennis/t5.png',
    ],
    [
        'slug'        => 't6',
        'name'        => 't6',
        'description' => 'Diving for the ball on the grass, concealed by a muted maroon rectangular void.',
        'image_url'   => '/images/nfts/geotennis/t6.png',
    ],
    [
        'slug'        => 't7',
        'name'        => 't7',
        'description' => 'A soaring serve on clay, mysteriously censored by a warm terracotta block.',
        'image_url'   => '/images/nfts/geotennis/t7.png',
    ],
];

foreach ($geotennisNfts as $data) {
    $refPrice = refPriceGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $geotennis->id,
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    $existingTokens = NftToken::where('nft_id', $nft->id)->count();
    for ($i = $existingTokens + 1; $i <= $editionsTotal; $i++) {
        $token = NftToken::create([
            'nft_id' => $nft->id,
            'serial_number' => $i,
            'owner_user_id' => $vlasUser->id,
            'status' => 'listed',
        ]);

        Listing::create([
            'token_id' => $token->id,
            'seller_user_id' => $sellerUserId,
            'status' => 'active',
            'ref_amount' => $refPrice,
            'ref_currency' => $defaultCurrency,
        ]);
    }
}

// --- Characters NFTs ---
$charactersNfts = [
    [
        'slug'        => 'carl',
        'name'        => 'carlos',
        'description' => 'A greyscale, slightly glitchy rendering of a familiar figure with wide-open arms.',
        'image_url'   => '/images/nfts/characters/carl.png',
    ],
    [
        'slug'        => 'him',
        'name'        => 'Him',
        'description' => 'A haunting, distorted portrait with a fractured, digital-brushstroke texture.',
        'image_url'   => '/images/nfts/characters/him.png',
    ],
    [
        'slug'        => 'lee',
        'name'        => 'Lee',
        'description' => 'A high-contrast, thermal-colored tribute to an iconic martial arts legend.',
        'image_url'   => '/images/nfts/characters/lee.png',
    ],
    [
        'slug'        => 'mads',
        'name'        => 'Mads',
        'description' => 'An intense, shadowed visage with digital artifacts dripping from the collar.',
        'image_url'   => '/images/nfts/characters/mads.png',
    ],
    [
        'slug'        => 'mike',
        'name'        => 'Mike',
        'description' => 'A heavily pixel-sorted, explosive portrait composed of scattered digital shards.',
        'image_url'   => '/images/nfts/characters/mike.png',
    ],
    [
        'slug'        => 'qqq',
        'name'        => 'QQQ',
        'description' => 'A surreal, wireframe-like mannequin featuring distinctly out-of-place realistic lips.',
        'image_url'   => '/images/nfts/characters/qqq.png',
    ],
    [
        'slug'        => 'box',
        'name'        => 'Box',
        'description' => 'A stark, high-contrast monochrome silhouette of a deeply shadowed physique.',
        'image_url'   => '/images/nfts/characters/box.png',
    ],
];

foreach ($charactersNfts as $data) {
    $refPrice = refPriceGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $characters->id,
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    $existingTokens = NftToken::where('nft_id', $nft->id)->count();
    for ($i = $existingTokens + 1; $i <= $editionsTotal; $i++) {
        $token = NftToken::create([
            'nft_id' => $nft->id,
            'serial_number' => $i,
            'owner_user_id' => $vlasUser->id,
            'status' => 'listed',
        ]);

        Listing::create([
            'token_id' => $token->id,
            'seller_user_id' => $sellerUserId,
            'status' => 'active',
            'ref_amount' => $refPrice,
            'ref_currency' => $defaultCurrency,
        ]);
    }
}

// --- Fish NFTs ---
$fishNfts = [
    ['slug' => 'fish-1', 'name' => 'Abyssal Angler', 'description' => 'A mackerel soaring downward, watched over by an eerie, disembodied human eye.', 'image_url' => '/images/nfts/fish/fish1.png'],
    ['slug' => 'fish-2', 'name' => 'Neon Tetra', 'description' => 'A vertical fish composition topped with an unsettling, greyscale human gaze.', 'image_url' => '/images/nfts/fish/fish2.png'],
    ['slug' => 'fish-3', 'name' => 'Coral Crown', 'description' => 'A surreal aquatic dance featuring fish intertwined with a bold, painted human lip.', 'image_url' => '/images/nfts/fish/fish3.png'],
    ['slug' => 'fish-4', 'name' => 'Golden Koi', 'description' => 'A beautifully rendered blue fish sporting an unexpectedly human facial feature.', 'image_url' => '/images/nfts/fish/fish4.png'],
    ['slug' => 'fish-5', 'name' => 'Shadow Shark', 'description' => 'A bright red fish bearing a massive, unblinking cyclopean eye on its head.', 'image_url' => '/images/nfts/fish/fish5.png'],
    ['slug' => 'fish-6', 'name' => 'Beta Blaze', 'description' => 'A colorful aquatic creature swimming under the watchful gaze of a vivid blue eye.', 'image_url' => '/images/nfts/fish/fish6.png'],
    ['slug' => 'fish-7', 'name' => 'Crypto Puffer', 'description' => 'A heavily scaled composition featuring a strikingly realistic human eye with long lashes.', 'image_url' => '/images/nfts/fish/fish7.png'],
];

foreach ($fishNfts as $data) {
    $refPrice = refPriceGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $fish->id,
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    $existingTokens = NftToken::where('nft_id', $nft->id)->count();
    for ($i = $existingTokens + 1; $i <= $editionsTotal; $i++) {
        $token = NftToken::create([
            'nft_id' => $nft->id,
            'serial_number' => $i,
            'owner_user_id' => $vlasUser->id,
            'status' => 'listed',
        ]);

        Listing::create([
            'token_id' => $token->id,
            'seller_user_id' => $sellerUserId,
            'status' => 'active',
            'ref_amount' => $refPrice,
            'ref_currency' => $defaultCurrency,
        ]);
    }
}

// --- Faces NFTs ---
$facesNfts = [
    ['slug' => 'face-1', 'name' => 'face1', 'description' => 'A heavily blurred monochrome portrait outlined with crude, nervous digital scribbles.', 'image_url' => '/images/nfts/faces/face1.png'],
    ['slug' => 'face-2', 'name' => 'face2', 'description' => 'A muted, out-of-focus subject defaced with whimsical, childish white line art.', 'image_url' => '/images/nfts/faces/face2.png'],
    ['slug' => 'face-3', 'name' => 'face3', 'description' => 'A vibrant red and orange blur sporting a hastily drawn, toothy smile.', 'image_url' => '/images/nfts/faces/face3.png'],
    ['slug' => 'face-4', 'name' => 'face4', 'description' => 'A soft-focus blue portrait detailed with bizarre, floating facial contours.', 'image_url' => '/images/nfts/faces/face4.png'],
    ['slug' => 'face-5', 'name' => 'face5', 'description' => 'A colorful blur rocking distinctively drawn shades and a squiggly pout.', 'image_url' => '/images/nfts/faces/face5.png'],
    ['slug' => 'face-6', 'name' => 'face6', 'description' => 'An ethereal, indistinct face overlaid with delicate, abstract facial mapping.', 'image_url' => '/images/nfts/faces/face6.png'],
    ['slug' => 'face-7', 'name' => 'face7', 'description' => 'A hazy, deep blue portrait featuring roughly sketched eyebrows and a gaping mouth.', 'image_url' => '/images/nfts/faces/face7.png'],
];

foreach ($facesNfts as $data) {
    $refPrice = refPriceGbp($data['slug']);
    $nft = Nft::updateOrCreate(
        ['slug' => $data['slug']],
        [
            'collection_id'      => $faces->id,
            'name'               => $data['name'],
            'description'        => $data['description'],
            'image_url'          => $data['image_url'],
            'editions_total'     => $editionsTotal,
            'editions_remaining' => $editionsTotal,
            'is_active'          => true,
        ]
    );

    $existingTokens = NftToken::where('nft_id', $nft->id)->count();
    for ($i = $existingTokens + 1; $i <= $editionsTotal; $i++) {
        $token = NftToken::create([
            'nft_id' => $nft->id,
            'serial_number' => $i,
            'owner_user_id' => $vlasUser->id,
            'status' => 'listed',
        ]);

        Listing::create([
            'token_id' => $token->id,
            'seller_user_id' => $sellerUserId,
            'status' => 'active',
            'ref_amount' => $refPrice,
            'ref_currency' => $defaultCurrency,
        ]);
    }
}

echo "Done.\n";