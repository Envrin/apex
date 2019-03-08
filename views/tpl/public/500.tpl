
<h1>Error</h1>

<div class="row"><div class="col-md-12">
    <p><b>~err_message~</b></p><br />

    <e:if '~config.core:mode~' == 'devel'>
        <p><i>
            File: ~err_file~<br />
            Line: ~err_line~
        </i></p><br />
    </e:if>

</div></div><br />

<e:if '~config.core:mode~' == 'devel'>
    <h3>Debug Information</h3>

    <e:function alias="display_tabcontrol" tabcontrol="core:debugger">
</e:if>



</blockquote><br />



