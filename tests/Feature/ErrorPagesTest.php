<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Testing\TestResponse;

class ErrorPagesTest extends TestCase
{
    public function test_404_error_page_renders_with_mascot_illustration()
    {
        $response = $this->get('/route-palsu-yang-pasti-tidak-ada-12345');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('SegiDaerah2.png');
    }

    public function test_preview_routes_render_all_error_pages()
    {
        $cases = [
            '403' => ['Akses Terbatas / Ditolak', 'SegiDaerah1.png'],
            '404' => ['Halaman Tidak Ditemukan', 'SegiDaerah2.png'],
            '419' => ['Sesi Halaman Berakhir', 'SegiDaerah1.png'],
            '429' => ['Terlalu Banyak Permintaan', 'SegiDaerah1.png'],
            '500' => ['Terjadi Kendala Teknis', 'SegiDaerah2.png'],
            '503' => ['Sedang Dalam Pemeliharaan', 'SegiDaerah2.png'],
        ];

        foreach ($cases as $code => [$expectedText, $expectedImage]) {
            $response = $this->get("/preview-error/{$code}");
            $response->assertStatus((int)$code);
            $response->assertSee($expectedText);
            $response->assertSee($expectedImage);
        }
    }

    public function test_error_views_do_not_query_database_or_resolve_logged_in_user(): void
    {
        DB::shouldReceive('connection')->never();
        Auth::shouldReceive('guard')->never();
        foreach ([403,404,419,429,500,503] as $code) {
            $html=view('errors.'.$code)->render();
            $this->assertStringContainsString('SegiDaerah',$html);
            $this->assertStringNotContainsString('user-scalable=no',$html);
        }
    }

    public function test_real_csrf_failure_returns_419_for_html_and_json_without_redirecting(): void
    {
        config(['app.debug'=>false]);
        $handler=app(ExceptionHandler::class);
        $request=Request::create('/form-uji','POST');
        $this->app->instance('request',$request);
        TestResponse::fromBaseResponse($handler->render($request,new TokenMismatchException()))
            ->assertStatus(419)->assertSee('Sesi Halaman Berakhir')->assertDontSee('window.location.reload()',false);
        $request->headers->set('Accept','application/json');
        TestResponse::fromBaseResponse($handler->render($request,new TokenMismatchException()))
            ->assertStatus(419)->assertJsonStructure(['message']);
    }

    public function test_post_errors_do_not_offer_reload_that_could_resend_a_transaction(): void
    {
        $this->app->instance('request',Request::create('/pesanan','POST'));
        foreach ([419,429,500,503] as $code) $this->assertStringNotContainsString('window.location.reload()',view('errors.'.$code)->render());
    }

    public function test_preview_remains_unavailable_when_environment_changes_to_production(): void
    {
        $this->app['env']='production';
        $this->get('/preview-error/500')->assertNotFound();
    }
}
