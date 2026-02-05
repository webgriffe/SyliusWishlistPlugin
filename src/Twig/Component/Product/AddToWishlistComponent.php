<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Twig\Component\Product;

use BitBag\SyliusWishlistPlugin\Processor\AddProductVariantToWishlistProcessorInterface;
use Sylius\Bundle\ShopBundle\Twig\Component\Product\AddToCartFormComponent;
use Sylius\Bundle\ShopBundle\Twig\Component\Product\Trait\ProductLivePropTrait;
use Sylius\Bundle\ShopBundle\Twig\Component\Product\Trait\ProductVariantLivePropTrait;
use Sylius\Bundle\UiBundle\Twig\Component\TemplatePropTrait;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\TwigHooks\LiveComponent\HookableLiveComponentTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Webmozart\Assert\Assert;

/** @psalm-suppress PropertyNotSetInConstructor */
#[AsLiveComponent]
final class AddToWishlistComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;
    use HookableLiveComponentTrait;
    use TemplatePropTrait;
    use ProductLivePropTrait;
    use ProductVariantLivePropTrait;

    public function __construct(
        private readonly AddProductVariantToWishlistProcessorInterface $addProductVariantToWishlistProcessor,
        private readonly ProductVariantResolverInterface $productVariantResolver,
        ProductRepositoryInterface $productRepository,
        ProductVariantRepositoryInterface $productVariantRepository,
    ) {
        $this->initializeProduct($productRepository);
        $this->initializeProductVariant($productVariantRepository);
    }

    #[PostMount]
    public function postMount(): void
    {
        $subject = $this->product;
        Assert::notNull($subject, 'Product must be set for AddToWishlistComponent');
        /** @var ProductVariantInterface|null $variant * */
        $variant = $this->productVariantResolver->getVariant($subject);
        $this->variant = $variant;
    }

    #[LiveListener(AddToCartFormComponent::SYLIUS_SHOP_VARIANT_CHANGED)]
    public function updateProductVariant(#[LiveArg] mixed $variantId): void
    {
        if (null === $variantId) {
            $this->variant = null;

            return;
        }

        $changedVariant = $this->productVariantRepository->find($variantId);
        if ($changedVariant === $this->variant) {
            return;
        }

        if ($changedVariant === null) {
            $this->variant = null;

            return;
        }

        $this->variant = $changedVariant->isEnabled() ? $changedVariant : null;
    }

    #[LiveAction]
    public function addToWishlist(#[LiveArg] ?int $wishlistId = null): RedirectResponse
    {
        $productVariant = $this->variant;
        Assert::notNull($productVariant, 'Product variant must be set for AddToWishlistComponent');

        return $this->addProductVariantToWishlistProcessor->process(
            $productVariant,
            $wishlistId,
        );
    }
}
