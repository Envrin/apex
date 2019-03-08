
<h1>Manage Pages</h1>

<e:form>

<e:tab_control>

    <e:tab_page name="Public Site">
        <h3>Public Site</h3>

        <p>The below table lists all pages within the public site.  You may define a speicif layout and page title below for each page as desired.</p>

        <e:function alias="display_table" table="core:cms_pages" area="public">
        <center><e:submit value="update_public" label="Update Public Site Pages">
    </e:tab_page>

    <e:tab_page name="Member Area">
        <h3>Member Area</h3>
        <p>The below talbe lists all pages contained within the member's area.  You may specify a layout and title for each page as desired.</p>

        <e:function alias="display_table" table="core:cms_pages" area="members">
        <center><e:submit value="update_members" label="Update Member ARea Pages">
    </e:tab_page>

</e:tab_control>

    

