<?php

namespace Koeeru\Central\Helpers;

use Illuminate\Http\Request;

class ExtractSubdomain
{
    public static function extract(Request $request): ?string
    {
        $host = $request->getHost();

        if (empty($host)) {
            return null;
        }

        $subdomains = explode('.', $host);

        if (count($subdomains) < 2) {
            return null;
        }

        return $subdomains[0];
    }
}
