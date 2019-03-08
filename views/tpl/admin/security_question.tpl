
<h1>Security Question</h1>

<p>To continue with your login, please answer the following security question.</p>

<e:form action="/admin/index">
<input type="hidden" name="username" value="~username~">
<input type="hidden" name="password" value="~password~">
<input type="hidden" name="question_id" value="~question_id~">


<e:form_table>
	<e:ft_textbox name="answer" label="~question~">
	<e:ft_submit value="login" label="Answer Security Question">
</e:form_table>
</form>

{ttheme.footer}

