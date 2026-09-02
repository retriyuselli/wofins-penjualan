<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicDataPribadiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('gaji')) {
            $this->merge([
                'gaji' => preg_replace('/[^\d]/', '', (string) $this->input('gaji')),
            ]);
        }

        if ($this->has('nomor_telepon')) {
            $this->merge([
                'nomor_telepon' => preg_replace('/\D+/', '', (string) $this->input('nomor_telepon')),
            ]);
        }

        foreach (['nama_lengkap', 'email', 'pekerjaan', 'alamat', 'motivasi_kerja', 'pelatihan'] as $field) {
            if ($this->filled($field)) {
                $this->merge([
                    $field => trim(strip_tags((string) $this->input($field))),
                ]);
            }
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s.\'’-]+$/u'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('data_pribadis', 'email')->whereNull('deleted_at'),
            ],
            'nomor_telepon' => ['nullable', 'string', 'max:16', 'regex:/^[0-9]{8,16}$/'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today', 'after:1920-01-01'],
            'jenis_kelamin' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'foto' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,gif', 'max:1024', 'dimensions:max_width=4000,max_height=4000'],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'gaji' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'motivasi_kerja' => ['nullable', 'string', 'max:3000'],
            'pelatihan' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'nomor_telepon.regex' => 'Nomor telepon tidak valid.',
            'foto.required' => 'Foto profil wajib diunggah.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus jpg, jpeg, png, atau gif.',
            'foto.max' => 'Ukuran foto maksimal 1MB.',
            'foto.dimensions' => 'Resolusi foto terlalu besar.',
        ];
    }
}
