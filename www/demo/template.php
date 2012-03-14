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

<!DOCTYPE html>
<!--[if IEMobile 7 ]><html class="ie iem7"><![endif]-->
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"><![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"><![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"><![endif]-->
<!--[if (gte IE 9)|(gt IEMobile 7) ]><html class="ie" lang="en"><![endif]-->
<!--[if !(IEMobile) | !(IE)]><!--><html lang="en"><!-- InstanceBegin template="/Templates/fixed.dwt" codeOutsideHTMLIsLocked="false" --><!--<![endif]-->
<head>
<?php include dirname(__DIR__) . '/../../wdn/templates_3.1/includes/metanfavico.html'; ?>
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
    
    $Id: fixed.dwt | 1e98ba6f3cd3310802e61545987e6582d0abac6f | Wed Feb 15 11:42:58 2012 -0600 | Kevin Abel  $
-->
<?php include dirname(__DIR__) . '/../../wdn/templates_3.1/includes/scriptsandstyles.html'; ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>UNL | Chat Demo | <?php echo $title?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<!-- Place optional header elements here -->
<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url ?>js/chat.php"></script>
<style type="text/css">
#maincontent .grid1, #maincontent  .grid2, #maincontent  .grid3, #maincontent  .grid4, #maincontent  .grid5, #maincontent  .grid6, #maincontent  .grid7, #maincontent  .grid8, #maincontent  .grid9, #maincontent  .grid10, #maincontent  .grid11, #maincontent  .grid12 {
    margin-bottom: 15px;
    padding-top: 5px;
    padding-bottom: 5px;
    background: rgba(111,191,77,.4);
    border: solid 1px #6FBF4D;
    border-width: 1px 0;
    text-align:center;
}
</style>
<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="document" -->
</head>
<body class="fixed">
    <nav class="skipnav">
        <a class="skipnav" href="#maincontent">Skip Navigation</a>
    </nav>
    <div id="wdn_wrapper">
        <div id='visitorChat_launcher_container'>
            <div id='visitorChat_launcher' class='visitorChat_offline'>
                Chat
            </div>
        </div>
        <header id="header" role="banner">
            <a id="logo" href="http://www.unl.edu/" title="UNL website">UNL</a>
            <span id="wdn_institution_title">University of Nebraska&ndash;Lincoln</span>
            <span id="wdn_site_title"><!-- InstanceBeginEditable name="titlegraphic" -->College of Agricultural Sciences &amp; Natural Resources <span>A division of the College of Arts &amp; Sciences</span><!-- InstanceEndEditable --></span>
            <?php include dirname(__DIR__) . '/../../wdn/templates_3.1/includes/idm.html'; ?>
            <?php include dirname(__DIR__) . '/../../wdn/templates_3.1/includes/wdnTools.html'; ?>
        </header>
        <div id="wdn_navigation_bar">
            <nav id="breadcrumbs">
                <!-- WDN: see glossary item 'breadcrumbs' -->
                <h3 class="wdn_list_descriptor hidden">Breadcrumbs</h3>
                <!-- InstanceBeginEditable name="breadcrumbs" -->
                <ul>
                    <li><a href="http://www.unl.edu/" title="University of Nebraska–Lincoln">UNL</a></li>
                    <li><?php echo $title?></li>
                </ul>
                <!-- InstanceEndEditable -->
            </nav>
            <div id="wdn_navigation_wrapper">
                <nav id="navigation" role="navigation">
                    <h3 class="wdn_list_descriptor hidden">Navigation</h3>
                    <!-- InstanceBeginEditable name="navlinks" -->
                    <ul>
                        <li><a href="index.php" title="Home">Home</a></li>
                        <li><?php echo $link;?></li>
                    </ul>
                    <!-- InstanceEndEditable -->
                </nav>
            </div>
        </div>
        <div id="wdn_content_wrapper">
            <div id="pagetitle">
                <!-- InstanceBeginEditable name="pagetitle" -->
                <h1><?php echo $title?></h1>
                <!-- InstanceEndEditable -->
            </div>
            <div id="maincontent" role="main">
                <!--THIS IS THE MAIN CONTENT AREA; WDN: see glossary item 'main content area' -->
                <!-- InstanceBeginEditable name="maincontentarea" -->
                  <h2 class="sec_header"><?php echo $title?></h2>
                  <div class="grid1 first"> grid1 </div>
                  <div class="grid11"> grid11 </div>
                  <div class="grid2 first"> grid2 </div>
                  <div class="grid10"> grid10 </div>
                  <div class="grid3 first"> grid3 </div>
                  <div class="grid9"> grid9 </div>
                  <div class="grid4 first"> grid4 </div>
                  <div class="grid8"> grid8 </div>
                  <div class="grid4 first"> grid4 </div>
                  <div class="grid4"> grid4 </div>
                  <div class="grid4"> grid4 </div>
                  <div class="grid5 first"> grid5 </div>
                  <div class="grid7"> grid7 </div>
                  <div class="grid6 first"> grid6 </div>
                  <div class="grid6"> grid6 </div>
                  <div class="grid3 first"> grid3 </div>
                  <div class="grid3"> grid3 </div>
                  <div class="grid1"> grid1 </div>
                  <div class="grid5"> grid5 </div>
                  <h3 class="sec_header">Heading 3</h3>
                  <h4 class="sec_header">Heading 4</h4>
                  <h5 class="sec_header">Heading 5</h5>
                  <h6 class="sec_header">Heading 6</h6>
                  <div class="grid1 first"> grid1 </div>
                  <div class="grid5"> grid5 </div>
                  <div class="grid3"> grid3 </div>
                  <div class="grid3"> grid3 </div>
                  <div class="grid3 first"> 3 </div>
                  <div class="grid9">
                    <div class="grid3 first">3</div>
                    <div class="grid3">3</div>
                    <div class="grid3">3</div>
                    <div class="grid5 first">5</div>
                    <div class="grid2">2</div>
                    <div class="grid2">2</div>
                  </div>
                  <div class="grid8 first">
                    <div class="grid3 first">3</div>
                    <div class="grid3">3</div>
                    <div class="grid2">2</div>
                    <div class="grid1 first">1</div>
                    <div class="grid1">1</div>
                    <div class="grid6">6</div>
                  </div>
                  <div class="grid4"> 4 </div>
                  <!-- InstanceEndEditable -->
                <div class="clear"></div>
                <?php include dirname(__DIR__) . '/../../wdn/templates_3.1/includes/noscript.html'; ?>
                <!--THIS IS THE END OF THE MAIN CONTENT AREA.-->
            </div>
        </div>
        <footer id="footer">
            <div id="footer_floater"></div>
            <div class="footer_col" id="wdn_footer_feedback">
