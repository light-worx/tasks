<?php

namespace App\Http\Controllers\Pwa;

use Illuminate\Routing\Controller;

class AppController extends Controller
{
    public function home()
    {
        return view('pwa-app.home');
    }
}