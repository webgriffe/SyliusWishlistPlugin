<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @psalm-api
 */
final class BitBagSyliusWishlistPlugin extends Bundle
{
    use SyliusPluginTrait;

    #[\Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
