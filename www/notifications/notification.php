<?php
if (file_exists(dirname(dirname(dirname(__FILE__))) . '/config.inc.php')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/config.inc.php';
} else {
    require dirname(dirname(dirname(__FILE__))) . '/config.sample.php';
}

$message = "Alert!";
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<html>
<head>
    <title>New Assignment</title>
    <link rel="icon" type="image/png" href="/wdn/templates_4.1/includes/icons/favicon-32x32.png?v=m223gpjb0w" sizes="32x32" />
    <script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url?>js\jquery.min.js"></script>
    <style type="text/css">
        body {
            height: 140px;
            width: 280px;
            text-align: center;
        }
        
        h2 {
            margin-bottom: 5px;
        }

        h3 {
            margin-top: 5px;
            margin-bottom: 5px;
        }
        
        #notification {
            background-color: rgba(255,255,255,0.7);
            width: 400px;
            height: 200px;
            margin-left: auto;
            margin-right: auto;
            border-radius: 15px;
            border: 2px solid black;
            margin-top: 200px;
        }
        
        #container {
            width: 800px;
            height: 400px;
            text-align: center;
        }
        
        #details {
            font-size: 10px;
        }
    </style>
    
    <script type="text/javascript">
        $(document).ready(function() {
            startflashing();
        });
        
        function startflashing(newCode) {
            if (newCode == '#000000' || newCode == undefined) {
                newCode = '#E01B1B';
                timeout = 500;
            } else {
                newCode = '#000000';
                timeout = 1500;
            }
            
            $("body").css("background-color", newCode);
            
            setTimeout("startflashing('"+newCode+"')",timeout);
        }
    </script>
</head>
<body>
<div id='container'>
    <div id='notification'>
        <h2>UNL Chat System</h2>
        <h3><h3><?php echo $message ?></h3></h3>
    </div>
</div>
</body>
</html>