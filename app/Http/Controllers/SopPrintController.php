<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use Illuminate\Http\Request;

class SopPrintController extends Controller
{
    /**
     * Display print-friendly view of SOP
     */
    public function show($id, Request $request)
    {
        $sop = Sop::with(['category', 'creator', 'updater', 'revisions.revisor'])
            ->findOrFail($id);

        \Illuminate\Support\Facades\Gate::authorize('view', $sop);

        $isPrint = $request->has('print');

        return view('sop.print', compact('sop', 'isPrint'));
    }

    /**
     * Generate PDF of SOP (future enhancement)
     */
    public function pdf($id)
    {
        $sop = Sop::with(['category', 'creator', 'updater'])
            ->findOrFail($id);

        \Illuminate\Support\Facades\Gate::authorize('view', $sop);

        // TODO: Implement PDF generation using DomPDF or similar
        // For now, redirect to print view
        return redirect()->route('sop.print', ['id' => $id, 'print' => 1]);
    }
}
