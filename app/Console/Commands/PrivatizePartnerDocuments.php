<?php

namespace App\Console\Commands;

use App\Models\PartnerApplication;
use App\Services\PartnerDocumentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizePartnerDocuments extends Command
{
    protected $signature = 'kemitraan:protect-documents';
    protected $description = 'Pindahkan dokumen kemitraan lama ke penyimpanan privat setelah memeriksa salinannya.';

    public function handle(PartnerDocumentService $documents): int
    {
        $failed = 0;
        foreach (PartnerApplication::whereNotNull('document_path')->lazyById() as $application) {
            $old = $application->document_path;
            try {
                $new = $documents->privatize($old);
                $application->update(['document_path' => $new]);
                // Also clean up an old public copy when re-running after an interrupted operation.
                $publicPath = substr($new, 8);
                if (Storage::disk('public')->exists($publicPath)
                    && ! Storage::disk('public')->delete($publicPath)) {
                    throw new \RuntimeException('Salinan publik belum dapat dihapus.');
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->error('Dokumen pengajuan #'.$application->id.' belum terlindungi: '.$exception->getMessage());
            }
        }
        $this->info('Pemeriksaan selesai. Dokumen yang perlu ditindaklanjuti: '.$failed);
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
