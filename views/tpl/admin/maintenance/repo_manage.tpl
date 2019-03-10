
<h1>Manage Repository</h1>
<e:form action="admin/maintenance/package_manager">
<input type="hidden" name="repo_id" value="~repo.id~">

<e:box>
    <e:box_header title="Repo Details">
        <p>You may update the username / password used for this repository below.</p>
    </e:box_header>

    <e:form_table>
        <e:ft_custom label="Repo URL" contents="~repo.url~">
        <e:ft_textbox name="repo_username" label="Username" value="~repo.username~">
        <e:ft_textbox name="repo_password" label="Password" value="~repo.password~">
        <e:ft_submit value="update_repo" label="Update Repository">
    </e:form_table>

</e:box>

