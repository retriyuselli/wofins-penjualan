<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionAgreementAcceptanceService;
use App\Services\SubscriptionAgreementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionAgreementGateController extends Controller
{
    public function show(
        Request $request,
        SubscriptionAgreementAcceptanceService $acceptances,
        SubscriptionAgreementService $agreements,
    ): View|RedirectResponse {
        $user = $request->user();
        abort_unless($user, 403);

        if (! $acceptances->needsAcceptance($user)) {
            return redirect()->intended('/admin');
        }

        $company = $acceptances->companyFor($user);
        abort_unless($company, 403, 'Akun belum terhubung ke perusahaan.');

        return view('front.setujui-perjanjian', [
            'user' => $user,
            'company' => $company,
            'data' => $agreements->viewData($company),
            'version' => $agreements->version(),
        ]);
    }

    public function accept(Request $request, SubscriptionAgreementAcceptanceService $acceptances): RedirectResponse
    {
        $request->validate([
            'agree' => ['accepted'],
        ], [
            'agree.accepted' => 'Centang persetujuan sebelum masuk ke aplikasi.',
        ]);

        $user = $request->user();
        abort_unless($user, 403);

        $acceptance = $acceptances->accept($user, $request);

        $message = $acceptance->emailed_at
            ? 'Perjanjian disetujui. Salinan kontrak sudah dikirim ke email Anda.'
            : 'Perjanjian disetujui. Salinan email gagal terkirim; unduh kontrak dari menu Perusahaan bila perlu.';

        return redirect()
            ->intended('/admin')
            ->with('success', $message);
    }
}
