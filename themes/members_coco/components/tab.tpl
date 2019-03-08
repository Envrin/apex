
<tab_control>
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            ~nav_items~
        </ul>

        <div class="tab-content">
            ~tab_pages~
        </div>
    </div>
</tab_control>

<nav_item>
    <li class="~active~"><a href="#tab~tab_num~" data-toggle="tab">~name~</a></li>
</nav_item>

<tab_page>
    <div class="tab-pane ~active~" id="$tab~tab_num~">
        ~contents~
    </div>
</tab_page>

