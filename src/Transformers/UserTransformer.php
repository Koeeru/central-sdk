<?php

namespace Koeeru\Central\Transformers;

use Koeeru\Central\Contracts\TransformerInterface;
use Koeeru\Central\Helpers\AttributeConverter;
use Koeeru\Central\Helpers\ExternalIdFinder;
use Koeeru\Central\Traits\PermissionHelpers;

class UserTransformer implements TransformerInterface
{
    use PermissionHelpers;
    public static function transform(array $item): array
    {
        $externalId = isset($item['mapping']) ? ExternalIdFinder::find($item['mapping'], config('central.app_id')) : null;
        $userAttributes = isset($item['attributes']) ? AttributeConverter::convert($item['attributes']) : [];
        $companies = isset($item['companyAssociations']) ? array_map(fn ($val) => $val['company'], $item['companyAssociations']) : [];

        return array_merge([
            'id' => $externalId ?? $item['id'],
            'name' => $item['name'],
            'email' => $item['email'],
            'firstName' => $item['firstName'],
            'lastName' => $item['lastName'],
            'status' => $item['status'],
            'role' => static::getRolesOrPermissions($item['companyAssociations']),
            'permission' => static::getRolesOrPermissions($item['companyAssociations'], 'permissions'),
            'emailVerified' => $item['emailVerified'],
            'companies' => $companies,
            'companyAssociations' => $item['companyAssociations'],
            'createdAt' => $item['createdAt'],
            'updatedAt' => $item['updatedAt'],
        ], $userAttributes);
    }
}
