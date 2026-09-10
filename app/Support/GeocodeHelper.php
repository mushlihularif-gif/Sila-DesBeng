<?php

namespace App\Support;

class GeocodeHelper
{
    /**
     * Lakukan reverse geocode koordinat lat & lng menjadi nama alamat/lokasi.
     */
    public static function reverse($lat, $lng): ?string
    {
        if (empty($lat) || empty($lng)) {
            return null;
        }

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
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
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
            // Silent fallback
        }

        return null;
    }
}

