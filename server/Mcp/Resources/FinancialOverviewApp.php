<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Interactive financial overview for the active Diwanio workspace. / نظرة مالية تفاعلية لمساحة Diwanio النشطة.')]
#[AppMeta(prefersBorder: true)]
final class FinancialOverviewApp extends AppResource
{
    public function handle(Request $request): Response
    {
        return Response::view('mcp.financial-overview-app', [
            'title' => __('mcp.financial_overview.title'),
        ]);
    }
}
