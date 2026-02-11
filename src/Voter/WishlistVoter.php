<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Voter;

use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, WishlistInterface>
 */
final class WishlistVoter extends Voter
{
    public const string UPDATE = 'update';

    public const string DELETE = 'delete';

    public function __construct(private Security $security)
    {
    }

    #[\Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::UPDATE,
            self::DELETE,
        ];

        return in_array($attribute, $attributes, true) && $subject instanceof WishlistInterface;
    }

    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof ShopUserInterface) {
            $user = null;
        }
        $wishlist = $subject;

        switch ($attribute) {
            case self::UPDATE:
                return $this->canUpdate($wishlist, $user);
            case self::DELETE:
                return $this->canDelete($wishlist, $user);
        }

        throw new \LogicException(sprintf('Unsupported attribute: "%s"', $attribute));
    }

    public function canUpdate(WishlistInterface $wishlist, ?ShopUserInterface $user): bool
    {
        if (!$this->security->isGranted('ROLE_USER') && !$wishlist->getShopUser() instanceof ShopUserInterface) {
            return true;
        }

        return $this->security->isGranted('ROLE_USER') && $wishlist->getShopUser() === $user;
    }

    public function canDelete(WishlistInterface $wishlist, ?ShopUserInterface $user): bool
    {
        return $this->canUpdate($wishlist, $user);
    }
}
