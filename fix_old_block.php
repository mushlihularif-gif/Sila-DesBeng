<?php
$content = file_get_contents('app/Http/Controllers/User/BerandaController.php');

$pattern = '/\s*\/\/ Handle Search\s*if \(\$search\) \{.*?\/\/ Dapatkan tahun yang dipilih/s';

$newSearchBlock = <<<PHP

        // Handle Search
        if (\$search) {
            \$searchResults = \$this->performGlobalSearch(\$search);
        }

        // Dapatkan tahun yang dipilih
PHP;

$content = preg_replace($pattern, $newSearchBlock, $content);
file_put_contents('app/Http/Controllers/User/BerandaController.php', $content);
echo "Replaced old block successfully.\n";
