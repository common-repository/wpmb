=== WPMB ===
Contributors: bargolf
Tags: motionbased, garmin, running, fitness, cycling
Tested up to: 2.5
Stable tag: 1.2

A plugin to show your most recent MotionBased activities in the side bar.

== Description ==


This plugin provides a side-bar widget that reads your five most recent public motionbased activities via
the RSS feed from your motionbased.com account and displays them in the side bar area of your WordPress site.

The plugin can be configured to show various parameters about your activities including; distance, duration,
location and event type. In addition distances can be shown in either Miles, Kilometers or both.

== Installation ==


1. Upload `wpmb.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in Wordpress
1. Drag the WPMB widget from the 'Available Widgets' area to the required side bar on the 'Widgets' menu in the 'Presentation' area of Wordpress
1. Configure the plugin by clicking on the icon in the right hand area of the widget box.


== Frequently Asked Questions ==

= I don't see any activities on my site after activating the plugin =

It's not enough to just activate the plugin. You need to drag the plugin widget from the 'Available Widgets' area of the 'Widgets'
page in the 'Presentation' area of Wordpress to the desired position in the Sidebar area.

= I don't see any activities on my site after dragging the widget over =

Did you configure the Widget with your MotionBased.com account name? You need to click on the little icon that is in the right hand area of
the widget once you've dragged it to the sidebar. This pops up a window for you to configure the plugin's properties. Once you're done click
on the 'X' in the top-right of the pop-up then click the 'Save Changes>>' button.

= I've dragged the widget and configured it. There was nowhere to put my password! =

The WPMB plugin does not require your MotionBased.com password - it derives all of its information from your publicly available 
MotionBased activity RSS feed.

= I've dragged the widget and configured it. I still don't see anything =

Have you got the right account name? For example, my MotionBased.com account is 'bargolf', note _not_ bargolf.motionbased.com. You can check
you've got the right account name by typing http://<your account name>.motionbased.com into your web-browser address area. This should show
you your MotionBased activity digest.

= I've got the right account name, but I _still_ don't see anything =

The WPMB plugin can only access your public MotionBased activities that are currently published through you're MotionBased activity digest.
Try entering http://<your account name>.motionbased.com/rss into your browser and see if it shows you anything. If it doesn't this may be
because you haven't uploaded any MotionBased.com activities yet - or you have but you haven't made them public.

= How do I make an activity public in MotionBased so that WPMB can see it =

Visit MotionBased.com and login using your account details. Go to your 'Digest' page and click on the activity you wish to publish. Then
click on the 'Activity Options' link toward the top-right of the activity details page. Make sure the 'Private: Hide from TrailNetwork' tick
box is unticked then click 'Submit'.

= Why does WPMB only show 5 activities - I have many more than this =

*FIXED* - This limitation is no longer present. You can now configure the number of activities to show.

= This is cool - I wish I could have this on my FaceBook profile page =

You can! This plugin is derived from code I originally wrote for my FaceBook application 'MyMotionBased'. Just visit 
http://apps.facebook.com/mymotionbased/ to see what that application can do.


== Change Log ==

*1.2*

* Fix to use Curl library if fopen URL wrapper isn't enabled (allow_url_fopen)

*1.1*

* Added configuration option to allow the number of activities to show to be specified
* Fixed erroneous tag closure in configuration page
* Fixed typo on configuration page

*1.0*

* Initial release
