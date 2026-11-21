<?php

namespace Tests\Feature;

use App\Models\Region;
use App\Models\User;
use App\Support\AssistantKnowledge;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatbotAssistantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'ddos.whitelist' => ['127.0.0.1'],
            'services.gemini.api_key' => 'test-gemini-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('regions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type')->nullable();
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->timestamps();
        });

        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->nullable();
            $t->timestamps();
        });

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('uuid')->nullable();
            $t->string('name');
            $t->string('name_hash')->nullable();
            $t->string('username')->nullable();
            $t->string('email')->unique();
            $t->string('password');
            $t->string('nik')->nullable();
            $t->string('nik_hash')->nullable();
            $t->string('phone')->nullable();
            $t->string('phone_hash')->nullable();
            $t->string('role')->default('user');
            $t->string('status')->default('aktif');
            $t->unsignedBigInteger('region_id')->nullable();
            $t->string('verification_status')->default('verified');
            $t->timestamps();
        });

        $kec = Region::create(['name' => 'Kecamatan Bengkalis', 'type' => 'kecamatan']);
        $desa = Region::create(['name' => 'Desa Pematang Duku Timur', 'type' => 'desa', 'parent_id' => $kec->id]);
        Region::create(['name' => 'Desa Wonosari', 'type' => 'desa', 'parent_id' => $kec->id]);

        DB::table('services')->insert([
            ['name' => 'Penjualan Gas', 'slug' => 'penjualan-gas'],
            ['name' => 'Penyewaan Alat', 'slug' => 'penyewaan-alat'],
        ]);

        $user = new User();
        $user->uuid = Str::uuid()->toString();
        $user->name = 'Warga Uji';
        $user->username = 'wargauji';
        $user->email = 'rahasia@example.test';
        $user->password = Hash::make('RahasiaKuat123!');
        $user->nik = '1403010101010001';
        $user->phone = '081234567890';
        $user->role = 'user';
        $user->status = 'aktif';
        $user->region_id = $desa->id;
        $user->verification_status = 'verified';
        $user->save();
    }

    public function test_system_instruction_contains_accurate_system_flows_kmipn_fact_and_no_pii(): void
    {
        $user = User::firstOrFail();
        $instruction = AssistantKnowledge::buildSystemInstruction($user);

        // Fakta lomba KMIPN Sulawesi 2026 Juara 3 & tim pengembang
        $this->assertStringContainsString('Kompetisi Mahasiswa Informatika Politeknik Nasional (KMIPN)', $instruction);
        $this->assertStringContainsString('Sulawesi', $instruction);
        $this->assertStringContainsString('2026', $instruction);
        $this->assertStringContainsString('Juara 3', $instruction);
        $this->assertStringContainsString('Mushlihul Arif', $instruction);

        // Konteks dinamis dari database (wilayah & layanan)
        $this->assertStringContainsString('Desa Pematang Duku Timur', $instruction);
        $this->assertStringContainsString('Desa Wonosari', $instruction);
        $this->assertStringContainsString('Penjualan Gas', $instruction);

        // Panduan alur nyata sistem (pesan, chat petugas, notifikasi, berita/staf, kemampuan LLM umum)
        $this->assertStringContainsString('Chat Petugas Layanan', $instruction);
        $this->assertStringContainsString('/notifikasi', $instruction);
        $this->assertStringContainsString('Kabar & Informasi Daerah', $instruction);
        $this->assertStringContainsString('/kelola-kabar', $instruction);
        $this->assertStringContainsString('DILARANG menyebut \'Midtrans\'', $instruction);
        $this->assertStringContainsString('PERTANYAAN UMUM DI LUAR SISTEM SEGIDAERAH', $instruction);

        // Privasi: NIK, email, telepon tidak pernah masuk ke prompt Gemini
        $this->assertStringNotContainsString('1403010101010001', $instruction);
        $this->assertStringNotContainsString('rahasia@example.test', $instruction);
        $this->assertStringNotContainsString('081234567890', $instruction);
    }

    public function test_chatbot_endpoint_sends_grounding_prompt_and_alternating_history_to_gemini(): void
    {
        $capturedPayload = null;
        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();
                return Http::response([
                    'candidates' => [[
                        'content' => [
                            'parts' => [[
                                'text' => 'Tim Gen Hello World pernah mengikuti KMIPN di Sulawesi tahun 2026 dan meraih Juara 3.',
                            ]],
                        ],
                    ]],
                ], 200);
            },
        ]);

        $res = $this->postJson('/chatbot/ask', [
            'message' => 'lomba apa yang diikuti pengembangnya?',
            'history' => [
                ['role' => 'model', 'text' => 'Halo!'], // model di awal harus dibuang agar Gemini valid
                ['role' => 'user', 'text' => 'siapa pembuatnya?'],
                ['role' => 'model', 'text' => 'Tim Gen Hello World.'],
                ['role' => 'user', 'text' => 'lomba apa yang diikuti pengembangnya?'],
            ],
        ]);

        $res->assertOk()
            ->assertJsonPath('reply', 'Tim Gen Hello World pernah mengikuti KMIPN di Sulawesi tahun 2026 dan meraih Juara 3.');

        $this->assertNotNull($capturedPayload);
        $this->assertSame('user', $capturedPayload['contents'][0]['role']);
        $this->assertSame('model', $capturedPayload['contents'][1]['role']);
        $this->assertSame('user', $capturedPayload['contents'][2]['role']);
        $this->assertStringContainsString('KMIPN', $capturedPayload['system_instruction']['parts'][0]['text']);
        $this->assertSame(0.35, $capturedPayload['generationConfig']['temperature']);
    }

    public function test_static_fallback_is_accurate_and_has_no_mojibake(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'unavailable'], 503),
        ]);

        $res = $this->postJson('/chatbot/ask', [
            'message' => 'lomba apa yang pernah diikuti tim pengembang?',
        ]);

        $res->assertOk();
        $reply = (string) $res->json('reply');
        $this->assertStringContainsString('KMIPN', $reply);
        $this->assertStringContainsString('Sulawesi', $reply);
        $this->assertStringContainsString('2026', $reply);
        $this->assertStringContainsString('Juara 3', $reply);
        $this->assertStringNotContainsString('ðŸ', $reply);
    }
}
