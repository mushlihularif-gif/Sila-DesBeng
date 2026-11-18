<?php

namespace Tests\Feature;

use App\Http\Controllers\User\WilayahAdminController;
use App\Models\User;
use App\Support\RegionalCitizens;
use App\Support\UserDirectory;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class UserDirectoryAuditTest extends TestCase
{
    use EditorialDatabase;

    public function test_partial_encrypted_names_and_email_search_keep_the_region_scope(): void
    {
        $this->user(1)->update(['name' => 'Zahra Putri', 'email' => 'zahra@example.test']);
        $this->user(8)->update(['name' => 'Amir Putra', 'email' => 'amir@example.test']);
        $this->user(6)->update(['name' => 'Zahra Putri', 'email' => 'foreign@example.test']);
        $this->assertStringStartsWith('$chacha20$', $this->user(1)->getRawOriginal('name'));
        $admin = $this->user(2);
        $this->actingAs($admin);
        foreach (['zahra', 'PUTRI', 'zahra@example.test'] as $search) {
            $result = UserDirectory::paginate(RegionalCitizens::query($admin), $search, 15);
            $this->assertSame([1], $result->pluck('id')->all());
        }
        $this->assertSame([], UserDirectory::search(RegionalCitizens::query($admin), 'foreign')->pluck('id')->all());
        $this->assertSame([], UserDirectory::search(RegionalCitizens::query($admin), '%')->pluck('id')->all());
        $page = app(WilayahAdminController::class)->indexWarga(Request::create('/', 'GET', ['search' => 'zahra']));
        $this->assertSame([1], $page->getData()['wargas']->pluck('id')->all());
    }

    public function test_sort_and_pagination_use_decrypted_names_for_every_page(): void
    {
        $this->user(1)->update(['name' => 'Zahra']);
        $this->user(8)->update(['name' => 'Amir']);
        $query = User::whereIn('id', [1, 8])->with('region');
        $first = UserDirectory::paginate($query, null, 1);
        $this->assertSame(2, $first->total());
        $this->assertSame([8], $first->pluck('id')->all());
        $this->assertTrue($first->first()->relationLoaded('region'));
        Paginator::currentPageResolver(fn () => 2);
        try {
            $this->assertSame([1], UserDirectory::paginate($query, null, 1)->pluck('id')->all());
        } finally {
            Paginator::currentPageResolver(fn () => 1);
        }
    }
}
