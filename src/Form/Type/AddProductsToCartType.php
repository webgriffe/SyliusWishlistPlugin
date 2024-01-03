<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Form\Type;

use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use Doctrine\Common\Collections\Collection;
use Sylius\Bundle\CoreBundle\Form\Type\Order\AddToCartType;
use Sylius\Bundle\OrderBundle\Factory\AddToCartCommandFactoryInterface;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AddProductsToCartType extends AbstractType
{
    private AddToCartCommandFactoryInterface $addToCartCommandFactory;

    private CartItemFactoryInterface $cartItemFactory;

    private OrderItemQuantityModifierInterface $orderItemQuantityModifier;

    /** @var string[] */
    private array $validationGroups;

    public function __construct(
        AddToCartCommandFactoryInterface $addToCartCommandFactory,
        CartItemFactoryInterface $cartItemFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        array $validationGroups,
    ) {
        $this->addToCartCommandFactory = $addToCartCommandFactory;
        $this->cartItemFactory = $cartItemFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->validationGroups = $validationGroups;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var WishlistProductInterface $wishlistProduct */
        foreach ($options['wishlist_products'] as $key => $wishlistProduct) {
            if (is_int($key)) {
                $key = (string) $key;
            }
            $builder
                ->add($key, AddToCartType::class, [
                    'label' => false,
                    'required' => false,
                    'product' => $wishlistProduct->getProduct(),
                    'data' => $this->addToCartCommandFactory->createWithCartAndCartItem(
                        $options['cart'],
                        $this->createCartItem($wishlistProduct),
                    ),
                    'is_wishlist' => true,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('cart')
            ->setRequired('wishlist_products')
            ->setAllowedTypes('wishlist_products', Collection::class)
            ->setDefault('data_class', null)
            ->setDefault('validation_groups', $this->validationGroups)
        ;
    }

    private function createCartItem(WishlistProductInterface $wishlistProduct): OrderItemInterface
    {
        /** @var OrderItemInterface $cartItem */
        $cartItem = $this->cartItemFactory->createForProduct($wishlistProduct->getProduct());
        $cartItem->setVariant($wishlistProduct->getVariant());

        $this->orderItemQuantityModifier->modify($cartItem, 0);

        return $cartItem;
    }
}
