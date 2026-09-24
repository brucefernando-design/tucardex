<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($request->action, fn ($q, $a) => $q->where('action', $a))
            ->when($request->type, fn ($q, $t) => $q->where('auditable_type', $t))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $types = AuditLog::select('auditable_type')->distinct()->pluck('auditable_type');

        return view('audit.index', compact('logs', 'types'));
    }
}
