<?php
$html = file_get_contents('http://siladesbeng.test/admin/manajemen-pengguna?filter_kecamatan_id=2');
if (strpos($html, 'filter_desa_id') !== false) {
    echo "Found filter_desa_id dropdown\n";
    preg_match('/<select id="filter_desa_id".*?>(.*?)<\/select>/is', $html, $matches);
    if(isset($matches[1])) echo $matches[1];
} else {
    echo "Not found";
}
