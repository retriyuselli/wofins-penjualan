<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SubscriptionAgreementAcceptance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SubscriptionAgreementAcceptanceService
{
    public function __construct(private SubscriptionAgreementService $agreements) {}

    public function version(): string
    {
        return $this->agreements->version();
    }

    public function shouldSkip(?User $user): bool
    {
        if (! $user) {
            return true;
        }

        if (! Schema::hasTable('subscription_agreement_acceptances')) {
            return true;
        }

        if (config('wofins.agreement.skip_super_admin')
            && method_exists($user, 'hasRole')
            && $user->hasRole('super_admin')) {
            return true;
        }

        return false;
    }

    public function needsAcceptance(?User $user): bool
    {
        if ($this->shouldSkip($user) || ! $user) {
            return false;
        }

        $company = $this->companyFor($user);
        if (! $company) {
            return false;
        }

        return ! SubscriptionAgreementAcceptance::query()
            ->where('user_id', $user->id)
            ->where('version', $this->version())
            ->exists();
    }

    public function companyFor(User $user): ?Company
    {
        if (method_exists($user, 'company')) {
            $related = $user->company;
            if ($related instanceof Company) {
                return $related;
            }
        }

        return Company::query()->first();
    }

    public function accept(User $user, Request $request): SubscriptionAgreementAcceptance
    {
        $company = $this->companyFor($user);
        abort_unless($company, 403, 'Akun belum terhubung ke perusahaan.');

        $acceptance = SubscriptionAgreementAcceptance::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'version' => $this->version(),
            ],
            [
                'company_id' => $company->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 512),
            ]
        );

        if (! $acceptance->emailed_at) {
            $this->emailCopy($user, $company, $acceptance);
        }

        return $acceptance;
    }

    public function emailCopy(User $user, Company $company, SubscriptionAgreementAcceptance $acceptance): void
    {
        try {
            $data = $this->agreements->viewData($company);
            $pdf = $this->agreements->pdfContents($company);
            $filename = $this->agreements->filename($company);
            $recipients = array_values(array_unique(array_filter([
                $user->email,
                $company->email,
            ])));

            Mail::send('emails.subscription-agreement-accepted', [
                'user' => $user,
                'company' => $company,
                'acceptance' => $acceptance,
                'data' => $data,
            ], function ($message) use ($recipients, $filename, $pdf, $data) {
                $message->to($recipients)
                    ->subject('Salinan Perjanjian Berlangganan WOFINS — '.$data['legal_name'])
                    ->attachData($pdf, $filename, ['mime' => 'application/pdf']);
            });

            $acceptance->forceFill(['emailed_at' => now()])->save();
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim email perjanjian berlangganan.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
