<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class MasterController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('master/Index');
    }
}
