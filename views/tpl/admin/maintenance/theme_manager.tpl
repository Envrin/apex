
<h1>Theme Manager</h1>

<e:form>

<e:tab_control>

    <e:tab_page name="Public Site">
        <h3>Public Site</h3>
        <p>Below shows the current theme being used for the public web site, plus all themes that are available to you from the configured repositories on this system.</p>

        <e:form_table><tr>
        <td valign="top"><img src="/themes/~config.core:theme_public~/screenshot.png" border="0"></td>
        <td valign="top">
            <b>Current Theme:</b> 
            <select name="theme_public"><option value="public_coc">Coco - Public Site</option></select><br />
            <center><e:submit value="update_public" label="Change Public Site Theme"></center><br />
        </td></tr></e:form_table><br />

        <h3>Available Themes</h3><br />

    </e:tab_page>

    <e:tab_page name="Member Area">
        <h3>Member Area</h3>
        <p>Below shows the current theme being used for the member's area, plus all themes that are available to you from the configured repositories on this system.</p>

        <e:form_table><tr>
        <td valign="top"><img src="/themes/~config.users:theme_members~/screenshot.png" border="0"></td>
        <td valign="top">
            <b>Current Theme:</b> 
            <select name="theme_members"><option value="members_coco">Coco - Member Area</option></select><br />
            <center><e:submit value="update_members" label="Change Member Area Theme"></center><br />
        </td></tr></e:form_table><br />

        <h3>Available Themes</h3><br />

    </e:tab_page>


    <e:tab_page name="Admin Panel">
        <h3>Admin Panel</h3>
        <p>Below shows the current theme being used for the administration panel, plus all themes that are available to you from the configured repositories on this system.</p>

        <e:form_table><tr>
        <td valign="top"><img src="/themes/~config.core:theme_admin~/screenshot.png" border="0"></td>
        <td valign="top">
            <b>Current Theme:</b> 
            <select name="theme_admin"><option value="limitless">Limitless</option></select><br />
            <center><e:submit value="update_admin" label="Change Admin Panel Theme"></center><br />
        </td></tr></e:form_table><br />

        <h3>Available Themes</h3><br />

    </e:tab_page>


</e:tab_control>


