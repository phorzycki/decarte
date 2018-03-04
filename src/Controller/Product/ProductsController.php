<?php

namespace Decarte\Shop\Controller\Product;

use Decarte\Shop\Repository\Product\ProductCollectionRepository;
use Decarte\Shop\Repository\Product\ProductRepository;
use Decarte\Shop\Repository\Product\ProductTypeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProductsController extends Controller
{
    /**
     * @Route("/sklep/{type}", name="shop_list_collections", requirements={"type": "[0-9a-z\-]+"})
     * @param string $type
     * @return Response
     */
    public function listCollectionsAction(
        string $type,
        ProductTypeRepository $productTypeRepository,
        ProductCollectionRepository $productCollectionRepository
    ): Response {
        $productType = $productTypeRepository->findBySlugName($type);
        if (!$productType) {
            throw $this->createNotFoundException('Nie znaleziono produktów tego typu');
        }

        $productCollections = $productCollectionRepository->getProductCollections($productType->getId());
        if (!$productCollections) {
            throw $this->createNotFoundException('Nie znaleziono produktów tego typu');
        }

        return $this->render('shop/list-collections.html.twig', [
            'productType' => $productType,
            'productCollections' => $productCollections,
        ]);
    }

    /**
     * @Route(
     *     "/sklep/{type}/{slugName}",
     *     name="shop_view_collection",
     *     requirements={"type": "[0-9a-z\-]+", "slugName": "[a-z0-9\-]+"}
     * )
     * @param string $type
     * @param string $slugName
     * @return Response
     */
    public function viewCollectionAction(
        string $type,
        string $slugName,
        ProductCollectionRepository $productCollectionRepository
    ): Response {
        $productCollection = $productCollectionRepository->findBySlugName($type, $slugName);
        if (!$productCollection) {
            throw $this->createNotFoundException('Nie znaleziono kolekcji produktów');
        }

        $productType = $productCollection->getProductType();
        $allCollections = $productCollectionRepository->getProductCollections($productType->getId());

        return $this->render('shop/view-collection.html.twig', [
            'productCollection' => $productCollection,
            'allCollections' => $allCollections,
        ]);
    }

    /**
     * @Route(
     *     "/sklep/{type}/{slugName}/{id}",
     *     name="shop_view_product",
     *     requirements={"type": "[0-9a-z\-]+", "slugName": "[a-z0-9\-]+", "id": "\d+"}
     * )
     * @param string $type Used only for SEO.
     * @param string $slugName Used only for SEO.
     * @param int $id
     * @return Response
     */
    public function viewProductAction(
        string $type,
        string $slugName,
        int $id,
        ProductRepository $productRepository
    ): Response {
        $product = $productRepository->find($id);
        if (!$product || !$product->isVisible()) {
            throw $this->createNotFoundException('Nie znaleziono produktu');
        }

        $previousProduct = $productRepository->findPrevious($product);
        $nextProduct = $productRepository->findNext($product);
        $previousPath = null;
        $nextPath = null;

        if ($previousProduct) {
            $previousPath = $this->generateUrl('shop_view_product', [
                'type' => $previousProduct->getProductCollection()->getProductType()->getSlugName(),
                'slugName' => $previousProduct->getProductCollection()->getSlugName(),
                'id' => $previousProduct->getId(),
            ]);
        }

        if ($nextProduct) {
            $nextPath = $this->generateUrl('shop_view_product', [
                'type' => $nextProduct->getProductCollection()->getProductType()->getSlugName(),
                'slugName' => $nextProduct->getProductCollection()->getSlugName(),
                'id' => $nextProduct->getId(),
            ]);
        }

        return $this->render('shop/view-product.html.twig', [
            'product' => $product,
            'previousPath' => $previousPath,
            'nextPath' => $nextPath,
            'previousUrl' => $previousPath ? $this->getParameter('canonical_domain') . $previousPath : null,
            'nextUrl' => $nextPath ? $this->getParameter('canonical_domain') . $nextPath : null,
        ]);
    }
}