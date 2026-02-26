<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Processor;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistProductFactoryInterface;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AddProductVariantToWishlistProcessor implements AddProductVariantToWishlistProcessorInterface
{
    public function __construct(
        private WishlistContextInterface $wishlistContext,
        private WishlistProductFactoryInterface $wishlistProductFactory,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private WishlistRepositoryInterface $wishlistRepository,
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher,
        private string $wishlistCookieToken,
    ) {
    }

    #[\Override]
    public function process(ProductVariantInterface $productVariant, ?int $wishlistId = null): RedirectResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new ResourceNotFoundException();
        }

        $wishlist = $this->wishlistContext->getWishlist($request);
        if (!$wishlist instanceof WishlistInterface) {
            throw new ResourceNotFoundException();
        }

        $wishlistProduct = $this->wishlistProductFactory->createForWishlistAndVariant($wishlist, $productVariant);

        $this->addProductToWishlist($wishlist, $productVariant, $wishlistProduct);
        $response = new RedirectResponse($this->urlGenerator->generate('bitbag_sylius_wishlist_plugin_shop_wishlist_list_products'));

        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface || !is_object($token->getUser())) {
            $this->addWishlistToResponseCookie($wishlist, $response);
        }

        return $response;
    }

    private function addProductToWishlist(
        WishlistInterface $wishlist,
        ProductVariantInterface $variant,
        WishlistProductInterface $wishlistProduct,
    ): void {
        /** @var Session $session */
        $session = $this->requestStack->getSession();

        $flashBag = $session->getFlashBag();

        if ($wishlist->hasProductVariant($variant)) {
            /** @var ProductInterface $product */
            $product = $wishlistProduct->getProduct();
            $flashBag->add(
                'error',
                $this->translator->trans(
                    'bitbag_sylius_wishlist_plugin.ui.wishlist_has_product_variant',
                    ['%productName%' => $product->getName()],
                ),
            );

            return;
        }

        $wishlist->addWishlistProduct($wishlistProduct);
        $this->eventDispatcher->dispatch(new GenericEvent($wishlistProduct), 'bitbag_sylius_wishlist_plugin.wishlist.pre_add');
        $this->wishlistRepository->add($wishlist);
        $this->eventDispatcher->dispatch(new GenericEvent($wishlistProduct), 'bitbag_sylius_wishlist_plugin.wishlist.post_add');

        $flashBag->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.added_wishlist_item'));
    }

    private function addWishlistToResponseCookie(WishlistInterface $wishlist, Response $response): void
    {
        /** @var int $strtotime */
        $strtotime = strtotime('+1 year');

        $cookie = new Cookie($this->wishlistCookieToken, $wishlist->getToken(), $strtotime);

        $response->headers->setCookie($cookie);
    }
}
