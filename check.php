<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo \App\Models\Notification::where('type', 'approval_success')->orderByDesc('id')->take(3)->get(['id', 'title', 'user_id', 'type', 'created_at']);
echo "\n";
echo \App\Models\Notification::orderByDesc('id')->take(3)->get(['id', 'title', 'user_id', 'type', 'created_at']);
