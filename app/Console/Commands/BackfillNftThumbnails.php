<?php

namespace App\Console\Commands;

use App\Models\Nft;
use App\Services\ThumbnailService;
use Illuminate\Console\Command;

class BackfillNftThumbnails extends Command
{
    protected $signature = 'nfts:backfill-thumbnails
                            {--force : Re-generate even if thumbnail exists}
                            {--collection-id= : Only process NFTs from a specific collection id}';

    protected $description = 'Generate/repair 720px WebP thumbnails for NFTs';

    public function handle(): int
    {
        $query = Nft::query();
        $collectionId = $this->option('collection-id');

        if (! empty($collectionId)) {
            $query->where('collection_id', (int) $collectionId);
        }

        $total = $query->count();

        if ($total === 0) {
            $scope = ! empty($collectionId) ? " in collection #{$collectionId}" : '';
            $this->info("No NFTs found{$scope}. Nothing to do.");
            return self::SUCCESS;
        }

        $scope = ! empty($collectionId) ? " in collection #{$collectionId}" : '';
        $this->info("Checking {$total} NFT(s){$scope}...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $skipped = 0;
        $alreadyPresent = 0;
        $force = (bool) $this->option('force');

        $query->chunkById(50, function ($nfts) use ($bar, &$success, &$skipped, &$alreadyPresent, $force) {
            foreach ($nfts as $nft) {
                if (! $force && ! $this->needsThumbnailGeneration($nft)) {
                    $alreadyPresent++;
                    $bar->advance();
                    continue;
                }

                $sourcePath = ThumbnailService::resolveAbsolutePath($nft->image_url);

                if (! $sourcePath) {
                    $this->newLine();
                    $this->warn("  Skipped NFT #{$nft->id} ({$nft->name}): source image not found at {$nft->image_url}");
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Determine output directory based on the original image path
                $relative = ltrim($nft->image_url, '/');
                $dir = dirname($relative);
                $outputDir = $dir . '/thumbs';

                $thumbnailUrl = ThumbnailService::generate($sourcePath, $outputDir, 'thumb');

                if ($thumbnailUrl) {
                    $nft->update(['thumbnail_url' => $thumbnailUrl]);
                    $success++;
                } else {
                    $this->newLine();
                    $this->warn("  Failed to generate thumbnail for NFT #{$nft->id} ({$nft->name})");
                    $skipped++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. {$success} generated, {$alreadyPresent} already present, {$skipped} skipped.");

        return self::SUCCESS;
    }

    private function needsThumbnailGeneration(Nft $nft): bool
    {
        if (empty($nft->thumbnail_url)) {
            return true;
        }

        $thumbnailPath = public_path(ltrim((string) $nft->thumbnail_url, '/'));
        return ! file_exists($thumbnailPath);
    }
}
