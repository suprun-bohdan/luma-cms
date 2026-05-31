<?php

declare(strict_types=1);

namespace App\Modules\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Seo\Services\RobotsTxtBuilder;
use Illuminate\Http\Response;

final class RobotsController extends Controller
{
    public function __invoke(RobotsTxtBuilder $builder): Response
    {
        return response($builder->build(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
