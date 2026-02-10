<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin;

use BitBag\SyliusWishlistPlugin\DependencyInjection\TwigHooksProfilerPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigHooksProfilerPass());
    }
}
