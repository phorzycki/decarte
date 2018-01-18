<?php

namespace OrderBundle\Repository;

use Doctrine\ORM\EntityRepository;
use OrderBundle\Entity\Order;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionOrderRepository
{
    /** @var SessionInterface */
    private $session;

    /** @var EntityRepository */
    private $productRepository;

    /** @var EntityRepository */
    private $realizationTypeRepository;

    /** @var EntityRepository */
    private $deliveryTypeRepository;

    private $order;

    public function __construct(
        SessionInterface $session,
        EntityRepository $productRepository,
        EntityRepository $realizationTypeRepository,
        EntityRepository $deliveryTypeRepository
    ) {
        $this->realizationTypeRepository = $realizationTypeRepository;
        $this->deliveryTypeRepository = $deliveryTypeRepository;
        $this->productRepository = $productRepository;
        $this->session = $session;
    }

    public function getOrder(): Order
    {
        if (!$this->order) {
            $serializedOrder = $this->session->get('order');
            if ($serializedOrder) {
                $this->order = $this->deserialize($serializedOrder);
            } else {
                $this->order = new Order();
            }
        }

        return $this->order;
    }

    protected function deserialize(string $serializedOrder)
    {
        $orderArray = json_decode($serializedOrder, true);
        $order = new Order();

        if ($orderArray) {
            $order
                ->setCity($orderArray['city'])
                ->setEmail($orderArray['email'])
                ->setName($orderArray['name'])
                ->setStreet($orderArray['street'])
                ->setPostalCode($orderArray['postalCode'])
                ->setPhone($orderArray['phone'])
                ->setNotes($orderArray['notes'])
                ->setTotalPrice($orderArray['price']);

            foreach ($orderArray['items'] as $itemArray) {
                $product = $this->productRepository->find($itemArray['productId']);
                $order->addItem($product, $itemArray['quantity'], $itemArray['unitPrice']);
            }

            if ($orderArray['realizationTypeId']) {
                $realizationType = $this->realizationTypeRepository->find($orderArray['realizationTypeId']);
                $realizationType->setPrice($orderArray['realizationPrice']);
                $order->setRealizationType($realizationType);
            }

            if ($orderArray['deliveryTypeId']) {
                $deliveryType = $this->deliveryTypeRepository->find($orderArray['deliveryTypeId']);
                $deliveryType->setPrice($orderArray['deliveryPrice']);
                $order->setDeliveryType($deliveryType);
            }
        }

        return $order;
    }

    public function persist(Order $order)
    {
        $this->session->set('order', json_encode($order));
    }

    public function clear()
    {
        $this->session->remove('order');
    }
}
