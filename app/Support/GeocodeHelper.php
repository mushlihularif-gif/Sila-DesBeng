<?php

namespace App\Support;

class GeocodeHelper
{
    /**
     * Lakukan reverse geocode koordinat lat & lng menjadi nama alamat/lokasi.
     * Menggunakan multi-tier fallback (Google Maps -> OpenStreetMap Nominatim -> BigDataCloud).
     */
    public static function reverse($lat, $lng): ?string
    {
        if (empty($lat) || empty($lng)) {
            return null;
        }

        // Tier 1: Coba Google Geocoding API jika API key tersedia di sistem
        $googleKey = null;
        try {
            $googleKey = function_exists('config') ? config('services.google_maps.api_key') : null;
        } catch (\Throwable $e) {
            $googleKey = null;
        }
        if (!empty($googleKey)) {
            try {
                $googleUrl = 'https://maps.googleapis.com/maps/api/geocode/json?' . http_build_query([
                    'latlng' => "{$lat},{$lng}",
                    'language' => 'id',
                    'key' => $googleKey,
                ]);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $googleUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $res = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200 && !empty($res)) {
                    $json = json_decode($res, true);
                    if (($json['status'] ?? '') === 'OK' && !empty($json['results'][0]['formatted_address'])) {
                        return $json['results'][0]['formatted_address'];
                    }
                }
            } catch (\Throwable $e) {
                // Lanjut ke Tier 2
            }
        }

        // Tier 2: OpenStreetMap Nominatim (dengan IPv4 resolver untuk respon cepat < 1 detik)
        try {
            $url = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SiladesBeng/1.0 (layanan@siladesbeng.bengkaliskab.go.id)');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Accept-Language: id,id-ID;q=0.9,en;q=0.8'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    $displayName = $data['display_name'] ?? null;
                    $addr = $data['address'] ?? [];

                    $parts = [];
                    if (!empty($addr['road'])) {
                        $parts[] = $addr['road'];
                    }
                    if (!empty($addr['suburb'])) {
                        $parts[] = $addr['suburb'];
                    }
                    if (!empty($addr['village'])) {
                        $parts[] = 'Desa ' . $addr['village'];
                    } elseif (!empty($addr['town'])) {
                        $parts[] = $addr['town'];
                    } elseif (!empty($addr['city_district'])) {
                        $parts[] = 'Kec. ' . $addr['city_district'];
                    }
                    if (!empty($addr['county'])) {
                        $parts[] = $addr['county'];
                    }

                    if (!empty($parts)) {
                        return implode(', ', $parts);
                    }

                    if (!empty($displayName)) {
                        return $displayName;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Lanjut ke Tier 3
        }

        // Tier 3: BigDataCloud Reverse Geocode Client API
        try {
            $bdcUrl = "https://api.bigdatacloud.net/data/reverse-geocode-client?" . http_build_query([
                'latitude' => $lat,
                'longitude' => $lng,
                'localityLanguage' => 'id'
            ]);

            $ch = curl_init($bdcUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $bdcRes = curl_exec($ch);
            $bdcCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($bdcCode === 200 && !empty($bdcRes)) {
                $bdcData = json_decode($bdcRes, true);
                if (is_array($bdcData)) {
                    $locality = $bdcData['locality'] ?? '';
                    $city = $bdcData['city'] ?? '';
                    $parts = array_filter([$locality, $city]);
                    if (!empty($parts)) {
                        return implode(', ', array_unique($parts));
                    }
                }
            }
        } catch (\Throwable $e) {
            // Selesai
        }

        return null;
    }

    /**
     * Hasilkan static map Base64 berresolusi tinggi dengan marker pin
     * untuk disematkan pada dokumen cetak PDF.
     */
    public static function getStaticMapBase64($lat, $lng): ?string
    {
        if (empty($lat) || empty($lng)) {
            return null;
        }

        try {
            $url = "https://maps.wikimedia.org/img/osm-intl,15,{$lat},{$lng},600x300.png";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SiladesBeng/1.0 (layanan@siladesbeng.bengkaliskab.go.id)');
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $raw = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code !== 200 || empty($raw)) {
                return null;
            }

            // Jika extension GD aktif, gambar pin penanda merah di titik tengah peta
            if (function_exists('imagecreatefromstring')) {
                $img = @imagecreatefromstring($raw);
                if ($img) {
                    $w = imagesx($img);
                    $h = imagesy($img);
                    $centerX = (int)($w / 2);
                    $centerY = (int)($h / 2);

                    $red = imagecolorallocate($img, 220, 38, 38);
                    $darkRed = imagecolorallocate($img, 153, 27, 27);
                    $white = imagecolorallocate($img, 255, 255, 255);

                    // Kepala Pin (Lingkaran)
                    imagefilledellipse($img, $centerX, $centerY - 14, 20, 20, $red);
                    imageellipse($img, $centerX, $centerY - 14, 20, 20, $darkRed);
                    imagefilledellipse($img, $centerX, $centerY - 14, 8, 8, $white);

                    // Ujung Pin (Segitiga menunjuk tepat ke koordinat)
                    $points = [
                        $centerX - 6, $centerY - 7,
                        $centerX + 6, $centerY - 7,
                        $centerX,     $centerY
                    ];
                    imagefilledpolygon($img, $points, $red);
                    imagepolygon($img, $points, $darkRed);

                    ob_start();
                    imagepng($img);
                    $output = ob_get_clean();
                    imagedestroy($img);

                    return base64_encode($output);
                }
            }

            return base64_encode($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

