<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusWishlistPlugin\Behat\Page\Shop;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Sylius\Component\Core\Model\ProductInterface;
use Webmozart\Assert\Assert;

class WishlistPage extends SymfonyPage implements WishlistPageInterface
{
    public function getItemsCount(): int
    {
        $var = $this->getDocument()->find('css', '[data-test-wishlist-primary-items-count]');
        Assert::notNull($var, 'Wishlist items count element not found on the page.');

        return (int) $var->getText();
    }

    public function hasProduct(string $productName): bool
    {
        $productElements = $this->getDocument()->findAll('css', '[data-test-wishlisst-item-name]');

        /** @var NodeElement $productElement */
        foreach ($productElements as $productElement) {
            if ($productName === $productElement->getText()) {
                return true;
            }
        }

        return false;
    }

    public function removeProduct(string $productName): void
    {
        $wishlistElements = $this->getDocument()->findAll('css', '[data-test-wishlist-remove-item]');

        /** @var NodeElement $wishlistElement */
        foreach ($wishlistElements as $wishlistElement) {
            if ($productName === $wishlistElement->getAttribute('data-product-name')) {
                $wishlistElement->click();
            }
        }
    }

    public function selectProductQuantity(string $productName, int $quantity): void
    {
        $addToCartElements = $this->getDocument()->findAll('css', '[data-test-wishlist-item-quantity] input');

        /** @var NodeElement $addToCartElement */
        foreach ($addToCartElements as $addToCartElement) {
            if ($productName === $addToCartElement->getAttribute('data-product-name')) {
                $addToCartElement->setValue((string) $quantity);
            }
        }
    }

    public function addProductToCart(): void
    {
        $var = $this->getDocument()->find('css', '[data-test-wishlist-add-all-to-cart]');
        Assert::notNull($var, 'Add all to cart button not found on the page.');
        $var->press();
    }

    public function hasProductInCart(string $productName): bool
    {
        $var = $this->getDocument()->find('css', '.ui.cart.popup > .list > .item > strong');
        Assert::notNull($var, 'Cart product name element not found on the page.');
        $productNameOnPage = $var->getText();

        return $productName === $productNameOnPage;
    }

    public function hasProductOutOfStockValidationMessage(ProductInterface $product): bool
    {
        $outOfStockValidationErrorElement = $this->getDocument()->find('css', '.sylius-flash-message p');

        if (null === $outOfStockValidationErrorElement) {
            return false;
        }

        $message = sprintf('%s does not have sufficient stock.', $product->getName());

        return $outOfStockValidationErrorElement->getText() === $message;
    }

    public function getRouteName(): string
    {
        return 'bitbag_sylius_wishlist_plugin_shop_wishlist_list_products';
    }
}
