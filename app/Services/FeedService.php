<?php

namespace App\Services;

use App\Models\Feed;

class FeedService {

    public function retrieveFeed(int $feedId, ?string $only, ?int $includePosts): array {
        
        // Retrieve the feed from the db
        $feed = Feed::find($feedId);

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

}

