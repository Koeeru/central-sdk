<?php

namespace Koeeru\Central\Enums;

enum CacheDefinitions: string
{
    case CENTRAL_COMPANIES_ALL = 'central:companies:all';
    case CENTRAL_COMPANIES_LIST = 'central:companies:list';
    case CENTRAL_COMPANIES_IDENTIFIER = 'central:companies:identifier';
}
