# Upgrade plugin guide

## Upgrade from version v2.x to v3.x

The v3 is now compatible with Sylius 2.x, so you need to update your Sylius version to 2.x before upgrading the plugin. Some changes not listed here may be required, so please refer to the Sylius 2.x upgrade guide for more details.

- The route `@BitBagSyliusWishlistPlugin/Resources/config/config.yml` has been renamed to `@BitBagSyliusWishlistPlugin/config/config.yaml`.
- The route `@BitBagSyliusWishlistPlugin/Resources/config/routing.yml` has been renamed to `@BitBagSyliusWishlistPlugin/config/routes.yaml`.
- Templates names and paths changed:
  - `templates/Common/_addToWishlist.html.twig` is now `templates/common/add_to_wishlist.html.twig`
  - `templates/Common/_removeFromWishlist.html.twig` is now `templates/common/remove_from_wishlist.html.twig`
  - `templates/Common/widget.html.twig` is now `templates/common/widget.html.twig`
  - `templates/WishlistDetails/_globalActions.html.twig` is now `templates/wishlist_details/global_actions.html.twig`
  - `templates/WishlistDetails/index.html.twig` is now `templates/wishlist_details/index.html.twig`
  - `templates/WishlistDetails/_item.html.twig` is now `templates/wishlist_details/item.html.twig`
  - `templates/WishlistDetails/_variantPrice.html.twig` is now `templates/wishlist_details/variant_price.html.twig`
- Add to wishlist from the product page is now performed via a Symfony UI component. Take a look at the template `templates/components/product/show/add_to_wishlist.html.twig` and the related UI action `BitBag\SyliusWishlistPlugin\Processor\AddProductVariantToWishlistProcessor`
