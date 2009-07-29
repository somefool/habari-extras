Plugin: Simple Blacklist
URL: http://habariproject.org
Version: 1.3.3
Author: Habari Project

Purpose 

Any blog that allows people to comment will receive spam. Simple Blacklist can be a part of your spam fighting armament that will examine all comments against a list of words, phrases, and ip addresses that you manually add to Simple Blacklist. Any time there is a match, Simple Blacklist will silently discard the comment so you never have to see it or deal with it.

Requirements 

Habari 0.7 alpha or higher.

Installation

1. Copy the plugin directory into your user/plugins directory or the site's plugins directory.
2. Go to the plugins page of your Habari admin panel.
3. Click on the Activate button for Simple Blacklist.

Usage

Click on the Configure option for Simple Blacklist. The configuration dialog contains two settings.
1. A text box in which you enter the words, phrases, urls, and ip addresses. Each entry must be on its own line. Whenever you find a new item you wish to include in the blacklist, come back here and add it to the list.
2. A checkbox to allow you to decide whether frequent commenters must have their comments compared to the blacklist or not.

After you've made your changes to the options, click the Save button to save them to your database, then click the Close button to close the dialog.

Uninstallation

1. Got to the plugins page of your Habari admin panel.
2. Click on the Deactivate button.
3. Delete the simpleblacklist directory from your Habari installation.

Cleanup

1. The plugin places several items in your Options table. All are prepended with 'simpleblacklist__'. You can safely delete these entries after you uninstall the plugin.

Changelog
Version 1.3.3
Updated: Works with 0.7 XML changes per r3624.

Version 1.3.2
Fixed: Blacklist textarea is now resizable, and automatically alphabetized.

Version 1.3.1-alpha2
Change: Yes/No dropdown made into to a checkbox

Version 1.3.1-alpha
Fixed: ip addresses weren't being converted to quad form before being checked against the blacklist

Version 1.1.1
Fixed: Updated to use new user object properly

Version 1.1
Change: Changed configuration dialog to conform to Habari's updated form's interface
Change: Prepended name to entries in the options table changed from 'simpleblacklist:' to simpleblacklist_' to conform to new storage guidelines. You will need to copy your old blacklisted words, phrases, and ip addresses. After you do so, you can safely delete the old settings.

Version 1.0
Initial release
