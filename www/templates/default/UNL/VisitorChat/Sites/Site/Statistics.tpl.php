<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/timeline/timeline-min.js"></script>
<script type="text/javascript" src="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/moment.min.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo \UNL\VisitorChat\Controller::$url; ?>js/timeline/timeline.css">
<link rel="stylesheet" type="text/css" href="<?php echo \UNL\VisitorChat\Controller::$url; ?>css/timeline.css">

<?php
$statusStatistics = $context->getRawObject()->getStatusStatistics();
?>
    
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
        var original_data = <?php echo json_encode($statusStatistics); ?>

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
            
            var user = "";
            if (original_data[item]['user'] !== undefined) {
                user = " | " + original_data[item]['user'] + " changed to " + original_data[item]['status'] + " (" + original_data[item]['reason'] + ")";
            }
            
            var totalPeopleOnline = (num)?num:'';

            var content = '<div class="bar" style="' + style + '" ' +
                    ' title="' + num + ' ' + person + ' available for ' + diff + user + '">' + totalPeopleOnline + '</div>';

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
        var timeline = new links.Timeline(document.getElementById('statustimeline'));

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


<div class='grid12 first stats-table'>
    <h3>Site Availability</h3>
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
    <div id="statustimeline"></div>
</div>

<!-- Conversation Stats -->
<?php $stats = $context->getConversationStats()->getRawObject(); ?>

<section class="grid4 first shadow-right">
<h1 class="big-number"><?php echo $statusStatistics['percent_online']; ?></h1>
<p>Chat Online</p>
<p class="inline-number"><span class="percent"><?php echo $statusStatistics['percent_online_business']; ?></span><span class="right-text">During<br />8:00-5:00</span></p>
</section>

<section class="grid8">
    <div class="grid4 first">
        <h1 class="big-number"><?php echo $stats['conversation_types']['answered']; ?></h1>
        <p>Answered</p>
    </div>
    <div class="grid4">
        <h1 class="big-number"><?php echo $stats['conversation_types']['unanswered']; ?></h1>
        <p>Unanswered</p>
    </div>
    
<!-- Assignments Stats -->
<?php $stats = $context->getAssignmentStats()->getRawObject(); ?>
    
    <table class="assign-stats">
        <tbody>
            <tr>
                <td>
                    <h3><?php echo $stats['assignment_types']['completed']; ?></h3>
                    <p>Completed</p>
                </td>
                <td>
                    <h3><?php echo $stats['assignment_types']['left']; ?></h3>
                    <p>Left</p>
                </td>
                
                <td>
                    <h3><?php echo $stats['assignment_types']['expired']; ?></h3>
                    <p>Expired</p>
                </td>
                
                <td>
                    <h3><?php echo $stats['assignment_types']['rejected']; ?></h3>
                    <p>Rejected</p>
                </td>
                
                <td>
                    <h3><?php echo $stats['assignment_types']['failed']; ?></h3>
                    <p>Failed</p>
                </td>
                
            </tr>
        </tbody>
    </table>
</section>


<!--
<div class='grid12 first'>
    <div class='grid6 first'>
        
        <table class="zentable neutral">
            <thead><tr><th colspan='2'>Conversation Statistics</th></thead>
            <tbody>
                <tr>
                    <td>
                        <span title="The total number of conversations started">Total</span>
                    </td>
                    <td>
                        <?php echo $stats['total']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="The total number of conversations that were answered by atleast 1 operator">Answered</span>
                    </td>
                    <td>
                        <?php echo $stats['conversation_types']['answered']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="The total number of conversations that went unanswered by operators">Unanswered</span>
                    </td>
                    <td>
                        <?php echo $stats['conversation_types']['unanswered']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="">Percent of time Online</span>
                    </td>
                    <td>
                        <?php echo $statusStatistics['percent_online']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="">Percent of time Online from 8 to 5 Mon - Friday</span>
                    </td>
                    <td>
                        <?php echo $statusStatistics['percent_online_business']; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    

    
    <div class='grid6'>
        
        <table class="zentable neutral">
            <thead><tr><th colspan='2'>Assignment Statistics</th></thead>
            <tbody>
                <tr>
                    <td>
                        <span title="The total number of assignments created">Total</span>
                    </td>
                    <td>
                        <?php echo $stats['total']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="The assignment was answered and stayed in the conversation until the conversation was completed">Completed</span>
                    </td>
                    <td>
                        <?php echo $stats['assignment_types']['completed']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="The assignment was not answered">Expired</span>
                    </td>
                    <td>
                        <?php echo $stats['assignment_types']['expired']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="The assignment clicked 'reject' when prompted to answer an assignment">Rejected</span>
                    </td>
                    <td>
                        <?php echo $stats['assignment_types']['rejected']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="The conversation was closed before the assignment could be responded to">Failed</span>
                    </td>
                    <td>
                        <?php echo $stats['assignment_types']['failed']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span title="An operator left a conversation before it was completed">Left</span>
                    </td>
                    <td>
                        <?php echo $stats['assignment_types']['left']; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
-->