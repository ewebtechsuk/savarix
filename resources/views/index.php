<!DOCTYPE html>
<!--[if lt IE 7]>      <html lang="en" ng-app="myApp" class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html lang="en" ng-app="myApp" class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html lang="en" ng-app="myApp" class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> 
<html lang="en" ng-app="myApp" class="no-js"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>East London Estate Agents</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src="/resources/views/bower_components/html5-boilerplate/dist/js/vendor/modernizr-2.8.3.min.js"></script>

  <!-- Bootstrap -->
  <link type="text/css" rel="stylesheet" href="/resources/views/css/styles.css">
  <link type="text/css" rel="stylesheet" href="/resources/views/lib/jscal2/jscal2.css">
  <link type="text/css" rel="stylesheet" href="/resources/views/css/jquery-ui.css">


  <link rel="stylesheet" href="/resources/views/css/standard/styles/logo-gnomen.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/css/standard/styles/property-page.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/css/standard/styles/quick-links.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/css/standard/styles/styles.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/css/standard/styles/top-banner.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/css/standard/styles/unit-page.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/css/standard/styles/workflow.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/lib/jscal2/jscal2.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/lib/tooltip2/style.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/lib/jquery-sortable/jquery.ui.core.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/lib/uploadify/uploadify.css" type="text/css">
  <link rel="stylesheet" href="/resources/views/lib/diary/jquery-ui.css" type="text/css">


  <link type="text/css" rel="stylesheet" href="/resources/views/lib/slick/slick.css" />
  <link type="text/css" rel="stylesheet" href="/resources/views/lib/slick/slick-theme.css" />
  <link href="/resources/views/css/font.css" rel="stylesheet">
  <link href="/resources/views/css/main.css" rel="stylesheet">
  
  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  
  <script type="text/javascript" src="/resources/views/lib/jquery.min.js"></script>
  <script type="text/javascript" src="/resources/views/lib/javascript.js"></script>
  <script type="text/javascript" src="/resources/views/lib/jquery.js"></script>
  <script type="text/javascript" src="/resources/views/lib/highcharts.js"></script>

  <script type="text/javascript" src="/resources/views/lib/datetimepicker.js" ></script>
  <script type="text/javascript" src="/resources/views/lib/jscal2/jscal2.js"></script>
  <script type="text/javascript" src="/resources/views/lib/jscal2/en.js"></script>
  <script type="text/javascript" src="/resources/views/lib/slick/slick.js"></script>

  <script type="text/javascript" src="/resources/views/bower_components/tinymce/tinymce.js"></script>
  <script type="text/javascript" src="/resources/views/bower_components/angular/angular.js"></script>
  <script type="text/javascript" src="/resources/views/bower_components/angular-ui-tinymce/src/tinymce.js"></script>

  <script src="/resources/views/bower_components/angular-route/angular-route.js"></script>
  <script src="/resources/views/bower_components/angular-resource/angular-resource.js"></script>
  <script src="/resources/views/js/app.js"></script>
  <script src="/resources/views/js/config/routes.js"></script>
  <script src="/resources/views/pages/home/home.js"></script>
  <script src="/resources/views/pages/sell/sell.js"></script>

  <!--- Controllers for every components --->
  <!-- Controllers for Property -->
  <script src="/resources/views/js/factories/Property.js"></script>
  <script src="/resources/views/pages/property/property.module.js"></script>
  <script src="/resources/views/pages/property/property.js"></script>
  <script src="/resources/views/pages/property/AddProperty/add-property.module.js"></script>
  <script src="/resources/views/pages/property/AddProperty/add-property.controller.js"></script>
  <script src="/resources/views/pages/property/ListProperty/list-property.module.js"></script>
  <script src="/resources/views/pages/property/ListProperty/list-property.controller.js"></script>
  <script src="/resources/views/pages/property/ViewProperty/view-property.module.js"></script>
  <script src="/resources/views/pages/property/ViewProperty/view-property.controller.js"></script>
  <!-- Property --!>

  <script src="/resources/views/components/version/version.js"></script>
  <script src="/resources/views/components/version/version-directive.js"></script>
  <script src="/resources/views/components/version/interpolate-filter.js"></script>
  <script src="/resources/views/lib/tooltip2/script.js" type="text/javascript"></script>

  <script src="/resources/views/js/main.js"></script>

