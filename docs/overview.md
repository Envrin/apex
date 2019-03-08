
# General Structure / Overview

Apex has a modular design allowing you to easily customize the system as desired, and although is fairly structured allowing for efficient and standardized development, also provides 
all the flexibility you need as a developer.  Apex has a very straight forward structure that is easy to learn, and is specifically designed for online 
operations that consist of an administration panel, public web site, and generally a member's area.


### Directory Structure

The below table lists the various directories within Apex, and their use.

Directory | Description
------------- |-------------
/data/ | Stores all necessary data, such as backup archives, repository archives if you're running a repository, etc.
/docs/ | This user guide, plus if available, there will be a sub-directory for each package installed with .md files providing documentation for that specific package.
/ etc/ | Configuration files.  The main config.php file, plus one sub-directory for each package installed containing the package.php configuration file, SQL install / remove files, updates, etc.
/lib/ | The system libraries.  These are libraries used to power the core of Apex.
/log/ | Stores the main access_log log file, plus all debug sessions.
/public/ | The public document root of the server.
/public/themes/ | Contains one sub-directory for every theme installed, and includes all theme files that need to be publicly available such as Javascript and CSS files.
/src | The PHP code for all installed packages.  Contains a sub-directory for each installed package, the library files pertaining to the package are directly inside, plus various other sub-directories depending on which components the package consists of.
/themes/ | Contains one sub-directory for each theme installed, and consists of various theme files that do not need to be available publicly, such as the layout files, header and footer, etc.
/tmp | Any temporary files, such as when unpacking a package / upgrade that's been downloaded, when compiling a package to be published to a repository, etc.
/vendor/ | The standard Composer /vendor/ directory.
/views | Contains all templates / TPL files, which are displayed within the web browser.  Also includes a /php/ sub-directory, which contains all the PHP code executed for each specific template.
/apex.php | The main apex.php script that is used for various development / maintenance functions, such as creating components, publishing and instaling packages, installing upgrades, and more.


### Namespaces / Autoloader

All Apex code exists within the `apex` namespace, it uses it's own small autoloader found at `/src/autoload.php`, plus also loads Composer's standard autoloader 
for any packages installed via Composer that you need.  

Although not strictly PS4 compliant, the Apex autoloader first tries every namespace being loaded.  It checks 
if the namespace begins with `apex`, and if so, drops it and checks the following directories for the file:

- /lib/
- /lib/third_party/
- /src/

For example, `apex\template` would load the file at /lib/template.php, `apex\core\forms` would load /src/core/forms.php, and 
`apex\users\form\register` would load /src/users/form/register.php.

If the Apex autoloader doesn't find the necessary class, Composer's autoloader will take over, and 
check the /vendor/ directory for the appropriate class.


### Registry

All request / response data is handled by the `apex/registry` class, and is used extensively throughout Apex.  It consists of all input arrays, the URI / action being requested, configuration variables, the 
authenticated user and their language / timezone, the response to output, etc.  For full information, please visit the [Request Handling](request_handling.md) page.


