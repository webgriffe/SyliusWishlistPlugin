<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class BitBagSyliusWishlistExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration([], $container);
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yml');

        /** @var string $value */
        $value = $config['wishlist_cookie_token'];
        $container->setParameter('wishlist_cookie_token', $value);
    }

    #[\Override]
    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);

        $config = $this->getCurrentConfiguration($container);
        /** @var array $registeredResources */
        $registeredResources = $config['resources'];
        $this->registerResources('bitbag_sylius_wishlist_plugin', 'doctrine/orm', $registeredResources, $container);
    }

    #[\Override]
    protected function getMigrationsNamespace(): string
    {
        return 'BitBag\SyliusWishlistPlugin\Migrations';
    }

    #[\Override]
    protected function getMigrationsDirectory(): string
    {
        return '@BitBagSyliusWishlistPlugin/src/Migrations';
    }

    #[\Override]
    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return ['Sylius\Bundle\CoreBundle\Migrations'];
    }

    private function getCurrentConfiguration(ContainerBuilder $container): array
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration([], $container);
        $configs = $container->getExtensionConfig($this->getAlias());

        return $this->processConfiguration($configuration, $configs);
    }
}
