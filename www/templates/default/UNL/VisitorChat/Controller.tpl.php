<!DOCTYPE html>
<!--[if IEMobile 7 ]><html class="ie iem7"><![endif]-->
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"><![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"><![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"><![endif]-->
<!--[if (gte IE 9)|(gt IEMobile 7) ]><html class="ie" lang="en"><![endif]-->
<!--[if !(IEMobile) | !(IE)]><!--><html lang="en"><!-- InstanceBegin template="/Templates/fixed.dwt" codeOutsideHTMLIsLocked="false" --><!--<![endif]-->
<head>
<?php virtual("/wdn/templates_3.1/includes/metanfavico.html"); ?>
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
<?php virtual("/wdn/templates_3.1/includes/scriptsandstyles.html"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>UNL | Visitor Chat</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<!-- Place optional header elements here -->
<script type="text/javascript" src="/wdn/templates_3.1/scripts/plugins/ui/jQuery.ui.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/wdn/templates_3.1/scripts/plugins/ui/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo \UNL\VisitorChat\Controller::$url;?>css/operator.css" />
<?php
    //load model-specific css.
    if (file_exists(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/" . str_replace("\\", "/", $context->options['model']) . ".css")) {
         echo "<link rel='stylesheet' type='text/css' media='screen' href='" . \UNL\VisitorChat\Controller::$url . "css/" . str_replace("\\", "/", $context->options['model']) . ".css' />";
    }
?>
<script type="text/javascript" src="/wdn/templates_3.1/scripts/plugins/ui/jQuery.ui.js"></script>
<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url ?>js/chat.php?for=<?php echo \UNL\VisitorChat\User\Service::getCurrentUser()->type;?>"></script>

<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="document" -->
</head>
<body class="fixed">
    <nav class="skipnav">
        <a class="skipnav" href="#maincontent">Skip Navigation</a>
    </nav>
    <div id="wdn_wrapper">
        <header id="header" role="banner">
            <a id="logo" href="http://www.unl.edu/" title="UNL website">UNL</a>
            <span id="wdn_institution_title">University of Nebraska&ndash;Lincoln</span>
            <span id="wdn_site_title"><!-- InstanceBeginEditable name="titlegraphic" -->Visitor Chat System<!-- InstanceEndEditable --></span>
            <?php virtual("/wdn/templates_3.1/includes/idm.html"); ?>
            <?php virtual("/wdn/templates_3.1/includes/wdnTools.html"); ?>
        </header>
        <div id="wdn_navigation_bar">
            <nav id="breadcrumbs">
                <!-- WDN: see glossary item 'breadcrumbs' -->
                <h3 class="wdn_list_descriptor hidden">Breadcrumbs</h3>
                <!-- InstanceBeginEditable name="breadcrumbs" -->
                <ul>
                    <li><a href="http://www.unl.edu/" title="University of Nebraska–Lincoln">UNL</a></li>
                    <li>Home</li>
                </ul>
                <!-- InstanceEndEditable -->
            </nav>
            <div id="wdn_navigation_wrapper">
                <nav id="navigation" role="navigation">
                    <h3 class="wdn_list_descriptor hidden">Navigation</h3>
                    <!-- InstanceBeginEditable name="navlinks" -->
                    <ul>
                      <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('manage');?>">Dashboard</a></li>
                      <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history');?>">History</a>
                            <ul>
                              <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history');?>">My History</a></li>
                              <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history/sites');?>">Managed Site History</a></li>
                            </ul>
                        </li>
                        <?php 
                        if (\UNL\VisitorChat\User\Service::getCurrentUser()) {
                        ?>
                        <li>
                            <a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('user/settings');?>"><?php echo \UNL\VisitorChat\User\Service::getCurrentUser()->name;?></a>
                            <ul>
                                <li><a href="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('user/settings');?>">Settings</a></li>
                                <li><a href="<?php echo \UNL\VisitorChat\Controller::$url?>logout" title="Log Out">Logout</a></li>
                            </ul>
                        </li>
                        <?php 
                        }
                        ?>
                    </ul>
                    <!-- InstanceEndEditable -->
                </nav>
            </div>
        </div>
        <div id="wdn_content_wrapper">
            <div id="pagetitle">
                <!-- InstanceBeginEditable name="pagetitle" -->
                <h1>UNL Visitor Chat</h1>
                <!-- InstanceEndEditable -->
            </div>
            <div id="maincontent" role="main">
                <!--THIS IS THE MAIN CONTENT AREA; WDN: see glossary item 'main content area' -->
                <!-- InstanceBeginEditable name="maincontentarea" -->
                <div id='visitorChat_container'> 
                    <?php 
                    echo $savvy->render($context->actionable);
                    ?>
                </div>
                
                <div id="chatRequest" title="Incoming Chat Request">
                    You have an incoming chat request.
                    This request will expire in <span id="chatRequestCountDown">10</span> seconds.
                </div>
                
                <div id="alert" title="Alert">
                </div>
                
                <div id="shareChat" title="Share">
                </div>
                
                <div id='visitorChat_sound_container'>
                    <audio id='visitorChat_sound' src='<?php echo \UNL\VisitorChat\Controller::$url ?>audio/message.wav'></audio>
                </div>
                <div id="visitorChat_brightBox">
                  <p>Hello all!</p>
                </div>
                <!-- TemplateEndEditable -->
                <div class="clear"></div>
               <?php virtual("/wdn/templates_3.1/includes/noscript.html"); ?>
                <!--THIS IS THE END OF THE MAIN CONTENT AREA.-->
            </div>
        </div>
        <footer id="footer">
            <div id="footer_floater"></div>
            <div class="footer_col" id="wdn_footer_feedback">
                <?php virtual("/wdn/templates_3.1/includes/feedback.html"); ?>
            </div>
            <div class="footer_col" id="wdn_footer_related">
                <!-- InstanceBeginEditable name="leftcollinks" -->
                
                <!-- InstanceEndEditable --></div>
            <div class="footer_col" id="wdn_footer_contact">
                <!-- InstanceBeginEditable name="contactinfo" -->
                <h3>Contact Info</h3>
                E-mail <a href="mailto:s-mfairch4@unl.edu">Michael Fairchild</a> for questions, comments or bugs found in this system. <br />
                Visit the project on <a href="http://github.com/unl/VisitorChat">github</a>.
                <!-- InstanceEndEditable --></div>
            <div class="footer_col" id="wdn_footer_share">
                <?php virtual("/wdn/templates_3.1/includes/socialmediashare.html"); ?>
            </div>
            <!-- InstanceBeginEditable name="optionalfooter" -->
            <!-- InstanceEndEditable -->
            <div id="wdn_copyright">
                <div>
                    <!-- InstanceBeginEditable name="footercontent" -->
                    <!-- InstanceEndEditable -->
                   <?php virtual("/wdn/templates_3.1/includes/wdn.html"); ?>
                </div>
                <?php virtual("/wdn/templates_3.1/includes/logos.html"); ?>
            </div>
        </footer>
    </div>
</body>
<!-- InstanceEnd --></html>