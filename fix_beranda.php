<?php
$lines = file('app/Http/Controllers/User/BerandaController.php');
$newLines = [];
$methods = [];
$inMethods = false;

foreach ($lines as $i => $line) {
    if ($i == 29) { // Line 30 (0-indexed 29) has the stray `    }`
        $inMethods = true;
    }
    if ($i == 233) { // Line 234 has `        // Dapatkan tahun yang dipilih...`
        $inMethods = false;
    }
    
    if ($inMethods) {
        if ($i > 29) {
            $methods[] = $line;
        }
    } else {
        if (trim($line) === '}' && $i > 540) { // The last } of the file
            foreach ($methods as $m) {
                $newLines[] = $m;
            }
        }
        $newLines[] = $line;
    }
}
file_put_contents('app/Http/Controllers/User/BerandaController.php', implode("", $newLines));
