<?php

namespace App\Services;

use App\Models\Feed;

class FeedService {

    public function retrieveFeed(int $feedId, ?string $only, ?int $includePosts) {
        
        // Retrieve the feed from the db
        $feed = Feed::find($feedId);

        // If the feed has been found
        if(!empty($feed)) {
            
            $sources = match($only) {
                'instagram' => $feed->instagramSource,
                'tiktok' => $feed->tiktokSource,
                default => [$feed->instagramSource, $feed->tiktokSource]
            };

            if(!empty($includePosts)) {
                $feedPosts = $feed->posts()->limit($includePosts)->get();
            }

            return [
                'feed' => [$feed->id, $feed->name],
                'feedSources' => $sources,
                'feedPosts' => $feedPosts ?? []
            ];
        }

        // Feed: {feedId}
        // Sources:
        // ...
        // Posts:
        // ...
    }

}

