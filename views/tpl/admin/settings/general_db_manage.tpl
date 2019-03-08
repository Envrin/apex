
<h1>Manage Database Server</h1>

<e:form action="admin/settings/general">
<input type="hidden" name="server_id" value="~server_id~">

<e:box>
    <e:box_header title="Database Details">
        <p>Make any desired changes to the database connection information below.  Please note, this 
        database user should have read-only access to the database, and should not have any write privileges.</p>
    </e:box_header>

    <e:function alias="display_form" form="core:db_server" record_id="~server_id~">

</e:box>



