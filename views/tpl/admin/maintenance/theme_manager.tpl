
<h1>Theme Manager</h1>

<e:form>

<e:tab_control>

    <e:tab_page name="Public Site">
        <h3>Public Site</h3>
        <p>Below shows the current theme being used for the public web site, plus all themes that are available to you from the configured repositories on this system.</p>

        <e:form_table><tr>
        <td valign="top"><img src="/themes/~config.core:theme_public~/screenshot.png" width="300px" height="225px" border="0"></td>
        <td valign="top">
            <b>Current Theme:</b> 
            <select name="theme_public">~public_theme_options~</select><br />
            <center><e:submit value="update_public" label="Change Public Site Theme"></center><br />
        </td></tr></e:form_table><br />

        <h3>Available Themes</h3><br 3>

        <p>Below shows all themes available to you from the repositories configured on this system.  To download and install a theme, login to your server via SSH, 
        change to the installation directory, and type:  php apex.php install_theme THEME_ALIAS [PURCHASE_CODE]</p>

        <p><b>NOTE:</b> The purchase code in the above command is only required if you see a "Visit ThemeForest" button included with the theme below.  If so, you can visit the theme on ThemeForest by clicking that 
        button.  You must first purchase the theme, at which point you will receive a license key in plain text which includes the purchase code.  You must specify that purchase 
        code when installing the theme, as it will verify the purchase via Envato's API.</p>

        <e:data_table><thead><tr>
            <th>Preview</th>
            <th>Alias</th>
            <th>Details</th>
        </tr></thead><tbody>

        <e:section name="public_themes">
        <tr>
            <td valign="top" align="center"><img src="~public_themes.thumbnail_url~" border="0" width="300[x" height="225px"></td>
            <td valign="top">~public_themes.alias~</td>
            <td valign="top">
                <b>~public_themes.name~</b><br />
                ~public_themes.description~<br />

                <b>Author:</b> ~public_themes.author_name~ (~public_themes.author_email~)<br />
                ~public_themes.themeforest_button~
            </td>
        </tr>
        </e:section>
        </tbody></e:data_table><br />

    </e:tab_page>

    <e:tab_page name="Member Area">
        <h3>Member Area</h3>
        <p>Below shows the current theme being used for the member's area, plus all themes that are available to you from the configured repositories on this system.</p>

        <e:form_table><tr>
        <td valign="top"><img src="/themes/~config.users:theme_members~/screenshot.png" width="300px" height="225px" border="0"></td>
        <td valign="top">
            <b>Current Theme:</b> 
            <select name="theme_members">~members_theme_options~</select><br />
            <center><e:submit value="update_members" label="Change Member Area Theme"></center><br />
        </td></tr></e:form_table><br />

        <h3>Available Themes</h3

        <p>Below shows all themes available to you from the repositories configured on this system.  To download and install a theme, login to your server via SSH, 
        change to the installation directory, and type:  php apex.php install_theme THEME_ALIAS [PURCHASE_CODE]</p>

        <p><b>NOTE:</b> The purchase code in the above command is only required if you see a "Visit ThemeForest" button included with the theme below.  If so, you can visit the theme on ThemeForest by clicking that 
        button.  You must first purchase the theme, at which point you will receive a license key in plain text which includes the purchase code.  You must specify that purchase 
        code when installing the theme, as it will verify the purchase via Envato's API.</p>

        <e:data_table><thead><tr>
            <th>Preview</th>
            <th>Alias</th>
            <th>Details</th>
        </tr></thead><tbody>

        <e:section name="members_themes">
        <tr>
            <td valign="top" align="center"><img src="~members_themes.thumbnail_url~" border="0" width="300[x" height="225px"></td>
            <td valign="top">~members_themes.alias~</td>
            <td valign="top">
                <b>~members_themes.name~</b><br />
                ~members_themes.description~<br />

                <b>Author:</b> ~members_themes.author_name~ (~members_themes.author_email~)<br />
                ~members_themes.themeforest_button~
            </td>
        </tr>
        </e:section>
        </tbody></e:data_table><br />

    </e:tab_page>

</e:tab_control>

