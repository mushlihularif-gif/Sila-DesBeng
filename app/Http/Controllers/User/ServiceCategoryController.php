<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function show(Request $request, string $category): View
    {
        $definitions = [
            'belanja-kebutuhan' => [
                'title' => 'Belanja & Kebutuhan',
                'description' => 'Pilih kebutuhan gas rumah tangga atau jelajahi produk lokal dari Pasar Daerah.',
                'image' => 'Admin/img/menu3dberanda/belanja-kebutuhan.png',
                'items' => [
                    [
                        'title' => 'Gas Daerah',
                        'description' => 'Pesan gas LPG dari unit penjualan di wilayah Anda.',
                        'image' => 'User/img/elemen/F2.png',
                        'route' => 'gas.sales',
                        'services' => ['Penjualan Gas'],
                    ],
                    [
                        'title' => 'Pasar Daerah',
                        'description' => 'Jelajahi dan beli produk unggulan dari pelaku usaha daerah.',
                        'image' => 'Admin/img/menu3dberanda/pasar-daerah.webp',
                        'route' => 'pasar.index',
                        'public' => true,
                    ],
                ],
            ],
            'layanan-daerah' => [
                'title' => 'Layanan Daerah',
                'description' => 'Pilih layanan daerah yang Anda butuhkan.',
                'image' => 'Admin/img/menu3dberanda/layanan-daerah.webp',
                'items' => [
                    [
                        'title' => 'Penyewaan Alat',
                        'description' => 'Sewa perlengkapan acara dan kebutuhan lainnya.',
                        'image' => 'User/img/elemen/F1.png',
                        'route' => 'rental.equipment',
                        'services' => ['Penyewaan Alat'],
                    ],
                    [
                        'title' => 'Penyewaan Transportasi',
                        'description' => 'Cari kendaraan untuk keperluan perjalanan atau angkutan.',
                        'image' => 'User/img/elemen/mobil.png',
                        'route' => 'mobil.rental.equipment',
                        'services' => ['Penyewaan Mobil', 'Penyewaan Transportasi'],
                    ],
                    [
                        'title' => 'Fasilitas Umum',
                        'description' => 'Lihat dan ajukan peminjaman fasilitas umum.',
                        'image' => 'User/img/elemen/fasilitas.png',
                        'route' => 'user.fasilitas-umum.equipment',
                        'services' => ['Fasilitas Umum', 'Peminjaman Fasilitas Umum'],
                    ],
                    [
                        'title' => 'Pelaporan Warga',
                        'description' => 'Sampaikan laporan mengenai kondisi lingkungan sekitar.',
                        'image' => 'User/img/elemen/lapor.png',
                        'route' => 'pelaporan.landing',
                        'services' => ['Pelaporan Warga'],
                    ],
                ],
            ],
        ];

        abort_unless(isset($definitions[$category]), 404);

        $regionId = auth()->user()?->region_id ?: $request->integer('region_id');
        $region = $regionId
            ? Region::with(['services' => fn ($query) => $query->wherePivot('is_active', true)])->find($regionId)
            : null;
        $activeServices = $region?->services->pluck('name')->all();
        $definition = $definitions[$category];

        $items = collect($definition['items'])
            ->filter(function (array $item) use ($region, $activeServices) {
                if (($item['public'] ?? false) || !$region || empty($item['services'])) {
                    return true;
                }

                return count(array_intersect($item['services'], $activeServices ?? [])) > 0;
            })
            ->map(function (array $item) use ($regionId) {
                $routeName = $item['route'];
                $url = route($routeName);

                if ($regionId) {
                    $url .= '?' . http_build_query(['region_id' => $regionId]);
                } elseif (!($item['public'] ?? false)) {
                    $url = route('bumdes.profil', ['redirect' => $routeName]);
                }

                $item['url'] = $url;

                return $item;
            })
            ->values();

        return view('users.service-category', [
            'category' => $definition,
            'items' => $items,
            'region' => $region,
        ]);
    }
}
