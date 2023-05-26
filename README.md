# com.klangsoft.overrides

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