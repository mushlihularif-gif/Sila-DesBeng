<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $t) {
            $t->string('workflow_state', 24)->default('draft')->index();
            $t->boolean('is_citizen')->default(false);
            $t->json('draft_data')->nullable();
            $t->unsignedInteger('revision')->default(0);
            $t->unsignedBigInteger('editor_id')->nullable();
            $t->string('edit_token', 64)->nullable();
            $t->timestamp('edit_expires_at')->nullable();
            foreach (['submitted_at', 'handled_at', 'rejected_at', 'published_at', 'scheduled_at', 'ends_at', 'reminded_at', 'cleanup_notified_at'] as $column) {
                $t->timestamp($column)->nullable();
            }
            $t->text('rejection_reason')->nullable();
            $t->unsignedBigInteger('merged_into_id')->nullable()->index();
            $t->unsignedBigInteger('contribution_to')->nullable()->index();
            $t->unsignedBigInteger('publisher_id')->nullable();
            $t->unsignedBigInteger('views_count')->default(0);
            $t->string('image_disk', 16)->default('public');
        });
        DB::table('announcements')->where('is_active', true)->update([
            'workflow_state' => 'published', 'published_at' => DB::raw('created_at'),
        ]);
        // Legacy RT postings stored the audience in region_id. Never interpret NULL as everyone.
        DB::table('announcements')->where('post_category', 'Pengumuman')
            ->whereNull('target_audience_id')->whereNotNull('region_id')
            ->update(['target_audience_id' => DB::raw('region_id'), 'target_audience_type' => 'region']);
        Schema::table('announcement_images', function (Blueprint $t) {
            $t->string('disk', 16)->default('public');
            $t->unsignedBigInteger('contributor_id')->nullable();
            $t->string('contributor_name')->nullable();
            $t->boolean('is_staged')->default(false);
            $t->boolean('pending_removal')->default(false);
        });
        Schema::create('announcement_sources', function (Blueprint $t) {
            $t->id();
            $t->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('source_id')->nullable();
            $t->unsignedBigInteger('contributor_id')->nullable();
            $t->string('contributor_name');
            $t->boolean('uses_text')->default(false);
            $t->boolean('is_staged')->default(false);
            $t->boolean('draft_uses_text')->nullable();
            $t->text('excerpt')->nullable();
            $t->timestamps();
            $t->unique(['announcement_id', 'source_id']);
        });
        Schema::create('announcement_activities', function (Blueprint $t) {
            $t->id();
            $t->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('actor_name');
            $t->string('action', 32);
            $t->json('details')->nullable();
            $t->timestamps();
        });
        Schema::create('announcement_presence', function (Blueprint $t) {
            $t->id();
            $t->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('user_id');
            $t->timestamp('seen_at');
            $t->unique(['announcement_id', 'user_id']);
        });
        Schema::create('announcement_views', function (Blueprint $t) {
            $t->id();
            $t->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $t->string('fingerprint', 64);
            $t->date('day');
            $t->unique(['announcement_id', 'fingerprint', 'day']);
        });
    }

    public function down(): void
    {
        foreach (['announcement_views', 'announcement_presence', 'announcement_activities', 'announcement_sources'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('announcement_images', fn (Blueprint $t) => $t->dropColumn(['disk', 'contributor_id', 'contributor_name', 'is_staged', 'pending_removal']));
        Schema::table('announcements', fn (Blueprint $t) => $t->dropColumn([
            'workflow_state', 'is_citizen', 'draft_data', 'revision', 'editor_id', 'edit_token', 'edit_expires_at',
            'submitted_at', 'handled_at', 'rejected_at', 'published_at', 'scheduled_at', 'ends_at', 'reminded_at',
            'cleanup_notified_at', 'rejection_reason', 'merged_into_id', 'contribution_to', 'publisher_id', 'views_count', 'image_disk',
        ]));
    }
};
