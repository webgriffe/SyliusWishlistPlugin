<?php

declare(strict_types=1);

namespace spec\BitBag\SyliusWishlistPlugin\EventSubscriber;

use BitBag\SyliusWishlistPlugin\EventSubscriber\ClearCookieWishlistItemsSubscriber;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class ClearCookieWishlistItemsSubscriberSpec extends ObjectBehavior
{
    public function let(StorageInterface $cookieStorage): void
    {
        $this->beConstructedWith($cookieStorage, 'bitbag_sylius_wishlist');
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ClearCookieWishlistItemsSubscriber::class);
    }

    function it_does_nothing_if_not_shop_user(
        StorageInterface $cookieStorage,
        TokenInterface $token,
        AdminUserInterface $adminUser
    ): void {
        $token->getUser()->willReturn($adminUser);
        $logoutEvent = new LogoutEvent(new Request(), $token->getWrappedObject());
        $cookieStorage->set('bitbag_sylius_wishlist', null)->shouldNotBeCalled();
        $this->onLogout($logoutEvent);
    }

    function it_clears_cookie_on_logout_for_shop_user(
        StorageInterface $cookieStorage,
        TokenInterface $token,
        ShopUserInterface $shopUser
    ): void {
        $token->getUser()->willReturn($shopUser);
        $logoutEvent = new LogoutEvent(new Request(), $token->getWrappedObject());
        $cookieStorage->set('bitbag_sylius_wishlist', null)->shouldBeCalled();
        $this->onLogout($logoutEvent);
    }
}

