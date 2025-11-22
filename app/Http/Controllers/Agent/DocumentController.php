<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents for the agent app.
     */
    public function index()
    {
        return view('agent.documents.index');
    }
}
