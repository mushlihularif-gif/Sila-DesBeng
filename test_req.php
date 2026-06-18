<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create("/", "POST", [], [], ["profile" => ["name" => "test.jpg", "type" => "", "tmp_name" => "", "error" => 1, "size" => 0]]);
var_dump("HAS:", $req->has("profile"));
var_dump("HAS_FILE:", $req->hasFile("profile"));
var_dump("ALL:", array_keys($req->all()));
var_dump("FILE:", $req->file("profile"));
