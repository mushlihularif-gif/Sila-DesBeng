<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\KycVerification;
use App\Models\User;
use App\Services\IdentityDocumentStorage;
use App\Services\IdentityReviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\Support\IdentityDatabase;
use Tests\TestCase;

class IdentityReviewAuditTest extends TestCase
{
    use IdentityDatabase;

    private function submission(): KycVerification
    {
        $kyc = $this->pending();
        $kyc->update(['nik_from_ocr' => '0000000000000001', 'name_from_ocr' => 'Nama Terverifikasi',
            'desa_from_ocr' => 'Desa Lain', 'address_from_ocr' => 'Alamat pada KTP', 'rt_from_ocr' => '99']);
        User::findOrFail(1)->update(['verification_status' => 'pending', 'address' => 'Domisili sekarang', 'rt' => '01']);
        $admin = User::findOrFail(2);
        $admin->forceFill(['role' => 'admin_desa', 'region_id' => 7])->save();
        $this->actingAs($admin);

        return $kyc;
    }

    private function decide(KycVerification $kyc, bool $approved = true): bool
    {
        return app(IdentityReviewService::class)->reviewKyc($kyc->id, Auth::user(), $approved, 'Hasil pemeriksaan');
    }

    public function test_approval_preserves_domicile_and_removes_files_only_after_success(): void
    {
        $kyc = $this->submission();
        $paths = [$kyc->ktp_image_path, $kyc->face_image_path];
        $this->assertTrue($this->decide($kyc));
        $owner = User::findOrFail(1);
        $this->assertSame('verified', $owner->verification_status);
        $this->assertSame('Nama Terverifikasi', $owner->name);
        $this->assertSame(7, $owner->region_id);
        $this->assertSame('Domisili sekarang', $owner->address);
        $this->assertSame('01', $owner->rt);
        $this->assertNull($kyc->fresh()->ktp_image_path);
        Storage::disk('private')->assertMissing($paths);
        $this->assertSame(1, DB::table('notifications')->count());
        $this->deny(fn () => $this->decide($kyc, false), 409);
        $this->assertSame('verified', $owner->fresh()->verification_status);
        $this->assertSame(1, DB::table('notifications')->count());
    }

    public function test_rejection_is_visible_to_the_citizen_and_cannot_be_approved_again(): void
    {
        $kyc = $this->submission();
        $this->assertTrue($this->decide($kyc, false));
        $this->assertSame('rejected', User::find(1)->verification_status);
        $this->assertNull(User::find(1)->verified_at);
        $this->assertSame('rejected', $kyc->fresh()->status);
        $this->deny(fn () => $this->decide($kyc), 409);
    }

    public function test_incomplete_corrupt_or_swapped_documents_cannot_be_approved(): void
    {
        $kyc = $this->submission();
        $original = Storage::disk('private')->get($kyc->face_image_path);
        foreach ([null, 'broken', Storage::disk('private')->get($kyc->ktp_image_path)] as $bytes) {
            if ($bytes === null) {
                Storage::disk('private')->delete($kyc->face_image_path);
            } else {
                Storage::disk('private')->put($kyc->face_image_path, $bytes);
            }
            try {
                $this->decide($kyc);
                $this->fail('Invalid documents accepted');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('admin_notes', $e->errors());
            }
            $this->assertSame('pending', $kyc->fresh()->status);
            $this->assertSame('pending', User::find(1)->verification_status);
        }
        Storage::disk('private')->put($kyc->face_image_path, $original);
        $this->assertTrue($this->decide($kyc));
    }

    public function test_draft_and_older_submission_cannot_be_reviewed(): void
    {
        $kyc = $this->submission();
        $kyc->update(['status' => 'draft']);
        $this->deny(fn () => $this->decide($kyc), 409);
        $kyc->update(['status' => 'pending']);
        KycVerification::create(['user_id' => 1, 'status' => 'draft']);
        $this->deny(fn () => $this->decide($kyc), 409);
        Storage::disk('private')->assertExists($kyc->ktp_image_path);
    }

