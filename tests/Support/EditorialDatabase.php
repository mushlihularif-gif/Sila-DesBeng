<?php
namespace Tests\Support;
use App\Models\{Announcement, User};
use App\Services\EditorialService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Http, Schema};
trait EditorialDatabase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Schema::create('regions', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('type'); $t->unsignedBigInteger('parent_id')->nullable(); });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); foreach (['name','role','verification_status'] as $field) $t->string($field);
            $t->string('name_hash')->nullable();
            $t->unsignedBigInteger('region_id')->nullable(); $t->string('email')->nullable(); $t->string('phone')->nullable(); $t->timestamps();
        });
        Schema::create('laporans', fn (Blueprint $t) => $t->id());
        (require database_path('migrations/2025_12_02_151523_create_files_table.php'))->up();
        (require database_path('migrations/2026_06_15_190313_create_announcements_table.php'))->up();
        (require database_path('migrations/2026_07_31_220841_enhance_announcements_for_news_and_targeted_scopes.php'))->up();
        (require database_path('migrations/2026_10_28_120000_add_editorial_workflow.php'))->up();
        Schema::create('notifications', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); foreach (['type','title','message','link','icon','image'] as $field) $t->text($field)->nullable();
            $t->boolean('is_read')->default(false); $t->timestamp('sent_at')->nullable(); $t->timestamps();
        });
        DB::table('regions')->insert([
            ['id'=>1,'name'=>'Desa A','type'=>'desa','parent_id'=>null], ['id'=>2,'name'=>'RW 02','type'=>'rw','parent_id'=>1],
            ['id'=>3,'name'=>'RT 01','type'=>'rt','parent_id'=>2], ['id'=>4,'name'=>'RT 02','type'=>'rt','parent_id'=>2],
            ['id'=>5,'name'=>'Desa B','type'=>'desa','parent_id'=>null], ['id'=>6,'name'=>'RW 02','type'=>'rw','parent_id'=>5],
            ['id'=>7,'name'=>'RT 01','type'=>'rt','parent_id'=>6],
        ]);
        foreach ([[1,'user',3],[2,'admin_rt',3],[3,'admin_rw',2],[4,'admin_desa',1],[5,'admin_rt',4],[6,'user',7],[7,'admin_desa',5],[8,'user',3]] as [$id,$role,$region]) {
            DB::table('users')->insert(['id'=>$id,'name'=>'Akun '.$id,'role'=>$role,'region_id'=>$region,'verification_status'=>$id === 8 ? 'unverified':'verified']);
        }
    }

    protected function user(int $id): User { return User::findOrFail($id); }
    protected function service(): EditorialService { return app(EditorialService::class); }
    protected function content(int $owner = 1, string $category = 'Berita', string $state = 'pending'): Announcement
    {
        return Announcement::create([
            'admin_id'=>$owner,'region_id'=>$this->user($owner)->region_id,'title'=>'Gotong royong warga','description'=>str_repeat('Warga membersihkan lingkungan dan saluran air. ',3),
            'type'=>'Event','post_category'=>$category,'workflow_state'=>$state,'is_active'=>$state === 'published',
            'target_audience_id'=>$category === 'Pengumuman' ? $this->user($owner)->region_id : null,
            'is_citizen'=>$owner === 1,'submitted_at'=>now(),'location'=>'Balai warga','event_date'=>now(),
        ]);
    }

}
