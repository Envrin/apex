
<h1>Create Notification</h1>

<script type="text/javascript">

    function addMergeField() {
        var form = document.forms[0];
        var box = form.merge_vars;

        var field = box.options[box.selectedIndex].value;
        if (field == '') { return; }

        form.contents.value += '~' + field + '~';
    }

</script>

<e:form action="/admin/settings/notifications" enctype="multipart/form-data">
<input type="hidden" name="controller" value="~controller~">

<e:box>
    <e:box_header title="Notification Details">
        <p>To continue, select the sender, recipient, and the condition that must be met for this e-mail notification to be automatically sent.</p>
    </e:box_header>

    <e:function alias="core:notification_condition" controller="~controller~" condition_vars="~condition_vars~">
</e:box>

<e:box>
    <e:box_header title="Message Contents">
        <p>To complete creation of the new notification, enter the contents of the message below.</p>
    </e:box_header>

    <e:form_table><tr>
        <e:ft_select name="content_type" data_source="hash:core:notification_content_type" required="1">
        <e:ft_textbox name="subject" required="1">
        <e:ft_textbox name="attachment" type="file">
        <tr>
            <td>Merge Variables:<</td>
            <td>
                <select name="merge_vars">~merge_variable_options~</select><br /> 
                <a href="javascript:addMergeField();" class="btn btn-primary btn-md">Add</a>
            </td>
        </tr><tr>
            <td valign="top">Message Contents:</td>
            <td valign="top"><textarea name="contents" style="width: 500px; height: 300px;" wrap="virtual"></textarea></td>
        </tr>

        <e:ft_submit value="create" label="Create E-Mail Notification">
    </e:form_table>

</e:box>

