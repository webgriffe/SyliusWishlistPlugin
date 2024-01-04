<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\CliCommand;

use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RemoveDisabledProductsFromWishlistCommand extends Command
{
    protected static $defaultName = 'bitbag:wishlist:remove-disabled-products';

    /**
     * @param RepositoryInterface<WishlistProductInterface> $wishlistProductRepository
     */
    public function __construct(
        private readonly RepositoryInterface $wishlistProductRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $wishlistProducts = $this->wishlistProductRepository->findAll();

        foreach ($wishlistProducts as $wishlistProduct) {
            if ($wishlistProduct->getProduct()->isEnabled()) {
                continue;
            }

            $output->writeln(sprintf(
                'Removing disabled product with code "%s" from wishlist "%s".',
                (string) $wishlistProduct->getProduct()->getCode(),
                (string) $wishlistProduct->getWishlist()->getId(),
            ));
            $wishlistProduct->getWishlist()->removeProduct($wishlistProduct);
            $this->wishlistProductRepository->remove($wishlistProduct);
        }

        return self::SUCCESS;
    }
}
