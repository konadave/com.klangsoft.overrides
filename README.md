# com.klangsoft.overrides

## Extension File Overrides

An extension may supply modified copies of core PHP and template files, and by doing so causes CiviCRM to use those files in place of the original so long as the extension is enabled. There's nothing wrong with doing this; some functionality would be very difficult, if not impossible, to implement otherwise. The only downside is that they can be hard to maintain.

So what happens when the core file that the extension has overridden gets modified? CiviCRM will continue to use the overriden file provided by the extension. This means a security patch may not get applied, or you might miss out on some new feature that was added, etc.

This extension makes it easier to keep track of which extensions override core files, and to detect when a core file that's been overriden gets updated.

The first time you access the extension, at http://yourdomain/civicrm/overrides, it will create a snapshot of the core files that are being overridden in your installation. Any time in the future when you make a change to your site that could include core CiviCRM files, revist the extension page. If any of the files have changed, they will be listed in red.

If a file is shown as changed, you should first notify the maintainer of the extension to let them know that their extension may need to be updated to work with the latest core file(s). If you are unable to reach the maintainer, or they're not interested in doing anything about it, then you or someone in your IT department should look into what changed and merge them into the extension's overrides. The other option is to disable the extension.

After all changes have been resolved, have the extension save an updated snapshot.