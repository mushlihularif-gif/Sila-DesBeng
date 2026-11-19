<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicNewsPageTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    public function test_public_detail_renders_without_the_retired_report_relationship(): void
    {
        $news = $this->content(1, 'Berita', 'published');
        $news->update(['is_active' => true, 'published_at' => now()]);
        $this->get(route('announcements.show', $news->id))
            ->assertOk()->assertSee($news->title);
    }

    public function test_related_news_excludes_drafts_and_other_villages_announcements(): void
    {
        $this->actingAs($this->user(1));
        $news = $this->content(1, 'Berita', 'published');
        $news->update(['is_active' => true, 'published_at' => now()]);
        $draft = $this->content(6, 'Berita', 'draft');
        $draft->update(['is_active' => true, 'title' => 'Draft must stay private']);
        $foreign = $this->content(7, 'Pengumuman', 'published');
        $foreign->update(['is_active' => true, 'target_audience_id' => 5, 'title' => 'Other village notice']);
        $local = $this->content(4, 'Pengumuman', 'published');
        $local->update(['is_active' => true, 'target_audience_id' => 1, 'title' => 'Own village notice']);

        $this->get(route('announcements.show', $news->id))
            ->assertOk()->assertSee($local->title)
            ->assertDontSee($draft->title)->assertDontSee($foreign->title)
            ->assertViewHas('relatedAnnouncements', fn ($items) => $items->pluck('id')->all() === [$local->id]);
        $this->get(route('announcements.show', $foreign->id))->assertNotFound();
        $this->get(route('announcements.show', $draft->id))->assertNotFound();
    }
}
