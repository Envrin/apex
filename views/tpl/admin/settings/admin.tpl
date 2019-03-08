
<h1>Administrators</h1>

<e:form>

<e:tab_control>

    <e:tab_page name="Existing Administrators">
        <h3>Existing Administrators</h3>
        <p>The below table lists all existing administrator accounts that you may manage or delete.</p>

        <e:function alias="display_table" table="core:admin">
    </e:tab_page>

    <e:tab_page name="Create New Administrator">
        <h3>Create New Administrator</h3>
        <p>You may create a new administrator account by completing the below form with the desired information.</p>

        <e:function alias="display_form" form="core:admin">
    </e:tab_page>

    <e:tab_page name="Recent Activity">
        <h3>Recent Activity</h3>
        <p>The below table lists the previous 50 login sessions into the administration panel.  You can view the exact pages and requests 
        views by clicking the desired Manage button below.</p>

        <e:function alias="display_table" table="core:auth_history" type="admin">
    </e:tab_page>

</e:tab_control>

