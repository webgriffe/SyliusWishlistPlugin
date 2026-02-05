<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Context;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Symfony\Component\HttpFoundation\Request;

interface WishlistContextInterface
{
    public function getWishlist(Request $request): ?WishlistInterface;
}
