<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create("/", "POST", [], [], ["profile" => ["name" => "test.jpg", "type" => "", "tmp_name" => "", "error" => 1, "size" => 0]]);
$validator = Illuminate\Support\Facades\Validator::make($req->all(), [
    'profile' => 'nullable|image|max:8192'
]);
var_dump($validator->fails());
var_dump($validator->errors()->all());
