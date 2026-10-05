<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        if (! Company::showsProductsOnWeb()) {
            abort(404);
        }

        $search = trim((string) $request->query('q'));
        $categorySlug = trim((string) $request->query('kategori'));

        $visible = function ($query) {
            $query->where('is_approved', true)
                ->where('is_active', true)
                ->where('show_on_web', true);
        };

        $categories = Category::query()
            ->whereHas('products', $visible)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where($visible)
            ->when($categorySlug !== '', function ($query) use ($categorySlug) {
                $query->whereHas('category', function ($categoryQuery) use ($categorySlug) {
                    $categoryQuery->where('slug', $categorySlug);
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->with('category')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('front.product', [
            'products' => $products,
            'search' => $search,
            'categories' => $categories,
            'categorySlug' => $categorySlug,
        ]);
    }
}
