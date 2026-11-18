<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class UserDirectory
{
    /** The caller must scope the query to the viewer's permitted users first. */
    private static function matchingIds(Builder $scopedQuery, ?string $search): Collection
    {
        $terms = preg_split('/\s+/u', mb_strtolower(trim($search ?? '')), -1, PREG_SPLIT_NO_EMPTY);
        // Encrypted names cannot be searched or sorted with SQL LIKE/ORDER BY.
        // Read only directory fields inside the existing scope, never a global user list.
        $rows = (clone $scopedQuery)->withoutEagerLoads()->reorder()->select(['users.id', 'users.name', 'users.email'])->cursor();
        $matches = collect();
        foreach ($rows as $user) {
            $name = $user->name ?? '';
            $haystack = mb_strtolower($name.' '.($user->email ?? ''));
            foreach ($terms as $term) {
                if (! str_contains($haystack, $term)) {
                    continue 2;
                }
            }
            $matches->push(['id' => $user->id, 'name' => mb_strtolower($name)]);
        }

        return $matches->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']) ?: ($a['id'] <=> $b['id']);
        })->pluck('id')->values();
    }

    public static function search(Builder $scopedQuery, string $search): Builder
    {
        return $scopedQuery->whereIn('users.id', self::matchingIds($scopedQuery, $search)->all());
    }

    public static function paginate(Builder $scopedQuery, ?string $search, int $perPage): LengthAwarePaginator
    {
        $ids = self::matchingIds($scopedQuery, $search);
        $page = Paginator::resolveCurrentPage();
        $pageIds = $ids->forPage($page, $perPage)->values();
        $users = (clone $scopedQuery)->whereIn('users.id', $pageIds)->get()->keyBy('id');
        $items = $pageIds->map(fn ($id) => $users->get($id))->filter()->values();

        return new LengthAwarePaginator($items, $ids->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(), 'query' => request()->query(),
        ]);
    }
}
