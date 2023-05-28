# Extension File Overrides

# EXISTING 1.X USERS PLEASE READ THIS!

The previous version of this extension was very basic. You would take a snapshot to indicate that your extensions are up to date. After running your CiviCRM upgrade, you would check back to see if any changes were found. It simply compared the overrides in the extension with the CiviCRM files on the server, that's it.

The newest version of this extension works quite a bit differently. It looks up the version of CiviCRM that was current as of the release date of the extension, and commpares the overrides in the extension against that version of the file, thus showing what changes the extension makes. Another comparison is made between that version of the core file and the currently installed version of CiviCRM to determine what changes have been made in CiviCRM since. It will even try to merge things up for you.

This works very well for general purpose, generally distributed extensions meant for public consumption. But for your in-house/client specific extensions, it depends on your vigilance. An example; looking at a client site that has a lot of customizations, we have a handful of custom extensions with several overrides each, all dating back to the late 2010s. The overrides in these extensions have been kept up to date, but nobody ever bothered to change the release date for them. Looking at *Extension File Overrides* for this client will produce "incorrect" results because of it.

Since I know the overrides are up to date, I could just go through and change the release date of each extension to that of the currently installed version of CiviCRM. What I should do, however, is check `git` history and set the release date of the extension based on latest update to an override.

You can check the [CiviCRM Release Notes](https://github.com/civicrm/civicrm-core/blob/master/release-notes.md) to find the release date for a specific version of CiviCRM.

---
# Extension File Overrides

An extension may supply modified copies of core PHP and template files (aka an override), and by doing so causes CiviCRM to use those files in place of the original so long as the extension is enabled. There's nothing wrong with doing this; some functionality would be very difficult, if not impossible, to implement otherwise. The major downside is that they can be hard to maintain.

So what happens when the core file that the extension has overridden gets modified? CiviCRM will continue to use the overridden file provided by the extension. This means a security patch may not get applied, or you might miss out on some new feature that was added, etc.

This extension keeps tabs on which core files are overridden by an extension and will set system status to critical whenever it sees the core file has been modified. From the extension user interface, you will be able to see which files are overridden, which have been updated, see what changes the extension originally made, what has changed in CiviCRM since, potential merges, and download a `.zip` with some files to help you get the changes incorporated into the override.

---

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.4+
* CiviCRM (*FIXME: Version number*)

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.klangsoft.overrides@https://github.com/konadave/com.klangsoft.overrides/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/konadave/com.klangsoft.overrides.git
cv en overrides
```

## Support

Want to provide some feedback? Did you find an issue that needs addressed? Do you have a feature request or suggestion for the documentation? Please check out the <a href="https://github.com/konadave/com.klangsoft.overrides/issues" target="_blank">issue queue</a> to create a new issue or chime in on an existing one.

Are you a developer looking to help out with some solid code? Please check out the <a href="https://github.com/konadave/com.klangsoft.overrides/issues" target="_blank">issue queue</a>, work up a merge request, and submit for consideration. All help is appreciated.

Are you an organization with some funds to spare and would like to aid the continued development of this and other quality CiviCRM extensions? Consider contributing to the "tip jar" by scanning the following PayPal QR code. Please don't feel obligated to contribute one of the suggested amounts; give what you can, any amount is both helpful and appreciated. Thanks!

![Tips are appeciated!](images/qrcode.png)