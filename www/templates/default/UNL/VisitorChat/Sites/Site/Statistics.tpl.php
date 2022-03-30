<?php
$page->addScript(\UNL\VisitorChat\Controller::$url ."js/timeline/timeline-min.js");
$page->addScript(\UNL\VisitorChat\Controller::$url . "js/moment.min.js");
$page->addStyleSheet(\UNL\VisitorChat\Controller::$url . "js/timeline/timeline.css");
$page->addStyleSheet(\UNL\VisitorChat\Controller::$url . "css/timeline.css");

$statusStatistics = $context->getRawObject()->getStatusStatistics();

$page->addScriptDeclaration("
    WDN.initializePlugin('jqueryui', [function () {
        var $ = require('jquery');
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
    
            return \"RGB(\" + parseInt(R*255) + \",\" + parseInt(G*255) + \",\" + parseInt(B*255) + \")\";
        };
        
        // Called when the Visualization API is loaded.
        WDN.jQuery(function(){
            var original_data = " . json_encode($statusStatistics) . "
    
            // Create and populate a data table.
            var data = [];
            var min  = false;
            var max  = false;
    
            for (item in original_data['statuses']) {
                if (!min) {
                    min = new Date(original_data['statuses'][item]['start'] - 86400000);
                }
    
                var num = original_data['statuses'][item]['total'];
                var maxNum = 20;
                var height = Math.round(num / maxNum * 70 + 20);
    
                var color = 'red';
    
                if (num > 0) {
                    var hue = Math.min(Math.max(180+(num*6)), 240)// hue between 0 (red) and 120 (green)
                    //alert(hue);
                    color = hsv2rgb(hue, 0.95, 0.95);
                }
    
                //Calculate a human readable difference
                var diff = moment.duration(moment(original_data['statuses'][item]['start']).diff(moment(original_data['statuses'][item]['end']))).humanize();
    
                style = 'height:' + height + '%;' +
                        'background-color: ' + color + ';';
                
                var person = \"person\";
                
                if (num > 0) {
                    person = \"people\";
                }
                
                var user = \"\";
                if (original_data['statuses'][item]['user'] !== undefined) {
                    user = \" | \" + original_data['statuses'][item]['user'] + \" changed to \" + original_data['statuses'][item]['status'] + \" (\" + original_data['statuses'][item]['reason'] + \")\";
                }
                
                var totalPeopleOnline = (num)?num:'';
    
                var content = '<div class=\"bar\" style=\"' + style + '\" ' +
                        ' title=\"' + num + ' ' + person + ' available for ' + diff + user + '\">' + totalPeopleOnline + '</div>';
    
                //add to array
                data.push({
                    'start': new Date(original_data['statuses'][item]['start']),
                    'end': new Date(original_data['statuses'][item]['end']),  // end is optional
                    'content': content
                });
            }
            
            max = new Date(original_data['statuses'][item]['end'] + 86400000);
            
            // specify options
            options = {
                \"width\":  \"100%\",
                \"height\": \"200px\",
                \"style\": \"box\", // optional
                \"stackEvents\": false,
                \"animateZoom\": false,
                \"animate\": false,
                \"intervalMin\": 10000,
                \"max\": max,
                \"min\": min,
                \"showNavigation\": true,
                \"showCurrentTime\": false,
                \"start\": min
            };
    
            // Instantiate our timeline object.
            var timeline = new links.Timeline(document.getElementById('statustimeline'));
    
            // Draw our timeline with the created data and options
            timeline.draw(data, options);
        });
    
        WDN.jQuery(function() {
            WDN.jQuery( \"#from\" ).datepicker({
                defaultDate    : \"+1w\",
                changeMonth    : true,
                changeYear     : true,
                numberOfMonths : 1,
                dateFormat     : \"yy-mm-dd\",
                onClose: function( selectedDate ) {
                    WDN.jQuery( \"#to\" ).datepicker( \"option\", \"minDate\", selectedDate );
                }
            });
            WDN.jQuery( \"#to\" ).datepicker({
                defaultDate    : \"+1w\",
                changeMonth    : true,
                changeYear     : true,
                numberOfMonths : 1,
                dateFormat     : \"yy-mm-dd\",
                onClose: function( selectedDate ) {
                    WDN.jQuery( \"#from\" ).datepicker( \"option\", \"maxDate\", selectedDate );
                }
            });
        });
    }]);");
?>

<div class='grid12 first stats-table'>
    <h2>Site Availability</h2>
    <div id='dateRange' class="dcf-pb-6">
        <form action='<?php echo $context->getURL(); ?>' class="stats-form">
          <div class="dcf-input-group">
            <label class="dcf-label" for="from">From</label>
            <input class="dcf-input-text" type="text" id="from" name="start" value="<?php echo $context->start; ?>" />
            <label class="dcf-label" for="to">to</label>
            <input class="dcf-input-text" type="text" id="to" name="end" value="<?php echo $context->end; ?>" />
            <input type='hidden' name='url' value='<?php echo $context->url ?>' />
            <input class="dcf-btn dcf-btn-primary" type='submit' value='Submit' />
          </div>
        </form>
    </div>
    <div id="statustimeline"></div>
</div>

<!-- Conversation Stats -->
<?php $stats = $context->getConversationStats()->getRawObject(); ?>

<h2>Chat Stats</h2>

<div class="grid2 first shadow-right">
    <div class='grid2 first'>
        <p class="med-number"><span class='percent'><?php echo $statusStatistics['percent_online']; ?></span></p>
        <p class='stat-title'>Chat Online</p>
    </div>
    <div class='grid2 first'>
        <p class="med-number"><span class='percent'><?php echo $statusStatistics['percent_online_business']; ?></span></p>
        <p class="stat-title">Online during<br />8:00-5:00</p>
    </div>
</div>

<div class="grid10">
    <div class="grid5 first" title="The total number of conversations that were answered by at least 1 operator">
        <p class="big-number"><?php echo $stats['conversation_types']['answered']; ?></p>
        <p>Answered</p>
    </div>
    <div class="grid5" title="The total number of conversations that went unanswered by operators">
        <p class="big-number"><?php echo $stats['conversation_types']['unanswered']; ?></p>
        <p>Unanswered</p>
    </div>
    
<!-- Assignments Stats -->
<?php $stats = $context->getAssignmentStats()->getRawObject(); ?>
    
    <div class="assign-stats">
        <div class='grid2 first' title="The assignment was answered and stayed in the conversation until the conversation was completed">
            <p class="med-number"><?php echo $stats['assignment_types']['completed']; ?></p>
            <p class="stat-title">Completed</p>
        </div>
        
        <div class='grid2' title="An operator left a conversation before it was completed">
            <p class="med-number"><?php echo $stats['assignment_types']['left']; ?></p>
            <p class="stat-title">Left</p>
        </div>
        
        <div class='grid2' title="The assignment was not answered">
            <p class="med-number"><?php echo $stats['assignment_types']['expired']; ?></p>
            <p class="stat-title">Expired</p>
        </div>
        
        <div class='grid2' title="The assignment clicked 'reject' when prompted to answer an assignment">
            <p class="med-number"><?php echo $stats['assignment_types']['rejected']; ?></p>
            <p class="stat-title">Rejected</p>
        </div>

        <div class='grid2' title="The conversation was closed before the assignment could be responded to">
            <p class="med-number"><?php echo $stats['assignment_types']['failed']; ?></p>
            <p class="stat-title">Failed</p>
        </div>
    </div>
</div>