<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function stream(Document $record)
    {
        Gate::authorize('view', $record);

        $record->load(['category', 'recipientsList', 'creator.activeEmployee']);

        $filename = 'document-'.Str::slug($record->document_number ?: $record->title).'.pdf';
        $pdf = Pdf::loadView('documents.pdf', [
            'record' => $record,
            'company' => Company::query()->first(),
        ]);

        return $pdf->stream($filename);
    }
}
