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
<title>UNL | VisitorChat</title>

<!-- InstanceEndEditable --><!-- InstanceBeginEditable name="head" -->
<!-- Place optional header elements here -->
<script type="text/javascript" src="/wdn/templates_3.0/scripts/plugins/ui/jQuery.ui.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/wdn/templates_3.0/scripts/plugins/ui/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo \UNL\VisitorChat\Controller::$url;?>css/operator.css" />
<!-- InstanceEndEditable -->
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

                <li><a href="<?php echo \UNL\VisitorChat\Controller::$url?>" title="Visitor Chat">VisitorChat</a></li>
                <li>Client Login Page</li>
            </ul>
            <!-- InstanceEndEditable --></div>

        <div id="wdn_navigation_wrapper">
            <div id="navigation"><!-- InstanceBeginEditable name="navlinks" -->
                <ul>
                    <li><a href="<?php echo \UNL\Visitorchat\Controller::$URLService->generateSiteURL('manage');?>">Dashboard</a></li>
                    <li><a href="<?php echo \UNL\Visitorchat\Controller::$URLService->generateSiteURL('history');?>">History</a>
                        <ul>
                          <li><a href="<?php echo \UNL\Visitorchat\Controller::$URLService->generateSiteURL('history');?>">My History</a></li>
                        </ul>
                    </li>
                    <li><a href="<?php echo \UNL\VisitorChat\Controller::$url?>logout" title="Log Out">Logout</a></li>
                </ul>
                <!-- InstanceEndEditable --></div>
        </div>
    </div>

    <div id="wdn_content_wrapper">
        <div id="titlegraphic"><!-- InstanceBeginEditable name="titlegraphic" -->
            <h1>VisitorChat </h1>

            <!-- InstanceEndEditable --></div>
        <div id="pagetitle"><!-- InstanceBeginEditable name="pagetitle" -->
                <h2>VisitorChat</h2>
                <!-- InstanceEndEditable --></div>
        <div id="maincontent">
            <!--THIS IS THE MAIN CONTENT AREA; WDN: see glossary item 'main content area' -->
            <!-- InstanceBeginEditable name="maincontentarea" -->
            <div id='visitorChat_container'> 
                <?php 
                echo $savvy->render($context->actionable);
                ?>
            </div>
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
<form action="http://www1.unl.edu/comments/" method="post" id="wdn_feedback_comments" title="WDN Feedback Form" class="comments">
    <fieldset><legend>Comments for this page</legend>
    <ol>
        <li class="wdn_comment_name">
            <label for="wdn_comment_name">Name (optional)</label>

            <input type="text" name="name" id="wdn_comment_name" />
        </li>
        <li class="wdn_comment_email">

            <label for="wdn_comment_email">Email (optional)</label>
            <input type="text" name="email" id="wdn_comment_email" />
        </li>
        <li><label for="wdn_comments">Comments</label>

          <textarea rows="2" cols="20" name="comment" id="wdn_comments"></textarea>
        </li>
    </ol>

    <input type="submit" value="Submit" name="submit" class="wdn_comment_submit" /></fieldset>
</form>

            </div>
            <div class="footer_col">
                <!-- InstanceBeginEditable name="leftcollinks" -->
                <!-- InstanceEndEditable --></div>
            <div class="footer_col">
                <!-- InstanceBeginEditable name="contactinfo" -->
                <!-- InstanceEndEditable -->
            </div>
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
            <div id="wdn_copyright">
                <!-- InstanceBeginEditable name="footercontent" -->
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