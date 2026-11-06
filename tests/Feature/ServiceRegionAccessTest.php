<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\{SupirController, UnitFasilitasUmumController};
use App\Models\Supir;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ServiceRegionAccessTest extends TestCase
{
    use \Tests\Support\EditorialDatabase { setUp as setupEditorial; }

    protected function setUp(): void
    {
        $this->setupEditorial();
        Schema::create('supirs',function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->integer('user_id')->nullable();
            foreach (['nama','tipe','kontak','status','foto','layanan'] as $field) $t->string($field)->nullable();
            $t->boolean('is_sewa_mobil')->default(false); $t->boolean('is_fasilitas_umum')->default(false); $t->timestamps();
        });
        Schema::create('fasilitas_umums',function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('nama_fasilitas'); $t->string('kategori')->nullable();
        });
        Schema::create('fasilitas_umum_bookings',function (Blueprint $t) {
            $t->id(); $t->integer('fasilitas_id'); $t->softDeletes();
        });
        DB::table('supirs')->insert([
            ['id'=>1,'region_id'=>1,'nama'=>'Pengurus lokal','tipe'=>'pengurus_gedung'],
            ['id'=>2,'region_id'=>5,'nama'=>'Pengurus luar','tipe'=>'pengurus_gedung'],
        ]);
        DB::table('fasilitas_umums')->insert([
            ['id'=>1,'region_id'=>1,'nama_fasilitas'=>'Balai lokal'],
            ['id'=>2,'region_id'=>5,'nama_fasilitas'=>'Balai luar'],
        ]);
        $this->actingAs($this->user(4));
    }

    private function assertUnavailable(callable $action): void
    {
        try { $action(); $this->fail('Foreign record was accessible'); }
        catch (ModelNotFoundException $e) { $this->assertNotEmpty($e->getModel()); }
    }

    public function test_admin_cannot_change_or_delete_foreign_personnel_and_facilities(): void
    {
        $supir=app(SupirController::class); $facility=app(UnitFasilitasUmumController::class);
        $this->assertUnavailable(fn ()=>$supir->update(Request::create('/','PUT'),Supir::findOrFail(2)));
        $this->assertUnavailable(fn ()=>$supir->destroy(Supir::findOrFail(2)));
        $this->assertUnavailable(fn ()=>$facility->show(2));
        $this->assertUnavailable(fn ()=>$facility->update(Request::create('/','PUT'),2));
        $this->assertUnavailable(fn ()=>$facility->destroy(2));
        $this->assertSame(2,DB::table('supirs')->count());
        $this->assertSame(2,DB::table('fasilitas_umums')->count());
    }

    public function test_personnel_assignment_accepts_own_rt_resident_but_not_other_village(): void
    {
        $controller=app(SupirController::class);
        $data=['nama'=>'Pengurus lokal','tipe'=>'pengurus_gedung','status'=>'Tersedia','user_id'=>6];
        try { $controller->update(Request::create('/','PUT',$data),Supir::findOrFail(1)); $this->fail('Foreign resident accepted'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('user_id',$e->errors()); }
        $data['user_id']=1;
        $controller->update(Request::create('/','PUT',$data),Supir::findOrFail(1));
        $this->assertSame(1,Supir::findOrFail(1)->user_id);
    }

    public function test_facility_cannot_assign_foreign_caretaker(): void
    {
        $data=['nama_fasilitas'=>'Balai','deskripsi'=>'Balai warga','stok'=>1,'status'=>'Tersedia','kategori'=>'Gedung','lokasi'=>'Desa','status_biaya'=>'gratis','pengurus_ids'=>[2]];
        try { app(UnitFasilitasUmumController::class)->update(Request::create('/','PUT',$data),1); $this->fail('Foreign caretaker accepted'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('pengurus_ids.0',$e->errors()); }
    }

    public function test_deleting_facility_preserves_even_archived_bookings(): void
    {
        DB::table('fasilitas_umum_bookings')->insert(['fasilitas_id'=>1,'deleted_at'=>now()]);
        try { app(UnitFasilitasUmumController::class)->destroy(1); $this->fail('Historical booking parent deleted'); }
        catch (HttpException $e) { $this->assertSame(409,$e->getStatusCode()); }
        $this->assertSame(2,DB::table('fasilitas_umums')->count());
        $this->assertSame(1,DB::table('fasilitas_umum_bookings')->count());
    }

    public function test_unassigned_admin_and_legacy_roles_cannot_manage_personnel(): void
    {
        foreach (['admin_desa','admin_kecamatan','admin_kabupaten','staff','user'] as $role) {
            $actor=$this->user(4); $actor->role=$role; $actor->region_id=null; $this->actingAs($actor);
            try { app(SupirController::class)->destroy(Supir::findOrFail(1)); $this->fail('Unsupported manager accepted'); }
            catch (HttpException $e) { $this->assertSame(403,$e->getStatusCode()); }
        }
    }
}
