<?php

namespace Tests\Feature;

use App\Http\Controllers\HelpCenterController;
use Illuminate\Http\Request;
use Tests\TestCase;

class HelpCenterAuditTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    public function test_help_page_renders_without_the_nonexistent_ticket_table(): void
    {
        $this->actingAs($this->user(1));
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $html = app(HelpCenterController::class)->index()->render();
        $this->assertStringContainsString('segidaerah@inovasia.site', $html);
        $this->assertStringNotContainsString('sungaipakning', $html);
        $this->assertStringContainsString('abstract-canvas', $html);
        if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($directory.'/help-center.html', $html);
        foreach (['auth.reset-password','errors.service-unavailable'] as $page) {
            $html = view($page)->render();
            $this->assertStringContainsString('segidaerah-ui.css', $html);
            if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($directory.'/'.$page.'.html', $html);
        }
    }

    public function test_legacy_ticket_routes_lead_to_help_without_claiming_to_save_a_ticket(): void
    {
        $controller = app(HelpCenterController::class);
        foreach ([$controller->myTickets(), $controller->create(), $controller->show(999),
            $controller->store(Request::create('/', 'POST')), $controller->close(999)] as $response) {
            $this->assertSame(route('help-center.index').'#hubungi', $response->getTargetUrl());
            $this->assertStringContainsString('belum tersedia', $response->getSession()->get('info'));
        }
    }
}
