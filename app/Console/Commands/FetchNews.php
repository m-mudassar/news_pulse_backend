<?php
namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Category;
use App\Models\News;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchNews extends Command
{
    protected $signature = 'news:fetch';
    protected $description = 'Fetch news articles from external APIs and store them in the database';

    private function fetchArticlesFromNewsApi()
    {
        $apiUrl = "https://newsapi.org/v2/everything";

        $apiKey = config('app.news_api_key');

        $response = Http::get($apiUrl, [
            'apiKey' => $apiKey,
            'q' => 'technology'
        ]);

        if ($response->successful()) {
            $articles = $response->json()['articles'];
            foreach ($articles as $article) {
                $author = Author::firstOrCreate(['name' => $article['author'] ?? 'Unknown']);
                $source = Source::firstOrCreate(['name' => $article['source']['name']]);
                $category = Category::firstOrCreate(['name' => 'technology']);

                News::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'title' => $article['title'],
                        'content' => $article['content'] ?? '',
                        'description' => $article['description'] ?? '',
                        'author_id' => $author->id,
                        'source_id' => $source->id,
                        'category_id' => $category->id,
                        'url' => $article['url'],
                        'image_url' => $article['urlToImage'],
                        'published_at' => Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s'),
                    ]
                );
            }
        } else {
            Log::error('Failed to fetch news articles from news api : ' . $response->body());
            $this->error('Failed to fetch news articles from news api.');
        }
    }

    private function fetchArticlesFromNewYorkTimes()
    {
        $apiUrl = "https://api.nytimes.com/svc/news/v3/content/all/all.json";

        $apiKey = config('app.new_york_times_api_key');

        $response = Http::get($apiUrl, [
            'api-key' => $apiKey
        ]);

        if ($response->successful()) {
            $articles = $response->json()['results'];
            foreach ($articles as $article) {
                $author = Author::firstOrCreate(['name' => $article['byline'] ?? 'Unknown']);
                $source = Source::firstOrCreate(['name' => $article['source']]);
                $category = Category::firstOrCreate(['name' => $article['section']]);

                News::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'title' => $article['title'],
                        'content' => $article['abstract'] ?? '',
                        'description' => $article['abstract'] ?? '',
                        'author_id' => $author->id,
                        'source_id' => $source->id,
                        'category_id' => $category->id,
                        'url' => $article['url'],
                        'image_url' => !empty($article['multimedia']) && isset($article['multimedia'][0]['url'])
                            ? $article['multimedia'][0]['url']
                            : '',
                        'published_at' => Carbon::parse($article['published_date'])->format('Y-m-d H:i:s'),
                    ]
                );
            }
        } else {
            Log::error('Failed to fetch news articles from new york times: ' . $response->body());
            $this->error('Failed to fetch news articles from new york times.');
        }

    }

    private function fetchArticlesFromGuardian()
    {
        $apiUrl = "https://content.guardianapis.com/search";

        $apiKey = config('app.guardian_api_key');

        $response = Http::get($apiUrl, [
            'api-key' => $apiKey,
            'show-fields' => 'thumbnail,trailText',
            'show-tags' => "contributor",
            'page-size' => 20
        ]);

        if ($response->successful()) {
            $articles = $response->json()['response']['results'];
            foreach ($articles as $article) {
                $author = Author::firstOrCreate([
                    'name' => !empty($article['tags']) && isset($article['tags'][0]['webTitle'])
                        ? $article['tags'][0]['webTitle'] : 'Unknown'
                ]);
                $source = Source::firstOrCreate(['name' => $article['sectionName']]);
                $category = Category::firstOrCreate(['name' => $article['pillarName']]);

                News::updateOrCreate(
                    ['url' => $article['webUrl']],
                    [
                        'title' => $article['webTitle'],
                        'content' => !empty($article['fields']['trailText']) ? $article['fields']['trailText'] : '',
                        'description' => !empty($article['fields']['trailText']) ? $article['fields']['trailText'] : '',
                        'author_id' => $author->id,
                        'source_id' => $source->id,
                        'category_id' => $category->id,
                        'url' => $article['webUrl'],
                        'image_url' => !empty($article['fields']['thumbnail']) ? $article['fields']['thumbnail'] : '',
                        'published_at' => Carbon::parse($article['webPublicationDate'])->format('Y-m-d H:i:s'),
                    ]
                );
            }
        } else {
            Log::error('Failed to fetch news articles from guardian : ' . $response->body());
            $this->error('Failed to fetch news articles from guardian.');
        }
    }

    public function handle()
    {
        echo "Fetching news articles from news api...\n";
        $this->fetchArticlesFromNewsApi();
        echo "Fetching news articles from new york times...\n";
        $this->fetchArticlesFromNewYorkTimes();
        echo "Fetching news articles from guardian...\n";
        $this->fetchArticlesFromGuardian();

        $this->info('News articles fetched successfully.');
    }
}
