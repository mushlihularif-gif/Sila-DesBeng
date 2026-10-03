<?php

namespace App\Support;

use App\Models\Region;
use Illuminate\Support\Collection;

/** Shared, visibility-aware region filtering for public service catalogs. */
class RegionCatalogFilter
{
    /**
     * Region IDs the current listing is allowed to display.
     * A null scope means the listing is not restricted to a particular region.
     */
    public static function idsFor(?int $regionId, ?array $visibleRegionIds = null): array
    {
        if (! $regionId || ! Region::whereKey($regionId)->exists()) {
            return [];
        }

        $ids = array_merge([$regionId], Region::getDescendantIds($regionId));

        if ($visibleRegionIds !== null) {
            $ids = array_intersect($ids, $visibleRegionIds);
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Build filter options from regions that actually contain catalog items,
     * including their parent regions, and keep them inside the visible scope.
     */
    public static function options(array $availableRegionIds, ?array $visibleRegionIds = null): Collection
    {
        $tree = Region::query()->get(['id', 'name', 'parent_id'])->keyBy('id');
        $optionIds = [];

        foreach ($availableRegionIds as $availableId) {
            $current = $tree->get((int) $availableId);
            $depth = 0;

            while ($current && $depth++ < 20) {
                $optionIds[] = (int) $current->id;
                $current = $current->parent_id ? $tree->get($current->parent_id) : null;
            }
        }

        $optionIds = array_values(array_unique($optionIds));
        if ($visibleRegionIds !== null) {
            $optionIds = array_values(array_intersect($optionIds, $visibleRegionIds));
        }

        $options = $tree->only($optionIds)->sortBy('name')->values();
        $options->each(function (Region $region) use ($tree): void {
            $parts = [$region->name];
            $current = $region;
            $depth = 0;

            while ($current->parent_id && $depth++ < 20) {
                $current = $tree->get($current->parent_id);
                if (! $current) {
                    break;
                }
                $parts[] = $current->name;
            }

            $region->filter_label = implode(' › ', array_reverse($parts));
        });

        return $options;
    }
}
