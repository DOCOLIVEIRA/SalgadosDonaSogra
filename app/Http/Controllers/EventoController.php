<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class EventoController extends Controller
{
    public function index(): View
    {
        return view('evento');
    }
}
