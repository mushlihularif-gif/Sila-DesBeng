<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Laporan;
use Illuminate\Support\Facades\DB;

class EncryptExistingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:encrypt-existing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt existing PII data (phone, address, nama, lokasi) using ChaCha20-Poly1305';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data encryption migration for ChaCha20-Poly1305...');

        // 1. Migrate Users
        $this->info('Encrypting User data (phone, address)...');
        $users = User::all();
        $userCount = 0;

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                // The ChaCha20Encrypted cast will automatically encrypt when we save,
                // but we need to trigger a 'dirty' state.
                // It only encrypts if it's not already encrypted.
                
                // Get raw attributes from database to check if they need encryption
                $rawPhone = $user->getRawOriginal('phone');
                $rawAddress = $user->getRawOriginal('address');
                
                $needsSave = false;
                
                if ($rawPhone && !str_starts_with($rawPhone, '$chacha20$')) {
                    $user->phone = $rawPhone; // Re-assigning triggers the set() method of the cast
                    $needsSave = true;
                }
                
                if ($rawAddress && !str_starts_with($rawAddress, '$chacha20$')) {
                    $user->address = $rawAddress;
                    $needsSave = true;
                }

                if ($needsSave) {
                    $user->save();
                    $userCount++;
                }
            }
            DB::commit();
            $this->info("Successfully encrypted {$userCount} user records.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to encrypt user data: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // 2. Migrate Laporans
        $this->info('Encrypting Laporan data (nama, lokasi)...');
        $laporans = Laporan::all();
        $laporanCount = 0;

        DB::beginTransaction();
        try {
            foreach ($laporans as $laporan) {
                $rawNama = $laporan->getRawOriginal('nama');
                $rawLokasi = $laporan->getRawOriginal('lokasi');
                
                $needsSave = false;
                
                if ($rawNama && !str_starts_with($rawNama, '$chacha20$')) {
                    $laporan->nama = $rawNama;
                    $needsSave = true;
                }
                
                if ($rawLokasi && !str_starts_with($rawLokasi, '$chacha20$')) {
                    $laporan->lokasi = $rawLokasi;
                    $needsSave = true;
                }

                if ($needsSave) {
                    $laporan->save();
                    $laporanCount++;
                }
            }
            DB::commit();
            $this->info("Successfully encrypted {$laporanCount} laporan records.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to encrypt laporan data: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('Encryption migration completed successfully! All existing PII is now secured.');
        
        return Command::SUCCESS;
    }
}
