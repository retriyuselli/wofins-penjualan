<?php

namespace Tests\Feature;

use App\Support\AdminPanelRequest;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class AdminNotFoundDoesNotRedirectHomeTest extends TestCase
{
    public function test_admin_path_is_detected(): void
    {
        $this->assertTrue(AdminPanelRequest::matches(Request::create('/admin/orders/missing-slug', 'GET')));
        $this->assertFalse(AdminPanelRequest::matches(Request::create('/produk/tidak-ada', 'GET')));
    }

    public function test_admin_not_found_stays_on_panel(): void
    {
        $request = Request::create('http://localhost/admin/orders/slug-tidak-ada', 'GET');
        $response = app(ExceptionHandler::class)->render($request, new NotFoundHttpException);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertFalse($response->isRedirect());
        $this->assertStringContainsString('Kembali ke dashboard', $response->getContent());
    }

    public function test_public_not_found_still_redirects_home(): void
    {
        $request = Request::create('http://localhost/halaman-publik-tidak-ada', 'GET');
        $response = app(ExceptionHandler::class)->render($request, new NotFoundHttpException);

        $this->assertTrue($response->isRedirect());
    }

    public function test_livewire_not_found_returns_json_not_home_redirect(): void
    {
        $request = Request::create('http://localhost/livewire-62c68c11/update', 'POST');
        $request->headers->set('X-Livewire', 'true');
        $response = app(ExceptionHandler::class)->render($request, new NotFoundHttpException);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('Halaman tidak ditemukan.', $response->getData(true)['message'] ?? null);
    }

    public function test_admin_method_not_allowed_does_not_redirect_home(): void
    {
        $request = Request::create('http://localhost/admin/orders', 'PUT');
        $response = app(ExceptionHandler::class)->render(
            $request,
            new MethodNotAllowedHttpException(['GET', 'POST'])
        );

        $this->assertSame(405, $response->getStatusCode());
        $this->assertFalse($response->isRedirect());
    }
}
