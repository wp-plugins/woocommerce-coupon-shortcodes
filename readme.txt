=== WooCommerce Coupon Shortcodes ===
Contributors: itthinx
Donate link: http://www.itthinx.com/plugins/woocommerce-coupon-shortcodes
Tags: conditional, coupon, coupons, discount, discounts, display, info, information, marketing, promotion, shortcode, shortcodes, woocommerce
Requires at least: 3.3
Tested up to: 3.6.1
Stable tag: 1.0.0
License: GPLv3

Show coupon discount info using shortcodes. Allows to render coupon information and content conditionally, based on the validity of coupons.

== Description ==

This extension for [WooCommerce](http://wordpress.org/extend/plugins/woocommerce) allows you to render coupon information and show content based on the validity of coupons.

Customers can be motivated to proceed with their purchase, offering them to use specific coupons
when the contents in the cart qualify for it, or by offering them to purchase additional items
so they can use a coupon.

Extended coupon discount info for volume discounts is shown automatically, if the [WooCommerce Volume Discount Coupons](http://www.itthinx.com/plugins/woocommerce-volume-discount-coupons) is installed.

= Conditional Shortcodes =

It provides the conditional shortcodes

`[coupon_is_valid]` and
`[coupon_is_not_valid]`

that allow to enclose content which is shown if the coupon is (or is not) valid.

= Coupon Info Shortcodes =

It also provides shortcodes that allow to render the coupon code, its description and an automatic description of the discount:

`[coupon_code]` (this one makes sense mostly when used inside one of the conditional shortcodes).
`[coupon_description]`
`[coupon_discount]`

= Examples =

Showing a coupon when the cart contents qualify for a coupon to be applied: 

`[coupon_is_valid code="superdiscount"]
You qualify for a discount!
Use the coupon code [coupon_code] to take advantage of this great discount : [coupon_discount]
[/coupon_is_valid]`

Showing a coupon that is not valid for the current cart and motivating to add items:

`[coupon_is_not_valid code="25off"]
If you purchase 5 Widgets, you can use the coupon [coupon_code] to get 25% off your purchase!
[/coupon_is_not_valid]`

= Documentation and Support =

Full usage instructions and help is provided on these pages:

- [Documentation](http://www.itthinx.com/documentation/woocommerce-coupon-shortcodes/)
- [WooCommerce Coupon Shortcodes plugin page and Support](http://www.itthinx.com/plugins/woocommerce-coupon-shortcodes/)


== Installation ==

1. Upload or extract the `woocommerce-coupon-shortcodes` folder to your site's `/wp-content/plugins/` directory. You can also use the *Add new* option found in the *Plugins* menu in WordPress.  
2. Enable the plugin from the *Plugins* menu in WordPress.

== Frequently Asked Questions ==

= Where is the documentation? =

[Documentation](http://www.itthinx.com/documentation/woocommerce-coupon-shortcodes/)

= I have a question, where do I ask? =

You can leave a comment at the [WooCommerce Coupon Shortcodes plugin page](http://www.itthinx.com/plugins/woocommerce-coupon-shortcodes/).


== Screenshots ==

See the plugin page [Groups](http://www.itthinx.com/plugins/woocommerce-discount-coupons/)


== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
* Initial release.
