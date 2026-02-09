<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/** @internal */
final class TwigHooksProfilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var string $env */
        $env = $container->getParameter('kernel.environment');
        if ($env === 'dev') {
            return;
        }

        if ($container->hasDefinition('sylius_twig_hooks.renderer.hook.profiler')) {
            $container->removeDefinition('sylius_twig_hooks.renderer.hook.profiler');
        }

        if ($container->hasDefinition('sylius_twig_hooks.renderer.hookable.profiler')) {
            $container->removeDefinition('sylius_twig_hooks.renderer.hookable.profiler');
        }
    }
}
