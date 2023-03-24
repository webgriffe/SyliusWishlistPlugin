<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RemoveProductFromWishlistAction
{
    private WishlistContextInterface $wishlistContext;

    private ProductRepositoryInterface $productRepository;

    private EntityManagerInterface $wishlistProductManager;

    private FlashBagInterface $flashBag;

    private TranslatorInterface $translator;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        WishlistContextInterface $wishlistContext,
        ProductRepositoryInterface $productRepository,
        EntityManagerInterface $wishlistProductManager,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->wishlistContext = $wishlistContext;
        $this->productRepository = $productRepository;
        $this->wishlistProductManager = $wishlistProductManager;
        $this->urlGenerator = $urlGenerator;
        $this->flashBag = $requestStack->getSession()->getFlashBag();
        $this->translator = $translator;
    }

    public function __invoke(Request $request): Response
    {
        /** @var ProductInterface $product */
        $product = $this->productRepository->find($request->get('productId'));

        if (null === $product) {
            throw new NotFoundHttpException();
        }

        $wishlist = $this->wishlistContext->getWishlist($request);

        foreach ($wishlist->getWishlistProducts() as $wishlistProduct) {
            if ($product === $wishlistProduct->getProduct()) {
                $this->wishlistProductManager->remove($wishlistProduct);
            }
        }

        $this->wishlistProductManager->flush();
        $this->flashBag->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.removed_wishlist_item'));

        return new RedirectResponse($this->urlGenerator->generate('bitbag_sylius_wishlist_plugin_shop_wishlist_list_products'));
    }
}
