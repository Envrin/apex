
# CLI - General Commands

Various general commands are available such as listing, searching, and installing packages to your system.  Below lists all 
general commands.


### `list_packages`

**Description:** Lists all packages available to the system from all repositories configured on the system.

**Usage:** `php apex.php list_packages`


### `search TERM`

**Description** Searches all repositories configured on the system for a specific search term.

**Example:** `php apex.php search mailing list`


### `install PACKAGE1 PACKAGE2...`

**Description:** Downloads a package from a repository, and installs it on the system.  This command allways you to specify multiple packages at one time as well.

**Example:** `php apex.php install users transaction support`


### `upgrade [PACKAGE]`

**Description:** Checks the repositories, and automatically downloads and installs and available upgrades.  if desired, you may optionally specify a single package to upgrade.  Otherwise, all installed packages will be upgraded.  It's recommended to run this command about once a week to ensure your packages are always up to date.

**Example:** `php apex.php upgrade`


### `check_upgrades [PACKAGE]`

**Description:** Checks the repositories for any available upgrades, and provides information on the latest available version of each package.  This does not download or install the upgrades, and instead only provides what upgrades are availalbe.

**Example:** `php check_upgrades`


### `list_themes`

**Description:** Lists all themes available to this system from all repositories configured on this system.

**Example:** `php apex.php list_themes`


### `install_theme THEME_ALIAS`

**Description:** Downloads the specified theme from the repository, and installs it on the system.

**Example:** `php apex.php install_theme mydesign`


### `change_theme AREA THEME_ALIAS`

**Description:** Allows you to change the currently active theme on an area.  The `AREA` should be either "public" or "members", and then the alias of the theme you would like to activate.  The theme must already be installed on the system.

**Example:** `php apex.php change_theme public mydesign`
  






