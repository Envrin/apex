
# Create a Repository

If desired, you may easily setup your own repository where you can host your own private packages, copy existing public packages over to enahcen them on your own, or 
anything else you may need.  To create a repository, first ensure the Development Toolkit package is installed on your server.  Login via SSH, 
change to the installation directory and type:

`php apex.php install devkit`

Once installed, visit the Devel Kit->Settings menu of the administration panel, and define the repository settings as desired.  Next, create a user via the Users->Create New User menu of the 
administration panel, then add your repository to the configuration by typing the following at the terminal:

`php apex.php add_repo http://localhost/ USERNAME PASSWORD`

Obviously, change the above variables as necessary.  Ensure the URL points to your server, and you specify the username and password of the user you just created.  Now go ahead and install / download any packages you desire with the 
standard `php apex.php install PACKAGE` command.  For any packages you wish to host yourself instead of using the main public Apex repository, visit the 
Devel Kit->Packages menu of the administration panel, manage the desired package(s) and change the repository to your own.  Once done, when you publish the package or any upgrades, they will be uploaded to your 
own repository.

That's it!  Now when installing systems on other servers, just add your repository same as you did above, and that system will then begin searching your repository for any 
available packages and upgrades as well.


