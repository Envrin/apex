
<h1>Backup Manager</h1>

<script type="text/javascript">

    var current_service = '~config.core:backups_remote_service~';
    function changeService(box) { 
        var service = box.options[box.selectedIndex].value;
        if (document.getElementById('service_' + current_service)) { 
            document.getElementById('service_' + current_service).style.display = 'none';
        }

        if (document.getElementById('service_' + service)) { 
            document.getElementById('service_' + service).style.display = '';
        }
    }

    if (document.getElementById('service_' + current_service)) { 
        document.getElementById('service_' + current_service).style.display = '';
    }


</script>

<e:form>

<e:box>
    <e:box_header title="Backup Details">
        <p>You may define your desired backup settings below.  The software conducts both, database backups, plus full backups which include both the database and entire filesystem.</p>
    </e:box_header>

    <e:form_table>
        <e:ft_boolean name="backups_enable" value="~config.core:backups_enable~" label="Enable Backups?">
        <e:ft_boolean name="backups_save_locally" value="~config.core:backups_save_locally~" label="Save Backups Locally?">
        <e:ft_date_interval name="backups_db_interval" value="~config.core:backups_db_interval~" label="Database Backup Interval" add_time="1">
        <e:ft_date_interval name="backups_full_interval" value="~config.core:backups_full_interval~" label="Full Backup Interval" add_time="1">
        <e:ft_date_interval name="backups_retain_length" value="~config.core:backups_retain_length~" label="Length to Retain Backups?">
        <e:ft_seperator label="Remote Backups">
        <e:ft_select name="backups_remote_service" value="~config.core:backups_remote_service~" label="Service" data_source="hash:core:backups_remote_services" onchange="changeService(this);">

        <tbody id="service_aws" style="display: none;">
            <e:ft_textbox name="backups_aws_access_key" value="~config.core:backups_aws_access_key~" label="Access Key">
            <e:ft_textbox name="backups_aws_access_secret" value="~config.core:backups_aws_access_secret~" label="Access Secret">
        </tbody>

        <tbody id="service_dropbox" style="display: none;">
            <e:ft_textbox name="backups_dropbix_client_id" value="~config.core:backups_dropbox_client_id~" label="Client ID">
            <e:ft_textbox name="backups_dropbix_client_secret" value="~config.core:backups_dropbox_client_secret~" label="Client Secret">
            <e:ft_textbox name="backups_dropbix_access_token" value="~config.core:backups_dropbox_access_token~" label="Access Token">
        </tbody>

        <tbody id="service_google_drive" style="display: none;">
            <e:ft_textbox name="backups_gdrive_client_id" value="~config.core:backups_gdrive_client_id~" label="Client ID">
            <e:ft_textbox name="backups_gdrive_client_secret" value="~config.core:backups_gdrive_client_secret~" label="Client Secret">
            <e:ft_textbox name="backups_gdrive_refresh_token" value="~config.core:backups_gdrive_refresh_token~" label="Refresh Token">
        </tbody>

        <e:ft_submit value="update" label="Update Backup Settings">
    </e:form_table>

</e:box>



