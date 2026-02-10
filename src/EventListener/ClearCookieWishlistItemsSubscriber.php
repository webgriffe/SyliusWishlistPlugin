<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\EventListener;

use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Resource\Storage\StorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class ClearCookieWishlistItemsSubscriber implements EventSubscriberInterface
{
    public function __construct(private StorageInterface $cookieStorage, private string $wishlistCookieToken)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();

        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $this->cookieStorage->set($this->wishlistCookieToken, null);
    }
}
