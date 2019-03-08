
<h1>Manage Placeholder</h1>

<e:form action="admin/cms/placeholders">
<input type="hidden" name="uri" value="~placeholder~">

<e:box>
    <e:box_header title="Placeholder Details">
        <p>Below lists all placeholders available for this template.  Enter any desired contents you would like in the 
        provided textboxes, and it will be replaced with the place holders when viewing the page.  This helps ensure any modifications made to the template are not overwritten when upgrades are released.</p>

        <e:form_table><tr>
        <td><b>URI:</b></td>
        <td>~placeholder~</td>
        </tr>

        <e:section name="holders">
        <tr>
            <td valign="top">~holders.name~:</td>
        <td valign="top"><textarea name="contents_~holders.alias~" wrap="virtual">~holders.contents~</textarea><br /><br />
        </tr>
        </e:section>

        <e:ft_submit value="update" label="Update Placeholders">
    </e:form_table>

</e:box>


