<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RemoveProductVariantFromWishlistAction
{
    private FlashBagInterface $flashBag;

    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private ProductVariantRepositoryInterface $productVariantRepository,
        private EntityManagerInterface $wishlistProductManager,
        RequestStack $requestStack,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
    }

    public function __invoke(Request $request, string $variantId): Response
    {
        /** @var ProductVariantInterface|null $variant */
        $variant = $this->productVariantRepository->find($variantId);
        if (null === $variant) {
            throw new NotFoundHttpException();
        }

        $wishlist = $this->wishlistContext->getWishlist($request);
        if (!$wishlist instanceof WishlistInterface) {
            throw new ResourceNotFoundException();
        }

        foreach ($wishlist->getWishlistProducts() as $wishlistProduct) {
            if ($variant === $wishlistProduct->getVariant()) {
                $this->eventDispatcher->dispatch(new GenericEvent($wishlistProduct), 'bitbag_sylius_wishlist_plugin.wishlist.pre_remove');
                $this->wishlistProductManager->remove($wishlistProduct);
            }
        }

        $this->wishlistProductManager->flush();
        $this->eventDispatcher->dispatch(new GenericEvent($variant, ['wishlist' => $wishlist]), 'bitbag_sylius_wishlist_plugin.wishlist.post_remove');

        $this->flashBag->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.removed_wishlist_item'));

        return new RedirectResponse($this->urlGenerator->generate('bitbag_sylius_wishlist_plugin_shop_wishlist_list_products'));
    }
}
