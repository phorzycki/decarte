<?php

declare(strict_types=1);

namespace Decarte\Shop\Tests;

use Decarte\Shop\Entity\Order\DeliveryType;
use Decarte\Shop\Entity\Order\Order;
use Decarte\Shop\Entity\Order\RealizationType;
use Decarte\Shop\Entity\Product\Product;
use Decarte\Shop\Entity\Product\ProductCollection;
use Decarte\Shop\Entity\Product\ProductType;
use PHPUnit\Framework\TestCase;

abstract class AbstractOrderTest extends TestCase
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Product[]
     */
    protected $products;

    protected function setUp(): void
    {
        $this->products = [];

        $this->products[0] = new Product();
        $this->products[0]->setId(1)->setPrice(20)->setProductCollection($this->buildProductCollection(1));

        $this->products[1] = new Product();
        $this->products[1]->setId(2)->setPrice(30)->setProductCollection($this->buildProductCollection(2));

        $this->order = new Order();
        $this->order
            ->setRealizationType($this->getRealizationType())
            ->setDeliveryType($this->getDeliveryType())
            ->setCity('Gdańsk')
            ->setEmail('a@b.pl')
            ->setName('Jan Kowalski')
            ->setNotes('Xxx')
            ->setTaxId('12345')
            ->setPhone('111222333')
            ->setPostalCode('80-534')
            ->setStreet('Starowiejska')
            ->addItem($this->products[0], 1, $this->products[0]->getPrice())
            ->addItem($this->products[1], 2, $this->products[1]->getPrice());
    }

    protected function getJsonEncodedOrder(): string
    {
        return trim(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'order.json'));
    }

    protected function getRealizationType(): RealizationType
    {
        $realizationType = new RealizationType();
        $realizationType
            ->setId(2)
            ->setPrice(15);

        return $realizationType;
    }

    protected function getDeliveryType(): DeliveryType
    {
        $deliveryType = new DeliveryType();
        $deliveryType
            ->setId(1)
            ->setPrice(10);

        return $deliveryType;
    }

    protected function buildProductCollection(int $minimumQuantity): ProductCollection
    {
        $productType = new ProductType();
        $productCollection = new ProductCollection();
        $productCollection
            ->setProductType($productType)
            ->setMinimumQuantity($minimumQuantity);

        return $productCollection;
    }
}
