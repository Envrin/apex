
# Installation

Installation of Apex is very simple.  We suggest Ubuntu 18.04, but any LINUX distribution will work.  Ensure you're running PHP 7.2 or later, then ensure your server meets the requirements with the following command via SSH.

~~~
sudo apt-get update
sudo apt-get install redis rabbitmq-server libfreetype6-dev php php-mbstring php-json php-curl php-zip php-mysqli php-tokenizer php-redis php-bcmath php-gd php-gmp composer mysql-server
~~~

Once done, install Apex by following the below steps:

1. Grab the Apex code with:  `git clone https://github.com/envrin/apex.git`
2. Rename the newly created /apex/ directory as necessary, and modify Niginx / Apache configuration so the /public/ sub-directory is the document root of the server.
3. Change to the installation directory, and run: `composer update`
4. Start the installation wizard with `php apex.php`.  The wizard should be quite straight forward, and if unsure, just leave the server type to the default of "all".
5. Follow any instructions the installation wizard provides, such as moving the apex script to the /etc/init.d/ directory, etc.
6. Done!  Enjoy Apex.

### Installing Packages

Once the base installation is done, you can easily install additional packages.  You may list 
all packages available with:  `php apex.php list_packages`

You can install a package with, for example for the "users" package:  `php apex.php install users`

You can also install multiple packages at one time with for example:  `php apex.php install users transaction support`


### Private Packages

You may also have access to private packages.  In this case, you must first update the repository with your login details before you can install the package.  To do this, run:

`php apex.php update_repo apex.envrin.com`

When prompted, enter the username and password you were provided.  Once done, you can go ahead and install your private packages as normal with:  `php apex.php install PACKAGE`


### Running Multiple Workers

If you are running a more intensive operation with heavy resource usage and processing times (eg. processing the entire bitcoin blockchain), you may want to 
spawn multiple worker processes and have the load evenly distributed amongst them, helping provide much fater processing times.  For example, instead of having only one process handling the entire 
load, you can spread the load evenly amongst five processes.

To do this, first entire your server type is set to "web", by typing the following at the terminal:  `php apex.php server_type web`

This will switch your server type to "web", meaning 

