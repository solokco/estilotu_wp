# WooCommerce Currency Switcher - Subscriptions Integration

## Version 1.x
####1.3.7.170607
* Improved compatibility with Subscriptions 2.2.7.
	* Fixed display of "From" price for variable subscriptions.

####1.3.6.170531
* Improved compatibility with Subscriptions 2.2.7.
	* Fixed handling of subscription switching. Thanks to Mr. T.Steur for the contribution.

####1.3.5.170425
* Improved compatibility with Subscriptions 2.2.5.
	* Fixed handling of subscriptions sale prices.

####1.3.4.170422
* Refactored logic used to calculate final subscription price. The new logic fixes the conversion in calls to static method WC_Subscriptions_Product::get_price_string() and the new functions `wcs_get_price_including_tax` and `wcs_get_price_excluding_tax`.

####1.3.3.170413
* Fixed logic handling renewals and resubscriptions.

####1.3.2.170405
* Fixed logic used to determine when product prices should be triggered.

####1.3.1.170405
* Improved compatibility with WooCommerce 3.0 and Subscription 2.2.
	* Altered conversion logic to ensure that subscription prices are converted correctly.
	* Updated requirements.

####1.3.0.160617
* Added handling of new exceptions introduced in WooCommerce 2.6. The new logic prevents WooCommerce from throwing a fatal error when an orphaned product variation is found.

####1.2.14.151215
* Fixed bug in saving of variations with WooCommerce 2.4 and Subscriptions 2.0. The bug prevented the variations from being saved, in some circumstances.

####1.2.13.151208
* Added workaround for bug #1040 of Subscriptions plugin. The bug caused the wrong currency to be used at checkout for subscription renewals. See  https://github.com/Prospress/woocommerce-subscriptions/issues/1040.
* Passed product price type to `convert_product_price_from_base()` call.

####1.2.12.151109
* Updated requirements.
* Fixed call to conversion logic for product prices. The new call triggers a filter that can be used to round product prices.
* Fixed loading of Messages controller. The controller now uses the correct tex domain.

####1.2.11.150910
* Updated download link for Aelia Foundation Classes.

####1.2.10.150824
* Fixed bug in update checking logic.
* Updated requirement checking class.

####1.2.9.150815
* Improved support for WooCommerce 2.4:
	* Fixed issue caused by the caching logic used to handle variations in WooCommerce 2.4.3.

####1.2.8.141010
* Changed links to point to new website at [http://aelia.co](http://aelia.co).

####1.2.7.141008
* Removed debug message.

####1.2.6.140820
* Fixed minor bugs in user interface:
	* Removed notice messages from pricing interface for simple and variable subscriptions.
	* Fixed reference to text domain variable in variable subscriptions pricing interface.

####1.2.5.140819
* Updated logic used to for requirements checking.

####1.2.4.140724
* Removed deprecated method `WC_Aelia_CS_Subscriptions::check_requirements()`.

####1.2.3.140715
* Fixed bug that prevented currency prices for non-subscription products from being saved.

####1.2.2.140704
* Fixed reference to root WC_Product class in Aelia\WC\CurrencySwitcher\Subscriptions\Subscriptions_Integration.

####1.2.1.140623
* Redesigned plugin to use Aelia Foundation Classes.

####1.2.0.140619
* Added support for variable subscriptions.

####1.1.8.140519-beta
* Added subscription coupons to the list of the coupons to be converted by the Currency Switcher.

####1.1.7.140419-beta
* Updated base classes.

####1.1.6.140414-beta
* Redesigned interface for manual pricing of simple subscriptions.

####1.1.5.140331-beta
* Implemented handling of manual prices for simple subscriptions.
* Cleaned up unneeded code.

####1.1.1.140331-beta
* Removed unneeded hook.

####1.1.0.140324-beta
* Implemented basic conversion of simple subscriptions.

####1.0.1.140318
* Updated base classes.

####1.0.0.140220
* Initial release.
