<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Form\Type\AddProductsToCartType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\OrderBundle\Controller\AddToCartCommandInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final readonly class ListWishlistProductsAction
{
    private FlashBagInterface $flashBag;

    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private CartContextInterface $cartContext,
        private FormFactoryInterface $formFactory,
        private OrderModifierInterface $orderModifier,
        private EntityManagerInterface $cartManager,
        RequestStack $requestStack,
        private TranslatorInterface $translator,
        private Environment $twigEnvironment,
    ) {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
    }

    public function __invoke(Request $request): Response
    {
        $wishlist = $this->wishlistContext->getWishlist($request);
        if (!$wishlist instanceof WishlistInterface) {
            throw new ResourceNotFoundException();
        }
        $cart = $this->cartContext->getCart();

        $form = $this->formFactory->create(AddProductsToCartType::class, null, [
            'cart' => $cart,
            'wishlist_products' => $wishlist->getWishlistProducts(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->handleCartItems($form)) {
                $this->flashBag->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.added_to_cart'));
            } else {
                $this->flashBag->add('error', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.increase_quantity'));
            }

            return new Response(
                $this->twigEnvironment->render('@BitBagSyliusWishlistPlugin/wishlist_details/index.html.twig', [
                    'wishlist' => $wishlist,
                    'form' => $form->createView(),
                ]),
            );
        }

        /** @psalm-suppress UnnecessaryVarAnnotation */
        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {
            $this->flashBag->add('error', $error->getMessage());
        }

        return new Response(
            $this->twigEnvironment->render('@BitBagSyliusWishlistPlugin/wishlist_details/index.html.twig', [
                'wishlist' => $wishlist,
                'form' => $form->createView(),
            ]),
        );
    }

    private function handleCartItems(FormInterface $form): bool
    {
        $result = false;

        /** @var iterable $data */
        $data = $form->getData();
        /** @var AddToCartCommandInterface $command */
        foreach ($data as $command) {
            if (0 < $command->getCartItem()->getQuantity()) {
                $result = true;
                $this->orderModifier->addToOrder($command->getCart(), $command->getCartItem());
                $this->cartManager->persist($command->getCart());
            }
        }

        $this->cartManager->flush();

        return $result;
    }
}
