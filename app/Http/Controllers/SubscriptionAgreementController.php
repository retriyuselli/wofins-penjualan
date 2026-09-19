<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\SubscriptionAgreementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SubscriptionAgreementController extends Controller
{
    public function stream(Company $company, SubscriptionAgreementService $agreements): Response
    {
        Gate::authorize('view', $company);

        @ini_set('max_execution_time', '120');
        @ini_set('memory_limit', '256M');

        $data = $agreements->viewData($company);
        $pdf = Pdf::loadView('pdf.perjanjian_berlangganan', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf->stream($agreements->filename($company));
    }
}
