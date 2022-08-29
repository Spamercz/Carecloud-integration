<?php

declare(strict_types = 1);

namespace App\Model\BigCommerce;

use App\Model\CareCloud\CareCloudClient;
use App\Model\UserCreateInterface;
use CrmCareCloud\Webservice\RestApi\Client\ApiException;
use CrmCareCloud\Webservice\RestApi\Client\Model\Card;
use CrmCareCloud\Webservice\RestApi\Client\Model\Customer;
use CrmCareCloud\Webservice\RestApi\Client\Model\CustomersBody;
use CrmCareCloud\Webservice\RestApi\Client\Model\CustomerSourceRecord;
use CrmCareCloud\Webservice\RestApi\Client\Model\PersonalInformation;
use Exception;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class UserCreate implements UserCreateInterface
{

    public function __construct(
        private readonly CareCloudClient $careCloudClient,
        private readonly BigCommerceUserFactory $bigCommerceUserFactory,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function process(RequestInterface $request): ResponseInterface
    {
        $bigCommerceUser = $this->extractData($request);

        // TODO To achieve SRP every method should have own class with interface
        $personalInformation = $this->createPersonalInformation($bigCommerceUser);
        $customerSource = $this->createCustomerSourceRecord($bigCommerceUser);
        $customer = $this->createCustomer($personalInformation);
        $customerBody = $this->getCustomerBody($customer, $customerSource);
        $card = $this->getCard();

        $response = $this->callApi($customerBody, $card);

        $this->updateBigCommerceUser($bigCommerceUser, $response);

        return new Response(
            body: Json::encode($response)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function extractData(RequestInterface $request): BigCommerceUser
    {
        try {
            $body = $request->getBody()->getContents();
            $decoded = Json::decode($body);

            return $this->bigCommerceUserFactory->create($decoded);

        } catch (JsonException $jsonException) {
            throw new InvalidArgumentException($jsonException->getMessage());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function callApi($customerBody, $card): array
    {
        try {
            return $this->careCloudClient->provide()
                ->customersApi()
                ->postCustomerExtended($customerBody, $card)
            ;

        } catch (ApiException | Exception $apiException) {
            throw new InvalidArgumentException($apiException->getMessage());
        }
    }

    private function updateBigCommerceUser(BigCommerceUser $bigCommerceUser, array $response): void
    {
        $bigCommerceUser->customFields[] = [
            'fieldId' => 'CareCloud',
            'fieldValue' => $response['customer_id'],
        ];

        // TODO call BigCommerceAPI to save custom value with ID
    }

    private function createPersonalInformation(BigCommerceUser $bigCommerceUser): PersonalInformation
    {
        $personalInformation = new PersonalInformation();
        $personalInformation->setFirstName($bigCommerceUser->firstName);
        $personalInformation->setLastName($bigCommerceUser->lastName);
        $personalInformation->setEmail($bigCommerceUser->email);
        $personalInformation->setBirthdate($bigCommerceUser->dateOfBirth);

        return $personalInformation;
    }

    private function createCustomerSourceRecord(BigCommerceUser $bigCommerceUser): CustomerSourceRecord
    {
        $customerSource = new CustomerSourceRecord();
        $customerSource->setCustomerSourceId(Config::CUSTOMER_SOURCE_ID);
        $customerSource->setExternalId($bigCommerceUser->id);

        return $customerSource;
    }

    private function createCustomer(PersonalInformation $personalInformation): Customer
    {
        $customer = new Customer();
        $customer->setPersonalInformation($personalInformation);

        return $customer;
    }

    private function getCustomerBody(Customer $customer, CustomerSourceRecord $customerSource): CustomersBody
    {
        $customerBody = new CustomersBody();
        $customerBody->setCustomer($customer);
        $customerBody->setCustomerSource($customerSource);

        return $customerBody;
    }

    private function getCard(): Card
    {
        // TODO User does not have card, but sdk must receive some card
        $card = new Card();
        $card->setCardNumber('1000000000');
        $card->setCardTypeId('8bed991c68a470e7aaeffbf048');
        $card->setValidFrom('2021-11-01');
        $card->setValidTo('2023-11-02');
        $card->setStoreId(null);

        return $card;
    }

}
