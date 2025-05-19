<?php

namespace Koeeru\Central\Services;

use Koeeru\Central\ApiCaller;
use Koeeru\Central\EndpointManager;
use Illuminate\Http\Request;

class CompanyService
{
    protected ApiCaller $apiCaller;

    protected EndpointManager $endpointManager;

    public function __construct()
    {
        $request = app(Request::class);
        $this->apiCaller = new ApiCaller(request: $request, validateBearerToken: false);
        $this->endpointManager = new EndpointManager();
    }


    public function getListCompany()
    {
        try {
            $companies = $this->apiCaller->get(
                $this->endpointManager->getListCompanyEndpoint()
            );

            if (empty($companies)) {
                return null;
            }

            $companyModelClass = config('central.models.company');
            $companyTransformedClass = config('central.transformers.company');

            if (!class_exists($companyModelClass)) {
                throw new \RuntimeException("Company model class '{$companyModelClass}' not found.");
            }

            if(class_exists($companyTransformedClass)) {
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
}
