<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Order\AddToCartType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @psalm-suppress MissingTemplateParam
 */
final class AddToCartTypeExtension extends AbstractTypeExtension
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var bool $isWishlist */
        $isWishlist = $options['is_wishlist'];
        if (!$isWishlist) {
            $builder
                ->add('addToWishlist', SubmitType::class, [
                    'label' => 'bitbag_sylius_wishlist_plugin.ui.add_to_wishlist',
                    'attr' => [
                        'class' => 'bitbag-add-variant-to-wishlist ui icon labeled button',
                    ],
                ])
            ;
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('is_wishlist', false)
            ->setAllowedTypes('is_wishlist', 'bool')
        ;
    }

    public function getExtendedType(): string
    {
        return AddToCartType::class;
    }

    #[\Override]
    public static function getExtendedTypes(): array
    {
        return [AddToCartType::class];
    }
}
