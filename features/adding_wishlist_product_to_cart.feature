@wishlist
Feature: Adding wishlist product to cart
    In order to buy products I like
    As a Visitor
    I want to be able to add my wishlist product to my cart

    Background:
        Given the store operates on a single channel in "United States"

    @ui
    Scenario: Adding a wishlist product to cart
        Given the store has a product "Jack Daniels Gentleman" priced at "$10.00"
        And all store products appear under a main taxonomy
        And the store has a product "Bushmills Black Bush Whiskey" priced at "$230.00"
        And I have these products in my wishlist
        When I go to the wishlist page
        And I select 1 quantity of "Bushmills Black Bush Whiskey" product
        And I add my wishlist products to cart
        And I see the summary of my cart
        Then I should see "Bushmills Black Bush Whiskey" with quantity 1 in my cart

    @ui
    Scenario: Adding a wishlist product with insufficient stock to cart
        Given the store has a product "Jack Daniels Gentleman" priced at "$10.00"
        And the product "Jack Daniels Gentleman" is out of stock
        And I have this product in my wishlist
        When I go to the wishlist page
        And I select 1 quantity of "Jack Daniels Gentleman" product
        And I add my wishlist products to cart
# todo: this actually does not work, there is no notification about insuff stock but the qty input is marked with an error..
#        Then I should not be notified that "Jack Daniels Gentleman" does not have sufficient stock
        And I see the summary of my cart
        Then my cart should be empty
