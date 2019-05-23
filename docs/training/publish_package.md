
# Apex Training - Publish Package

Now that the development of our package is done, we need to publish it to a repository so it can be instantly installed 
on other systems.  The ability to setup a repository, publish your package, install packages on another install, and release upgrades is 
explained in this page.


### Setup Your Repository

If you recall, when we initially created the marketplace package, we specified to add it to the local repository, so let's ensure it's setup properly.  Login 
to the administration panel, and if you haven't already, create one user account via the Userse->Create New User menu.  Then 
visit the Devel Kit->Settings menu, ensure the repository is enabled, the URL correctly points to your system, and the username and password are of a valid user in the database.  


### Publish Package

You can very easily publish the package, simply open terminal and type:

`php apex.php publish marketplace`


### Install Package

It's extremely simple teasily install it on any Apex system on the internet.  If necessary, go ahead and 
install another clean Apex installation by following the [Installation Guide](../install.md).  Once you have a system you wish to 
install the package on, you first must add the repository by opening terminal and typing:

`php apex.php add_repo URL USERNAME PASSWORD`

With the URL being the full URL to the system hosting the repository and published package, and the username and password being the user you previously created in that system, for example:

`php apex.php add_repo http://127.0.0.1:8080 myuser mypassword`

Once the repository is successfully added to the system, install the package by simply typing:

`php apex.php install marketplace`

Voila, and that's all there is to it.  The marketplace package will now be successfully installed on the new Apex system.


### Release and Install Upgrades

Back in the development system, open terminal and type:

`php apex.php create_upgrade marketplace`

This will create a new upgrade point on the marketplace package, and from this point on, any modifications to any files or components will be tracked.  A new directory will also be 
created at */etc/marketplace/upgrades/1.0.1/* where you can define SQL code to execute upon installing the upgrade, etc.

Once the upgrade point has been created, go ahead and make any desired modifications to the marketplace package you wish.  Once you are happy with the changes, you can publish the upgrade and make it available to all 
systems by opening terminal and typing:

`php apex.php publish_upgrade marketplace`

That's it.  Now in the second Apex system that we just previously installed the marketplace package on, open terminal and simply type:

`php apex.php upgrade`

That will download the upgrade we recently published, and install it on the system.  This allows for hands free version control within Apex, without having to keep track of modifications made.


### Conclusion

That's the end of the Apex training guide for developers.  Naturally, it didn't go through every last aspect of Apex, but everything is fully docuemnted within the main 
documentation.  We hope you've enjoyed this guide, and look forward to hearing from you.


 

`php apex.php publish marketplace'


### Install Package

Now that the package has been published to a repository, we can i



