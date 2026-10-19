<?php

namespace Tests\Feature;

use App\Http\Controllers\User\WilayahAdminController;
use App\Models\{Announcement, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WilayahContentAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('regions', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('type'); $t->unsignedBigInteger('parent_id')->nullable();
        });
        Schema::create('announcements', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('admin_id'); $t->unsignedBigInteger('region_id');
            foreach (['title', 'description', 'type', 'post_category'] as $key) $t->string($key);
            $t->dateTime('event_date')->nullable(); $t->string('location')->nullable(); $t->string('image_path')->nullable(); $t->timestamps();
        });
        DB::table('regions')->insert([
            ['id' => 1, 'name' => 'Desa Uji', 'type' => 'desa', 'parent_id' => null],
            ['id' => 2, 'name' => 'RT Uji', 'type' => 'rt', 'parent_id' => 1],
            ['id' => 3, 'name' => 'Desa Lain', 'type' => 'desa', 'parent_id' => null],
        ]);
        $user = new User(); $user->id = 10; $user->region_id = 2; $user->role = 'admin_rt';
        Auth::setUser($user);
    }

    private function content(int $author = 10): Announcement
    {
        return Announcement::create(['admin_id' => $author, 'region_id' => 1, 'title' => 'Judul awal', 'description' => 'Isi awal', 'type' => 'Event', 'post_category' => 'Berita']);
    }

    private function updateRequest(int $region = 1): Request
    {
        return Request::create('/wilayah/konten/1', 'PUT', ['title' => 'Judul diperbarui', 'description' => 'Isi diperbarui', 'type' => 'Event', 'target_region_id' => $region]);
    }

    public function test_author_can_edit_and_delete_their_content_published_to_a_parent_region(): void
    {
        $content = $this->content();
        $controller = app(WilayahAdminController::class);
        $view = $controller->editKonten($content);
        $this->assertSame('user.wilayah.konten-edit', $view->name());
        $this->assertEqualsCanonicalizing([1, 2], $view->getData()['regions']->pluck('id')->all());
        $response = $controller->updateKonten($this->updateRequest(), $content);
        $this->assertSame(route('wilayah.berita.index'), $response->getTargetUrl());
        $this->assertSame('Judul diperbarui', $content->fresh()->title);
        $this->assertSame('Berita', $content->fresh()->post_category);
        $controller->destroyKonten($content);
        $this->assertSame(0, Announcement::count());
    }

    public function test_another_author_cannot_edit_or_delete_content(): void
    {
        $content = $this->content(99);
        $controller = app(WilayahAdminController::class);
        foreach (['edit', 'update', 'delete'] as $action) {
            try {
                match ($action) {
                    'edit' => $controller->editKonten($content),
                    'update' => $controller->updateKonten($this->updateRequest(), $content),
                    'delete' => $controller->destroyKonten($content),
                };
                $this->fail('Expected authorization failure');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
        $this->assertSame('Judul awal', $content->fresh()->title);
    }

    public function test_author_cannot_move_content_to_an_unrelated_region(): void
    {
        $content = $this->content();
        try {
            app(WilayahAdminController::class)->updateKonten($this->updateRequest(3), $content);
            $this->fail('Expected validation failure');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('target_region_id', $e->errors());
        }
        $this->assertSame(1, $content->fresh()->region_id);
    }
}