<div id="visitorChat_footercontainer">
<form id='visitorchat_clientLogin_email' class='unl_visitorchat_form' name='input' method="post" action="<?php echo \UNL\VisitorChat\Controller::$url;?>clientLogin" style="display: block">
    <fieldset><legend>Client Login</legend>
        <ul>
            <li class="visitorChat_info">
                <label for="visitorChat_name"></label>
                <input type="text" name="name" id="visitorChat_name"/>
            </li>
            <li class="visitorChat_info">
                <label for="visitorChat_email"></label>
                <input type="text" name="email" class="validate-email" id="visitorChat_email"/>
            </li>
            <li class="visitorChat_info">
                <input type="checkbox" id="visitorChat_email_fallback" name="email_fallback" value="1" />
                <span id="email-fallback-text">If no operators are available,<br />I would like to receive an email.</span>
            </li>
            <li class="visitorChat_center">
                <textarea rows="3" cols="25" class="required-entry" id='visitorChat_messageBox' name="message"></textarea>
            </li>
            
        </ul>
    </fieldset>
    <input type="hidden" name="initial_url" id="initial_url" value=""/>
    <input id="visitorChat_login_chatmethod" type="hidden" name="method" value="EMAIL" />
    <input id='visitorChat_login_sumbit' type="submit" value="Submit" name="visitorChat_login_sumbit" />
</form>
</div>
            </div>
            <div class="footer_col" id="wdn_footer_related">
                <!-- InstanceBeginEditable name="leftcollinks" -->
                example
                <!-- InstanceEndEditable --></div>
            <div class="footer_col" id="wdn_footer_contact">
                <!-- InstanceBeginEditable name="contactinfo" -->
                example
                <!-- InstanceEndEditable --></div>
            <div class="footer_col" id="wdn_footer_share">
                <?php include dirname(__DIR__) . '/../../wdn/templates_3.1/includes/socialmediashare.html'; ?>
            </div>
            <!-- InstanceBeginEditable name="optionalfooter" -->
            <!-- InstanceEndEditable -->
            <div id="wdn_copyright">
                <div>
                    <!-- InstanceBeginEditable name="footercontent" -->
                    <!-- InstanceEndEditable -->
                    <?php include dirname(__DIR__) . '/../../wdn/templates_3.1/includes/wdn.html'; ?>
                </div>
                <?php include dirname(__DIR__) . '/../../wdn/templates_3.1/includes/logos.html'; ?>
            </div>
        </footer>
    </div>
</body>
<!-- InstanceEnd --></html>
