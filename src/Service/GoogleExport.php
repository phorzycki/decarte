<?php

namespace Decarte\Shop\Service;

use Decarte\Shop\Entity\Product\Product;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleExport
{
    private $router;
    private $imagineCacheManager;
    private $canonicalDomain;
    private $productImagesDirectory;
    private $merchantId;
    private $shoppingService;

    public function __construct(
        UrlGeneratorInterface $router,
        CacheManager $imagineCacheManager,
        string $canonicalDomain,
        string $productImagesDirectory,
        ?string $merchantId,
        ?string $googlePrivateKey
    ) {
        if (empty($merchantId) || empty($googlePrivateKey)) {
            return;
        }

        $client = new \Google_Client();
        $client->setApplicationName('decARTe');
        $client->setAuthConfig(json_decode($googlePrivateKey, true));
        $client->setScopes(\Google_Service_ShoppingContent::CONTENT);

        $this->router = $router;
        $this->imagineCacheManager = $imagineCacheManager;
        $this->canonicalDomain = $canonicalDomain;
        $this->productImagesDirectory = $productImagesDirectory;
        $this->merchantId = $merchantId;
        $this->shoppingService = new \Google_Service_ShoppingContent($client);
    }

    public function exportProduct(Product $product)
    {
        if (!$this->shoppingService) {
            return;
        }

        $exportedProduct = $this->transformProduct($product);
        $response = $this->shoppingService->products->insert($this->merchantId, $exportedProduct);

        return $response;
    }

    public function exportProductsCollection($products)
    {
        if (!$this->shoppingService) {
            return;
        }

        $client = $this->shoppingService->getClient();
        $client->setUseBatch(true);
        $batch = new \Google_Http_Batch($client);

        foreach ($products as $product) {
            $request = $this->exportProduct($product);
            $batch->add($request);
        }

        return $batch->execute();
    }

    public function deleteProduct(Product $product)
    {
        if (!$this->shoppingService) {
            return;
        }

        return $this->shoppingService->products->delete($this->merchantId, $this->buildProductId($product->getId()));
    }

    protected function buildProductId(int $id): string
    {
        return sprintf('%s:%s:%s:%s', 'online', 'pl', 'PL', $id);
    }

    protected function transformProduct(Product $product): \Google_Service_ShoppingContent_Product
    {
        $exportedProduct = new \Google_Service_ShoppingContent_Product();
        $exportedProduct->setOfferId($product->getId());
        $exportedProduct->setChannel('online');
        $exportedProduct->setContentLanguage('pl');
        $exportedProduct->setTargetCountry('PL');

        $exportedProduct->setTitle($product->getProductCollection()->getName() . ' - ' . $product->getName());
        $exportedProduct->setProductType(join(' > ', [
            $product->getProductCollection()->getProductType()->getName(),
            $product->getProductCollection()->getName(),
        ]));
        $exportedProduct->setPrice($this->getPrice($product));
        $exportedProduct->setAvailability('preorder');
        $exportedProduct->setDescription(strip_tags($product->getDescriptionSEO()));
        $exportedProduct->setBrand('decARTe');
        $exportedProduct->setCondition('new');

        $exportedProduct->setLink($this->canonicalDomain . $this->router->generate(
            'shop_view_product',
            [
                'type' => $product->getProductCollection()->getProductType()->getSlugName(),
                'slugName' => $product->getProductCollection()->getSlugName(),
                'id' => $product->getId(),
            ]
        ));

        $exportedProduct->setImageLink($this->getCanonicalImageUrl($product));

        return $exportedProduct;
    }

    protected function getPrice(Product $product): \Google_Service_ShoppingContent_Price
    {
        $money = Money::PLN($product->getPrice());
        $formatter = new DecimalMoneyFormatter(new ISOCurrencies());

        return new \Google_Service_ShoppingContent_Price([
            'currency' => $money->getCurrency()->getCode(), 'value' => $formatter->format($money)
        ]);
    }

    protected function getCanonicalImageUrl(Product $product): string
    {
        $absoluteLink = $this->imagineCacheManager->getBrowserPath(
            '/' . $this->productImagesDirectory . '/' . $product->getCoverImage()->getImageName(),
            'product_full'
        );

        $urlParts = parse_url($absoluteLink);

        return $this->canonicalDomain . $urlParts['path'];
    }
}