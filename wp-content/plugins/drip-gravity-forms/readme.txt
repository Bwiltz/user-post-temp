=== Drip Email Campaigns + Gravity Forms ===
Contributors: gravityplus, getdrip
Donate link: https://www.getdrip.com/
Tags: gravity forms, gravityforms, drip, getdrip, email, email marketing, marketing automation
Requires at least: 3.0.1
Tested up to: 4.7
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates Gravity Forms with personalized Email Marketing tool Drip.

== Description ==

Static, one-to-many broadcast email newsletter communication is dying, and **marketing automation is [the future of email marketing](https://www.getdrip.com/blog/email-marketing/why-marketing-automation-is-the-future-of-email-marketing/?utm_source=wordpress.org&utm_medium=web&utm_campaign=drip-gravity-forms)**.

**Does your current email solution** allow you to _easily send emails to your list based on any action they take_ inside your emails and on your website? Like

* expressing interest in a topic
* downloading a sample chapter of your book
* starting a trial of your software, or
* viewing your upgrade page

Does it _view an email address as a single person_, no matter which “campaign” or “list” they are subscribed to?

Does it give you the ability to _craft your entire user journey in a gorgeous, visual flow_?

Can you use automated lead scoring to _pinpoint your hottest prospects_ and then automatically add them to your favorite CRM software?

Does it _prune your list_ of inactive subscribers so you're not paying more than you need?

If you want to use email to

* **inspire your target customers and earn their trust**
* **transform skeptical users into successful customers**
* **turn successful customers into raving fans who bring new business straight to your door**

_You should try Drip_.

> **[Drip](https://www.getdrip.com)** is lightweight email marketing automation that doesn't suck. It is *the* easiest way to build your email list and send personalized email campaigns to **the right person at** exactly **the right time**.

This plugin makes it dead simple to build your own custom forms with [Gravity Forms](https://rocketgenius.pxf.io/c/1212782/445235/7938), and send data to Drip whenever the form is submitted. _Note: You'll need to have your own copy of **[Gravity Forms](https://rocketgenius.pxf.io/c/1212782/445235/7938)** (any license)_

When a form is submitted, you can

* Create or update subscribers
* Create custom fields
* Record new events with custom properties
* Tag or untag subscribers
* Subscribe or unsubscribe users to campaigns
* Intelligently choose which Drip action to perform based on the information that's submitted
* And more...


= The Drip Process =

1. Simply [sign up for a Drip account](https://www.getdrip.com/pricing)
2. [Install this plugin](https://wordpress.org/plugins/drip-gravity-forms/installation/) to integrate your Gravity Forms with Drip's email marketing software

= More Information =

- [Try Drip free for 21 days](https://www.getdrip.com)
- [How does Drip work?](https://www.getdrip.com/tour)

= Need Help? =

[Contact our friendly support staff](https://www.getdrip.com/contact) for assistance.

### Translations welcome!

== Installation ==

### Prerequisites

* Make sure you have your own copy of _[Gravity Forms](https://rocketgenius.pxf.io/c/1212782/445235/7938)_. This plugin does not include Gravity Forms, but will work with any of the Gravity Forms licenses.

* You'll also need a [Drip](https://www.getdrip.com) account


### Steps

1. Upload the plugin to your WordPress site. There are three ways to do this:

    **WordPress dashboard search**

    * In your WordPress dashboard, go to the **Plugins** menu and click the _Add New_ button
    * Search for `Gravity Forms Drip`
    * Click to install the plugin that says Drip Email Campaigns + Gravity Forms by gravity+ for Drip

    **WordPress dashboard upload**

    * Download the plugin zip file by clicking the orange download button on this page
    * In your WordPress dashboard, go to the **Plugins** menu and click the _Add New_ button
    * Click the _Upload_ link
    * Click the _Choose File_ button to upload the zip file you just downloaded

    **FTP upload**

    * Download the plugin zip file by clicking the orange download button on this page
    * Unzip the file you just downloaded
    * FTP in to your site
    * Upload the `drip-gravity-forms` folder to the `/wp-content/plugins/` directory

2. Visit the **Plugins** menu in your WordPress dashboard, find `Gravity Forms Drip Add-On` in your plugin list, and click the _Activate_ link

3. Visit the **Forms->Settings** menu, select the _Drip_ tab, and add your Drip API token. Save your settings.


### What's next?

**Get the field guide** where we'll walk you through setting up your form, connecting it to Drip, and maximizing your integration to increase your conversions.


== Frequently Asked Questions ==

= Do I need to have my own copy of Gravity Forms for this plugin to work? =
Yes, you need to install the [Gravity Forms plugin](https://rocketgenius.pxf.io/c/1212782/445235/7938 "visit the Gravity Forms website") for this plugin to work.

= Do I need to have a Drip account? =

Yep. [Sign up here](https://www.getdrip.com/pricing) for a free trial.

= Who can I contact for help? =

We're always happy to help with setting it up - just [contact us](https://www.getdrip.com/contact) to get started!

== Screenshots ==

1. Find your API Token in your Drip's Settings > General Settings page
2. Enter your API token on the Gravity Forms Drip settings page
3. Add a Drip feed

== Changelog ==

= 2.1.1 (November 2019) =
* Fix bug where custom fields with a blank identifier are sent.

= 2.1.0 (September 2016) =
* Add WP 4.6 compatibility

= 2.0.0 (April 2016) =
* Add Gravity Forms Add-On Framework, which brings standard UI & developer hooks
* Add mapping any form field or metadata to any Drip custom field
* Add new Drip actions: subscribe/unsubscribe
* Add new Drip options: tagging, custom event properties, user ID, campaign email start, lead scoring
* Add updated conditional logic options
* Add PayPal integration so Drip action is only taken when payment is successful
* Add new Drip WordPress API for developers
* Add pot file for translators

= 1.2.3 =
* Tweaked naming of posted variable to fix custom field naming conflict

= 1.2.2 =
* Changed name of internal API wrapper class to avoid naming conflict with an external API wrapper

= 1.2.1 =
* Added support for checkbox lists

= 1.2.0 =
* Stop populating "occurred_at" in create event

== Upgrade Notice ==

= 2.1.0 =
WordPress 4.6 compatibility

= 2.0.0 =
New features, new UI, fixes
