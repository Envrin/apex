
<h1>Menus</h1>

<e:form>
<e:tab_control>

    <e:tab_page name="Public Site">
        <h3>Public Site</h3>

        <p>Below lists all menus within the public site, which you may manage, delete, deactivate, and change the ordering of.</p>

        <e:function alias="display_table" table="core:cms_menus" area="public">
        <center><e:submit value="update_public" label="Update Public Site Menus"></center>

    </e:tab_page>

    <e:tab_page name="Member Area">
        <h3>Member Area</h3>

        <p>The below table lists all menus within the member's area, which you may manage, delete, deactivate, and change the ordering of.</p>
        <e:function alias="display_table" table="core:cms_menus" area="members">
        <center><e:submit value="update_members" label="Update Member Area Menus"></center>
    </e:tab_page>

    <e:tab_page name="Add New Menu">
        <h3>Add New menu</h3>

        <p>You may add a new menu to either, the public site or member's area by completing the below for.</p>

        <e:function alias="display_form" form="core:cms_menus" area="public">
    </e:tab_page>

</e:tab_control>


