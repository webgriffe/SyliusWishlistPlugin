<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class RenderHeaderTemplateAction
{
    public function __construct(private WishlistContextInterface $wishlistContext, private Environment $twigEnvironment)
    {
    }

    public function __invoke(Request $request): Response
    {
        $wishlist = $this->wishlistContext->getWishlist($request);

        return new Response(
            $this->twigEnvironment->render('@BitBagSyliusWishlistPlugin/common/widget.html.twig', [
                'wishlist' => $wishlist,
            ]),
        );
    }
}
