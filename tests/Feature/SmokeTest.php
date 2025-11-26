<?php
namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{  
    public function test_database_connection_is_working(): void
    {
    // connection exists
     $this->assertTrue(
    \DB::connection()->getPdo() !== null,
     'Db connection fail'
        );

     // database tables tets
     $collections = \DB::table('collections')->count();
    $nfts = \DB::table('nfts')->count();

    $this->assertIsInt($collections);
    $this->assertIsInt($nfts);
    }

    // hmepage loader test 
     
    public function test_homepage_loads(): void
    {
    $this->get('/')
     ->assertStatus(200);
    }
    public function test_cache_is_working(): void
{
    cache()->put('smoke_test_key', 'test_value', 60);
    $this->assertEquals('test_value', cache()->get('smoke_test_key'));
    cache()->forget('smoke_test_key');
}
}