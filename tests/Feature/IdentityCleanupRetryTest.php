<?php

namespace Tests\Feature;

use App\Models\KycVerification;
use App\Models\User;
use App\Services\IdentityDocumentStorage;
use App\Services\IdentityFileCleanup;
use App\Services\IdentityReviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\IdentityDatabase;
use Tests\TestCase;

class IdentityCleanupRetryTest extends TestCase
{
    use IdentityDatabase;

    public function test_failed_replacement_deletion_is_tracked_and_scheduler_retries_without_touching_new_files(): void
    {
        $kyc = $this->pending();
        $old = $kyc->face_image_path;
        $real = Storage::disk('private');
        $disk = \Mockery::mock($real);
        $disk->shouldReceive('delete')->byDefault()->andReturnUsing(fn ($file) => $real->delete($file));
        $attempt = 0;
        $disk->shouldReceive('delete')->with($old)->twice()->andReturnUsing(function ($path) use ($real, &$attempt) {
            return ++$attempt === 1 ? false : $real->delete($path);
        });
        Storage::shouldReceive('disk')->with('private')->andReturn($disk);
        app(IdentityDocumentStorage::class)->save($kyc, ['face' => $this->image()]);
        $new = $kyc->face_image_path;
        $this->assertNotSame($old, $new);
        $this->assertDatabaseHas('identity_cleanup_queue', ['path' => $old, 'attempts' => 1]);
        $this->artisan('kyc:clean-files')->assertSuccessful();
        $this->assertFalse($real->exists($old));
        $this->assertTrue($real->exists($new));
        $this->assertSame('pending', $kyc->fresh()->status);
    }

    public function test_expired_draft_deletion_keeps_retry_intent_and_does_not_delete_recent_or_pending_records(): void
    {
        $kyc = $this->pending();
        $path = $kyc->ktp_image_path;
        $kyc->forceFill(['status' => 'draft', 'updated_at' => now()->subHours(3)])->save();
        $recent = KycVerification::create(['user_id' => 2, 'status' => 'draft']);
        $pending = KycVerification::create(['user_id' => 2, 'status' => 'pending']);
        $real = Storage::disk('private');
        $disk = \Mockery::mock($real);
        $disk->shouldReceive('delete')->byDefault()->andReturnUsing(fn ($file) => $real->delete($file));
        $attempt = 0;
        $disk->shouldReceive('delete')->with($path)->andReturnUsing(function ($file) use ($real, &$attempt) {
            return ++$attempt === 1 ? false : $real->delete($file);
        });
        Storage::shouldReceive('disk')->with('private')->andReturn($disk);
        $this->artisan('kyc:clean-drafts')->assertSuccessful();
        $this->assertNull($kyc->fresh());
        $this->assertNotNull($recent->fresh());
        $this->assertNotNull($pending->fresh());
        $this->assertDatabaseHas('identity_cleanup_queue', ['path' => $path]);
        $this->artisan('kyc:clean-files')->assertSuccessful();
        $this->assertFalse($real->exists($path));
    }

    public function test_scheduler_recovers_finished_references_from_older_release_and_retains_pending_documents(): void
    {
        $kyc = $this->pending();
        $paths = [$kyc->ktp_image_path, $kyc->face_image_path];
        $this->assertFalse(app(IdentityReviewService::class)->cleanup($kyc));
        $this->assertSame(0, DB::table('identity_cleanup_queue')->count());
        $kyc->update(['status' => 'approved']);
        $this->artisan('kyc:clean-files')->assertSuccessful();
        $this->assertNull($kyc->fresh()->ktp_image_path);
        $this->assertNull($kyc->fresh()->face_image_path);
        Storage::disk('private')->assertMissing($paths);
        $this->artisan('kyc:clean-files')->assertSuccessful();
    }

    public function test_new_draft_does_not_orphan_failed_deletion_of_previous_submission(): void
    {
        $old = $this->pending();
        $path = $old->ktp_image_path;
        $real = Storage::disk('private');
        $disk = \Mockery::mock($real);
        $disk->shouldReceive('delete')->byDefault()->andReturnUsing(fn ($file) => $real->delete($file));
        $attempt = 0;
        $disk->shouldReceive('delete')->with($path)->andReturnUsing(function ($file) use ($real, &$attempt) {
            return ++$attempt === 1 ? false : $real->delete($file);
        });
        Storage::shouldReceive('disk')->with('private')->andReturn($disk);
        $new = app(IdentityDocumentStorage::class)->createDraft(User::find(1), [], $this->image());
        $this->assertNull($old->fresh());
        $this->assertTrue($real->exists($new->ktp_image_path));
        $this->assertDatabaseHas('identity_cleanup_queue', ['path' => $path]);
    }

    public function test_invalid_cleanup_options_or_paths_cannot_delete_documents(): void
    {
        $this->artisan('kyc:clean-drafts', ['--hours' => 0])->assertFailed();
        $this->artisan('kyc:clean-files', ['--limit' => -1])->assertFailed();
        $cleanup = app(IdentityFileCleanup::class);
        $this->assertFalse($cleanup->attempt('untracked.enc'));
        $this->expectException(\InvalidArgumentException::class);
        $cleanup->enqueue('../outside.enc');
    }
}
