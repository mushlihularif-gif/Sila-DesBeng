import os
import re

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\dashboard\index.blade.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_chart_logic = """                        $countMap = [
                            'Penyewaan Alat' => ['count' => $rentalCount ?? 0, 'color' => '#ffc107'],
                            'Penjualan Gas' => ['count' => $gasCount ?? 0, 'color' => '#696cff'],
                            'Penyewaan Mobil' => ['count' => $mobilCount ?? 0, 'color' => '#0dcaf0']
                        ];
                        
                        foreach($activeRevenueServices as $serviceName) {
                            $c = $countMap[$serviceName]['count'] ?? 0;
                            $donutSeries[] = $c;
                            $donutLabels[] = $serviceName . " " . $c . " Transaksi";
                            $donutColors[] = $countMap[$serviceName]['color'] ?? '#8592a3';
                            $totalDonut += $c;
                        }
                    @endphp
                    var optionsOrder = {
                        series: {!! json_encode($donutSeries) !!},
                        chart: {
                            type: "donut",
                            width: "100%",
                            height: 220,"""

new_chart_logic = """                        $countMap = [
                            'Penyewaan Alat' => ['count' => $rentalCount ?? 0, 'color' => '#ffc107'],
                            'Penjualan Gas' => ['count' => $gasCount ?? 0, 'color' => '#dc3545'],
                            'Penyewaan Mobil' => ['count' => $mobilCount ?? 0, 'color' => '#0dcaf0'],
                            'Fasilitas Umum' => ['count' => $fasilitasCount ?? 0, 'color' => '#198754'],
                            'Pasar Daerah' => ['count' => $pasarCount ?? 0, 'color' => '#696cff']
                        ];
                        
                        foreach($activeRevenueServices as $serviceName) {
                            $c = $countMap[$serviceName]['count'] ?? 0;
                            $donutSeries[] = $c;
                            $donutLabels[] = $serviceName . " " . $c . " Transaksi";
                            $donutColors[] = $countMap[$serviceName]['color'] ?? '#8592a3';
                            $totalDonut += $c;
                        }
                    @endphp
                    var optionsOrder = {
                        series: {!! json_encode($donutSeries) !!},
                        chart: {
                            type: "donut",
                            width: "100%",
                            height: 320,"""

content = content.replace(old_chart_logic, new_chart_logic)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("index.blade.php updated successfully!")
