Plugin: Woopra
URL: http://www.awhitebox.com/woopra-plugin-for-habari
Version: 0.6
Author: Ali B.

Purpose 

The Woopra plugin allows you to enable Woopra's tracking service for
your site/blog. It also lets you exclude the inclusion of the code for
certain users of your site and to tag visitors if they are logged in.

Requirements 

The theme must call $theme->footer() in its footer template. If for
some reason the template does not call that method, you can simply add
the following to your template's footer.php, right before the </body>
closing tag: <?php $theme->footer(); ?>

Installation

1. Copy the plugin directory into your user/plugins directory.
2. Go to the plugins page of your Habari admin panel.
3. Click on the Activate button for Woopra plugin.

Configuration

If you have a woopra account, activating the plugin will enable Woopra
tracking. You can also specify whether you want to tag memebers of
your site when they visit, check the 'Enabled' check box and decide
what type of avatar you want Woopra to display for those visitors:
	*Disabled: No avatar is displayed
	*Local user image: Habari has a field to specify the image URL for registered users, this option will set Woopra to display that image.
	*Gravatar: Based on the registered user email, his/her Gravatar will be displayed in Woopra.

Uninstallation

1. Got to the plugins page of your Habari admin panel.
2. Click on the Deactivate button.
3. Delete the 'woopra' directory from you user/plugins directory.

Changelog

Version 0.6 - Fix Exclude typo
Version 0.5 - Removed site id from configuration and JS code per last Woopra engine update (http://tinyurl.com/woopra-update).
Version 0.4 - Updated the configuration to work with the New FormUI. Now compatible with Habari 0.5 and 0.6-alpha (revision 2458).  
Version 0.3 - Initial release
