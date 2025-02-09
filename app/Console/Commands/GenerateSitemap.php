<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage; // Ensure you have a Product model
use Spatie\Sitemap\Sitemap; // If you have categories
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.xml file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sitemap...');
        $baseUrl = config('app.sitemap_base_url', env('SITEMAP_BASE_URL'));

        $sitemap = Sitemap::create();

        $staticPages = [
            ['url' => $baseUrl, 'priority' => 1.0, 'changefreq' => 'daily'],
            ['url' => "{$baseUrl}/about", 'priority' => 0.8, 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/contact", 'priority' => 0.8, 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $sitemap->add(
                Url::create("{$baseUrl}/{$page['url']}")
                    ->setPriority($page['priority'])
                    ->setChangeFrequency($page['changefreq'])
            );
        }

        $this->generateJsonFile($sitemap->getTags(), 'pages-sitemap.json');

        $sitemap = Sitemap::create();

        $products = Product::with(['media'])->get();
        foreach ($products as $product) {
            $sitemap->add(
                Url::create("{$baseUrl}/product/{$product->slug}")
                    ->setLastModificationDate($product->updated_at)
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->addImage($product->thumb->original_url, "$product->name image")
            );
        }

        $this->generateJsonFile($sitemap->getTags(), 'products-sitemap.json');

        $sitemap = Sitemap::create();

        $categories = Category::select('slug', 'updated_at')->get();
        foreach ($categories as $category) {
            $sitemap->add(
                Url::create("/category/{$category->slug}")
                    ->setLastModificationDate($category->updated_at)
                    ->setPriority(0.5)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            );
        }

        $this->generateJsonFile($sitemap->getTags(), 'categories-sitemap.json');

        // $blogPosts = BlogPost::select('slug', 'updated_at')->get();
        // foreach ($blogPosts as $post) {
        //     $sitemap->add(
        //         Url::create("/blog/{$post->slug}")
        //             ->setLastModificationDate($post->updated_at)
        //             ->setPriority(0.8)
        //             ->setChangeFrequency('daily')
        //     );
        // }

        $this->info('Sitemap successfully generated');
    }

    private function generateJsonFile($tags, $path): void
    {

        $sitemapData = [];

        foreach ($tags as $tag) {
            // Create an entry for each tag in the sitemap data
            $sitemapData[] = [
                'loc' => $tag->url,
                'lastmod' => isset($tag->lastModificationDate) ? $tag->lastModificationDate->toIso8601String() : null,
                'priority' => $tag->priority,
                'changefreq' => $tag->changeFrequency,
            ];

            // Get the index of the last added sitemap entry
            $lastIndex = count($sitemapData) - 1;

            if ($tag->images) {
                $sitemapData[$lastIndex]['images'] = [];
                foreach ($tag->images as $urlImage) {
                    $sitemapData[$lastIndex]['images'][] = [
                        'loc' => $urlImage->url,
                        'caption' => $urlImage->caption,
                    ];
                }
            }
        }

        $jsonSitemap = json_encode($sitemapData, JSON_PRETTY_PRINT);
        Storage::disk('public')->put($path, $jsonSitemap);
    }
}
