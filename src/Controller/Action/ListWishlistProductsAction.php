<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Form\Type\AddProductsToCartType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\OrderBundle\Controller\AddToCartCommandInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Webmozart\Assert\Assert;

final class ListWishlistProductsAction
{
    private WishlistContextInterface $wishlistContext;

    private CartContextInterface $cartContext;

    private FormFactoryInterface $formFactory;

    private OrderModifierInterface $orderModifier;

    private EntityManagerInterface $cartManager;

    private FlashBagInterface $flashBag;

    private TranslatorInterface $translator;

    private Environment $twigEnvironment;

    public function __construct(
        WishlistContextInterface $wishlistContext,
        CartContextInterface $cartContext,
        FormFactoryInterface $formFactory,
        OrderModifierInterface $orderModifier,
        EntityManagerInterface $cartManager,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        Environment $twigEnvironment,
    ) {
        $this->wishlistContext = $wishlistContext;
        $this->cartContext = $cartContext;
        $this->formFactory = $formFactory;
        $this->orderModifier = $orderModifier;
        $session = $requestStack->getSession();
        Assert::isInstanceOf($session, Session::class);
        $this->flashBag = $session->getFlashBag();
        $this->twigEnvironment = $twigEnvironment;
        $this->cartManager = $cartManager;
        $this->translator = $translator;
    }

    public function __invoke(Request $request): Response
    {
        $wishlist = $this->wishlistContext->getWishlist($request);
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
                $this->twigEnvironment->render('@BitBagSyliusWishlistPlugin/WishlistDetails/index.html.twig', [
                    'wishlist' => $wishlist,
                    'form' => $form->createView(),
                ]),
            );
        }

        foreach ($form->getErrors() as $error) {
            $this->flashBag->add('error', $error->getMessage());
        }

        return new Response(
            $this->twigEnvironment->render('@BitBagSyliusWishlistPlugin/WishlistDetails/index.html.twig', [
                'wishlist' => $wishlist,
                'form' => $form->createView(),
            ]),
        );
    }

    private function handleCartItems(FormInterface $form): bool
    {
        $result = false;

        /** @var AddToCartCommandInterface $command */
        foreach ($form->getData() as $command) {
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
