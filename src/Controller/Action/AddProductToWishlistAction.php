<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Factory\WishlistProductFactoryInterface;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AddProductToWishlistAction
{
    private FlashBagInterface $flashBag;

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private ProductRepositoryInterface $productRepository,
        private WishlistContextInterface $wishlistContext,
        private WishlistProductFactoryInterface $wishlistProductFactory,
        private ObjectManager $wishlistManager,
        RequestStack $requestStack,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private string $wishlistCookieToken,
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

        $wishlistProduct = $this->wishlistProductFactory->createForWishlistAndProduct($wishlist, $product);
        $wishlist->addWishlistProduct($wishlistProduct);
        if (null === $wishlist->getId()) {
            $this->wishlistManager->persist($wishlist);
        }

        $this->wishlistManager->flush();

        $this->flashBag->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.added_wishlist_item'));

        $response = new RedirectResponse($this->urlGenerator->generate('bitbag_sylius_wishlist_plugin_shop_wishlist_list_products'));

        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface || !is_object($token->getUser())) {
            $this->addWishlistToResponseCookie($wishlist, $response);
        }

        return $response;
    }

    private function addWishlistToResponseCookie(WishlistInterface $wishlist, Response $response): void
    {
        /** @var int $strtotime */
        $strtotime = strtotime('+1 year');
        $cookie = new Cookie($this->wishlistCookieToken, $wishlist->getToken(), $strtotime);

        $response->headers->setCookie($cookie);
    }
}
