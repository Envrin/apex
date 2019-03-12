
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

    <e:function alias="display_form" form="core:notification_message" controller="~controller~">

</e:box>

