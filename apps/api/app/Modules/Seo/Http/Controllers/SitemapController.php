<?php

declare(strict_types=1);

namespace App\Modules\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Seo\Services\SitemapGenerator;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function __invoke(SitemapGenerator $generator): Response
    {
        return response($generator->generateXml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
