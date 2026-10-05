<?php

namespace App\Http\Controllers;

use App\Exports\ProductExport;
use App\Models\Company;
use App\Models\Product;
use App\Services\ProductPricingCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductDisplayController extends Controller
{
    private function companyPreviewData(bool $forPdf = false): array
    {
        $company = null;
        if (Schema::hasTable('companies')) {
            $company = Company::query()->with('owner')->first();
        }

        if ($company && $company->logo_url && Storage::disk('public')->exists($company->logo_url)) {
            $logoPath = Storage::disk('public')->path($company->logo_url);
        } else {
            $logoPath = public_path('images/logomki.png');
        }

        $logoSrc = $this->encodeLogoForDisplay($logoPath, $forPdf);

        return [
            'company' => $company,
            'logoSrc' => $logoSrc,
            'companyName' => $company?->company_name ?? config('app.name'),
        ];
    }

    /**
     * Embed logo as base64. For PDF, resize/compress first —
     * full-size company logos (~1MB) make DomPDF extremely slow.
     */
    private function encodeLogoForDisplay(?string $logoPath, bool $forPdf = false): string
    {
        if (! is_string($logoPath) || $logoPath === '' || ! file_exists($logoPath)) {
            return '';
        }

        try {
            if ($forPdf) {
                $compressed = $this->compressLogoForPdf($logoPath);
                if ($compressed !== null) {
                    return $compressed;
                }
            }

            $mime = mime_content_type($logoPath) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($logoPath));
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Resize logo to max width ~220px and encode as JPEG for DomPDF.
     */
    private function compressLogoForPdf(string $logoPath): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $binary = @file_get_contents($logoPath);
        if ($binary === false || $binary === '') {
            return null;
        }

        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            return null;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        if ($srcW < 1 || $srcH < 1) {
            imagedestroy($source);

            return null;
        }

        $maxWidth = 110;
        $dstW = $srcW > $maxWidth ? $maxWidth : $srcW;
        $dstH = (int) max(1, round($srcH * ($dstW / $srcW)));

        $canvas = imagecreatetruecolor($dstW, $dstH);
        if ($canvas === false) {
            imagedestroy($source);

            return null;
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $dstW, $dstH, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        ob_start();
        imagejpeg($canvas, null, 82);
        $jpeg = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if ($jpeg === false || $jpeg === '') {
            return null;
        }

        return 'data:image/jpeg;base64,'.base64_encode($jpeg);
    }

    private function loadProductForDisplay(Product $product): Product
    {
        return $product->load([
            'category',
            'parent',
            'items.vendor',
            'items.vendor.category',
            'pengurangans',
            'penambahanHarga.vendor',
            'lastEditedBy',
        ]);
    }

    private function productDisplayData(Product $product, bool $forPdf = false): array
    {
        $this->loadProductForDisplay($product);

        return array_merge([
            'product' => $product,
            'pricing' => ProductPricingCalculator::calculateForProduct($product),
        ], $this->companyPreviewData($forPdf));
    }

    private function buildProductPdf(Product $product)
    {
        $data = $this->productDisplayData($product, forPdf: true);

        $pdf = Pdf::loadView('products.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf;
    }

    /**
     * Translucent tiled stamp so a downloaded offer cannot be reused cleanly.
     */
    private function quotationWatermark(string $label): string
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagettftext')) {
            return '';
        }

        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        if (! is_file($font)) {
            return '';
        }

        $label = trim($label);
        if ($label === '') {
            return '';
        }

        $size = 18;
        $box = imagettfbbox($size, 0, $font, $label);
        $textW = abs($box[2] - $box[0]);
        while ($textW > 820 && $size > 12) {
            $size--;
            $box = imagettfbbox($size, 0, $font, $label);
            $textW = abs($box[2] - $box[0]);
        }

        $tileW = $textW + 90;
        $tileH = 160;
        $tile = imagecreatetruecolor($tileW, $tileH);
        imagealphablending($tile, false);
        imagesavealpha($tile, true);
        $clear = imagecolorallocatealpha($tile, 0, 0, 0, 127);
        imagefilledrectangle($tile, 0, 0, $tileW, $tileH, $clear);
        imagealphablending($tile, true);
        $ink = imagecolorallocatealpha($tile, 11, 31, 58, 90);
        imagettftext($tile, $size, 0, 40, 95, $ink, $font, $label);

        $rotated = imagerotate($tile, 28, $clear);
        imagedestroy($tile);
        if ($rotated === false) {
            return '';
        }
        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);

        $pageW = 1240;
        $pageH = 1754;
        $page = imagecreatetruecolor($pageW, $pageH);
        imagealphablending($page, false);
        imagesavealpha($page, true);
        imagefilledrectangle($page, 0, 0, $pageW, $pageH, $clear);
        imagealphablending($page, true);

        $rw = imagesx($rotated);
        $rh = imagesy($rotated);
        $stepX = (int) max(1, $rw * 0.92);
        $stepY = (int) max(1, $rh * 0.78);
        for ($y = -((int) ($rh / 3)); $y < $pageH; $y += $stepY) {
            for ($x = -((int) ($rw / 3)); $x < $pageW; $x += $stepX) {
                imagecopy($page, $rotated, $x, $y, 0, 0, $rw, $rh);
            }
        }
        imagedestroy($rotated);

        ob_start();
        imagepng($page, null, 6);
        $png = ob_get_clean();
        imagedestroy($page);

        if (! is_string($png) || $png === '') {
            return '';
        }

        return 'data:image/png;base64,'.base64_encode($png);
    }

    private function ensureProductVisible(Product $product): void
    {
        if (! Company::showsProductsOnWeb()) {
            abort(404);
        }

        $isPublic = (bool) $product->is_approved
            && (bool) $product->is_active
            && (bool) $product->show_on_web;
        if (! $isPublic) {
            if (! auth()->check()) {
                abort(404);
            }

            Gate::authorize('view', $product);
        }
    }

    public function show(Product $product)
    {
        $this->ensureProductVisible($product);

        $product->load(['category', 'items.vendor.category']);
        $product->image_url = $product->image ? Storage::url($product->image) : null;

        return view('products.detail', compact('product'));
    }

    public function cetak(Product $product)
    {
        $this->ensureProductVisible($product);

        return view('products.cetak', $this->productDisplayData($product));
    }

    public function downloadCetakPdf(Product $product)
    {
        $this->ensureProductVisible($product);

        $data = $this->productDisplayData($product, forPdf: true);
        $reference = 'PROD-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT);
        $data['reference'] = $reference;
        $data['watermarkSrc'] = $this->quotationWatermark(
            trim(($data['companyName'] ?? '').' · PENAWARAN · '.$reference)
        );

        $pdf = Pdf::loadView('products.cetak-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isFontSubsettingEnabled' => true,
        ]);

        $fileName = 'penawaran-'.$product->slug.'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($fileName);
    }

    public function details(Product $product, string $action)
    {
        Gate::authorize('view', $product);

        if ($action === 'download') {
            return $this->downloadPdf($product);
        }

        if ($action === 'preview' || $action === 'print') {
            $viewData = array_merge(
                $this->productDisplayData($product, forPdf: false),
                ['action' => $action]
            );

            return view('products.details-preview', $viewData);
        }

        abort(404, 'Invalid action specified.');
    }

    public function downloadPdf(Product $product)
    {
        Gate::authorize('view', $product);

        $pdf = $this->buildProductPdf($product);
        $fileName = 'product-'.$product->slug.'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($fileName);
    }

    public function exportDetailToExcel(Product $product)
    {
        Gate::authorize('view', $product);

        return Excel::download(
            new ProductExport([$product->id]),
            'product_detail_'.Str::slug($product->name).'_'.now()->format('YmdHis').'.xlsx'
        );
    }
}
