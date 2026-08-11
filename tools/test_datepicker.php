<?php
echo "<h1>🧪 Date Picker Test</h1>";
echo "<p>This page tests if the date picker is working properly.</p>";

echo "<!-- Datepicker CSS -->
<link href='assets/css/plugins/datapicker/datepicker3.css' rel='stylesheet'>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.test-section { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>";

echo "<div class='test-section'>
<h3>Test Date Input Fields</h3>
<p>Try clicking the calendar icons or typing dates manually (MM/DD/YYYY format).</p>

<div class='form-group' style='margin-bottom: 15px;'>
    <label>Start Date:</label>
    <div class='input-group date' id='test_start_picker' style='width: 200px;'>
        <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
        <input type='text' id='test_start_date' class='form-control' placeholder='MM/DD/YYYY' autocomplete='off'/>
    </div>
</div>

<div class='form-group' style='margin-bottom: 15px;'>
    <label>End Date:</label>
    <div class='input-group date' id='test_end_picker' style='width: 200px;'>
        <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
        <input type='text' id='test_end_date' class='form-control' placeholder='MM/DD/YYYY' autocomplete='off'/>
    </div>
</div>

<button onclick='testDateValues()' style='margin-top: 10px;'>Check Date Values</button>
<div id='date-values' style='margin-top: 10px; padding: 10px; background: #f8f9fa;'></div>
</div>";

echo "<!-- jQuery and Datepicker JS -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='assets/js/plugins/datapicker/bootstrap-datepicker.js'></script>

<script>
console.log('Testing datepicker initialization...');

function initializeTestDatePickers() {
    console.log('Initializing test datepickers...');

    if (typeof $ === 'undefined') {
        console.error('jQuery not loaded!');
        document.getElementById('date-values').innerHTML = '<span class=\"error\">❌ jQuery not loaded</span>';
        return;
    }

    try {
        // Initialize test start date picker
        $('#test_start_picker').datepicker({
            format: 'mm/dd/yyyy',
            todayBtn: 'linked',
            todayHighlight: true,
            autoclose: true,
            clearBtn: true,
            orientation: 'bottom auto'
        }).on('changeDate', function(e) {
            console.log('Test start date changed:', e.format());
        });

        // Initialize test end date picker
        $('#test_end_picker').datepicker({
            format: 'mm/dd/yyyy',
            todayBtn: 'linked',
            todayHighlight: true,
            autoclose: true,
            clearBtn: true,
            orientation: 'bottom auto'
        }).on('changeDate', function(e) {
            console.log('Test end date changed:', e.format());
        });

        // Set default dates
        var currentYear = new Date().getFullYear();
        $('#test_start_picker').datepicker('setDate', new Date(currentYear, 0, 1));
        $('#test_end_picker').datepicker('setDate', new Date(currentYear, 11, 31));

        console.log('✅ Test datepickers initialized successfully');

        document.getElementById('date-values').innerHTML = '<span class=\"success\">✅ Datepickers loaded successfully</span>';

    } catch (error) {
        console.error('❌ Error initializing test datepickers:', error);
        document.getElementById('date-values').innerHTML = '<span class=\"error\">❌ Error: ' + error.message + '</span>';
    }
}

function testDateValues() {
    var startVal = document.getElementById('test_start_date').value;
    var endVal = document.getElementById('test_end_date').value;

    var result = '<strong>Current Values:</strong><br>' +
                'Start Date: ' + (startVal || 'empty') + '<br>' +
                'End Date: ' + (endVal || 'empty') + '<br>' +
                '<strong>Console:</strong> Check browser console (F12) for detailed logs';

    document.getElementById('date-values').innerHTML = result;
}

// Initialize on page load
$(document).ready(function() {
    initializeTestDatePickers();
});

// Also try after a delay
setTimeout(function() {
    if (!$('#test_start_picker').hasClass('hasDatepicker')) {
        console.log('Retrying test datepicker initialization...');
        initializeTestDatePickers();
    }
}, 1000);

console.log('Date picker test page loaded');
</script>";

echo "<div class='test-section'>
<h3>Instructions</h3>
<ol>
<li><strong>Calendar Icons:</strong> Click the calendar icons next to the date fields</li>
<li><strong>Manual Entry:</strong> Type dates in MM/DD/YYYY format (e.g., 01/15/2024)</li>
<li><strong>Check Values:</strong> Click 'Check Date Values' to see current input values</li>
<li><strong>Browser Console:</strong> Open F12 → Console tab to see detailed logs</li>
</ol>
</div>";

echo "<div class='test-section'>
<h3>Troubleshooting</h3>
<ul>
<li><strong>If calendars don't open:</strong> JavaScript/jQuery issue</li>
<li><strong>If manual entry doesn't work:</strong> Input field issue</li>
<li><strong>If nothing works:</strong> Check browser console for errors</li>
<li><strong>Try different browsers:</strong> Chrome, Firefox, Edge</li>
</ul>
</div>";
?>
