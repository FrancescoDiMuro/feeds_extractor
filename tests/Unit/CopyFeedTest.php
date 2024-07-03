<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

class CopyFeedTest extends TestCase
{   
    
    public string $targetConnection = 'sqlite_target';
    
    public function setUp(): void {
        parent::setUp();
        Artisan::call('migrate:fresh', ['--database' => 'sqlite_target']);
    }

    public function testCopyFeedWithExistingRecord(): void
    {
        // Inputs
        $feedId = 1;
        $tableName = 'feeds';
        
        $exitCode = Artisan::call(
            'copy', [
                'feedId' => $feedId
            ]
        );
       
        $this->assertEquals(
            expected: 0,
            actual: $exitCode
        );

        $this->assertDatabaseHas(
            table: $tableName,
            data: [
                'id' => $feedId
            ],
            connection: $this->targetConnection
        );
    }

    public function testCopyFeedWithNonExistingRecord(): void
    {
        // Inputs
        $feedId = 100;
        $tableName = 'feeds';
        
        $exitCode = Artisan::call(
            'copy', [
                'feedId' => $feedId
            ]
        );
       
        $this->assertEquals(
            expected: 0,
            actual: $exitCode
        );

        $this->assertDatabaseMissing(
            table: $tableName,
            data: [
                'id' => $feedId
            ],
            connection: $this->targetConnection
        );
    }

    public function testCopyFeedWithOnlyParameter(): void
    {
        // Inputs
        $feedId = 2;
        $instragramSourceId = 2;
        $only = 'instagram';
        
        $exitCode = Artisan::call(
            'copy', [
                'feedId' => $feedId,
                '--only' => $only
            ]
        );
       
        $this->assertEquals(
            expected: 0,
            actual: $exitCode
        );

        $this->assertDatabaseHas(
            table: 'feeds',
            data: [
                'id' => $feedId
            ],
            connection: $this->targetConnection
        );

        $this->assertDatabaseHas(
            table: 'instagram_sources',
            data: [
                'id' => $instragramSourceId,
                'feed_id' => $feedId
            ],
            connection: $this->targetConnection
        );
    }

    public function testCopyFeedWithPostsParameter(): void {

        // Inputs
        $feedId = 3;
        $instagramSourceId = 3;
        $tikTokSourceId = 3;
        $numberOfPosts = 10;

        // Clean-up
        Artisan::call('migrate:fresh', ['--database' => 'sqlite_target']);
        
        $exitCode = Artisan::call(
            'copy', [
                'feedId' => $feedId,
                '--include-posts' => $numberOfPosts
            ]
        );
       
        $this->assertEquals(
            expected: 0,
            actual: $exitCode
        );

        $this->assertDatabaseHas(
            table: 'feeds',
            data: [
                'id' => $feedId
            ],
            connection: $this->targetConnection
        );

        $this->assertDatabaseHas(
            table: 'instagram_sources',
            data: [
                'id' => $instagramSourceId,
                'feed_id' => $feedId
            ],
            connection: $this->targetConnection
        );

        $this->assertDatabaseHas(
            table: 'tik_tok_sources',
            data: [
                'id' => $tikTokSourceId,
                'feed_id' => $feedId
            ],
            connection: $this->targetConnection
        );

        $this->assertDatabaseCount(
            table: 'posts',
            count: $numberOfPosts,
            connection: $this->targetConnection
        );

    }

    public function testCopyFeedWithAllParameters(): void {

        // Inputs
        $feedId = 4;
        $tikTokSourceId = 4;
        $only = 'tiktok';
        $numberOfPosts = 5;

        // Clean-up
        Artisan::call('migrate:fresh', ['--database' => 'sqlite_target']);
        
        $exitCode = Artisan::call(
            'copy', [
                'feedId' => $feedId,
                '--only' => $only,
                '--include-posts' => $numberOfPosts
            ]
        );
       
        $this->assertEquals(
            expected: 0,
            actual: $exitCode
        );

        $this->assertDatabaseHas(
            table: 'feeds',
            data: [
                'id' => $feedId
            ],
            connection: $this->targetConnection
        );

        $this->assertDatabaseHas(
            table: 'tik_tok_sources',
            data: [
                'id' => $tikTokSourceId,
                'feed_id' => $feedId
            ],
            connection: $this->targetConnection
        );

        $this->assertDatabaseCount(
            table: 'posts',
            count: $numberOfPosts,
            connection: $this->targetConnection
        );
    }
}
