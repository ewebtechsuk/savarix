<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts for the agent app.
     */
    public function index()
    {
        return view('agent.contacts.index');
    }
}
