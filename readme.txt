=== Subscription Payu Latam ===
Contributors: Saul Morales Pacheco
Donate link: https://saulmoralespa.com/
Tags: commerce, e-commerce, commerce, wordpress ecommerce, store, sales, sell, shop, shopping, cart, checkout, configurable, payu, latam, subscription, Brasil, Colombia, México, Perú
Requires at least: 4.9.5
Tested up to: 4.9.8
Requires PHP: 5.6.0
Stable tag: 1.0.10
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Receive recurring payments for the countries Brazil, Colombia, Mexico and Peru

== Description ==

Subscription Payu Latam works together with the Woocommerce subscriptions plugin.
Receive recurring payments for the countries Brazil, Colombia, Mexico and Peru

* Note:! Important you must request payu support to activate payment processing using the SDK

== Installation ==

1. Download the plugin
2. Enter the administrator of your WordPress.
3. Enter Plugins / Add-New / Upload-Plugin.
4. Find the plugin downloaded on your computer and upload it like any other file.
5. After installing the .zip you can see it in the list of installed plugins, you can activate or deactivate it.
6. To configure the plugin you must go to: WooCommerce / Adjustments / Finish Purchase and Locate the tab Payu Latam Subscription.
7. Configure the plugin by entering Merchant id, Account id, Apikey, Apilogin provided by payu
8. Save Changes, if you have not done the configuration correctly you will be shown a warning, pay attention to this.
9. *Remember* you must ensure that the payment processing with the SDK is active


== Frequently Asked Questions ==

= Countries in which its use is available? =

Brazil, Colombia, Mexico and Peru

= Are you required to use a ssl certificate? =

No. But it is advisable that you consider using it since it is revealing for browsers

= How do I prove its operation? =

You must go to the Woocommerce settings / finalize purchase / Payu Latam Subscription and change the testing environment

= What should I take into account for production? =

**Have the activation of the SDK**

= ¿ What else should I keep in mind, that you have not told me ? =

1. You need to use the [Woocommerce subscriptions](https://github.com/wp-premium/woocommerce-subscriptions "plugin")
2. You must set the number of decimals to zero, see screenshots.
3. PayU does not allow Sign-up fee, see screenshots.

== Screenshots ==

1. Half payment configuration corresponds to screenshot-1.png
2. Change to zero decimals corresponds to screenshot-2.png
3. Sign-up fee empty corresponds to screenshot-3.png
4. **To perform tests you must use the credentials provided by payU** [see credentials](http://developers.payulatam.com/es/sdk/sandbox.html) and [fake credit card](https://es.fakenamegenerator.com/)

== Changelog ==

= 1.0.0 =
* Initial stable release
= 1.0.4 =
* Fixed do_action and refactor
= 1.0.5 =
* Translation missing
= 1.0.6 =
* fixed domain translation
= 1.0.7 =
* Requires OpenSSL version 1.0.1
= 1.0.8 =
* Fixed conflict load instance plugin
= 1.0.9 =
* Translate portuguese Brazil
= 1.0.10 =
* fixed retries for subscription day

== Translations ==
* English - default, always included
* English : Spanish, Español de españa, Colombia, México y Perú

== Additional Info ==
**Contribute** [repository on github](https://github.com/saulmoralespa/subscription-payu-latam)

== Credits ==
*  [Saul Morales Pacheco](http://saulmoralespa) [@saulmoralespa](http://twitter.com/saulmoralespa)