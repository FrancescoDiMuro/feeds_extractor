<?php

namespace App\Services;

use App\Models\Feed;

class FeedService {

    public function retrieveFeed(string $connection, int $feedId, ?string $only = null, ?int $includePosts = null): array {
        
        // Retrieve the feed from the db
        $feed = Feed::on($connection)->find($feedId);

        // If the feed has been found
        if(!empty($feed)) {
            
            // Retrieve the specified source, or all if not specified
            // If a value different by "instagram" or "tiktok" is used
            // (and so, even an empty value), the default value is used
            $sources = match($only) {
                'instagram' => $feed->instagramSource,
                'tiktok' => $feed->tiktokSource,
                default => [$feed->instagramSource, $feed->tiktokSource]
            };

            // If the user specified a number of posts
            // Setting 0 to $includePosts makes the condition false,
            // since empty($includePosts) == true
            if(!empty($includePosts)) {
                
                // Obtaining a specific number ($includePosts) of posts
                $feedPosts = $feed->posts()->orderBy('id')->limit($includePosts)->get();
            }

            // Prepare the data to return
            $feedData = [$feed->id, $feed->name];
            
            // If at least a source has been found
            if(!empty($sources)) {
                
                // If more than a source has been found,
                // then loop through them and extract the information;
                // else, just assign the source
                $feedSources = is_array($sources) 
                                ? array_map(
                                    function ($source) {
                                        return $source->toArray();
                                    }, 
                                    $sources
                                )
                                : [$sources->toArray()];
            }

            // Return the data
            return [
                'feed' => $feedData,
                'feedSources' => $feedSources ?? [],
                'feedPosts' => $feedPosts ?? []
            ];
        }

        return [];
    }

    public function copyFeed(
        string $sourceConnection,
        string $targetConnection,
        int $feedId, 
        ?string $only = null, 
        ?int $includePosts = null
    ) {

        // If the feed is found in the target db, then delete it
        if($feedOnTargetDb = Feed::on($targetConnection)->find($feedId)) {
            $feedOnTargetDb->delete();
        }
        
        // Retrieve the feed from the db
        $feed = Feed::on($sourceConnection)->find($feedId);

        // If the feed has been found from the source db
        if(!empty($feed)) {

            // Create a new feed in the target db
            $feedTarget = Feed::on($targetConnection)->create($feed->toArray());

            // Checking the value of "--only" option
            if($only === 'instagram') {

                // Create the related Instagram source in the target db
                $feedTarget->instagramSource()->create($feed->instagramSource->toArray());
            }
            elseif($only === 'tiktok') {

                // Create the related TikTok source in the target db
                $feedTarget->tiktokSource()->create($feed->tiktokSource->toArray());
            }
            else {

                // Create both related sources in the target db
                $feedTarget->instagramSource()->create($feed->instagramSource->toArray());
                $feedTarget->tiktokSource()->create($feed->tiktokSource->toArray());
            }

            // Checking the number of posts to be retrieved
            if(!empty($includePosts)) {

                // Create the related posts in the target db
                $feedTarget->posts()->createMany($feed->posts()->limit($includePosts)->get()->toArray());
            }

            return 1;
        }

        return 0;
    }
}