    public function test_review_checks_all_roles_and_current_region_again(): void
    {
        $kyc = $this->submission();
        $admin = Auth::user();
        foreach (['user', 'staff', 'admin_rt', 'admin_rw', 'super_admin'] as $role) {
            $admin->role = $role;
            $this->deny(fn () => $this->decide($kyc), 403);
        }
        $admin->role = 'admin_desa';
        DB::table('users')->where('id', 1)->update(['region_id' => 8]);
        $this->deny(fn () => $this->decide($kyc), 403);
    }

    public function test_invalid_or_already_verified_nik_is_rejected(): void
    {
        $kyc = $this->submission();
        User::find(2)->update(['nik' => '0000000000000001', 'verification_status' => 'verified']);
        foreach (['0000000000000001', 'short'] as $nik) {
            $kyc->update(['nik_from_ocr' => $nik]);
            try {
                $this->decide($kyc);
                $this->fail('Invalid identity accepted');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('admin_notes', $e->errors());
            }
            $this->assertSame('pending', $kyc->fresh()->status);
        }
    }

    public function test_database_failure_does_not_delete_photos_or_change_the_decision(): void
    {
        $kyc = $this->submission();
        KycVerification::updating(function ($record) {
            if ($record->status === 'approved') {
                throw new \RuntimeException('Simulated write failure');
            }
        });
        try {
            $this->decide($kyc);
            $this->fail('Expected database failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated write failure', $e->getMessage());
        } finally {
            KycVerification::flushEventListeners();
        }
        $this->assertSame('pending', $kyc->fresh()->status);
        $this->assertSame('pending', User::find(1)->verification_status);
        Storage::disk('private')->assertExists([$kyc->ktp_image_path, $kyc->face_image_path]);
        $this->assertSame(0, DB::table('notifications')->count());
    }

    public function test_cleanup_failure_retains_reference_without_reopening_decision(): void
    {
        $kyc = $this->submission();
        $realDisk = Storage::disk('private');
        $disk = \Mockery::mock($realDisk);
        $disk->shouldReceive('delete')->with($kyc->ktp_image_path)->once()->andReturn(false);
        $disk->shouldReceive('delete')->with($kyc->face_image_path)->once()
            ->andReturnUsing(fn ($path) => $realDisk->delete($path));
        Storage::shouldReceive('disk')->with('private')->andReturn($disk);
        $this->assertFalse($this->decide($kyc));
        $this->assertSame('approved', $kyc->fresh()->status);
        $this->assertNotNull($kyc->fresh()->ktp_image_path);
        $this->assertNull($kyc->fresh()->face_image_path);
    }

    public function test_legacy_review_cannot_bypass_active_kyc_or_revoke_verified_account(): void
    {
        $kyc = $this->submission();
        $this->deny(fn () => app(IdentityReviewService::class)->reviewLegacy(1, Auth::user(), true, null), 409);
        $this->decide($kyc);
        $this->deny(fn () => app(IdentityReviewService::class)->reviewLegacy(1, Auth::user(), false, 'Stale form'), 409);
        $this->assertSame('verified', User::find(1)->verification_status);
    }

    public function test_http_review_validation_is_visible_and_repeat_decisions_return_conflict(): void
    {
        $kyc = $this->submission();
        Storage::disk('private')->delete($kyc->face_image_path);
        $this->postJson(route('admin.kyc.approve', $kyc->id))->assertUnprocessable()->assertJsonValidationErrors('admin_notes');
        $this->post(route('admin.kyc.reject', $kyc->id), ['admin_notes' => 'Lengkapi foto wajah'])->assertRedirect(route('admin.kyc.index'));
        $this->postJson(route('admin.kyc.approve', $kyc->id))->assertConflict();
    }

    public function test_api_rejects_malformed_camera_data_and_missing_face_before_submission(): void
    {
        $kyc = $this->pending();
        $this->actingAs(User::findOrFail(1));
        foreach (['true', '"text"', '{broken'] as $data) {
            $this->postJson('/api/kyc/submit', ['kyc_id' => $kyc->id, 'face_data' => $data])
                ->assertUnprocessable()->assertJsonValidationErrors('face_data');
        }
        $kyc->update(['status' => 'draft', 'face_image_path' => null]);
        $this->postJson('/api/kyc/submit', ['kyc_id' => $kyc->id, 'face_data' => ['test' => true]])
            ->assertUnprocessable()->assertJsonValidationErrors('face_image');
        $this->postJson(route('kyc.submit'), ['kyc_id' => $kyc->id])
            ->assertUnprocessable()->assertJsonValidationErrors('face_image');
        $this->assertSame('draft', $kyc->fresh()->status);
    }

