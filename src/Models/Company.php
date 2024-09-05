<?php

namespace Koeeru\Central\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    protected $table = null;

    protected $fillable = [
        'id',
        'externalId',
        'name',
        'description',
        'code',
        'subdomain',
        'createdAt',
        'updatedAt',
    ];

    public function getSubdomain()
    {
        $host = request()->getHost();

        return "{$this->subdomain}.{$host}";
    }

}
