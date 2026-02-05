<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RemoveProductFromWishlistAction
{
    private FlashBagInterface $flashBag;

    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private ProductRepositoryInterface $productRepository,
        private EntityManagerInterface $wishlistProductManager,
        RequestStack $requestStack,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
    }

    public function __invoke(Request $request, string $productId): Response
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->find($productId);
        if (null === $product) {
            throw new NotFoundHttpException();
        }

        $wishlist = $this->wishlistContext->getWishlist($request);
        if (!$wishlist instanceof WishlistInterface) {
            throw new ResourceNotFoundException();
        }

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