    public function test_submission_is_atomic_and_a_second_submit_cannot_duplicate_notifications(): void
    {
        $kyc = $this->pending();
        $kyc->update(['status' => 'draft']);
        $this->postJson('/api/kyc/submit', ['kyc_id' => $kyc->id, 'face_data' => ['camera' => true]])->assertOk();
        $this->assertSame('pending', User::find(1)->verification_status);
        $this->assertSame(1, DB::table('admin_notifications')->count());
        $this->postJson('/api/kyc/submit', ['kyc_id' => $kyc->id, 'face_data' => ['camera' => true]])->assertUnprocessable();
        $this->assertSame(1, DB::table('admin_notifications')->count());
    }

    public function test_failed_notification_keeps_submission_and_account_unsubmitted(): void
    {
        $kyc = $this->pending();
        $kyc->update(['status' => 'draft']);
        AdminNotification::creating(fn () => throw new \RuntimeException('Notification write failed'));
        try {
            app(IdentityDocumentStorage::class)->save($kyc, [], ['status' => 'pending']);
            $this->fail('Expected write failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Notification write failed', $e->getMessage());
        } finally {
            AdminNotification::flushEventListeners();
        }
        $this->assertSame('draft', $kyc->fresh()->status);
        $this->assertSame('unverified', User::find(1)->verification_status);
        Storage::disk('private')->assertExists($kyc->ktp_image_path);
    }

    public function test_verified_account_cannot_reopen_identity_through_either_upload_flow(): void
    {
        $kyc = $this->submission();
        $this->decide($kyc);
        $owner = User::findOrFail(1);
        $storage = app(IdentityDocumentStorage::class);
        foreach ([fn () => $storage->createDraft($owner, [], $this->image()),
            fn () => $storage->save($owner, ['ktp' => $this->image()], ['verification_status' => 'pending'])] as $action) {
            try {
                $action();
                $this->fail('Verified account reopened');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        $this->assertSame('verified', $owner->fresh()->verification_status);
        $this->assertCount(0, Storage::disk('private')->allFiles());
    }

    public function test_legacy_approval_also_cleans_up_both_photos(): void
    {
        $owner = User::findOrFail(1);
        app(IdentityDocumentStorage::class)->save($owner,
            ['ktp' => $this->image(), 'face' => $this->image()], ['verification_status' => 'pending']);
        $admin = User::find(2);
        $admin->role = 'admin_desa';
        $admin->region_id = 7;
        $this->assertTrue(app(IdentityReviewService::class)->reviewLegacy(1, $admin, true, null));
        $this->assertSame('verified', $owner->fresh()->verification_status);
        $this->assertNull($owner->fresh()->ktp_photo_path);
        $this->assertNull($owner->fresh()->face_photo_path);
        $this->assertCount(0, Storage::disk('private')->allFiles());
    }

    public function test_review_page_renders_legacy_camera_data_and_hides_finalized_document_links(): void
    {
        $kyc = $this->submission();
        foreach (['2025_12_02_151523_create_files_table.php', '2026_06_15_182007_create_services_table.php',
            '2026_06_15_182008_create_region_services_table.php', '2026_06_22_151200_add_is_exclusive_to_region_services_table.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        view()->share('errors', new ViewErrorBag);
        $kyc->face_scan_data = 'legacy-invalid-camera-value';
        $html = view('admin.kyc.show', compact('kyc'))->render();
        $this->assertStringContainsString(route('admin.kyc.approve', $kyc->id), $html);
        $this->assertStringNotContainsString('Liveness Detection Valid', $html);
        $this->assertStringNotContainsString('zero footprint', $html);
        $kyc->status = 'approved';
        $html = view('admin.kyc.show', compact('kyc'))->render();
        $this->assertStringContainsString('Pembersihan berkas belum selesai', $html);
        $this->assertStringNotContainsString(route('media.secure.ktp', basename($kyc->ktp_image_path)), $html);
        $this->assertStringNotContainsString('id="form-approve"', $html);
    }
}
