
<h1>Package Manager</h1>

<e:form>

<e:tab_control>

    <e:tab_page name="Installed Packages">
        <h3>Installed Packages</h3>

        <p>The below table lists all packages currently installed on this system, including any available upgrades.  To install upgrades or remove packages, 
        you must do so via SSH and the apex.php script.  Please consult the documentation for further information.</p>

        <e:function alias="display_table" table="core:packages">

    </e:tab_page>

    <e:tab_page name="Available Packages">
        <h3>Available Packages</h3>
<p>The below table lists all packages that are currently available, but not installed on your system.  To install a package, you must do so via SSH with the apex.php script.  Please consult the docuemtation for further details.</p></br />

        <e:function alias="display_table" table="core:available_packages">

    </e:tab_page>

    <e:tab_page name="Repositories">
        <h3>Repositories</h3>

        <p>The below table lists all repositories currently configured on this system.  You may add /c manage login credentials to any repository listed by clicking the appropriate Manage button below.</p><br />

        <h5>Existing Repositories</h5>

        <e:function alias="display_table" table="core:repos">

        <h5>Add New Repository</h5>

        <p>You may add a new repository by entering the appropriate URL below, and once added, it will begin getting checked for any available packages and themes.  You only 
        need to enter a username and password if it's a private repository that you have been provided access to.</p>

        <e:function alias="display_form" form="core:repo">

    </e:tab_page>

</e:tab_control>



