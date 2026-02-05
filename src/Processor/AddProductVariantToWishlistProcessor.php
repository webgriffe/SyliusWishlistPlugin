<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Processor;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistProductInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistProductFactoryInterface;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
        private string $wishlistCookieToken,
    ) {
    }

    public function process(ProductVariantInterface $productVariant, ?int $wishlistId = null): RedirectResponse
    {
        $wishlist = $this->wishlistContext->getWishlist($this->requestStack->getCurrentRequest());
        if (null === $wishlist) {
            throw new ResourceNotFoundException();
        }

        $wishlistProduct = $this->wishlistProductFactory->createForWishlistAndVariant($wishlist, $productVariant);

        $this->addProductToWishlist($wishlist, $productVariant, $wishlistProduct);
        $response = new RedirectResponse($this->urlGenerator->generate('bitbag_sylius_wishlist_plugin_shop_wishlist_list_products'));

        $token = $this->tokenStorage->getToken();

        if (null === $token || !is_object($token->getUser())) {
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
            $flashBag->add(
                'error',
                $this->translator->trans(
                    'bitbag_sylius_wishlist_plugin.ui.wishlist_has_product_variant',
                    ['%productName%' => $wishlistProduct->getProduct()->getName()],
                ),
            );

            return;
        }

        $wishlist->addWishlistProduct($wishlistProduct);
        $this->wishlistRepository->add($wishlist);
        $flashBag->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.added_wishlist_item'));
    }

    private function addWishlistToResponseCookie(WishlistInterface $wishlist, Response $response): void
    {
        $cookie = new Cookie($this->wishlistCookieToken, $wishlist->getToken(), strtotime('+1 year'));

        $response->headers->setCookie($cookie);
    }
}
