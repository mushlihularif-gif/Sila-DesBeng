<?php

namespace App\Support;

use App\Models\Region;

/** Batas wilayah operasional; bukan peran administrator kecamatan. */
final class BengkalisScope
{
    public static function root(): Region
    {
        return Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->firstOrFail();
    }

    public static function ids(bool $includeRoot = true): array
    {
        $root = self::root();
        return array_map('intval', array_merge($includeRoot ? [$root->id] : [], Region::getDescendantIds($root->id)));
    }

    public static function assertContains(Region $region, bool $includeRoot = true): void
    {
        abort_unless(in_array((int) $region->id, self::ids($includeRoot), true), 404);
    }
}
