# pafiledb 
pafileDB Download Manager for phpBB and MXP-CMS
(based on https://www.phpbb.com/community/viewtopic.php?t=56035 and https://www.phpbb.com/community/viewtopic.php?t=2421316)
# Author [Credits]
Mohd Basri, PHP Arena, Jon Ohlsson (jonohlsson@hotmail.com) aka Haplo, dmzx, FlorinCB aka orynider
(https://sourceforge.net/p/mxpcms/svn/HEAD/tree/orynider/pafiledb/)

#Features 
Full featured File Manager with for example commenting, ratings, etc. Also nice blocks to list latest files and quick downloads.


# Install
1. Download the latest release.
2. In the `ext` directory of your phpBB board, create a new directory named `orynider` (if it does not already exist).
3. Copy the `pafiledb` folder to `phpBB/ext/orynider/` (if done correctly, you'll have the main extension class at (your forum root)/ext/orynider/pafiledb/composer.json).
4. Navigate in the ACP to `Customise -> Manage extensions`.
5. Look for `Download Manager` under the Disabled Extensions list, and click its `Enable` link.

## Uninstall
1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `Download Manager` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/orynider/pafiledb` folder.

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)
