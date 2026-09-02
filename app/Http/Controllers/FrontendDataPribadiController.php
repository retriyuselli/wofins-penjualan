<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicDataPribadiRequest;
use App\Models\DataPribadi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FrontendDataPribadiController extends Controller
{
    private const SESSION_OPENED_AT = 'data_pribadi_opened_at';

    private const SESSION_SUBMITTED = 'data_pribadi_submitted';

    public function create(Request $request): View
    {
        $request->session()->put(self::SESSION_OPENED_AT, now()->timestamp);

        return view('data-pribadi.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->isHoneypotTriggered($request)) {
            Log::notice('data_pribadi_blocked', [
                'reason' => 'honeypot',
                'ip' => $request->ip(),
            ]);

            return $this->fakeSuccess($request);
        }

        if (! $this->formWasOpenedProperly($request)) {
            return redirect()->route('data-pribadi.create')
                ->withErrors(['nama_lengkap' => 'Silakan buka formulir dari tautan resmi, isi dengan lengkap, lalu kirim ulang.'])
                ->withInput($request->except(['foto', 'company_website']));
        }

        $validated = app(StorePublicDataPribadiRequest::class)->validated();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $extension = strtolower((string) $file->guessExtension() ?: $file->extension());
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                return redirect()->route('data-pribadi.create')
                    ->withErrors(['foto' => 'Format foto harus jpg, jpeg, png, atau gif.'])
                    ->withInput($request->except(['foto', 'company_website']));
            }

            $validated['foto'] = $file->storeAs(
                'data-pribadi-fotos',
                Str::uuid()->toString().'.'.$extension,
                'public'
            );
        } elseif (isset($validated['foto']) && ! is_string($validated['foto'])) {
            unset($validated['foto']);
        }

        DataPribadi::create($validated);

        $request->session()->forget(self::SESSION_OPENED_AT);
        $request->session()->put(self::SESSION_SUBMITTED, true);

        return redirect()->route('data-pribadi.success')->with('success', 'Data pribadi berhasil disimpan!');
    }

    public function index(Request $request): View
    {
        $query = DataPribadi::query();

        if ($request->filled('search')) {
            $query->where('nama_lengkap', 'LIKE', '%'.$request->string('search')->toString().'%');
        }

        $dataPribadis = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('data-pribadi.index', compact('dataPribadis'));
    }

    public function success(Request $request): View|RedirectResponse
    {
        if (! $request->session()->pull(self::SESSION_SUBMITTED) && ! $request->session()->has('success')) {
            return redirect()->route('data-pribadi.create');
        }

        return view('data-pribadi.success');
    }

    private function isHoneypotTriggered(Request $request): bool
    {
        return filled($request->input('company_website'));
    }

    private function formWasOpenedProperly(Request $request): bool
    {
        $openedAt = (int) $request->session()->get(self::SESSION_OPENED_AT, 0);

        if ($openedAt < 1) {
            return false;
        }

        $elapsed = now()->timestamp - $openedAt;

        return $elapsed >= 3 && $elapsed <= 14400;
    }

    private function fakeSuccess(Request $request): RedirectResponse
    {
        $request->session()->put(self::SESSION_SUBMITTED, true);

        return redirect()->route('data-pribadi.success')->with('success', 'Data pribadi berhasil disimpan!');
    }
}
