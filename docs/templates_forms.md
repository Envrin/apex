
# Template Engine - Form Tables

The Apex template engine allows for quick and easy form tables to be implmented into any template manually.  This is similar to the 
[HTML Forms component](components/form.md) except these are placed directly within the TPL code.


### `<e:form_table> ... </e:form_table>`

**Description:** Creates a simple table with default width of 80%, and necessary cellpadding.  Should always be used when manually putting in a form or a simple two column text based table.  Requires a closing tab, and uses custom CSS class of "form_table" which should be added to every theme.

**Attributes**

Attribute | Required | Description
------------- |------------- |------------- 
width | No | Width of the table, defaults to 80%.
align | No | Alignment of the table, defaults to "left".


### `<e:ft_FIELD> / <e:FIELD>` Tags

Every form field is available via both the `<e:ft_FIELD>` and `<e:FIELD>` tags.  Within the `<e:form_table> ... </e:form_table>` tags, you can place the 
`<e:ft_FIELD>` tags, which are replaced with a two column table row, the left column being the label of the form field, and the right column being the form field itself.  Alternatively, you can also use the 
`<e:FIELD>` tags, which are only replaced with the form field itself, and no two column two.


### `<e:ft_seperator>`

**Description:** Generates a two column width row, with indented and bolded text.  Used to separate sets of form fields.

Attribute | Required | Description
------------- |------------- |------------- 
label | Yes | The text of the seperator.


### Additional Form Tags

The below table lists all additional available form field tags that can be used.  For the available attributes, please visit the 
[HTML Forms component](components/form.md) page, as the attributes are the exact same as those within the form PHP classes.

Tag | Description
------------- |------------- 
`<e:ft_textbox> / <e:textbox>` | Input textbox field.
`<e:ft_textarea> / <e:textarea>` | Input textarea form field.
`<e:ft_phone>` | Phone number.  Contains a small select list for the country count, and a text field for the phone number.
`<e:ft_date` | Date.  Consists of three select lists for the month, day, and year.
`<e:date_interval>` | Date interval.  One small textbox for an integer, and a small select list for the interval (days, weeks, months, years)
1>e:ft_select>` | Select list.
`<e:ft_boolean>` | Boolean.  Two radio buttons, Yes / No.
`<e:ft_custom>` | Custom row.
`<e:ft_blank>` | Blank row, colspan of 2.
`>e:ft_submit>` | Submit button.


### Example

~~~html
<e:form_table>
    <e:ft_seperator label="Contact Details">
    <e:ft_textbox name="full_name">
    <e:ft_textbox name="email" label="E-Mail Address" required="1" datatype="email">
    <e:ft_submit value="add" label="Add New Contact">
</e:form_table>
~~~



