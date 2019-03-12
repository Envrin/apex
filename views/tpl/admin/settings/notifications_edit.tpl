
<h1>Edit Notification</h1>

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
<input type="hidden" name="notification_id" value="~notification_id~">

<e:box>
    <e:box_header title="Notification Details">
        <p>To continue, select the sender, recipient, and the condition that must be met for this e-mail notification to be automatically sent.</p>
    </e:box_header>

    <e:function alias="core:notification_condition" notification_id="~notification_id~">
</e:box>

<e:box>
    <e:box_header title="Message Contents">
        <p>To complete creation of the new notification, enter the contents of the message below.</p>
    </e:box_header>

    <e:function alias="display_form" form="core:notification_message" record_id="~notification_id~">

</e:box>


