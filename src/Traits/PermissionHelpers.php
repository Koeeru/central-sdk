<?php

namespace Koeeru\Central\Traits;

use Illuminate\Support\Str;
use Koeeru\Central\Helpers\ExtractSubdomain;

trait PermissionHelpers
{
    public static function getRolesOrPermissions(array $companyAssociations, string $key = 'roles'): array
    {
        $subdomain = ExtractSubdomain::extract(request());

        foreach ($companyAssociations as $item) {
            if (self::isMatchingSubdomain($item, $subdomain)) {
                return self::extractItemsWithPrefix($item[$key]);
            }
        }

        return self::extractItemsWithPrefix($companyAssociations[0][$key]);
    }

    public static function extractItemsWithPrefix(array $permissions): array
    {
        $prefix = config('central.permission_prefix');
        $result = [];

        foreach ($permissions as $permission) {
            if (Str::startsWith($permission['name'], $prefix)) {
                $result[] = Str::after($permission['name'], "{$prefix}.");
            }
        }

        return $result;
    }

    public static function isMatchingSubdomain(array $item, string $subdomain): bool
    {
        return $item['company']['subdomain'] === $subdomain;
    }
}
