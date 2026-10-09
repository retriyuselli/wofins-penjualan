<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InvoiceShareLinkTest extends TestCase
{
    public function test_shared_invoice_pdf_route_is_signed_and_public(): void
    {
        $this->assertTrue(Route::has('invoice.share'));

        $route = Route::getRoutes()->getByName('invoice.share');
        $this->assertNotNull($route);

        $middleware = collect($route->gatherMiddleware());
        $this->assertTrue($middleware->contains(
            fn ($item) => is_string($item) && (str_contains($item, 'signed') || $item === 'signed')
        ));
        $this->assertFalse($middleware->contains('auth'));
    }

    public function test_unsigned_share_link_is_rejected(): void
    {
        $this->get('/invoice/wedding-eko-fitri/pdf')->assertForbidden();
    }
}
