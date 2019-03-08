
<h1>Manage Menu</h1>

<e:form action="admin/cms/menus">
<input type="hidden" name="menu_id" value="~menu_id~">

<e:box>
    <e:box_header title="Menu Details">
        <p>Make any desired changes to the menu information below, and submit the form to save all changes.</p>
    </e:box_header>

    <e:function alias="display_form" form="core:cms_menus" record_id="~menu_id~">
</e:box>


