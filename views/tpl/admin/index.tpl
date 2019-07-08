
<h1>Welcome to the admin panel.</h1>

<a:form>

<div class="w-100 overflow-auto order-2 order-md-1">

    <a:tab_control>

        <a:tab_page name="My Tickets">
            <h3>My Tickets</h3>

            <h5>Members</h5>
            <a:function alias="display_table" table="support:tickets" is_open="1" is_public="0" admin_id="~userid~" is_pending="0"><br />

            <h5>Public</h5>
            <a:function alias="display_table" table="support:tickets" is_open="1" is_public="1" admin_id="~userid~" is_pending="0">
        </a:tab_page>

        <a:tab_page name="New Tickets">
            <h3>New Tickets</h3>

            <h5>Members</h5>
            <a:function alias="display_table" table="support:tickets" is_open="1" is_public="0" admin_id="0" is_pending="0"><br />

            <h5>Public</h5>
            <a:function alias="display_table" table="support:tickets" is_open="1" is_public="1" admin_id="0" is_pending="0">
        </a:tab_page>

    </a:tab_control>

</div>

<div class="sidebar sidebar-light bg-transparent sidebar-component sidebar-component-right border-0 shadow-0 order-1 order-md-2 sidebar-expand-md">
    <div class="sidebar-content">

        <div class="card">
            <div class="card-header bg-transparent header-elements-inline">
                <span class="card-title font-weight-semibold">Users</span>
            </div>
            <div class="card-body">
                <ul class="media-list">
                    <li class="media">
                        <div class="media-body">
                            <span class="media-title font-weight-semibold">Total Users: <b>382</b></span><br />
                            <span class="media-title font-weight-semibold">New Users: <b>17</b></span><br />
                            <span class="media-title font-weight-semibold">Users Online: <b>51</b></span><br />
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-transparent header-elements-inline">
                <span class="card-title font-weight-semibold">Balances</span>
            </div>
            <div class="card-body">
                <ul class="media-list">
                    <li class="media">
                        <div class="media-body">
                            <span class="media-title font-weight-semibold">USD: <b>$8,4285.12</b></span><br />
                            <span class="media-title font-weight-semibold">EUR: <b>&euro;1488.51</b></span><br />
                            <span class="media-title font-weight-semibold">GBP: <b>&pound;961.63</b></span><br />
                        </div>
                    </li>
                </ul>
            </div>
        </div>



    </div>
</div>


