
# CLI -- Package / Upgrade Commands

Various CLS commands are available allowing you to easily create, publish, and delete packages and upgrades within repositories.  Below explains all CLI commands available.


### `create_package PACKAGE [REPO_ID]`

**Description:** Creates a new package on the local system, which can then be developed, and later published to a repository.  This creates the necessary directories within /etc/ and /src/, and upon creation, you can begin creting components on the package via the apex.php script.

**Example:** `php apex.php create_package casino`


### `scan PACKAGE`

**Description:** Scans the */etc/PACKAGE/packge.php* configuration file, and updates the database as needed.  Use this during development, after you have updated the package.php file with new information such as config variables or menus, run this to reflect the changes within the database and system.

**Example:** `php apex.php scan casino`


### `publish PACKAGE`

**Description:** After developing a package, you may publish it to the repository using this command.  This will compile the package, and upload it to the repository,.  Once done, you can then begin installing the package on other systems via the `install PACKAGE` command.

**Example:** `php apex.php publish casino`


### `delete_package PACKAGE`

**Description:** Completely removes a package and all of its components from the system.  Please note, this does not remove the package from any repositories it has been previously uploaded to, and only removes it from the local system.



### `create_upgrade PACKAGE [VERSION]`

**Description:** Creates a new upgrade point on the specified package.  You may optionally define a version for the upgrade, and if left unspecified, the system will simply increment the third element of the current version by one.  This will basically create an "image" of the package by creating a SHA1 hash of every file, and upon publishing the package, these hashes will be checked allowing the system to determine all modifications made to the package since the upgrade point was created.  This allows for virtually hands free version control.

**Example:** `php apex.php create_upgrade casino`


### `publish_upgrade [PACKAGE]`

**Description:** Once you've completed developing an upgrade after creating the upgrade point, you can upload it to the repository using this command.  Once uploaded, all systems with the package installed can install the upgrade on their system with the `apex.php upgrade` command.

**Example:** `php apex.php publish_upgrade casino`


### `create_theme THEME_ALIAS`

**Description:**  Creates a new theme, which can then later be published to a repository if desired.  This creates the necessary directory structure within the /themes/ and /public/themes/ directories, from which you can begin creating the theme.

**Example:** `php apex.php create_theme mydesign`


### `publish_theme THEME_ALIAS`

**Description:** Will publish the specified theme to the repository.  Once published, it can be installed on any system with the `apex.php install_theme THEME_ALIAS` command.

**Example:** `php apex.php publish_theme mydesign`


### `install_theme THEME_ALIAS [PURCHASE_CODE]`

**Description:** Will download the specified them from the repository, and install it on the system.  Apex integrates with the Envato (ThemeForest) marketplace, 
and many of the themes integrated into Apex will be listed on ThemeForest.  You can get the full URL to the theme on ThemeForest via the Maintenance->Theme Manager menu.  If your desired theme is listed on ThemeForest, you must first purchase it, at 
which time you will receive a license key text file, and inside that is your purchase code.  You must specify your purchase code when installing the theme, as it will be checked via Envato's API to verify the purchase.

**Example:** `php apex.php install_theme mydesign`


### `delete_theme THEME_ALIAS`

**Description:** Deletes the specified theme from the system, including all files and directories.  Please note, this only deletes the theme from your local system, and not any repositories.

**Example:** `php apex.php delete_theme mydesign`


### `change_theme AREA THEME_ALIAS`

**Description:** Allows you to change the currently active theme on an area.  The `AREA` should be either "public" or "members", and then the alias of the theme you would like to activate.  The theme must already be installed on the system.

**Example:** `php apex.php change_theme public mydesign`
  


### 

### 

