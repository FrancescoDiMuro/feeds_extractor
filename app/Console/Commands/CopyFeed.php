<?php

namespace App\Console\Commands;

use App\Services\FeedService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\App;

class CopyFeed extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy 
                            {feedId : Id of the feed to extract} 
                            {--only= : Specifies from which source extract the feeds} 
                            {--include-posts= : Specifies the number of posts to extract}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extracts a feed and the associated sources, optionally with posts, using the feed id.';

    protected function promptForMissingArgumentsUsing(): array {
        return [
            'feedId' => ['Which feed do you want to extract?', 'E.g. 123'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(FeedService $feedService) {
        $feedId = $this->argument('feedId');
        $only = $this->option('only');
        $includePosts = $this->option('include-posts');
        
        $value = $feedId . $only . $includePosts;

        $result = $feedService->retrieveFeed(
            feedId: $feedId, 
            only: $only, 
            includePosts: $includePosts
        );

        if(!empty($result)) {

            $feed = $result['feed'];
            $sources = $result['feedSources'];
            $posts = $result['feedPosts'];

            $sourcesRows = [];
            $this->line('FEEDS');
            $this->table(['id', 'name'], [$feed]);
            if(is_array($sources)) {
                foreach($sources as $source) {
                    $sourcesRows[] = $source->toArray();
                }
            }
            else {
                $sourcesRows[] = $sources->toArray();
            }
            $this->newLine();
            $this->line('SOURCES');
            $this->table(['id', 'name', 'fan_count', 'feed_id'], $sourcesRows);

            $this->newLine();
            $this->line('POSTS');
            $this->table(['id', 'url', 'feed_id'], $posts);

        }
        else {
            $this->warn('No feeds found with id "' . $feedId . '"!');
        }
        
    }
}
