
<h1>Security Question</h1>

<p>To continue with your login, please answer the following security question.</p>

<a:form action="/admin/index">
<input type="hidden" name="username" value="~username~">
<input type="hidden" name="password" value="~password~">
<input type="hidden" name="question_id" value="~question_id~">


<a:form_table>
	<a:ft_textbox name="answer" label="~question~">
	<a:ft_submit value="login" label="Answer Security Question">
</a:form_table>
</form>

{ttheme.footer}

