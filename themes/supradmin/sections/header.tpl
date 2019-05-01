<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><e:page_title textonly="1"></title>
    <meta name="author" content="SuggeElson" />
    <meta name="application-name" content="Supr admin template" />

    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Force IE9 to render in normla mode -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- Le styles -->
    <!-- Use new way for google web fonts 
    http://www.smashingmagazine.com/2012/07/11/avoiding-faux-weights-styles-google-web-fonts -->
    <!-- Headings -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css' />
    <!-- Text -->
    <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css' /> 
    <!--[if lt IE 9]>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400" rel="stylesheet" type="text/css" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:700" rel="stylesheet" type="text/css" />
    <link href="http://fonts.googleapis.com/css?family=Droid+Sans:400" rel="stylesheet" type="text/css" />
    <link href="http://fonts.googleapis.com/css?family=Droid+Sans:700" rel="stylesheet" type="text/css" />
    <![endif]-->

    <!-- Core stylesheets do not remove -->
    <link id="bootstrap" href="~theme_uri~/css/bootstrap/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link id="bootstrap-responsive" href="~theme_uri~/css/bootstrap/bootstrap-responsive.min.css" rel="stylesheet" type="text/css" />
    <link href="~theme_uri~/css/supr-theme/jquery.ui.supr.css" rel="stylesheet" type="text/css"/>
    <link href="~theme_uri~/css/icons.css" rel="stylesheet" type="text/css" />

    <!-- Plugins stylesheets -->
    <link href="~theme_uri~/plugins/misc/qtip/jquery.qtip.css" rel="stylesheet" type="text/css" />
    <link href="~theme_uri~/plugins/misc/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
    <link href="~theme_uri~/plugins/misc/search/tipuesearch.css" type="text/css" rel="stylesheet" />

    <link href="~theme_uri~/plugins/forms/uniform/uniform.default.css" type="text/css" rel="stylesheet" />

    <!-- Main stylesheets -->
    <link href="~theme_uri~/css/main.css" rel="stylesheet" type="text/css" /> 

    <!-- Custom stylesheets ( Put your own changes here ) -->
    <link href="~theme_uri~/css/custom.css" rel="stylesheet" type="text/css" /> 

    <!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->
    
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script type="text/javascript" src="~theme_uri~/js/libs/excanvas.min.js"></script>
      <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
      <script type="text/javascript" src="~theme_uri~/js/libs/respond.min.js"></script>
    <![endif]-->
    
    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="~theme_uri~/images/favicon.ico" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="~theme_uri~/images/apple-touch-icon-144-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="~theme_uri~/images/apple-touch-icon-114-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="~theme_uri~/images/apple-touch-icon-72-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" href="~theme_uri~/images/apple-touch-icon-57-precomposed.png" />
    
    <!-- Windows8 touch icon ( http://www.buildmypinnedsite.com/ )-->
    <meta name="application-name" content="~page_title~"/> 
    <meta name="msapplication-TileColor" content="#3399cc"/> 

    <!-- Load modernizr first -->
    <script type="text/javascript" src="~theme_uri~/js/libs/modernizr.js"></script>
	
    <!-- Charts plugins -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.js"></script>
    <script type="text/javascript" src="https://github.com/nagix/chartjs-plugin-streaming/releases/download/v1.1.0/chartjs-plugin-streaming.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pusher/4.1.0/pusher.js"></script>



    </head>
      
    <body>

	<!-- loading animation -->
    <div id="qLoverlay"></div>
    <div id="qLbar"></div>
        
    <div id="header">

        <div class="navbar">
            <div class="navbar-inner">
              <div class="container-fluid">
                <a class="brand" href="~/members/index">Supr.<span class="slogan">admin</span></a>
                <div class="nav-no-collapse">
                    <ul class="nav">
                        <li class="active"><a href="/members/index"><span class="icon16 icomoon-icon-screen-2"></span> <span class="txt">Dashboard</span></a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <span class="icon16 icomoon-icon-envelop"></span><span class="txt">Messages</span><span class="notification">~unread_messages~</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="menu">
                                    <ul class="messages" id="dropdown_messages">    
                                        <li class="header"><strong>Messages</strong> (~unread_messages~) messages</li>
                                            <e:dropdown_messages>
                                        <li class="view-all"><a href="/members/inbox">View all messages <span class="icon16 icomoon-icon-arrow-right-8"></span></a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                  
                    <ul class="nav pull-right usernav">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <span class="icon16 icomoon-icon-bell"></span><span class="notification">~unread_alerts~</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="menu">
                                    <ul class="notif" id="dropdown_alerts">
                                        <li class="header"><strong>Notifications</strong> (~unread_alerts~) items</li>
                                            <e:dropdown_alerts>
                                        <li class="view-all"><a href="/members/notifications">View all notifications <span class="icon16 icomoon-icon-arrow-right-8"></span></a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <e:if 1 == 1>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle avatar" data-toggle="dropdown">
								<img alt="" src="~/user_avatars/~id~" class="image" border="0" />
                                <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="menu">
                                    <ul>
                                        <li><a href="/members/index"><span class="icon16 icomoon-icon-home-3"></span>Dashboard</a></li>
                                        <li><a href="/members/account/update_profile"><span class="icon16 icomoon-icon-user-plus"></span>Dashboard</a></li>
                                        <li><a href="/members/financial/accounts"><span class="icon16 icon-money"></span>Payments</a></li>
                                        <li><a href="/members/inbox"><span class="icon16 icomoon-icon-envelop"></span>Inbox</a></li>
                                        <li><a href="/members/logout"><span class="icon16 icomoon-icon-exit-2"></span>Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
						</e:if>
						
                        <li><a href="/members/logout"><span class="icon16 icomoon-icon-exit"></span><span class="txt"> Logout</span></a></li>
                    </ul>
                </div><!-- /.nav-collapse -->
              </div>
            </div><!-- /navbar-inner -->
          </div><!-- /navbar --> 

    </div><!-- End #header -->

    <div id="wrapper">

        <!--Responsive navigation button-->  
        <div class="resBtn">
            <a href="#"><span class="icon16 minia-icon-list-3"></span></a>
        </div>

        <!--Sidebar background-->
        <div id="sidebarbg"></div>
        <!--Sidebar content-->
        <div id="sidebar">

            <div class="sidenav">

                <div class="sidebar-widget" style="margin: -1px 0 0 0;">
                    <h5 class="title" style="margin-bottom:0">Navigation</h5>
                </div><!-- End .sidenav-widget -->

                <div class="mainnav">
                    <ul>
                        <e:nav_menu>
                    </ul>
                </div>
            </div><!-- End sidenav -->

        </div><!-- End #sidebar -->

        <!--Body content-->
        <div id="content" class="clearfix">
            <div class="contentwrapper"><!--Content wrapper-->

                <div class="heading">

                    <h3><e:page_title textonly="1"></h3>                    

                    <ul class="breadcrumb">
                        <li>You are here:</li>
                        <li>
                            <a href="/members/index" class="tip" title="back to dashboard">
                                <span class="icon16 icomoon-icon-screen-2"></span>
                            </a>
                            <span class="divider">
                                <span class="icon16 icomoon-icon-arrow-right-3"></span>
                            </span>
                        </li>
                        <li class="active">Dashboard</li>
						<li>
                            <span class="divider">
                                <span class="icon16 icomoon-icon-arrow-right-3"></span>
                            </span>
						</li>
						<li><e:page_title textonly="1"></li>
                    </ul>

                </div><!-- End .heading-->

                <div class="row-fluid">

                    <div class="span12">

                        <e:user_message>

					
