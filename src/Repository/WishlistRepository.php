<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Repository;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ShopUserInterface;

final class WishlistRepository extends EntityRepository implements WishlistRepositoryInterface
{
    #[\Override]
    public function findOneByShopUser(ShopUserInterface $shopUser): ?WishlistInterface
    {
        /** @psalm-suppress QueryBuilderSetParameter */
        /** @var ?WishlistInterface $result */
        $result = $this->createQueryBuilder('o')
            ->where('o.shopUser = :shopUser')
            ->setParameter('shopUser', $shopUser)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    #[\Override]
    public function findByToken(string $token): ?WishlistInterface
    {
        /** @var ?WishlistInterface $oneOrNullResult */
        $oneOrNullResult = $this->createQueryBuilder('o')
            ->where('o.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();

        return $oneOrNullResult;
    }
}
