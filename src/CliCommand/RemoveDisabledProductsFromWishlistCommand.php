<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\CliCommand;

use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'bitbag:wishlist:remove-disabled-products',
    description: 'Removes disabled products from all wishlists',
)]
final class RemoveDisabledProductsFromWishlistCommand extends Command
{
    public function __construct(
        private readonly RepositoryInterface $wishlistProductRepository,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var WishlistProductInterface[] $wishlistProducts */
        $wishlistProducts = $this->wishlistProductRepository->findAll();
        foreach ($wishlistProducts as $wishlistProduct) {
            $product = $wishlistProduct->getProduct();
            if ($product === null || $product->isEnabled()) {
                continue;
            }

            $output->writeln(sprintf(
                'Removing disabled product with code "%s" from wishlist "%s".',
                (string) $product->getCode(),
                (string) $wishlistProduct->getWishlist()->getId(),
            ));
            $wishlistProduct->getWishlist()->removeProduct($wishlistProduct);
            $this->wishlistProductRepository->remove($wishlistProduct);
        }

        return self::SUCCESS;
    }
}
