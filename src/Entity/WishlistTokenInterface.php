<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Entity;

/**
 * @psalm-api
 */
interface WishlistTokenInterface
{
    public function getValue(): string;

    public function setValue(string $value): void;

    public function __toString(): string;
}
