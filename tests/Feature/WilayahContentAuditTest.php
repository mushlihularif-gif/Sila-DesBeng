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

    public function test_legacy_editor_redirects_and_cannot_bypass_handling_lock(): void
    {
        $content = $this->content();
        $controller = app(WilayahAdminController::class);
        $response = $controller->editKonten($content);
        $this->assertSame(route('content.show', $content), $response->getTargetUrl());
        foreach (['update', 'delete'] as $action) {
            try {
                $action === 'update' ? $controller->updateKonten($this->updateRequest(), $content) : $controller->destroyKonten($content);
                $this->fail('Legacy mutation bypassed shared editor');
            } catch (HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        }
        $this->assertSame('Judul awal', $content->fresh()->title);
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
                $this->assertSame($action === 'edit' ? 403 : 409, $e->getStatusCode());
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
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertSame(1, $content->fresh()->region_id);
    }
}
