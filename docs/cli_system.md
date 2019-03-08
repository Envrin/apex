
# CLI -- System / Maintenance

There are several CLI commands available for general system maintenance, such as addinf / updating repositories, updating connection information 
for both mySQL master database and RabbitMQ, and more.  Below explains all system maintenance commands available.


### `add_repo URL [USERNAME] [PASSWORD]`

**Description:** Adds a new repository to the system, which is then checked for available packages, themes and upgrades.  The URL is the base URL of the repository, and you 
may optionally specify a username and password in case of a private repository, which will be provided to you by the owner of the repository.

**Example:** `php apex.php add_repo http://repo.somevendor.com myuser mypassword`


### `update_repo URL`

**Description:** Allows you to update an exsiting repository already in the system with a username and password.  Specify the URL of the repo, and assuming 
it is already configured on the system, you will be prompted for a username and password, which will be provided to you by the owner of the 
repository.  This is to gain access to any private packages you may have purchased.

**Example:** `php apex.php update_repo repo.somevendor.com`



### `server_type TYPE`

**Description:** Changes the server type of the installation.  The TYPE must be one of the following:  `all, web, app, dbm, dbs, msg`.

**Example:** `php apex.php server_type web`


### `update_masterdb`

**Description:**Allows you to update connection information for the master mySQL database.  Useful if the database information has changed, as upon that happening, 
you will no longer be able to access the software and this information is stored within redis versus a plain text file.

**Example:** `php apex.php update_masterdb`


### `clear_dbslaves`

**Description:** Clears all slave database servers from redis, and begins only connecting to the master mySQL database.  This is useful if connection information on 
one or more slaves has changed without first being updated in the software, as it will result in various connection errors by the software.  Please note, upon clearing the slave database servers, 
you must etner the necessary slaves again via the Settings->General menu of the administration panel.

**Examploe:** `php apex.php clear_dbslaves`


### `update_rabbitmq`

**Description:** Allows you to update the connection information for RabbitMQ.  Useful if your connection information has changed, as it may result 
in the software throwing errors, and the RabbitMQ connection information is stored within redis versus a plain text file.


### `compile_core`

**Description:** Should never be needed, and compiles the core Apex framework for the Github repository.  Places the Github repo of Apex within the /tmp/apex/ directory.

**Example:** `php apex.php compile_core`



