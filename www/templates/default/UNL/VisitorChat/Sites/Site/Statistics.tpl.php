<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/timeline/timeline-min.js"></script>
<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/moment.min.js"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
    
<link rel="stylesheet" type="text/css" href="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/timeline/timeline.css">
<link rel="stylesheet" type="text/css" href="<?php echo \UNL\VisitorChat\Controller::$url; ?>css/timeline.css">

<script type="text/javascript">
    google.load("visualization", "1");

    // Set callback to run when API is loaded
    google.setOnLoadCallback(drawVisualization);

    // Called when the Visualization API is loaded.
    function drawVisualization() {
        var original_data = <?php echo json_encode($context->getRawObject()->getStatusStatistics()); ?>
        
        // Create and populate a data table.
        var data = [];
        var min  = false;
        var max  = false;
        
        for (item in original_data) {
            if (!min) {
                min = new Date(original_data[item]['start'] - 86400000);
            }
            
            var num = original_data[item]['total'];
            var maxNum = 20;
            
            var color = 'red';
            
            if (num > 0) {
                color = 'grey';
            }
            
            //Calculate a human readable difference
            var diff = moment.duration(moment(original_data[item]['start']).diff(moment(original_data[item]['end']))).humanize();
            
            height = Math.round(num / maxNum * 70 + 20);
            style = 'height:' + height + 'px;' +
                    'background-color: ' + color + ';';
                    
            var content = '<div class="bar" style="' + style + '" ' +
                    ' title="' + num + ' people Available for ' + diff + '">' + num + '</div>';
            
            //add to array
            data.push({
                'start': new Date(original_data[item]['start']),
                'end': new Date(original_data[item]['end']),  // end is optional
                'content': content
            });
        }

        max = new Date(original_data[item]['end'] + 86400000);

        // specify options
        options = {
            "width":  "100%",
            "height": "300px",
            "style": "box", // optional
            "stackEvents": false,
            "animateZoom": false,
            "animate": false,
            "intervalMin": 1000,
            "max": max,
            "min": min,
            "showNavigation": true
        };

        // Instantiate our timeline object.
        var timeline = new links.Timeline(document.getElementById('mytimeline'));

        // Draw our timeline with the created data and options
        timeline.draw(data, options);
    }
</script>
    
<div id="mytimeline"></div>
