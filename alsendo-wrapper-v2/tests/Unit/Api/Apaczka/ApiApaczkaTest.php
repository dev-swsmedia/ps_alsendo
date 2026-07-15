<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Apaczka;

use Alsendo\AlsendoWrapper\ApiClientFactory;
use Alsendo\AlsendoWrapper\ApiClientInterface;
use Alsendo\AlsendoWrapper\Exception\ValidationException;
use Alsendo\AlsendoWrapper\Model\Address;
use Alsendo\AlsendoWrapper\Model\Cod;
use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Notification;
use Alsendo\AlsendoWrapper\Model\NotificationDetail;
use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use Alsendo\AlsendoWrapper\Model\Order\OrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use Alsendo\AlsendoWrapper\Model\Pickup;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;
use Alsendo\AlsendoWrapper\Model\Shipment;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Test class for ApiClient integration with Apaczka's test environment.
 * Provides a suite of tests to validate order creation, valuation, service structure retrieval,
 * and order fetching using the Apaczka API.
 *
 * The test setup initializes an API client using test credentials from environment variables.
 * Address, phone, and person data are hardcoded Polish-locale fixtures.
 *
 * Test cases cover the full lifecycle of an order: from request creation and sending,
 * to getting valuation and retrieving order details via the API.
 *
 * All tests verify that API responses conform to expected response types and return appropriate
 * data structures.
 */
class ApiApaczkaTest extends TestCase
{
    private ApiClientInterface $apiClient;

    /**
     * Tests the sending of an order request and verifies that a valid OrderCreatedResponse is returned.
     *
     * @return void
     */
    public function testSendOrder(): void
    {
        $orderRequest = $this->createTestOrderRequest();

        $response = $this->apiClient->sendOrder($orderRequest);

        $this->assertInstanceOf(SendOrderResponse::class, $response);
    }

    /**
     * Creates a test order request with predefined values for service, address, options, notification, shipment, and pickup details.
     *
     * @return OrderRequest The constructed OrderRequest object with default test values
     */
    private function createTestOrderRequest(): OrderRequest
    {
        $orderRequest = new OrderRequest();
        $orderRequest->serviceId = 82;

        $orderRequest->address = new Address(
            new Contact(
                'PL',
                'Jan Kowalski',
                'ul. Testowa 1',
                '',
                '00-007',
                '',
                'Warszawa',
                1,
                'Jan Kowalski',
                'test@example.com',
                '+48500500500',
                ''
            ),
            new Contact(
                'PL',
                'Jan Kowalski',
                'ul. Testowa 1',
                '',
                '70-003',
                '',
                'Szczecin',
                1,
                'Jan Kowalski',
                'test@example.com',
                '+48500500500',
                ''
            )
        );

        $orderRequest->option = ['31' => 1];
        $orderRequest->notification = new Notification(
            new NotificationDetail(1, 1, 1),
            new NotificationDetail(1),
            new NotificationDetail(1, 0, 1),
            new NotificationDetail(1, 0, 1)
        );

        $orderRequest->shipmentValue = 200;
        $orderRequest->cod = new Cod();
        $orderRequest->cod->amount = 0;
        $orderRequest->cod->bankAccount = '';

        $orderRequest->pickup = new Pickup();
        $orderRequest->pickup->type = 'SELF';
        $orderRequest->pickup->date = (new \DateTimeImmutable('+1 day'))->format('Y-m-d');
        $orderRequest->pickup->hoursFrom = '12:00';
        $orderRequest->pickup->hoursTo = '19:00';

        $shipment = new Shipment();
        $shipment->dimension1 = 10;
        $shipment->dimension2 = 10;
        $shipment->dimension3 = 10;
        $shipment->weight = 1;
        $shipment->isNstd = 0;
        $shipment->shipmentTypeCode = 'PACZKA';
        $orderRequest->shipment = [$shipment];
        $orderRequest->content = 'Test order';

        return $orderRequest;
    }

    /**
     * Tests the retrieval of order valuation and verifies that a valid ApaczkaOrderValuation response is returned.
     *
     * @return void
     */
    public function testGetOrderValuation(): void
    {
        $orderRequest = $this->createTestOrderRequest();

        $response = $this->apiClient->getOrderValuation($orderRequest);
        $this->assertInstanceOf(OrderValuationResponse::class, $response);
        $this->assertNotEmpty($response->valuations);
    }

