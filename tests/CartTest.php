<?php

use AppBundle\Entity\Cart;
use AppBundle\Entity\CartItem;
use AppBundle\Entity\Product;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    /**
     * @var Cart
     */
    protected $cart;

    protected function setUp()
    {
        $product1 = new Product();
        $product1->setId(1)->setPrice(20);
        $item1 = new CartItem($product1);
        $item1->setQuantity(1);

        $product2 = new Product();
        $product2->setId(2)->setPrice(30);
        $item2 = new CartItem($product2);
        $item2->setQuantity(2);

        $this->cart = new Cart(1234);
        $this->cart
            ->addItem($item1)
            ->addItem($item2);
    }

    public function testCartId()
    {
        $this->assertEquals(1234, $this->cart->getId());

        $this->cart->setId(1337);
        $this->assertEquals(1337, $this->cart->getId());
    }

    public function testAddItems()
    {
        $this->assertEquals(2, count($this->cart->getItems()));
        $this->assertEquals(1, $this->cart->getItem(1)->getId());
        $this->assertEquals(2, $this->cart->getItem(2)->getId());
    }

    public function testRemoveItem()
    {
        $lastItem = $this->cart->getItem(2);
        $this->cart->removeItem($lastItem);

        $this->assertEquals(1, count($this->cart->getItems()));
        $this->assertEquals(1, $this->cart->getItem(1)->getId());
    }

    public function testTotalPrice()
    {
        $this->assertEquals(80, $this->cart->getTotalPrice());
    }
}
