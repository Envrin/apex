
<h1>Welcome to the admin panel.</h1>

<a:form>

<<!-- div class="w-100 overflow-auto order-2 order-md-1">

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

</div> -->


    <!-- Quick stats boxes -->
    <div class="row boxgraf">
        <div class="col-lg-4">

            <!-- Members online -->
            <div class="panel bg-teal-400">
                <div class="panel-body">
                    

                    <h3 class="no-margin">3,450</h3>
                    Members online
                    <div class="text-muted text-size-small">489 avg</div>
                </div>

                <div class="container-fluid">
                    <div id="members-online"></div>
                </div>
            </div>
            <!-- /members online -->

        </div>

        <div class="col-lg-4">

            <!-- Current server load -->
            <div class="panel bg-pink-400">
                <div class="panel-body">
                    

                    <h3 class="no-margin">49.4%</h3>
                    Current server load
                    <div class="text-muted text-size-small">34.6% avg</div>
                </div>

                <div id="server-load"></div>
            </div>
            <!-- /current server load -->

        </div>

        <div class="col-lg-4">

            <!-- Today's revenue -->
            <div class="panel bg-blue-400">
                <div class="panel-body">
                 
                    <h3 class="no-margin">$18,390</h3>
                    Today's revenue
                    <div class="text-muted text-size-small">$37,578 avg</div>
                </div>

                <div id="today-revenue"></div>
            </div>
            <!-- /today's revenue -->

        </div>
    </div>
    <!-- /quick stats boxes -->



    <!-- Support tickets -->
    <div class="panel panel-flat paneltickets">
        <div class="panel-heading">
            <h5 class="panel-title">My Tickets</h5>
        </div>

        
        <div class="table-responsive">
            <table class="table text-nowrap">
                <thead>
                     <tr class="active border-double">
                        <td colspan="10">Members</td>
                        
                    </tr>
                    <tr>
                        <th style="width: 50px">Date Opened</th>
                        <th style="width: 300px;">User</th>
                        <th>Subject</th>
                        <th class="text-center">View</th>
                        
                    </tr>
                </thead>
                <tbody>

                   
                    <tr>
                        <td class="text-center">
                            <h6 class="no-margin">12 <small class="display-block text-size-small no-margin">hours</small></h6>
                        </td>
                        <td>
                            <div class="media-body">
                                <a href="#" class="display-inline-block text-default text-semibold letter-icon-title">Annabelle Doney</a>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="text-default display-inline-block">
                                <span class="text-semibold">[#1183] Workaround for OS X selects printing bug</span>
                            </a>
                        </td>
                        <td class="text-center">
                           5
                        </td>
                    </tr>

                    <tr>
                        <td class="text-center">
                            <h6 class="no-margin">12 <small class="display-block text-size-small no-margin">hours</small></h6>
                        </td>
                        <td>
                            <div class="media-body">
                                <a href="#" class="display-inline-block text-default text-semibold letter-icon-title">Annabelle Doney</a>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="text-default display-inline-block">
                                <span class="text-semibold">[#1183] Workaround for OS X selects printing bug</span>
                            </a>
                        </td>
                        <td class="text-center">
                           5
                        </td>
                    </tr>


                    
                   
                </tbody>
            </table>

             <table class="table text-nowrap">
                <thead>
                     <tr class="active border-double">
                        <td colspan="10">Public</td>
                        
                    </tr>
                    <tr>
                        <th style="width: 50px">Date Opened</th>
                        <th style="width: 300px;">User</th>
                        <th>Subject</th>
                        <th class="text-center">View</th>
                        
                    </tr>
                </thead>
                <tbody>

                   
                    <tr>
                        <td class="text-center">
                            <h6 class="no-margin">12 <small class="display-block text-size-small no-margin">hours</small></h6>
                        </td>
                        <td>
                            <div class="media-body">
                                <a href="#" class="display-inline-block text-default text-semibold letter-icon-title">Annabelle Doney</a>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="text-default display-inline-block">
                                <span class="text-semibold">[#1183] Workaround for OS X selects printing bug</span>
                            </a>
                        </td>
                        <td class="text-center">
                           5
                        </td>
                    </tr>

                    <tr>
                        <td class="text-center">
                            <h6 class="no-margin">12 <small class="display-block text-size-small no-margin">hours</small></h6>
                        </td>
                        <td>
                            <div class="media-body">
                                <a href="#" class="display-inline-block text-default text-semibold letter-icon-title">Annabelle Doney</a>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="text-default display-inline-block">
                                <span class="text-semibold">[#1183] Workaround for OS X selects printing bug</span>
                            </a>
                        </td>
                        <td class="text-center">
                           5
                        </td>
                    </tr>


                    
                   
                </tbody>
            </table>

        </div>
    </div>
    <!-- /support tickets -->



    <!-- Support tickets -->
    <div class="panel panel-flat paneltickets">
        <div class="panel-heading">
            <h5 class="panel-title">New Tickets</h5>
        </div>

        
        <div class="table-responsive">
            <table class="table text-nowrap">
                <thead>
                     <tr class="active border-double">
                        <td colspan="10">Members</td>
                        
                    </tr>
                    <tr>
                        <th style="width: 50px">Date Opened</th>
                        <th style="width: 300px;">User</th>
                        <th>Subject</th>
                        <th class="text-center">View</th>
                        
                    </tr>
                </thead>
                <tbody>

                   
                    <tr>
                        <td class="text-center">
                            <h6 class="no-margin">12 <small class="display-block text-size-small no-margin">hours</small></h6>
                        </td>
                        <td>
                            <div class="media-body">
                                <a href="#" class="display-inline-block text-default text-semibold letter-icon-title">Annabelle Doney</a>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="text-default display-inline-block">
                                <span class="text-semibold">[#1183] Workaround for OS X selects printing bug</span>
                            </a>
                        </td>
                        <td class="text-center">
                           5
                        </td>
                    </tr>

                    <tr>
                        <td class="text-center">
                            <h6 class="no-margin">12 <small class="display-block text-size-small no-margin">hours</small></h6>
                        </td>
                        <td>
                            <div class="media-body">
                                <a href="#" class="display-inline-block text-default text-semibold letter-icon-title">Annabelle Doney</a>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="text-default display-inline-block">
                                <span class="text-semibold">[#1183] Workaround for OS X selects printing bug</span>
                            </a>
                        </td>
                        <td class="text-center">
                           5
                        </td>
                    </tr>


                    
                   
                </tbody>
            </table>

             <table class="table text-nowrap">
                <thead>
                     <tr class="active border-double">
                        <td colspan="10">Public</td>
                        
                    </tr>
                    <tr>
                        <th style="width: 50px">Date Opened</th>
                        <th style="width: 300px;">User</th>
                        <th>Subject</th>
                        <th class="text-center">View</th>
                        
                    </tr>
                </thead>
                <tbody>

                   
                    <tr>
                        <td class="text-center">
                            <h6 class="no-margin">12 <small class="display-block text-size-small no-margin">hours</small></h6>
                        </td>
                        <td>
                            <div class="media-body">
                                <a href="#" class="display-inline-block text-default text-semibold letter-icon-title">Annabelle Doney</a>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="text-default display-inline-block">
                                <span class="text-semibold">[#1183] Workaround for OS X selects printing bug</span>
                            </a>
                        </td>
                        <td class="text-center">
                           5
                        </td>
                    </tr>

                    <tr>
                        <td class="text-center">
                            <h6 class="no-margin">12 <small class="display-block text-size-small no-margin">hours</small></h6>
                        </td>
                        <td>
                            <div class="media-body">
                                <a href="#" class="display-inline-block text-default text-semibold letter-icon-title">Annabelle Doney</a>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="text-default display-inline-block">
                                <span class="text-semibold">[#1183] Workaround for OS X selects printing bug</span>
                            </a>
                        </td>
                        <td class="text-center">
                           5
                        </td>
                    </tr>


                    
                   
                </tbody>
            </table>

        </div>
    </div>
    <!-- /support tickets -->



<!-- <div class="sidebar sidebar-light bg-transparent sidebar-component sidebar-component-right border-0 shadow-0 order-1 order-md-2 sidebar-expand-md">
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
 -->

