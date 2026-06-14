<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IsewaProfileController extends Controller
{
    public function index()
    {
        return view('users.isewa-profile');
    }
}
