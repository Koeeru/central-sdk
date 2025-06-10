<?php

namespace Koeeru\Central\Services;

use Illuminate\Support\Facades\Cache;
use Koeeru\Central\ApiCaller;
use Koeeru\Central\EndpointManager;
use Illuminate\Http\Request;
use Koeeru\Central\Enums\CacheDefinitions;

class CompanyService
{
    protected ApiCaller $apiCaller;

    protected EndpointManager $endpointManager;

    public function __construct()
    {
        $request = app(Request::class);
        $this->apiCaller = new ApiCaller(request: $request, validateBearerToken: true);
        $this->endpointManager = new EndpointManager();
    }

    public function all(): array
    {
        try {
            $response = Cache::remember(
                CacheDefinitions::CENTRAL_COMPANIES_ALL->value,
                config('central.cache.ttl'),
                function () {
                    return collect($this->apiCaller->get(
                        $this->endpointManager->getListCompanyEndpoint()
                    ));
                });

            $companies = $response->get('data');

            $companyTransformedClass = config('central.transformers.company');

            if (class_exists($companyTransformedClass)) {
                $companies = array_map(function ($companyData) use ($companyTransformedClass) {
                    return $companyTransformedClass::transform($companyData);
                }, $companies);
            }

            return $companies;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error fetching company list: ' . $e->getMessage());
        }
    }


    public function getListCompany()
    {
        try {
            $response = Cache::remember(
                CacheDefinitions::CENTRAL_COMPANIES_LIST->value,
                config('central.cache.ttl'),
                function () {
                    return collect($this->apiCaller->get(
                        $this->endpointManager->getListCompanyEndpoint()
                    ));
                });

            $companies = $response->get('data');

            if (empty($companies)) {
                return null;
            }

            $companyModelClass = config('central.models.company');
            $companyTransformedClass = config('central.transformers.company');

            if (!class_exists($companyModelClass)) {
                throw new \RuntimeException("Company model class '{$companyModelClass}' not found.");
            }

            if (class_exists($companyTransformedClass)) {
                $companies = array_map(function ($companyData) use ($companyTransformedClass) {
                    return $companyTransformedClass::transform($companyData);
                }, $companies);
            }

            // convert the array of company data into an array of model instances
            return $companyModelClass::hydrate($companies);

        } catch (\Exception $e) {
            throw new \RuntimeException("Error retrieving user from central server: {$e->getMessage()}");
        }
    }

    public function getCompany(string $codeOrIdOrDbNameOrSubdomain = null)
    {
        try {
            $response = Cache::remember(
                CacheDefinitions::CENTRAL_COMPANIES_ALL->value . "_{$codeOrIdOrDbNameOrSubdomain}",
                config('central.cache.ttl'),
                function () use ($codeOrIdOrDbNameOrSubdomain) {
                    return collect($this->apiCaller->get(
                        $this->endpointManager->getCompanyEndpoint($codeOrIdOrDbNameOrSubdomain)
                    ));
                });

            $company = $response->get('data');

            $companyModelClass = config('central.models.company');
            $companyTransformedClass = config('central.transformers.company');

            if (!class_exists($companyModelClass)) {
                throw new \RuntimeException("Company model class '{$companyModelClass}' not found.");
            }

            if (class_exists($companyTransformedClass)) {
                $company = $companyTransformedClass::transform($company);
            }

            return $companyModelClass::make($company);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error retrieving company from central server: {$e->getMessage()}");
        }
    }
}

