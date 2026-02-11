<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Form\Type;

use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use Doctrine\Common\Collections\Collection;
use Sylius\Bundle\CoreBundle\Form\Type\Order\AddToCartType;
use Sylius\Bundle\OrderBundle\Factory\AddToCartCommandFactoryInterface;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @psalm-suppress MissingTemplateParam
 */
final class AddProductsToCartType extends AbstractType
{
    public function __construct(
        private AddToCartCommandFactoryInterface $addToCartCommandFactory,
        private CartItemFactoryInterface $cartItemFactory,
        private OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private array $validationGroups,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var iterable $wishlistProducts */
        $wishlistProducts = $options['wishlist_products'];
        /**
         * @var WishlistProductInterface $wishlistProduct
         * @var string|int $key
         */
        foreach ($wishlistProducts as $key => $wishlistProduct) {
            if (is_int($key)) {
                $key = (string) $key;
            }
            /** @var OrderInterface $cart */
            $cart = $options['cart'];
            $builder
                ->add($key, AddToCartType::class, [
                    'label' => false,
                    'required' => false,
                    'product' => $wishlistProduct->getProduct(),
                    'data' => $this->addToCartCommandFactory->createWithCartAndCartItem(
                        $cart,
                        $this->createCartItem($wishlistProduct),
                    ),
                    'is_wishlist' => true,
                ])
            ;
        }
    }

    #[\Override]
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
        /** @var ProductInterface $product */
        $product = $wishlistProduct->getProduct();
        $cartItem = $this->cartItemFactory->createForProduct($product);
        $cartItem->setVariant($wishlistProduct->getVariant());

        $this->orderItemQuantityModifier->modify($cartItem, 0);

        return $cartItem;
    }
}
