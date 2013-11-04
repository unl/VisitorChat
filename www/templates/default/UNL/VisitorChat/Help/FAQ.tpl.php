<ul>
    <li><a href='#howtouse'>How do I use the chat system?</a></li>
    <li><a href='#howtoadd'>How to I add my site to the chat system?</a></li>
    <li><a href='#shownicon'>Why does my site have chat enabled but I have not set it up?</a></li>
    <li><a href='#features'>What are some features of the chat system?</a></li>
    <li><a href='#cost'>How much does the chat system cost?</a></li>
    <li><a href='#notifications'>How do I turn on notifications?</a></li>
    <li><a href='#block'>How can I block someone?</a></li>
    <li><a href='#listsrv'>How can I be notified of issues or upgrades to the system?</a></li>
    <li><a href='#whyemails'>Why am I getting emails with a subject like "UNLchat [number]"?</a></li>
</ul>

<dl class='faq'>
    <dt id='howtouse'>How do I use the chat system?</dt>
    <dd>
        <p>We have some videos to show you how to use the the chat system.
        </p>
            <ul>
                <li><a href='http://go.unl.edu/unlchat_visitor'>Visitor Tutorial</a></li>
                <li><a href='http://go.unl.edu/unlchat_operator'>Operator Tutorial</a></li>
            </ul>
    </dd>
    <dt id='howtoadd'>How to I add my site to the chat system?</dt>
    <dd>
        <p>Currently, in order to set up your site in the chat system, you will need to send an email to <a href='mailto:mfairchild@unl.edu'>Michael Fairchild</a>.
            The email should contain the following information.
        </p>
            <ul>
                <li>The URL of the site that you want to set up.</li>
                <li>Names of personnel that will be answering chats.</li>
                <li>The role for each person.  Possible chat roles include Operator (can only answer chats) and Manager (can answer chats and view site history)</li>
            </ul>
        <p>We are working on automating this setup process, however that work is not yet completed.</p>
    </dd>
    
    <dt id='shownicon'>Why does my site have chat enabled but I have not set it up?</dt>
    <dd><p>Chances are your site is not defined in the <a href='http://www1.unl.edu/wdn/registry/'>WDN Registry</a> yet.
        Please visit the registry to ensure that your site is defined.  Note that any changes made in the registry can take up to 24 hours to take effect in the chat system.</p></dd>
    
    <dt id='features'>What are some features of the chat system?</dt>
    <dd>
        <p>
            The chat system allows visitors to UNL templated web pages to chat with site personnel as defined in the WDN Registry.  Some features include:</p>
            <ul>
                <li>Once a visitor starts a conversation, it will follow them to any UNL templated web page.</li>
                <li>New conversations will be routed to an operator that can help with the site that client started the conversation on.</li>
                <li>Operators can see where the conversation was started, the ip, browser name and operating system name of the client.</li>
                <li>Operators can view past conversations that they have taken part in.</li>
                <li>Managers can view all conversations for their site.</li>
                <li>Conversations are routed intelligently, so that if all operators for the site fail to answer the conversation, the next nearest site will be asked to answer the conversation instead.</li>
                <li>Operators can configure the number of conversations they can handle at any given time.</li>
            </ul>
    </dd>
    
    <dt id='cost'>How much does the chat system cost?</dt>
    <dd><p>The chat system is free for any UNL site.</p></dd>
    
    <dt id='notifications'>How do I turn on notifications?</dt>
    <dd><p>The chat system includes several kinds of notifications:</p>
        <ul>
            <li>Sound notifications.</li>
            <li>Page title notifications (the page title will flash when there is a new alert).</li>
            <li>Desktop notification support.</li>
        </ul>
        <p>
            Desktop notifications can only be used when the operator is using Google Chrome (currently the only browser that supports desktop notifications).
            If you are using google chrome, you can enable desktop notifications in the settings page.
        </p>
    </dd>

    <dt id='block'>How can I block someone?</dt>
    <dd><p>An operator or manager can block someone by IP address.</p>
        <ol>
            <li>During a conversation, hover over the visitor's name in the header section of the chat</li>
            <li>Click the Block link</li>
            <li>Confirm to end the conversation and then edit/submit the block.</li>
        </ol>
        <p>
            <img src="<?php echo \UNL\VisitorChat\Controller::$URLService->generateSiteURL('images/block_ip.png');?>" />
        </p>
        <p>
            Blocks should be temporary.  By default, the length of the block will get longer the more often the IP address has been blocked in the past.
            IP addresses of users can change, so it is important to not block an IP address permanently.
        </p>
        <p>
            IP address blocks are system wide and can be created/edited/disabled by any operator or manager regardless of what site they are assigned to.
            To disable an active block, simply edit it and change the status to DISABLED.
        </p>
    </dd>
    
    <dt id='listsrv'>How can I be notified of issues or upgrades to the system?</dt>
    <dd><p>We keep a listsrv where we send announcements regarding issues and/or upgrades to the system. <br />
        <a href='http://listserv.unl.edu/signup-anon/?LISTNAME=unlchat'>Sign up for announcements now</a>!
        </p>
    </dd>

    <dt id='whyemails'>Why am I getting emails with a subject like "UNLchat [number]"?</dt>
    <dd>
        <p>
            Visitors can send emails to site members when no operators are available for chat.  By default all members of the site
            in the <a href='http://www1.unl.edu/wdn/registry/'>WDN Registry</a> will receive the email.
        </p>
        <p>
            These emails can be sent to a site support email address instead of all of the members.  This can be set up by emailing <a href='mailto:mfairchild@unl.edu'>Michael Fairchild</a>.
        </p>
        <p>
            If you do not wish to receive these emails, you will have remove yourself from the site in the <a href='http://www1.unl.edu/wdn/registry/'>WDN Registry</a>.
            If the site that you want to remove yourself from is a <a href="http://unlcms.unl.edu/">UNLcms</a> site, you will have to remove yourself from the site in <a href="http://unlcms.unl.edu/">UNLcms</a>.
        </p>
    </dd>
</dl>