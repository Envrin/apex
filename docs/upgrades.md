
# Create, Publish and Install Upgrades

**NOTE:** Upgrades are still under heavy development, and not currently working correctly.  Nonetheless, this will give you an understanding of how they will work once finalized.

For all intents and purposes, Apex provided automated version control.  It will keep track of all files / components that 
have been modified, added or deleted, and will compile them into an upgrade that is published to the appropriate repository.  From there, 
the upgrades can be instantly installed on any Apex system with that package installed with one simple command.


### Create Upgrade Point

After you have initially published your package, you will want to create an upgrade point on it.  Open up terminal, 
change to the Apex installation directory, and type:

`php apex.php create_upgrade PACKAGE`

This will ask you for the next version number, and will create the upgrade point in the system.  Upon doing so, Apex will also 
scan all files within the package, and save their SHA1 hash.  This is used later when publishing the package to determine which files were modified.


### Upgrade Structure

After creating an upgrade point, a new directory will be created at /etc/PACKAGE/upgrades/VERSION.  This directory contains an upgrade.php file, which 
will be explained later in the next Apex upgrade as it's currently still under development, but it will be the configuration file for the upgrade similar to the 
package.php configuration file.  

This directory also contains install.sql and rollback.sql files, which allow you to execute SQL statements against the database during the upgrade 
and rollback if one occurs.


### Publish Upgrade

Once you have completed the upgrade, naturally you first want to test it by running all your unit tests via phpUnit with:

`php apex.php test PACKAGE`

Once you're ready to unleash the upgrade upon the world, simply type:

`php apex.php publish_upgrade PACKAGE`

It will compile the upgrade as necessary, and publish it to the repository where the package resides.  The upgrade is now 
instantly available to all Apex systems with the package installed.


### Installing Upgrades

Installing upgrades couldn't be easier.  If desired, you can always view a list of installed packages that have new upgrades available 
via the Maintenance-&gt;Package Manager menu of the administration panel.  To install avilalbe upgrades, simply open up terminal and type:

`php apex.php upgrade`

It will list all available upgrades, and confirm you wish to install them.  It will then simply download the appropriate upgrades from their respective 
repositories, and install them on the system.


### Rollback Upgrade

Apex will also fully support rollbacks of upgrades.  This feature is still under development, and will be released in the next upgrade of Apex.



