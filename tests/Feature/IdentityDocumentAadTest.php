<?php

namespace Tests\Feature;

use App\Http\Controllers\{KycController, MediaController};
use App\Http\Controllers\Api\KycController as ApiKycController;
use App\Models\{KycVerification, User};
use App\Services\{FileEncryptionService, IdentityDocumentContext, IdentityDocumentStorage, NotificationDeliveryService, OcrService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, DB, Http, Schema, Storage};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class IdentityDocumentAadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
        Http::preventStrayRequests();
        Storage::fake('private');
        $this->mock(NotificationDeliveryService::class)->shouldReceive('enqueue', 'enqueueAdmin')->andReturnNull();
        Schema::create('regions', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('type'); $t->integer('parent_id')->nullable();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            foreach (['name', 'name_hash', 'nik', 'nik_hash', 'phone', 'phone_hash', 'address', 'uuid', 'email', 'password', 'ktp_photo_path', 'face_photo_path', 'ktp_rejection_reason', 'gender', 'rt', 'rw'] as $col) $t->text($col)->nullable();
            $t->string('role')->default('user'); $t->string('status')->default('aktif');
            $t->string('verification_status')->default('unverified'); $t->integer('region_id')->nullable();
            $t->timestamp('verified_at')->nullable(); $t->timestamps();
        });
        Schema::create('kyc_verifications', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->string('status')->default('draft');
            foreach (['ktp_image_path','face_image_path','nik_from_ocr','name_from_ocr','address_from_ocr','rt_from_ocr','rw_from_ocr','kecamatan_from_ocr','desa_from_ocr','gender_from_ocr','nik_from_ocr_hash','name_from_ocr_hash','admin_notes'] as $col) $t->text($col)->nullable();
            $t->json('face_scan_data')->nullable(); $t->integer('reviewed_by')->nullable();
            $t->timestamp('reviewed_at')->nullable(); $t->timestamps();
        });
        // Production has already expanded the status enum to include draft.
        (require database_path('migrations/2026_11_16_000001_add_identity_document_encryption_versions.php'))->up();
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id(); $t->string('type'); $t->integer('reference_id'); $t->integer('region_id')->nullable();
            $t->string('title'); $t->text('message'); $t->boolean('is_read'); $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id(); $t->integer('user_id');
            foreach (['type','title','message','link','icon'] as $col) $t->text($col)->nullable();
            $t->boolean('is_read'); $t->timestamp('sent_at')->nullable(); $t->timestamps();
        });
        DB::table('regions')->insert([
            ['id'=>7, 'name'=>'Desa Uji', 'type'=>'desa'], ['id'=>8, 'name'=>'Desa Lain', 'type'=>'desa'],
        ]);
        DB::table('users')->insert([
            ['id'=>1, 'name'=>'Pemilik Sintetis', 'region_id'=>7], ['id'=>2, 'name'=>'Warga Lain', 'region_id'=>8],
        ]);
        Auth::setUser(User::findOrFail(1));
    }

    private function request(array $data = [], array $files = []): Request
    {
        $request = Request::create('/test', 'POST', $data, [], $files);
        $request->setUserResolver(fn () => Auth::user());
        return $request;
    }

    private function image(): string
    {
        return UploadedFile::fake()->image('synthetic.jpg', 40, 40)->get();
    }

    private function pending(): KycVerification
    {
        $kyc = KycVerification::create(['user_id'=>1, 'status'=>'pending']);
        app(IdentityDocumentStorage::class)->save($kyc, ['ktp'=>$this->image(), 'face'=>$this->image()]);
        return $kyc;
    }

    private function deny(callable $action, int $code): void
    {
        try { $action(); $this->fail('Akses seharusnya ditolak.'); }
        catch (HttpExceptionInterface $e) { $this->assertSame($code, $e->getStatusCode()); }
    }

    public function test_web_and_api_uploads_bind_ktp_and_face_to_server_context(): void
    {
        $this->mock(OcrService::class)->shouldReceive('extractKtpData')->twice()
            ->andReturn(['nik'=>'0000000000000001', 'name'=>'DATA UJI', 'address'=>'ALAMAT FIKTIF']);
        foreach ([KycController::class, ApiKycController::class] as $controllerClass) {
            $controller = app($controllerClass);
            $response = $controller->process($this->request(['user_id'=>2, 'ktp_encryption_version'=>1], ['ktp_image'=>UploadedFile::fake()->image('ktp.jpg')]));
            $this->assertSame(200, $response->getStatusCode());
            $kyc = KycVerification::latest('id')->firstOrFail();
            $this->assertSame(1, (int) $kyc->user_id);
            $this->assertSame(2, (int) $kyc->ktp_encryption_version);
            $face = $this->image();
            $data = ['kyc_id'=>$kyc->id, 'face_data'=>['test'=>true], 'face_encryption_version'=>1];
            if ($controllerClass === KycController::class) {
                $data['face_image'] = 'data:image/jpeg;base64,'.base64_encode($face);
                $request = $this->request($data);
            } else {
                $request = $this->request($data, ['face_image'=>UploadedFile::fake()->image('face.jpg')]);
            }
            $this->assertSame(200, $controller->submit($request)->getStatusCode());
            $kyc->refresh();
            $this->assertSame('pending', $kyc->status);
            foreach (['ktp','face'] as $type) {
                $column = $type.'_image_path';
                $this->assertSame(2, (int) $kyc->{$type.'_encryption_version'});
                $bytes = Storage::disk('private')->get($kyc->$column);
                $this->assertStringStartsWith('$chacha20:v2$', $bytes);
                $plain = FileEncryptionService::decrypt($bytes, IdentityDocumentContext::aad($kyc,$type,$kyc->$column), 2);
                $this->assertNotFalse(getimagesizefromstring($plain));
                $method = $type === 'ktp' ? 'secureKtpImage' : 'secureFaceImage';
                $this->assertSame(200, app(MediaController::class)->$method(basename($kyc->$column))->getStatusCode());
            }
        }
    }

    public function test_ocr_failure_leaves_no_new_file_or_draft(): void
    {
        $this->mock(OcrService::class)->shouldReceive('extractKtpData')->twice()->andReturn([]);
        foreach ([KycController::class, ApiKycController::class] as $controller) {
            $this->assertSame(400, app($controller)->process($this->request([], ['ktp_image'=>UploadedFile::fake()->image('test.jpg')]))->getStatusCode());
        }
        $this->assertCount(0, Storage::disk('private')->allFiles());
        $this->assertSame(0, KycVerification::count());
    }

    public function test_file_swaps_tampering_and_legacy_downgrade_are_rejected_by_media_endpoint(): void
    {
        $first = $this->pending();
        $second = $this->pending();
        $original = Storage::disk('private')->get($first->ktp_image_path);
        $replacements = [
            Storage::disk('private')->get($second->ktp_image_path),
            Storage::disk('private')->get($first->face_image_path),
            FileEncryptionService::encrypt($this->image()),
            $this->image(),
            substr($original, 0, -20),
        ];
        foreach ($replacements as $replacement) {
            Storage::disk('private')->put($first->ktp_image_path, $replacement);
            $this->deny(fn () => app(MediaController::class)->secureKtpImage(basename($first->ktp_image_path)), 404);
        }
        Storage::disk('private')->put($first->ktp_image_path, $original);
        $this->assertSame(200, app(MediaController::class)->secureKtpImage(basename($first->ktp_image_path))->getStatusCode());
    }

    public function test_authorization_is_still_required_before_decryption(): void
    {
        $kyc = $this->pending();
        $view = fn () => app(MediaController::class)->secureKtpImage(basename($kyc->ktp_image_path));
        Auth::setUser(User::find(2));
        $this->deny($view, 403);
        $admin = User::find(2); $admin->role='admin_desa'; Auth::setUser($admin);
        $this->deny($view, 403);
        $admin->region_id=7;
        $response = $view();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        foreach (['staff','admin_rt','admin_rw','super_admin'] as $role) {
            $admin->role=$role; $this->deny($view, 403);
        }
    }

    public function test_old_ktp_remains_readable_when_new_face_is_uploaded(): void
    {
        $kyc = KycVerification::create(['user_id'=>1, 'status'=>'pending', 'ktp_image_path'=>'kyc/ktp/old.enc']);
        Storage::disk('private')->put($kyc->ktp_image_path, FileEncryptionService::encrypt($this->image()));
        app(IdentityDocumentStorage::class)->save($kyc, ['face'=>$this->image()]);
        $this->assertSame(1, (int) $kyc->ktp_encryption_version);
        $this->assertSame(2, (int) $kyc->face_encryption_version);
        $this->assertSame(200, app(MediaController::class)->secureKtpImage('old.enc')->getStatusCode());
        $this->assertSame(200, app(MediaController::class)->secureFaceImage(basename($kyc->face_image_path))->getStatusCode());
    }

    public function test_legacy_user_route_also_writes_bound_documents(): void
    {
        $request = $this->request([], ['ktp_photo'=>UploadedFile::fake()->image('ktp.jpg'), 'face_photo'=>UploadedFile::fake()->image('face.jpg')]);
        app(\App\Http\Controllers\User\VerificationController::class)->store($request);
        $owner = User::find(1);
        $this->assertSame(2, (int) $owner->ktp_photo_encryption_version);
        $this->assertSame(2, (int) $owner->face_photo_encryption_version);
        $this->assertSame(200, app(MediaController::class)->secureKtpImage(basename($owner->ktp_photo_path))->getStatusCode());
        $admin = User::find(2); $admin->role='admin_desa'; $admin->region_id=7; Auth::setUser($admin);
        $this->assertSame(200, app(\App\Http\Controllers\Admin\UserVerificationController::class)->viewImage('ktp',1)->getStatusCode());
    }

    public function test_database_failure_preserves_previous_document_and_removes_new_file(): void
    {
        $kyc = $this->pending();
        $oldPath = $kyc->face_image_path;
        $before = Storage::disk('private')->allFiles();
        try {
            app(IdentityDocumentStorage::class)->save($kyc, ['face'=>$this->image()], ['nonexistent_column'=>'force-db-failure']);
            $this->fail('Expected database failure.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame($oldPath, $kyc->fresh()->face_image_path);
            $this->assertSame($before, Storage::disk('private')->allFiles());
        }
    }

    public function test_storage_failure_preserves_old_version_and_record(): void
    {
        $kyc = $this->pending();
        $oldPath = $kyc->face_image_path;
        $realDisk = Storage::disk('private');
        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('put')->once()->andReturnFalse();
        $disk->shouldReceive('delete')->once()->andReturnTrue();
        Storage::shouldReceive('disk')->with('private')->andReturn($disk);
        try {
            app(IdentityDocumentStorage::class)->save($kyc, ['face'=>$this->image()]);
            $this->fail('Expected failed write.');
        } catch (\RuntimeException $e) {
            $this->assertSame($oldPath, $kyc->fresh()->face_image_path);
            $this->assertTrue($realDisk->exists($oldPath));
        }
    }

    public function test_http_media_route_rejects_other_owner_and_accepts_correct_owner(): void
    {
        $kyc = $this->pending();
        $url = '/media/secure/ktp/'.basename($kyc->ktp_image_path);
        $this->actingAs(User::find(1))->get($url)->assertOk();
        $this->actingAs(User::find(2))->get($url)->assertForbidden();
    }

    public function test_replacement_is_bound_to_new_path_and_finalized_submissions_cannot_be_reopened(): void
    {
        $kyc = $this->pending();
        $oldPath = $kyc->face_image_path;
        $oldBytes = Storage::disk('private')->get($oldPath);
        app(IdentityDocumentStorage::class)->save($kyc, ['face'=>$this->image()]);
        Storage::disk('private')->assertMissing($oldPath);
        Storage::disk('private')->put($kyc->face_image_path, $oldBytes);
        $this->deny(fn () => app(MediaController::class)->secureFaceImage(basename($kyc->face_image_path)), 404);
        $kyc->update(['status'=>'approved']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(IdentityDocumentStorage::class)->save($kyc, ['face'=>$this->image()], ['status'=>'pending']);
    }
}
