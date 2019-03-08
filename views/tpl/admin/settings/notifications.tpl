
<h1>Notifications</h1>

<e:form action="/admin/settings/notifications_create">

<e:box>
    <e:box_header title="Existing Notifications">
        <p>The below table lists all existing e-mail notifications that are automatically 
        sent by the system, which you may manage or delete from below.</p>
    </e:box_header>

    <e:function alias="display_table" table="core:notifications">

</e:box>

<e:box>
    <e:box_header title="Create New Notification">
        <p>You may begin creating a new e-mail notification by completing the below form.  On 
    the next page you will be prompted to specify the message contents.</p>
    </e:box_header>

    <e:form_table>
        <e:ft_select name="controller" label="Type">~controller_options~</e:ft_select>
        <e:ft_submit value="create" label="Create E-Mail Notification">
	</e:form_table>

</e:box>

