
# Testing via phpUnit

**NOTE:** Unit tests are currently only very minimally implemented.  You can basically ignore this page for now, as it will be heavily modified shortly.

Apex fully integrates with the popular phpUnit to allow for unit tests.  Simply create a new unit test by going into terminal, change to the Apex installation directory, and type:

`php apex.php create test PACKAGE:ALIAS`

You can then automatically run the unit tests with:

`php apex.php test PACKAGE`

The above will run all unit tests in the given package.

