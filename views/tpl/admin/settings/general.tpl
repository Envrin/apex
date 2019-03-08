
<h1>General Settings</h1>

<e:form>

<e:tab_control>

    <e:tab_page name="General">
        <h3>General Settings</h3>

        <e:form_table>
            <e:ft_textbox name="domain_name" value="~config.core:domain_name~">
            <e:ft_select name="date_format" value="~config.core:date_format~" data_source="hash:core:date_formats">
            <e:ft_seperator label="Nexmo API Info">
            <e:ft_textbox name="nexmo_api_key" label="API Key" value="~config.core:nexmo_api_key~">
            <e:ft_textbox name="nexmo_api_secret" label="API Secret" value="~config.core:nexmo_api_secret~">
            <e:ft_seperator label="Google Recaptcha API">
            <e:ft_textbox name="recaptcha_site_key" value="~config.core:recaptcha_site_key~" label="ReCaptcha Site Key">
            <e:ft_textbox name="recaptcha_secret_key" value="~config.core:recaptcha_secret_key~" label="ReCaptcha Secret Key">
            <e:ft_seperator label="OpenExchange">
            <e:ft_textbox name="openexchange_app_id" value="~config.core:openexchange_app_id~" label="OpenExchange App ID">

            <e:ft_seperator label="System / Server">
            <e:ft_select name="mode" label="Server Mode" value="~config.core:mode~" data_source="hash:core:server_mode">
            <e:ft_select name="debug_level" value="~config.core:debug_level~" data_source="hash:core:debug_levels">
            <e:ft_select name="log_level" value="~config.core:log_level~" data_source="hash:core:log_levels">
            <e:ft_select name="default_language" value="~config.core:default_language~" data_source="stdlist:language:1">
            <e:ft_select name="default_timezone" value="~config.core:default_timezone~" data_source="stdlist:timezone">

            <e:ft_submit value="update_general" label="Update General Settings">
        </e:form_table>

    </e:tab_page>

    <e:tab_page name="Site Info">
        <h3>Site Info</h3>

        <p>Enter your site and contact information below, including URLs to your social media profiles.  This information will be displayed on the public web site in the appropriate places.</p>

        <e:form_table>
            <e:ft_textbox name="site_name" value="~config.core:site_name~" label="Company Nmae">
            <e:ft_textbox name="site_address" value="~config.core:site_address~" label="Street Address">
            <e:ft_textbox name="site_address2" value="~config.core:site_address2~" label="Street Address 2">
            <e:ft_textbox name="site_email" value="~config.core:site_email~" label="E-Mail Address">
            <e:ft_textbox name="site_phone" value="~config.core:site_phone~" label="Phone Number">
            <e:ft_textbox name="site_tagline" value="~config.core:site_tagline~" label="Site Tagline">
            <e:ft_seperator label="Social Media Profiles">
            <e:ft_textbox name="site_facebook" value="~config.core:site_facebook~" label="Facebook">
            <e:ft_textbox name="site_twitter" value="~config.core:site_twitter~" label="Twitter">
            <e:ft_textbox name="site_linkedin" value="~config.core:site_linkedin~" label="LinkedIn">
            <e:ft_textbox name="site_instagram" value="~config.core:site_instagram~" label="Instagram">
            <e:ft_submit value="site_info" label="Update Site Info">
        </e:form_table>

    </e:tab_page>

    <e:tab_page name="Security">
        <h3>Admin Panel Security</h3>

        <e:form_table><tr>
            <td valign="top"><b>Require 2FA?:</b><br />If yes, all users will be forced to use 2FA, meaning upon logging in the user will receive an e-mail containing a link they must click in to login.  If optional, users can update their profile and c hoose whether or not to use 2FA.<br /><br /></td>
            <td valign="top"><e:select name="require_2fa" value="~config.core:require_2fa~" required="1" data_source="hash:users:email_verification"></td>
        </tr><tr>
            <td valign="top"><b>Session Expire Time (minutes):</b><br />Number of minutes of inactivity before a user is automatically logged out, and must login again.<br /><br /></td>
            <td><e:textbox name="session_expire_mins" value="~config.core:session_expire_mins~" width="70px"></td>
        </tr><tr>
            <td valign="top"><b>Failed Login Attempts Allowed?</b><br />The number of simultaneous failed login attempts allowed in a row, before the user's account is automatically deactivated.  0 to disable this feature.<br /><br /></td>
            <td valign="top"><e:textbox name="password_retries_allowed" value="~config.core:password_retries_allowed~" width="60px;"></td>
        </tr><tr>
            <td valign="top"><b>Length to Retain User Session Logs?</b><br />The length of time to retain detailed session logs for users.  Basic session details are saved forever, and this only pertains to detailed log information such as exactly which pages were visted, and what form information was submitted.<br /><br /></td>
            <td valign="top"><e:date_interval name="session_retain_logs" value="~config.core:session_retain_logs~"></td>
        </tr><tr>
            <td valign="top"><b>Number of Security Questions?</b><br />The number of additional security questions users can submit.  When users login from a new computer and have security questions defined, they will be asked to answer one at random to login.<br /><br /></td>
            <td valign="top"><e:textbox name="num_security_questions" value="~config.core:num_security_questions~" width="20px;"></td>
        </tr><tr>
            <td valign="top"><b>Force Password Reset Interval?</b><br />Length of time users must reset their password, ensuring they don't use the same password for too long.  Leave blank to disable this feature.<br /><br /></td>
            <td valign="top"><e:date_interval" name="force_password_reset_time" value="~config.core:force_password_reset_time~"></td>
        </tr>

        <e:ft_submit value="security" label="Update Security Settings">
        </e:form_table>

    </e:tab_page>

    <e:tab_page name="Database Servers">
        <h3>Database Servers</h3>

        <p>From below you can manage all the various database servers that are utilized.  The software supports database replication, and you may add new slave database servers below, and manage existing servers.</p>

        <e:function alias="display_table" table="core:db_servers">
        <center><e:submit value="delete_database" label="Delete Checked Databases"></center><br />

        <h4>Add Database Server</h4>

        <e:function alias="display_form" form="core:db_server">
    </e:tab_page>

    <e:tab_page name="e-Mail Servers">
        <h3>E-Mail Servers</h3>

        <p>Below you can manage all the SMTP e-mail servers that are utilized by the system.  All outoing e-mail is evenly distributed amongst the SMTP servers listed below, so if volume ever increases, you may simply add a new SMTP server below and the load will immediately begin getting distributed to it.</p>

        <e:function alias="display_table" table="core:email_servers">
        <center><e:submit value="delete_email" label="Delete Checked E-Mail Servers"></center><br />

        <h4>Add SMTP E-Mail Server</h4>

        <e:function alias="display_form" form="core:email_server">

    </e:tab_page>

    <e:tab_page name="RabbitMQ">
        <h3>RabbitMQ Connection Info</h3>

        <p>You may modify the RabbitMQ connection information from below.  Please note, if you have a cluster of servers / droplets 
        for this operation, you only need to update the RabbitMQ on one system, and it will update throughout the entire cluster.<p>

        <e:form_table>
            <e:ft_textbox name="rabbitmq_host" value="~rabbitmq_host~">
            <e:ft_textbox name="rabbitmq_port" value="~rabbitmq.port~">
            <e:ft_textbox name="rabbitmq_user" value="~rabbitmq.user~">
            <e:ft_textbox name="rabbitmq_pass" value="~rabbitmq.pass~">
            <e:ft_submit value="update_rabbitmq" label="Update RabbitMQ Info">
        </e:form_table>

    </e:tab_page>

    <e:tab_page name="Reset Redis">
        <h3>Reset Redis</h3>

        <p>If ever needed, you may reset the redis database be entering RESET in the text box below.  This will go through all packages, and reset redis as needed.  Useful if you've transferred to a new server with a clean redis database, but a populated mySQL database.</p>

        <e:form_table>
            <e:ft_textbox name="redis_reset" label="Reset Redis">
            <e:ft_submit value="reset_redis" label="Reset Redis Database">
        </e:form_table>

    </e:tab_page>

</e:tab_control>


