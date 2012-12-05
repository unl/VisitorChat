<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/timeline/timeline-min.js"></script>
<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/moment.min.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/timeline/timeline.css">
<link rel="stylesheet" type="text/css" href="<?php echo \UNL\VisitorChat\Controller::$url; ?>css/timeline.css">

<script type="text/javascript">
    /**
     * Calculate the color based on the given value.
     * @param {number} H   Hue, a value be between 0 and 360
     * @param {number} S   Saturation, a value between 0 and 1
     * @param {number} V   Value, a value between 0 and 1
     */
    var hsv2rgb = function(H, S, V) {
        var R, G, B, C, Hi, X;

        C = V * S;
        Hi = Math.floor(H/60);  // hi = 0,1,2,3,4,5
        X = C * (1 - Math.abs(((H/60) % 2) - 1));

        switch (Hi) {
            case 0: R = C; G = X; B = 0; break;
            case 1: R = X; G = C; B = 0; break;
            case 2: R = 0; G = C; B = X; break;
            case 3: R = 0; G = X; B = C; break;
            case 4: R = X; G = 0; B = C; break;
            case 5: R = C; G = 0; B = X; break;

            default: R = 0; G = 0; B = 0; break;
        }

        return "RGB(" + parseInt(R*255) + "," + parseInt(G*255) + "," + parseInt(B*255) + ")";
    };
    
    // Called when the Visualization API is loaded.
    WDN.jQuery(function(){
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
            var height = Math.round(num / maxNum * 70 + 20);

            var color = 'red';

            if (num > 0) {
                var hue = Math.min(Math.max(180+(num*6)), 240)// hue between 0 (red) and 120 (green)
                //alert(hue);
                color = hsv2rgb(hue, 0.95, 0.95);
            }

            //Calculate a human readable difference
            var diff = moment.duration(moment(original_data[item]['start']).diff(moment(original_data[item]['end']))).humanize();

            style = 'height:' + height + '%;' +
                    'background-color: ' + color + ';';
            
            var person = "person";
            
            if (num > 0) {
                person = "people";
            }
            
            var totalPeopleOnline = (num)?num:'';

            var content = '<div class="bar" style="' + style + '" ' +
                    ' title="' + num + ' ' + person + ' available for ' + diff + '">' + totalPeopleOnline + '</div>';

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
            "height": "200px",
            "style": "box", // optional
            "stackEvents": false,
            "animateZoom": false,
            "animate": false,
            "intervalMin": 10000,
            "max": max,
            "min": min,
            "showNavigation": true
        };

        // Instantiate our timeline object.
        var timeline = new links.Timeline(document.getElementById('mytimeline'));

        // Draw our timeline with the created data and options
        timeline.draw(data, options);
    });

    WDN.jQuery(function() {
        WDN.jQuery( "#from" ).datepicker({
            defaultDate    : "+1w",
            changeMonth    : true,
            changeYear     : true,
            numberOfMonths : 1,
            dateFormat     : "yy-mm-dd",
            onClose: function( selectedDate ) {
                WDN.jQuery( "#to" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        WDN.jQuery( "#to" ).datepicker({
            defaultDate    : "+1w",
            changeMonth    : true,
            changeYear     : true,
            numberOfMonths : 1,
            dateFormat     : "yy-mm-dd",
            onClose: function( selectedDate ) {
                WDN.jQuery( "#from" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
    });
</script>
    
<div id='dateRange'>
    <form action='<?php echo $context->getURL(); ?>'>
        <label for="from">From</label>
        <input type="text" id="from" name="start" value="<?php echo $context->start; ?>" />
        <label for="to">to</label>
        <input type="text" id="to" name="end" value="<?php echo $context->end; ?>" />
        <input type='hidden' name='url' value='<?php echo $context->url ?>' />
        <input type='submit' value='Submit' />
    </form>
</div>

<div class='grid12 first'>
    <h3>Site Availability</h3>
    <div id="mytimeline"></div>
</div>

<div class='grid12 first'>
    <div class='grid6 first'>
        <h3>Conversation Statistics</h3>
        <table class="zentable neutral">
            <thead><tr><th>Type</th> <th>Value</th></tr></thead>
            <tbody>
            <?php
                $stats = $context->getConversationStats()->getRawObject();
                
                foreach ($stats['conversation_types'] as $type=>$value) {
                    echo "<tr>
                            <td>$type</td>
                            <td>$value</td>
                          </tr>";
                }
            ?>
            </tbody>
        </table>
    </div>
    
    <div class='grid6'>
        <h3>Assignment Statistics</h3>
        <table class="zentable neutral">
            <thead><tr><th>Type</th> <th>Value</th></tr></thead>
            <tbody>
            <?php
            $stats = $context->getAssignmentStats()->getRawObject();
        
            foreach ($stats['assignment_types'] as $type=>$value) {
                echo "<tr>
                        <td>$type</td>
                        <td>$value</td>
                      </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>