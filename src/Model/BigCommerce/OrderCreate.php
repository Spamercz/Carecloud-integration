<?php
declare(strict_types = 1);

namespace App\Model\BigCommerce;

use App\Model\CareCloud\CareCloudClient;
use App\Model\OrderCreateInterface;
use CrmCareCloud\Webservice\RestApi\Client\ApiException;
use CrmCareCloud\Webservice\RestApi\Client\Model\Customer;
use CrmCareCloud\Webservice\RestApi\Client\Model\OrderInvoicing;
use CrmCareCloud\Webservice\RestApi\Client\Model\OrderItem;
use CrmCareCloud\Webservice\RestApi\Client\Model\OrdersBody;
use InvalidArgumentException;
use Nette\Utils\JsonException;
use Nette\Utils\Json;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use GuzzleHttp\Psr7\Response;

final class OrderCreate implements OrderCreateInterface
{

    public function __construct(
        private readonly CareCloudClient $careCloudClient,
    ) {}

    // TODO - This is just suggestion how it should work
    public function process(RequestInterface $request): ResponseInterface
    {
        $order = $this->extractData($request);
        $bigCommerceUser = $this->getBigCommerceUser($order);
        $careCloudUser = $this->findCareCloudUser($bigCommerceUser);

        $careCloudOrder = $this->createCareCloudOrder($order, $careCloudUser);

        return new Response(
            body: $this->callApi($careCloudOrder)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function extractData(RequestInterface $request): Order
    {
        try {
            $body = $request->getBody()->getContents();
            $decoded = Json::decode($body);

            // TODO extract code to factory
            return new Order(
                $decoded->orderId,
                $decoded->cartId,
                $decoded->currency,
                $decoded->baseAmount,
                $decoded->discountAmount,
            );

        } catch (JsonException $jsonException) {
            throw new InvalidArgumentException($jsonException->getMessage());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function callApi(OrdersBody $ordersBody): bool
    {
        try {
            $this->careCloudClient->provide()
                ->ordersApi()
                ->postOrder($ordersBody)
            ;

            return TRUE;

        } catch (ApiException $apiException) {
            throw new InvalidArgumentException($apiException->getMessage());
        }
    }

    private function getBigCommerceUser(Order $order): stdClass
    {
        // TODO call BigCommerce API to get customer by ID
        return new stdClass($order->customerId);
    }

    // TODO if this was extracted according to SRP i could use it in UserUpdate and not duplicate code
    private function findCareCloudUser(stdClass $bigCommerceUser): Customer
    {
        return $this->careCloudClient->provide()
            ->customersApi()
            ->getCustomer($bigCommerceUser->customFields[0]->fieldValue)
            ->getData()
            ;
    }

    private function createCareCloudOrder(Order $order, Customer $careCloudUser): OrdersBody
    {
        // TODO foreach for items
        $orderItem = new OrderItem();
        $orderItem->setProductVariantId('8fcc724e1514dafb0a70228d3')
            ->setAmount(1)
            ->setUnitPrice(36)
            ->setVatRate(16);

        $orderItems[] = $orderItem;

        $invoicingData = new OrderInvoicing();
        $invoicingData->setPaymentId('8bd481170064960b1788109b8');

        $careCloudOrder = new \CrmCareCloud\Webservice\RestApi\Client\Model\Order();
        $careCloudOrder->setCustomerId($careCloudUser->getCustomerId())
            ->setCurrencyId('86e05affc7a7abefcd513ab400')
            ->setTotalPrice($order->baseAmount)
            ->setOrderItems($orderItems)
            ->setInvoicingData($invoicingData);

        $body = new OrdersBody();
        $body->setOrder($careCloudOrder);

        return $body;
    }

}
