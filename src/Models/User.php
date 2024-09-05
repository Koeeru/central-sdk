<?php

namespace Koeeru\Central\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Koeeru\Central\Contracts\PermissionInterface;
use Koeeru\Central\Traits\HasPermission;

class User extends Authenticatable implements PermissionInterface
{
    use HasPermission;

    protected $table = null;

    protected $fillable = [
        'id',
        'name',
        'email',
        'firstName',
        'lastName',
        'status',
        'language',
        'is_immutable',
        'role',
        'permission',
        'emailVerified',
        'companies',
        'companyAssociations',
        'createdAt',
        'updatedAt',
    ];


    public function companies()
    {
        return collect($this->companies)->map(function ($companyData) {
            return config('central.models.company')::make($companyData);
        });
    }
}
