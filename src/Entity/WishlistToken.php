<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Entity;

use Ramsey\Uuid\Uuid;

/**
 * @psalm-api
 */
final class WishlistToken implements WishlistTokenInterface
{
    private string $value;

    public function __construct(?string $value = null)
    {
        if ($value === null) {
            $this->value = $this->generate();
        } else {
            $this->setValue($value);
        }
    }

    #[\Override]
    public function getValue(): string
    {
        return $this->value;
    }

    #[\Override]
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    private function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
