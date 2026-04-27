<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function paystack()
    {
        return response()->json(['message' => 'Coming in Module 4.']);
    }
}
