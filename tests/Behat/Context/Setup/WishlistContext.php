<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusWishlistPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistProductFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\Setter\CookieSetterInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class WishlistContext implements Context
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private WishlistContextInterface $wishlistContext,
        private WishlistProductFactoryInterface $wishlistProductFactory,
        private EntityManagerInterface $wishlistManager,
        private FactoryInterface $taxonFactory,
        private FactoryInterface $productTaxonFactory,
        private EntityManagerInterface $productTaxonManager,
        private CookieSetterInterface $cookieSetter,
        private string $wishlistCookieToken,
    ) {
    }

    /**
     * @Given I have this product in my wishlist
     */
    public function iHaveThisProductInMyWishlist(): void
    {
        /** @var ProductInterface $product */
        $product = $this->productRepository->findOneBy([]);

        $this->addProductToWishlist($product);
    }

    /**
     * @Given I have these products in my wishlist
     */
    public function iHaveTheseProductsInMyWishlist(): void
    {
        $products = $this->productRepository->findAll();

        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $this->addProductToWishlist($product);
        }
    }

    /**
     * @Given all store products appear under a main taxonomy
     */
    public function allStoreProductsAppearUnderAMainTaxonomy(): void
    {
        /** @var TaxonInterface $taxon */
        $taxon = $this->taxonFactory->createNew();
        $taxon->setCode('main');
        $taxon->setSlug('main');
        $taxon->setName('Main');

        /** @var ProductInterface $product */
        foreach ($this->productRepository->findAll() as $product) {
            /** @var ProductTaxonInterface $productTaxon */
            $productTaxon = $this->productTaxonFactory->createNew();
            $productTaxon->setTaxon($taxon);
            $productTaxon->setProduct($product);
            $product->addProductTaxon($productTaxon);

            $this->productTaxonManager->persist($taxon);
            $this->productTaxonManager->persist($productTaxon);
            $this->productTaxonManager->flush();
        }
    }

    private function addProductToWishlist(ProductInterface $product): void
    {
        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistContext->getWishlist(new Request());
        /** @var WishlistProductInterface $wishlistProduct */
        $wishlistProduct = $this->wishlistProductFactory->createNew();
        $wishlistProduct->setProduct($product);
        /** @var ProductVariantInterface $variant */
        $variant = $product->getVariants()->first();
        $wishlistProduct->setVariant($variant);

        $wishlist->addWishlistProduct($wishlistProduct);

        $this->wishlistManager->persist($wishlist);
        $this->wishlistManager->flush();

        $this->cookieSetter->setCookie($this->wishlistCookieToken, $wishlist->getToken());
    }
}
