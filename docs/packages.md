
# Create, Publish and Install Packages

apex has a standardized modular design, which not only allows you to instantly install / remove packages from any system, but all packages fit together and interact with each 
other perfectly.  Plus you can also easily develop your own packages, and if desired upload them to a public repository, or to a 
private repository that you control, allowing you to easily distribute and maintain your commercial code to your clients.


## Package Basics

Each package has a directory located at */etc/PACKAGE_ALIAS* which holds 
configuration and installation details.  Below lists all files within this directory:

File | Description
------------- |------------- 
components.json | Will only exist once the package has been published, and is a JSON file containing information on all components included within the package.
package.php | The main package configuration file, and defines things such as configuration variables, hashes, and menus that are included in the package.  Full details on this package are found in a below section on this page.
install.sql | Optional, and if exists, all SQL code included within this file will be executed against the database upon installation.
install_after.sql | Optional, and if exists, all SQL within this file will be executed against the database at the very end of package installation, meaning after all configuration variables have been installed, and other PHP code has been executed.
| reset.sql | Optional, and if exists, all SQL code within will be executed when the package is reset from within the administration panel.  This is meant to clear all database from the package, and reset it to just after it was installed.
remove.sql | Optional, and if exists, all SQL code within this file will be executed against the database upon removal of the package.  Should drop all database tables created during installation.
/upgrades/ | Directory that contains details on all upgrade points created against this package.

All packages also have a sub-directory located at */src/PACKAGE_ALIAS* which holds all PHP code within the package.  The 
library files are storied directly within the sub-directory, plus there are also various other sub-directories inside which hold all PHP code for 
the various components created within the package.  For example, the PHP code for all HTML functions will be located at 
*/src/PACKAGE_ALIAS/htmlfunc/*, and so on.

Last, each package also has a sub-directory located at */docs/PACKAGE_ALIAS* to hold all .md files for docuemtnation of the package.


### Create Package

You can easily create a new package any time.  Within terminal change to the Apex installation directory, and type:

`php apex.php create_package PACKAGE_ALIAS`

Where `PACKAGE_ALIAS` is the alias of the new package is all lowercase.  This will prompt you for a few basic questions such as the full package name, 
and starting version number.  It will then add the package to the database, and create all necessary files and directories.


### package.php Configuration File

This is the main configuration file for the package.  At the top are a few properties for the 
package name, version, whether it's public / private package, and all should be fairly straight forward.  The `__construct()` function 
is where you populate various arrays defining the configuration of the package.  Due to its size, 
a separate page has been devoted to this function, which you can find at [package.php __construct() Function](packages_construct.md) page.


##### `Install_before()` and `install_after()`

These two functions allow you to execute additional PHP code before and after installation of the 
package.  The before code is executed before any installation has begun, not even the SQL code being executed.  The after code is executed once all installation is complete, 
even after all SQL code has been executed.


##### `reset()`

Optional, and executed whenever this package is reset, generally from within the administration panel.  This is meant to 
delete all data, and reset the package to just after it was initially installed.


##### `remove()`

Optional, and if exists is executed while the package is being removed from the system.


### Publish Package

Once development is complete, it is very easy to publish your package to a repository.  If you do not wish to publish to the main public Apex repository, 
manage the package via the Devel Kit-&gt;Packages menu of the administration panel, and change the repository assigned to the 
package.  If you wish to upload to your own private repository, please see the [Repositories](repos) page of this manual.

To publish your package, in terminal change to the installation directory, and type:

`php apex.php publish PACKAGE_ALIAS`

That's it.  The package will then be published to the repository, and can then be installed on any Apex system by typing:

`php apex.php install PACKAGE_ALIAS`



