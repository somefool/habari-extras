Plugin: Woopra
URL: http://www.awhitebox.com/woopra-plugin-for-habari
Version: 0.4
Author: Ali B.

Purpose 

The Woopra plugin allows you to include Woopra's tracking code to the footer of your site without the need of editing footer.php of your template. It also lets you exclude the inclusion of the code for certain user of your site and to tag the visitors of your site if they are logged in.

Requirements 

The theme must call $theme->footer() in its footer template. If for some reason the template does not call that method, you can simply add the following to your template's footer.php, right before the </body> closing tag: <?php $theme->footer(); ?>

Installation

1. Copy the plugin directory into your user/plugins directory.
2. Go to the plugins page of your Habari admin panel.
3. Click on the Activate button for Woopra plugin.

Configuration

After activating the plugin, you'll need to specify your Site ID. You can obtain your site id from Woopra member area (http://www.woopra.com/members/).
You can also specify whether you want to tag memebers of your site when they visit, check the 'Enabled' check box and decide what type of avatar you want Woopra to display for those visitors:
	*Disabled: No avatar is displayed
	*Local user image: Habari has a field to specify the image URL for registered users, this option will set Woopra to display that image.
	*Gravatar: Based on the registered user email, his/her Gravatar will be displayed in Woopra.

Uninstallation

1. Got to the plugins page of your Habari admin panel.
2. Click on the Deactivate button.
3. Delete the 'woopra' directory from you user/plugins directory.

Changelog

Version 0.4 - Updated the configuration to work with the New FormUI. Now compatible with Habari 0.5 and 0.6-alpha (revision 2458).  
Version 0.3 - Initial release
