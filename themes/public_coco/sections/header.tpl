<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> 
<html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title><e:page_title textonly="1"></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="">
        <meta name="keywords" content="coco bootstrap template, coco admin, bootstrap,admin template, bootstrap admin,">
        <meta name="author" content="Huban Creative">

        <link href="~theme_uri~/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
        <link href="~theme_uri~/assets/libs/pace/pace.css" rel="stylesheet" />
        <link href="~theme_uri~/assets/libs/animate-css/animate.min.css" rel="stylesheet" />
        <link href="~theme_uri~/assets/libs/iconmoon/style.css" rel="stylesheet" />

        <!-- LESS FILE <link href="~theme_uri~/assets/css/style.less" rel="stylesheet/less" type="text/css" /> -->
                <!-- Extra CSS Libraries Start -->
                <link href="~theme_uri~/assets/libs/owl-carousel/owl.carousel.css" rel="stylesheet" type="text/css" />
                <link href="~theme_uri~/assets/libs/owl-carousel/owl.theme.css" rel="stylesheet" type="text/css" />
                <link href="~theme_uri~/assets/libs/owl-carousel/owl.transitions.css" rel="stylesheet" type="text/css" />
                <link href="~theme_uri~/assets/libs/jquery-magnific/magnific-popup.css" rel="stylesheet" type="text/css" />
                <link href="~theme_uri~/assets/css/style.css" rel="stylesheet" type="text/css" />
                <!-- Extra CSS Libraries End -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

        <link rel="shortcut icon" href="~theme_uri~/assets/img/favicon.ico">
        <link rel="apple-touch-icon" href="~theme_uri~/assets/img/apple-touch-icon.png" />
        <link rel="apple-touch-icon" sizes="57x57" href="~theme_uri~/assets/img/apple-touch-icon-57x57.png" />
        <link rel="apple-touch-icon" sizes="72x72" href="~theme_uri~/assets/img/apple-touch-icon-72x72.png" />
        <link rel="apple-touch-icon" sizes="76x76" href="~theme_uri~/assets/img/apple-touch-icon-76x76.png" />
        <link rel="apple-touch-icon" sizes="114x114" href="~theme_uri~/assets/img/apple-touch-icon-114x114.png" />
        <link rel="apple-touch-icon" sizes="120x120" href="~theme_uri~/assets/img/apple-touch-icon-120x120.png" />
        <link rel="apple-touch-icon" sizes="144x144" href="~theme_uri~/assets/img/apple-touch-icon-144x144.png" />
        <link rel="apple-touch-icon" sizes="152x152" href="~theme_uri~/assets/img/apple-touch-icon-152x152.png" />    
    </head>
<body class=""><div id="wrapper">    <header>
        <div id="topbar">
	<div class="container">
		<div class="row">
			<div class="col-sm-6 col-xs-6">
			<span class="hidden-sm hidden-xs"><i class="icon-location4"></i>~config.core:site_name~</span><span class="vertical-space"></span> <i class="icon-phone4"></i>~config.core:site_phone~
			</div>
			<div class="col-sm-6 col-xs-6 text-right">
				<div class="btn-group social-links hidden-sm hidden-xs">	
					<e:if '~config.core:site_facebook~' != ''><a href="~config.core:site_facebook~" class="btn btn-link"><i class="icon-facebook4"></i></a></e:if>
					<e:if '~config.core:site_twitter~' != ''><a href="~config.core:site_twitter~" class="btn btn-link"><i class="icon-twitter4"></i></a></e:if>
				</div>
				<a href="/login" class="login-button">LOGIN</a><a href="/register" class="signup-button">SIGN UP</a>
			</div>
		</div>
		<div class="top-divider"></div>
	</div>
</div>            <nav class="navbar navbar-default" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#main-navigation">
                    <span class="icon-navicon"></span>
                </button>
                <a class="navbar-brand" href="/index">
                    <img src="~theme_uri~/assets/img/logo.png" data-dark-src="~theme_uri~/assets/img/logo_dark.png" alt="~config.core:site_name~" class="logo">
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="main-navigation">
                <ul class="nav navbar-nav navbar-right">
                    <e:nav_menu>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-->
    </nav>        

<section class="main-slider fullsize" data-stellar-background-ratio="0.5" style="background-image: url(~theme_dir~/images/headers/index.jpg)">
        <div class="slider-caption">
                <h1 data-animate="fadeInDown" data-delay="1000" data-duration="2s">Whenever you feel lost, <br>we are here to guide you!</h1>
        <a data-animate="fadeInUp" data-duration="2s" data-delay="1300" href="~site_url~/contact" class="btn btn-primary btn-lg">CONTACT US</a> </div>
</section>    </header>

    <e:page_title>

    <e:user_message>


