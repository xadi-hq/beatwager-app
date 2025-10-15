<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;

class ShortUrlController extends Controller
{
    /**
     * Redirect from short URL to target URL
     */
    public function redirect(Request $request, string $code)
    {
        $shortUrl = ShortUrl::where('code', $code)->firstOrFail();

        if ($shortUrl->isExpired()) {
            abort(410, 'This link has expired');
        }

        return redirect($shortUrl->target_url);
    }
}