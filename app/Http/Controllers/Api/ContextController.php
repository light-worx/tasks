<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskContext;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'owner_email' => 'required|email',
        ]);

        return TaskContext::where('organisation_id', $request->user()->organisation_id)
            ->where('owner_email', $request->owner_email)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}