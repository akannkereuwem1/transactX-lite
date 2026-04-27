<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show()
    {
        return response()->json(['message' => 'Coming in Module 5.']);
    }

    public function transactions()
    {
        return response()->json(['message' => 'Coming in Module 5.']);
    }
}
