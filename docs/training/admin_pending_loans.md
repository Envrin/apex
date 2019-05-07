
# Apex Training - Admin Panel - Pending Loans

Next up, let's complete the Loans->Pending Loans menu of the administration panel.  Start with creation of a new template again, open terminal and type:

`php apex.php create template admin/loans/pending lending`

Open the new .tpl file at */views/tpl/admin/loans/pending.tpl* and enter the following contents:

~~~

<h1>Pending Loans</h1>

<e:form>

<e:box>
    <e:box_header title="Loans">
        <p>The below table lists all pending loans which you may approve or decline as desired.</p>
    </e:box_header>

    <e:function alias="display_table" table="lending:loans" status="pending">
</e:box>

~~~


### Create Data Table

As you can see, there is a `<e:function>` tag in the above template, which displays the "lending:loans" table, so let's create that table now.  In 
terminal, type:

`php apex.php create table lending:loans`







