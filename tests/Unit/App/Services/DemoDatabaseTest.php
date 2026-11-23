<?php

use Carbon\Carbon;
use Services\DemoService;

final class DemoDatabaseTest extends TestCase
{
    public function test_persists_blog_posts_and_filters_future_and_old_entries(): void
    {
        $service = new DemoService();
        $current = $service->createBlogPost(['title' => 'Current fixture', 'content' => 'Content']);
        $old = $service->createBlogPost(['title' => 'Old fixture', 'published_at' => Carbon::now()->subDays(45)]);
        $future = $service->createBlogPost(['title' => 'Future fixture', 'published_at' => Carbon::now()->addDays(45)]);

        self::assertSame('Anonymous', $current['author']);
        self::assertSame('Current fixture', $service->getBlogPost($current['id'])['title']);
        self::assertNull($service->getBlogPost($future['id']));
        self::assertContains($old['id'], array_column($service->getBlogPosts(), 'id'));
        self::assertContains($current['id'], array_column($service->getRecentBlogPosts(), 'id'));
        self::assertNotContains($old['id'], array_column($service->getRecentBlogPosts(), 'id'));
        self::assertNotContains($future['id'], array_column($service->getBlogPosts(), 'id'));
    }

    public function test_saves_and_lists_fixture_samples(): void
    {
        $service = new DemoService();
        $sample = $service->createSample(['name' => 'Fixture', 'email' => 'fixture@example.test']);
        self::assertSame('Fixture', $sample['name']);
        self::assertSame('', $sample['description']);
        self::assertSame(['fixture@example.test'], array_column($service->getSamples(), 'email'));
    }
}
