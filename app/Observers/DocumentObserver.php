<?php

namespace App\Observers;

use App\Models\Document;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\Auth;

class DocumentObserver
{
    public function creating(Document $document): void
    {
        if (empty($document->created_by) && Auth::check()) {
            $document->created_by = Auth::id();
        }

        if (empty($document->document_number)) {
            $document->document_number = DocumentNumber::nextFor($document);
        }
    }
}
