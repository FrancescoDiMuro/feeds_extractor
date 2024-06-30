<?php

namespace App\Console\Commands;

use App\Services\FeedService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    protected $description = 'Extracts a feed and the associated sources, with an optional number of posts, using the feed id.';

    protected function promptForMissingArgumentsUsing(): array {
        return [
            'feedId' => ['Which feed do you want to extract?', 'E.g. 123'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(FeedService $feedService) {
        
        // Obtaining the various user's inputs
        $feedId = $this->argument('feedId');
        $specificSource = $this->option('only');
        $numberOfPosts = $this->option('include-posts');

        // Define the validation rules to validate the user's inputs
        $validationRules = [
            'feedId'=> ['integer'],
            'only' => ['nullable', Rule::in(['instagram', 'tiktok'])],
            'includePosts'=> ['nullable', 'integer', 'gte:1'],
        ];

        // Define the data which has to be validated
        $dataToBeValidated = array_combine(
            array_keys($validationRules),
            [$feedId, $specificSource, $numberOfPosts]
        );

        // Validate the data
        $validator = Validator::make(
            data: $dataToBeValidated, 
            rules: $validationRules
        );

        // If the validation fails
        if ($validator->fails()) {

            // Warn the user
            $this->warn('Cannot proceed with the feed extraction!');
        
            // Display the errors
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            // Exit code (KO)
            return 1;
        }

        // Retrieve an array of information from the feed,
        // with the specified parameters
        $feedInformation = $feedService->retrieveFeed(
            feedId: $feedId, 
            only: $specificSource, 
            includePosts: $numberOfPosts
        );

        // If the information have been correctly obtained
        if(!empty($feedInformation)) {

            // Extract the various information from the result array
            $feedData = $feedInformation['feed'];
            $sourcesData = $feedInformation['feedSources'];
            $posts = $feedInformation['feedPosts'];

            // Build the CLI from the result
            // Feeds
            $this->line('FEEDS');
            $this->table(['Id', 'Name'], [$feedData]);
            $this->newLine();

            // Sources
            if(!empty($sourcesData)) {
                $this->line('SOURCES');
                $this->table(['Id', 'Name', 'Fan Count', 'Feed Id'], $sourcesData);
                $this->newLine();
            }
            
            // Posts
            if(!empty($posts)) {
                $this->line('POSTS');
                $this->table(['Id', 'URL', 'Feed Id'], $posts);
            }
            
            // Exit code (OK)
            return 0;

        }
        else {
            
            // Warn the user
            $this->warn('No feeds found with id "' . $feedId . '"!');
            
            // Exit code (KO)
            return 1;
        }
        
    }
}
