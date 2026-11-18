<?php

namespace Tests\Support;

use App\Models\KycVerification;
use App\Models\User;
use App\Services\IdentityDocumentStorage;
use App\Services\NotificationDeliveryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

trait IdentityDatabase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
        Http::preventStrayRequests();
        Storage::fake('private');
        $this->mock(NotificationDeliveryService::class)->shouldReceive('enqueue', 'enqueueAdmin')->andReturnNull();
        Schema::create('regions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type');
            $t->integer('parent_id')->nullable();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            foreach (['name', 'name_hash', 'nik', 'nik_hash', 'phone', 'phone_hash', 'address', 'uuid', 'email', 'password', 'ktp_photo_path', 'face_photo_path', 'ktp_rejection_reason', 'gender', 'rt', 'rw'] as $col) {
                $t->text($col)->nullable();
            }
            $t->string('role')->default('user');
            $t->string('status')->default('aktif');
            $t->string('verification_status')->default('unverified');
            $t->integer('region_id')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->timestamps();
        });
        Schema::create('kyc_verifications', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->string('status')->default('draft');
            foreach (['ktp_image_path', 'face_image_path', 'nik_from_ocr', 'name_from_ocr', 'address_from_ocr', 'rt_from_ocr', 'rw_from_ocr', 'kecamatan_from_ocr', 'desa_from_ocr', 'gender_from_ocr', 'nik_from_ocr_hash', 'name_from_ocr_hash', 'admin_notes'] as $col) {
                $t->text($col)->nullable();
            }
            $t->json('face_scan_data')->nullable();
            $t->integer('reviewed_by')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });
        // Production has already expanded the status enum to include draft.
        (require database_path('migrations/2026_11_16_000001_add_identity_document_encryption_versions.php'))->up();
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id();
            $t->string('type');
            $t->integer('reference_id');
            $t->integer('region_id')->nullable();
            $t->string('title');
            $t->text('message');
            $t->boolean('is_read');
            $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            foreach (['type', 'title', 'message', 'link', 'icon'] as $col) {
                $t->text($col)->nullable();
            }
            $t->boolean('is_read');
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
        });
        DB::table('regions')->insert([
            ['id' => 7, 'name' => 'Desa Uji', 'type' => 'desa'], ['id' => 8, 'name' => 'Desa Lain', 'type' => 'desa'],
        ]);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Pemilik Sintetis', 'region_id' => 7], ['id' => 2, 'name' => 'Warga Lain', 'region_id' => 8],
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
        $kyc = KycVerification::create(['user_id' => 1, 'status' => 'pending']);
        app(IdentityDocumentStorage::class)->save($kyc, ['ktp' => $this->image(), 'face' => $this->image()]);

        return $kyc;
    }

    private function deny(callable $action, int $code): void
    {
        try {
            $action();
            $this->fail('Akses seharusnya ditolak.');
        } catch (HttpExceptionInterface $e) {
            $this->assertSame($code, $e->getStatusCode());
        }
    }
}
