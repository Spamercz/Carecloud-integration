<?php
declare(strict_types = 1);

namespace App\Model\BigCommerce;

use App\Model\CareCloud\CareCloudClient;
use App\Model\UserUpdateInterface;
use CrmCareCloud\Webservice\RestApi\Client\ApiException;
use CrmCareCloud\Webservice\RestApi\Client\Model\Customer;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

final class UserUpdate implements UserUpdateInterface
{

    public function __construct(
        private readonly CareCloudClient $careCloudClient,
    ) {}

    // TODO - This is just suggestion how it should work
    public function process(RequestInterface $request): ResponseInterface
    {
        $bigCommerceUser = $this->extractData($request);

        $careCloudUser = $this->findCareCloudUser($bigCommerceUser);
        $careCloudUser = $this->updateCareCouldUser($careCloudUser, $bigCommerceUser);

        return new Response(
            body: $this->callApi($careCloudUser, $careCloudUser->getCustomerId()) ? 'Success' : 'Failure'
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function extractData(RequestInterface $request): stdClass
    {
        try {
            $body = $request->getBody()->getContents();
            return Json::decode($body);

        } catch (JsonException $jsonException) {
            throw new InvalidArgumentException($jsonException->getMessage());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function callApi($customerBody, $customerId): bool
    {
        try {
            $this->careCloudClient->provide()
                ->customersApi()
                ->putCustomer($customerBody, $customerId)
            ;

            return TRUE;

        } catch (ApiException $apiException) {
            throw new InvalidArgumentException($apiException->getMessage());
        }
    }

    // TODO if this was extracted according to SRP i could use it in OrderCreate  and not duplicate code
    private function findCareCloudUser(stdClass $bigCommerceUser): Customer
    {
        return $this->careCloudClient->provide()
            ->customersApi()
            ->getCustomer($bigCommerceUser->customFields[0]->fieldValue)
            ->getData()
        ;
    }

    private function updateCareCouldUser(Customer $careCloudUser, stdClass $bigCommerceUser): Customer
    {
        if (isset($bigCommerceUser->firstName)) {
            $careCloudUser->getPersonalInformation()->setFirstName($bigCommerceUser->firstName);
        }
        if (isset($bigCommerceUser->lastName)) {
            $careCloudUser->getPersonalInformation()->setLastName($bigCommerceUser->lastName);
        }

        return $careCloudUser;
    }

}
