<?php

namespace App\Services;

use App\Models\{Announcement, Region, User};
use Illuminate\Database\Eloquent\Builder;

class ContentAccess
{
    public const ROLES = User::ROLES;

    public function manager(?User $user): bool
    {
        if (!$user || !$user->region_id || !($region = Region::find($user->region_id))) return false;
        $expectedTypes = ['admin_rt'=>['rt'],'admin_rw'=>['rw'],'admin_desa'=>['desa','kelurahan']];
        if (isset($expectedTypes[$user->role]) && !in_array($region->type, $expectedTypes[$user->role], true)) return false;
        return in_array($user->role, ['admin_rt', 'admin_rw', 'admin_desa', 'super_admin'], true);
    }

    public function area(User $user): array
    {
        if (!$this->manager($user)) return [];
        return array_map('intval', [$user->region_id, ...Region::getDescendantIds($user->region_id)]);
    }

    public function canManage(User $user, Announcement $item): bool
    {
        if (!$this->manager($user)) return false;
        if ($item->workflow_state === 'draft' && (int) $item->admin_id !== (int) $user->id) return false;
        // Pending requests for the parent RW must be reviewed at that level, never by their proposer.
        $region = $item->post_category === 'Pengumuman' ? $item->target_audience_id : $item->region_id;
        return in_array((int) $region, $this->area($user), true);
    }

    public function canInspect(User $user, Announcement $item): bool
    {
        return in_array($user->role, self::ROLES, true)
            && ((int) $item->admin_id === (int) $user->id || $this->canManage($user, $item));
    }

    public function canWrite(User $user, Announcement $item): bool
    {
        return $this->canManage($user, $item)
            || ($user->role === 'user' && (int) $item->admin_id === (int) $user->id && $item->workflow_state === 'draft'
                && $user->verification_status === 'verified'
                && (int) $item->region_id === (int) $user->region_id);
    }

    public function workspace(Builder $query, User $user): Builder
    {
        if (!in_array($user->role, self::ROLES, true)) return $query->whereRaw('1 = 0');
        $areas = $this->area($user);
        return $query->where(function (Builder $q) use ($user, $areas) {
            $q->where('admin_id', $user->id)->orWhere(function (Builder $q) use ($areas) {
                $q->where('workflow_state', '!=', 'draft')->where(function (Builder $q) use ($areas) {
                    $q->where(fn (Builder $q) => $q->where('post_category', 'Berita')->whereIn('region_id', $areas))
                        ->orWhere(fn (Builder $q) => $q->where('post_category', 'Pengumuman')->whereIn('target_audience_id', $areas));
                });
            });
        });
    }

    public function visible(Builder $query, ?User $user): Builder
    {
        $ancestors = $user?->region_id ? Region::garisLayananUntukWarga($user->region_id) : [];
        return $query->where('is_active', true)->whereIn('workflow_state', ['published','cancelled','archived'])->where(function (Builder $q) use ($ancestors) {
            $q->where('post_category', 'Berita')->orWhere(function (Builder $q) use ($ancestors) {
                $q->where('post_category', 'Pengumuman')->whereIn('target_audience_id', $ancestors);
            });
        });
    }

    public function targets(User $user): array
    {
        $ids = $this->area($user);
        $region = Region::with('parent')->find($user->region_id);
        if ($user->role === 'admin_rt' && $region?->type === 'rt' && $region->parent?->type === 'rw') {
            $ids[] = (int) $region->parent_id;
        }
        return array_values(array_unique($ids));
    }
}
