
<h1>Manage Administrator</h1>

<e:form action="admin/settings/admin">
<input type="hidden" name="admin_id" value="~admin_id~">

<e:box>
	<e:box_header title="Administrator Profile">
		<p>Make any desired changes to the administrator profile below, and submit the form to save all changes.</p>
	</e:box_header>

	<e:function alias="display_form" form="core:admin" record_id="~admin_id~">
</e:box>


