<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusWishlistPlugin\Behat\Page\Shop;

use Sylius\Behat\Page\Shop\Product\ShowPage;
use Webmozart\Assert\Assert;

class ProductShowPage extends ShowPage implements ProductShowPageInterface
{
    public function addVariantToWishlist(): void
    {
        $var = $this->getDocument()->find('css', '[data-test-wishlist-add-variant]');
        Assert::notNull($var, 'Wishlist add variant button not found on the page.');
        $var->click();

        // Wait for the ajax request to finish
        $this->getSession()->wait(5000, 'document.querySelectorAll("[data-test-flash-messages]").length > 0');
    }
}