</head>
<body>

  <div id="wrapper">
            <header>
                <a href="/" id="logo">
                    <img src="/resources/views/image/site-logo.png" alt="">
                </a>
                <div id="nav-holder">
                    <nav class="menu">
                        <a href="#" class="menu-link">Menu</a>
                        <ul>
                            <li>
                                <a href="">Buy</a>
                            </li>
                            <li>
                                <a href="#!/sell">Sell</a>
                            </li>
                            <li>
                                <a href="">Rent</a>
                            </li>
                            <li>
                                <a href="">Landlords</a>
                            </li>
                            <li>
                                <a href="">Discover</a>
                            </li>
                            <li>
                                <a href="">Investments</a>
                            </li>
                            <li>
                                <a href="">Valuation</a>
                            </li>
                            <li>
                                <a href="">About</a>
                            </li>
                            <li>
                                <a href="">Contact</a>
                            </li>
                            <li>
                                <a href="#!/property">Property</a>
                            </li>
                        </ul>
                    </nav>
                    <a href="" id="contact">Call : 123 456 7890</a>
                </div>
                <div id="crumb_holder">
                    <div id="crumb">
                        <a href="">Home</a>
                        <span>Lorem Ipsum</span>
                    </div>
                    <div id="sub_crumb">
                        @auth
                            <span>Welcome, {{ Auth::user()->name }}.</span>
                            <a href="{{ url('/dashboard') }}">Dashboard</a>
                        @else
                            <span>You are not logged in.</span>
                            <a href="{{ url('/login') }}">Log in</a>
                            / <a href="{{ url('/register') }}">Register</a>
                        @endauth
                    </div>
                </div>
            </header>
            <main>

  <!--[if lt IE 7]>
      <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
  <![endif]-->

  <div ng-view></div>

  </main>
      <footer>
          <section class="top-section">
              <div class="container">
                  <ul>
                      <li>
                          <h4>About</h4>
                          <a href="">About Andrew Hayward Estate Agent</a>
                          <a href="">Sales jobs in London</a>
                          <a href="">Property Management in London</a>
                          <a href="">Tenancy FAQs</a>
                          <a href="">Mobile apps</a>
                          <a href="">Contact us</a>
                      </li>
                      <li>
                          <h4>Our estate agencies</h4>
                          <a href="">Central London estate agents</a>
                          <a href="">East London estate agents</a>
                          <a href="">North London estate agents</a>
                          <a href="">South London estate agents</a>
                          <a href="">Surrey estate agents</a>
                          <a href="">West London estate agents</a>
                      </li>
                      <li>
                          <h4>Popular searches</h4>
                          <a href="">London property for sale</a>
                          <a href="">London lettings</a>
                          <a href="">London short lets</a>
                          <a href="">New Homes in London</a>
                      </li>
                      <li>
                          <h4>Property intelligence</h4>
                          <a href="">Area guides</a>
                          <a href="">House price reports</a>
                          <a href="">Rental reports</a>
                          <a href="">Home valuation service</a>
                      </li>
                  </ul>
              </div>
          </section>
          <section class="bottom-section">
              <div class="container">
                  <a href="" id="footer-logo">
                      <img src="/resources/views/image/site-logo.png" alt="">
                  </a>
                  <ul>
                      <li>
                          <a href="">Andrew Hayward</a>
                      </li>
                      <li>
                          <a href="">Sitemap</a>
                      </li>
                      <li>
                          <a href="">HelpTerms & Conditions</a>
                      </li>
                      <li>
                          <a href="">Cookies Policy</a>
                      </li>
                      <li>
                          <a href="">Low graphics versions: Sales</a>
                      </li>
                      <li>
                          <a href="">Lettings</a>
                      </li>
                      <li>
                          <a href="">RSS</a>
                      </li>
                  </ul>
              </div>
          </section>
      </footer>  </div>


  <!-- In production use:
  <script src="//ajax.googleapis.com/ajax/libs/angular/js/x.x.x/angular.min.js"></script>
  -->
  

</body>
</html>
