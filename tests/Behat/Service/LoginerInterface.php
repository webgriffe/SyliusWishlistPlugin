<?php

declare(strict_types=1);

namespace Tests\BitBag\SyliusWishlistPlugin\Behat\Service;

use Sylius\Component\Core\Model\ShopUserInterface;

interface LoginerInterface
{
    public function logIn(): void;

    public function logOut(): void;

    public function createUser(): ShopUserInterface;
}
