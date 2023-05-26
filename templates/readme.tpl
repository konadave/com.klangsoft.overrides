### {$ext.label}
{$ext.path}/**{$fn}**

After all overrides for this extension have been addressed, update `info.xml` as shown.
```xml
  <releaseDate>{$releaseDate}</releaseDate>
  <version>{$ext.version} upped in some way</version>
  <compatibility>
    <ver>{$coreVer}</ver>
  </compatibility>
```
The archive contains the following files:
* *README.md* - what you're reading now.
* *{$cn}-{$extVer}* - core version of the file that was current as of extension `releaseDate`.
* *{$cn}-{$coreVer}* - core version of the file for the currently installed version of CiviCRM.
* *patch-1.diff* - diff between *{$cn}-{$extVer}* and the extension; **#1 - Changes made by the extension**
* *patch-2.diff* - diff between *{$cn}-{$extVer}* and *{$cn}-{$coreVer}*; **#2 - Changes since made by CiviCRM**
{if $numPatches > 2}
* *patch-3.diff* - diff between changes on server and *{$cn}-{$coreVer}*; **#3 - Changes made on server filesystem**
{/if}
* *{$cn}-ext2core* - attempted merge; **#4 - Merge override into latest**
* *{$cn}-core2ext* - attempted merge; **#5 - Merge latest into override**

The last two files are the potential replacement for the override in the extension. How well the changes merged depends on how extensive the changes made by the extension and to the core file. Even if the merge is clean (#4 or #5 marked green in user interface), care should be taken to ensure that the changes made to the core file don't break the extension and that the changes made in the extension don't now break core.

#### About The Diffs
File comparison and patching are performed using the [Google diff-match-patch](https://github.com/google/diff-match-patch) library, which produces diffs in [Unidiff](https://github.com/google/diff-match-patch/wiki/Unidiff) format. Unlike other diff formats that include contextual text to help locate what's to be replaced, the Unidiff format relies more on finding the right looking text in the general area it's expected; on more complex changes you might have better luck using an external diff tool such as [Meld](https://meldmerge.org/).