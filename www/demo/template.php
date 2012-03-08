<?php 
/**
 * This is an example web page.  Note that the only reason why this page is PHP is so that
 * the url to the server is linked with this current instance of the visitorchat.  In most cases
 * the url will be static and no php will be required.
 */
if (file_exists(dirname(dirname(dirname(__FILE__))) . '/config.inc.php')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/config.inc.php';
} else {
    require dirname(dirname(dirname(__FILE__))) . '/config.sample.php';
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><!-- InstanceBegin template="/Templates/fixed.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<!--
    Membership and regular participation in the UNL Web Developer Network
    is required to use the UNL templates. Visit the WDN site at 
    http://wdn.unl.edu/. Click the WDN Registry link to log in and
    register your unl.edu site.
    All UNL template code is the property of the UNL Web Developer Network.
    The code seen in a source code view is not, and may not be used as, a 
    template. You may not use this code, a reverse-engineered version of 
    this code, or its associated visual presentation in whole or in part to
    create a derivative work.
    This message may not be removed from any pages based on the UNL site template.
    
    $Id: fixed.dwt 536 2009-07-23 15:47:30Z bbieber2 $
-->
<link rel="stylesheet" type="text/css" media="screen" href="/wdn/templates_3.0/css/all.css" />
<link rel="stylesheet" type="text/css" media="print" href="/wdn/templates_3.0/css/print.css" />
<script type="text/javascript" src="/wdn/templates_3.0/scripts/all.js"></script>
<!--[if lt IE 9]>
    <link rel="stylesheet" type="text/css" media="screen" href="/wdn/templates_3.0/css/ie.css" />
<![endif]-->
<meta name="author" content="University of Nebraska-Lincoln | Web Developer Network" />
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<meta http-equiv="content-language" content="en" />
<meta name="language" content="en" />
<link rel="shortcut icon" href="/wdn/templates_3.0/images/favicon.ico" />

<!-- InstanceBeginEditable name="doctitle" -->
<title>UNL | Chat Demo | <?php echo $title?></title>

<!-- InstanceEndEditable --><!-- InstanceBeginEditable name="head" -->
<!-- Place optional header elements here -->
<style type="text/css">
/*
.style1 {font-style: italic}
*/
</style>
<!-- InstanceEndEditable -->
<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url ?>js/chat.php"></script>
</head>
<body class="fixed">
<p class="skipnav"> <a class="skipnav" href="#maincontent">Skip Navigation</a> </p>

<div id="wdn_wrapper">
    <div id="header"> <a href="http://www.unl.edu/" title="UNL website"><img src="/wdn/templates_3.0/images/logo.png" alt="UNL graphic identifier" id="logo" /></a>
        <h1>University of Nebraska&ndash;Lincoln</h1>
        <div id="wdn_search">
    <form id="wdn_search_form" action="http://www.google.com/u/UNL1?sa=Google+Search&amp;q=" method="get">
        <fieldset>
            <label for="q">Search this site, all UNL or for a person</label>

            <input accesskey="f" id="q" name="q" type="text" />
            <input class="search" type="submit" value="Go" name="submit" />
        </fieldset>
    </form>
</div>
<h3 class="wdn_list_descriptor hidden">UNL Tools</h3>
<ul id="wdn_tool_links">
    <!-- <li style="border-color:#ac0203;"><a href="http://emergency.unl.edu/" class="alert tooltip" title="Emergency Alert: An alert has been issued!">Emergency</a></li> -->
    <li><a href="http://www1.unl.edu/feeds/" class="feeds tooltip" title="RSS Feeds: View and Subscribe to News Feeds">Feeds</a></li>

    <li><a href="http://forecast.weather.gov/MapClick.php?CityName=Lincoln&amp;state=NE&amp;site=OAX" class="weather tooltip" title="Weather: Local Forecast and Radar">Weather</a></li>
    <li><a href="http://events.unl.edu/" class="events tooltip" title="UNL Events: Calendar of Upcoming Events">Events</a></li>
    <li><a href="http://directory.unl.edu/" class="peoplefinder tooltip" title="UNL Directory: Search for Faculty, Staff, Students and Departments">Directory</a></li>
    <li><a href="http://www.unl.edu/unlpub/cam/cam1.shtml" class="webcams tooltip" title="Webcams: Live UNL Campus Cameras">Webcams</a></li>
</ul>

    </div>
    <div id="wdn_navigation_bar">

        <div id="breadcrumbs">
            <!-- WDN: see glossary item 'breadcrumbs' -->
            <!-- InstanceBeginEditable name="breadcrumbs" -->
            <ul>
                <li><a href="http://www.unl.edu/" title="University of Nebraska–Lincoln">UNL</a></li>
                                <li><?php echo $title?></li>
            </ul>
            <!-- InstanceEndEditable --></div>

        <div id="wdn_navigation_wrapper">
            <div id="navigation"><!-- InstanceBeginEditable name="navlinks" -->
                <ul>
    <li><a href="index.html" title="Home">Home</a></li>
    <li><?php echo $link?></li>
</ul>

                <!-- InstanceEndEditable --></div>
        </div>
    </div>

    <div id="wdn_content_wrapper">
        <div id="titlegraphic"><!-- InstanceBeginEditable name="titlegraphic" -->
            <h1><?php echo $title?></h1>
            <!-- InstanceEndEditable --></div>
        <div id="pagetitle"><!-- InstanceBeginEditable name="pagetitle" -->
                <h2><?php echo $title?></h2>
                <!-- InstanceEndEditable --></div>
        <div id="maincontent">
            <!--THIS IS THE MAIN CONTENT AREA; WDN: see glossary item 'main content area' -->
            <!-- InstanceBeginEditable name="maincontentarea" -->
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            TEXT! <br />
            <!--THIS IS THE END OF THE MAIN CONTENT AREA.-->
      </div>
        <div id="footer">
            <div id="footer_floater"></div>
            <div class="footer_col">

                <h3>Your Feedback</h3>
<form action="http://www1.unl.edu/comments/" method="post" id="wdn_feedback" title="WDN Feedback Form:4" class="rating">
    <fieldset><legend>Please rate this page</legend>
    <ol>
        <li><label for="r1">Your Rating:</label> 
            <select id="r1" name="rating">
                <option value="1">1</option>
                <option value="2">2</option>

                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </li>
    </ol>
    <input type="submit" value="Submit" name="submit" /></fieldset>
</form>

<!-- <div id="visitorChat_launchContainer"> -->
    <a id='visitorChat_launchButton' class='visitorChat_offline' href="<?php echo \UNL\VisitorChat\Controller::$url ?>">
        Chat with us now</a>
<!-- </div> -->
            </div>
            <div class="footer_col"><!-- InstanceBeginEditable name="leftcollinks" -->
                <h3>Related Links</h3>

                <!-- InstanceEndEditable --></div>
            <div class="footer_col"><!-- InstanceBeginEditable name="contactinfo" -->

                <h3>Contacting Us</h3>

                <!-- InstanceEndEditable --></div>
            <div class="footer_col">
                <h3>Share This Page</h3>
<ul class="socialmedia">
    <li><a href="http://go.unl.edu/?url=referer" id="wdn_createGoURL" rel="nofollow">Get a G<span>o</span>URL</a></li>
    <li class="outpost" id="wdn_emailthis"><a href="mailto:" title="Email this page to a friend" rel="nofollow">Email This Page</a></li>

    <li class="outpost" id="wdn_facebook"><a href="http://www.facebook.com/" title="Share this page on Facebook" rel="nofollow">Facebook</a></li>   
    <li class="outpost" id="wdn_twitter"><a href="http://www.twitter.com/" title="Share this page on Twitter" rel="nofollow">Twitter</a></li>
</ul>
            </div>
            <!-- InstanceBeginEditable name="optionalfooter" --> <!-- InstanceEndEditable -->
            <div id="wdn_copyright"><!-- InstanceBeginEditable name="footercontent" -->
                <script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-26427016-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

&copy; 2011 University of Nebraska&ndash;Lincoln | Lincoln, NE 68588 | 402-472-7211 | <a href="http://www.unl.edu/ucomm/aboutunl/" title="Click here to know more about UNL">About UNL</a> | <a href="http://www1.unl.edu/comments/" title="Click here to direct your comments and questions">comments?</a>
                <!-- InstanceEndEditable -->
                <br />
UNL web templates and quality assurance provided by the <a href="http://wdn.unl.edu/" title="UNL Web Developer Network">Web Developer Network</a>
<div id="wdn_logos">
    <a href="http://www.unl.edu/" title="UNL Home" id="unl_wordmark"><img src="/wdn/templates_3.0/css/footer/images/wordmark_white.png" alt="UNL Wordmark" /></a>

    <a href="http://www.cic.net/" title="CIC Website" id="cic_wordmark"><img src="/wdn/templates_3.0/css/footer/images/cic.png" alt="Committee on Institutional Cooperation Logo" /></a>
    <a href="http://www.bigten.org/" title="Big Ten Website" id="b1g_wordmark"><img src="/wdn/templates_3.0/css/footer/images/B1G.png" alt="Big Ten Logo" /></a>
</div>

                | <a href="http://validator.unl.edu/check/referer">W3C</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">CSS</a> <a href="http://www.unl.edu/" title="UNL Home" id="wdn_unl_wordmark"><img src="/wdn/templates_3.0/css/footer/images/wordmark.png" alt="UNL's wordmark" /></a> </div>
        </div>

    </div>
    <div id="wdn_wrapper_footer"> </div>
</div>
</body>
<!-- InstanceEnd -->
</html>