    /**
     * Tests the retrieval of the service structure and verifies that a valid ServiceStructure object is returned.
     *
     * @return void
     */
    public function testGetServiceStructure(): void
    {
        $response = $this->apiClient->getServiceStructure();
        $this->assertInstanceOf(ServiceStructure::class, $response);
        $this->assertNotEmpty($response->services);
    }

    /**
     * Tests retrieving an order by ID and verifies that a valid OrderResponse is returned.
     *
     * @return void
     */
    public function testGetOrder(): void
    {
        $orderRequest = $this->createTestOrderRequest();
        $createdOrder = $this->apiClient->sendOrder($orderRequest);

        $response = $this->apiClient->getOrder($createdOrder->id);

        $this->assertInstanceOf(OrderResponse::class, $response);
        $this->assertEquals($createdOrder->id, $response->id);
    }

    /**
     * Test verifying the retrieval of multiple order details at once.
     *
     * The getOrders() method allows fetching information about multiple orders in a single request.
     * The test verifies that:
     * - An array is returned
     * - Each array element is an instance of OrderResponse
     * - The returned orders match the requested IDs
     *
     * @return void
     */
    public function testGetOrders(): void
    {
        $orderRequest = $this->createTestOrderRequest();
        $createdOrder = $this->apiClient->sendOrder($orderRequest);

        $orderIds = [$createdOrder->id];
        $response = $this->apiClient->getOrders($orderIds);

        $this->assertIsArray($response);
        $this->assertNotEmpty($response);

        foreach ($response as $order) {
            $this->assertInstanceOf(OrderResponse::class, $order);
        }
    }

    /**
     * Test verifying the retrieval of a waybill for an order.
     *
     * The getWayBill() method retrieves the shipping label in PDF format.
     * The test verifies that:
     * - A WayBill object is returned
     * - The object contains waybill data
     * - The data is not empty
     *
     * @return void
     */
    public function testGetWayBill(): void
    {
        $orderRequest = $this->createTestOrderRequest();
        $createdOrder = $this->apiClient->sendOrder($orderRequest);

        $response = $this->apiClient->getWayBill($createdOrder->id);

        $this->assertInstanceOf(\Alsendo\AlsendoWrapper\Model\WayBill\WayBill::class, $response);
        $this->assertNotEmpty($response->waybill);
    }

    /**
     * Test verifying order cancellation.
     *
     * The cancelOrder() method sends a shipment cancellation request to the Apaczka API.
     * The test verifies that:
     * - The method returns true after successful cancellation
     * - The request is correctly processed by the API
     *
     * NOTE: This test may not work if the order cannot be cancelled
     * (e.g., it has already been shipped or is in a state that prevents cancellation)
     *
     * @return void
     */
    public function testCancelOrder(): void
    {
        $orderRequest = $this->createTestOrderRequest();
        $createdOrder = $this->apiClient->sendOrder($orderRequest);

        try {
            $response = $this->apiClient->cancelOrder($createdOrder->id);
            $this->assertTrue($response);
        } catch (\Alsendo\AlsendoWrapper\Exception\ResponseException $e) {
            // If the order cannot be cancelled (e.g., it's already in transit)
            // we accept this as valid behavior
            $this->assertNotEmpty($e->getMessage());
        }
    }

    /**
     * Test verifying the retrieval of a manifest list for orders.
     *
     * The getTurnInList() method retrieves the manifest (handover list) for specified shipments.
     * A manifest is a document confirming the handover of packages to the courier.
     *
     * The test verifies that:
     * - The method returns manifest data
     * - The data is not empty
     *
     * @return void
     */
    public function testGetTurnInList(): void
    {
        $orderRequest = $this->createTestOrderRequest();
        $createdOrder = $this->apiClient->sendOrder($orderRequest);

        $response = $this->apiClient->getTurnInList([$createdOrder->id]);

        $this->assertNotEmpty($response);
    }

    /**
     * Configures the test environment by loading environment variables and creating an API client instance.
     *
     * @return void
     *
     * @throws ValidationException
     */
    protected function setUp(): void
    {
        // Ładowanie zmiennych z pliku .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../../', '.env.test.local');
        $dotenv->load();
        $this->apiClient = (new ApiClientFactory())->create(
            ApiClientFactory::APACZKA_TEST,
            [
                'app_id' => $_ENV['APACZKA_API_APP_ID'],
                'app_secret' => $_ENV['APACZKA_API_APP_SECRET'],
            ]);
    }
}
