### Extension File Overrides

Select an override to few various file comparisons, and to download a ZIP archive containing some useful files to help with updating the override.

<i class="crm-i fa-bolt"></i> Conflict - Multiple extensions override the file, that's not possible! One of the extensions should be disabled to resolve the conflict.

<i class="crm-i fa-exclamation-triangle"></i> Modified - The core file has been changed, and those changes need to be incorporated into the override.

<i class="crm-i fa-server"></i> Server - The installed copy of the file has been modified on the server. If the extension is site specific, those changes should be incorporated into the override. Otherwise, the extension is interferring with those changes.

<i class="crm-i fa-clock-o"></i> Future - The extension was released after the installed version of CiviCRM and the file has changed. Future changes, from the perspective of the installed CiviCRM, are being incoporated via the extension. This may or may not be good. Ideally, the extension should have it's `<compatibility><ver>` in `info.xml` set to the CiviCRM version shown next to the release date of the extension.

