<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::with(['alert', 'triggeredBy']);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        if ($request->filled('trigger_type')) {
            $query->byTriggerType($request->input('trigger_type'));
        }

        $emailLogs = $query->latest()->paginate(20)->withQueryString();

        return view('email-logs.index', compact('emailLogs'));
    }

    public function show(EmailLog $emailLog)
    {
        $emailLog->load(['alert', 'triggeredBy']);

        return view('email-logs.show', compact('emailLog'));
    }
}
