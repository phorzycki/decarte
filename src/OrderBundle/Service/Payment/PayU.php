<?php

namespace OrderBundle\Service\Payment;

use OrderBundle\Entity\Order;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class PayU
{
    protected $continueUrl;
    protected $notifyUrl;

    public function __construct(
        string $environment,
        string $posId,
        string $signatureKey,
        string $clientId,
        string $clientSecret,
        string $continueUrl,
        string $notifyUrl
    ) {
        \OpenPayU_Configuration::setEnvironment($environment);
        \OpenPayU_Configuration::setMerchantPosId($posId);
        \OpenPayU_Configuration::setSignatureKey($signatureKey);
        \OpenPayU_Configuration::setOauthClientId($clientId);
        \OpenPayU_Configuration::setOauthClientSecret($clientSecret);
    }

    public function createOrder(Order $order): Response
    {
        $orderData = [
            'continueUrl' => $this->continueUrl,
            'notifyUrl' => $this->notifyUrl,
            'customerIp' => $_SERVER['REMOTE_ADDR'],
            'merchantPosId' => \OpenPayU_Configuration::getMerchantPosId(),
            'description' => 'New order',
            'currencyCode' => 'PLN',
            'totalAmount' => $order->getTotalPrice(),
            'extOrderId' => $order->getId(),
            'products' => [],
        ];

        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $orderData['products'][] = [
                'name' => join(' - ', [$product->getProductCollection()->getName(), $product->getName()]),
                'unitPrice' => $item->getUnitPrice(),
                'quantity' => $item->getQuantity(),
            ];
        }

        $payUResponse = \OpenPayU_Order::create($orderData);

        return new RedirectResponse($payUResponse->getResponse()->redirectUri);
    }
}
