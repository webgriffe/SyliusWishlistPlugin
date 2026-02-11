<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusWishlistPlugin\Behat\Page\Shop;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Shop\Product\IndexPage;

class ProductIndexPage extends IndexPage implements ProductIndexPageInterface
{
    public function addProductToWishlist(string $productName): void
    {
        $this->getSession()->setCookie('MOCKSESSID', 'foo');

        $wishlistElements = $this->getDocument()->findAll('css', '[data-test-wishlist-add-product]');

        /** @var NodeElement $wishlistElement */
        foreach ($wishlistElements as $wishlistElement) {
            if ($productName === $wishlistElement->getAttribute('data-product-name')) {
                $wishlistElement->click();

                return;
            }
        }
    }
}
