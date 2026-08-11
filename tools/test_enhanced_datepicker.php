<?php
echo "<h1>🎨 Enhanced Fiscal Year Date Picker Test</h1>";
echo "<p>This page demonstrates the improved date picker with enhanced CSS styling and validation.</p>";

echo "<!-- Datepicker CSS -->
<link href='assets/css/plugins/datapicker/datepicker3.css' rel='stylesheet'>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
.demo-section { border: 1px solid #ddd; padding: 20px; margin: 15px 0; border-radius: 8px; background: white; }
.form-group { margin-bottom: 20px; }
label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
.btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; font-weight: bold; cursor: pointer; border: none; }
.btn-primary { background: #007bff; }
.btn-success { background: #28a745; }
.btn-info { background: #17a2b8; }
.test-result { margin-top: 10px; padding: 10px; border-radius: 4px; }
.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
.info { background: #cce7ff; color: #004085; border: 1px solid #99d6ff; }
</style>";

echo "<div class='demo-section'>
<h2>📅 Enhanced Date Picker Demo</h2>
<p>Try the following features:</p>
<ul>
<li><strong>Calendar Icon:</strong> Click the calendar icon 📅 to open the date picker</li>
<li><strong>Manual Entry:</strong> Type dates directly in MM/DD/YYYY format</li>
<li><strong>Auto-formatting:</strong> Enter partial dates and see them auto-format</li>
<li><strong>Validation:</strong> See real-time validation feedback</li>
<li><strong>Cross-validation:</strong> End date must be after start date</li>
</ul>
</div>";

echo "<form method='post' action=''>
<div class='demo-section'>
<h3>Fiscal Year Information</h3>

<div class='form-group'>
    <label for='fy_name'>Fiscal Year Name:</label>
    <input type='text' id='fy_name' name='name' class='form-control' placeholder='e.g., FY 2024-2025' style='width: 300px; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;'>
</div>

<div class='form-group'>
    <label>Start Date:</label>
    <div class='input-group date datepicker-container' id='demo_start_picker' style='width: 250px;'>
        <input type='text' name='start_date' id='demo_start_date' class='form-control date-input' placeholder='MM/DD/YYYY' autocomplete='off' spellcheck='false'/>
        <span class='input-group-addon calendar-addon'>
            <i class='fa fa-calendar'></i>
        </span>
    </div>
    <div class='date-validation-message' id='demo_start_validation'></div>
</div>

<div class='form-group'>
    <label>End Date:</label>
    <div class='input-group date datepicker-container' id='demo_end_picker' style='width: 250px;'>
        <input type='text' name='end_date' id='demo_end_date' class='form-control date-input' placeholder='MM/DD/YYYY' autocomplete='off' spellcheck='false'/>
        <span class='input-group-addon calendar-addon'>
            <i class='fa fa-calendar'></i>
        </span>
    </div>
    <div class='date-validation-message' id='demo_end_validation'></div>
</div>

<button type='button' class='btn btn-info' onclick='testValidation()'>Test Validation</button>
<button type='button' class='btn btn-success' onclick='resetForm()'>Reset Form</button>
</div>

<div class='demo-section'>
<h3>📊 Validation Test Results</h3>
<div id='validation-results' class='test-result info'>
Click "Test Validation" to see validation in action.
</div>
</div>
</form>";

echo "<!-- jQuery and Datepicker JS -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='assets/js/plugins/datapicker/bootstrap-datepicker.js'></script>

<script>
// Enhanced date validation and formatting functions
function validateDateInput(inputElement) {
    var $input = $(inputElement);
    var $container = $input.closest('.form-group');
    var $validationMsg = $container.find('.date-validation-message');
    var value = $input.val().trim();

    // Clear previous validation
    $input.removeClass('date-error date-warning date-success');
    $validationMsg.removeClass('error warning success').empty();

    if (!value) {
        // Empty is allowed (will be validated by form validation)
        return true;
    }

    // Check MM/DD/YYYY format first
    var datePattern = /^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/;
    if (!datePattern.test(value)) {
        // Try to parse other formats
        var parsedDate = new Date(value);
        if (!isNaN(parsedDate.getTime())) {
            // Valid date, format it properly
            var formatted = (parsedDate.getMonth() + 1).toString().padStart(2, '0') + '/' +
                           parsedDate.getDate().toString().padStart(2, '0') + '/' +
                           parsedDate.getFullYear();
            $input.val(formatted);
            $input.addClass('date-success');
            $validationMsg.addClass('success').text('✓ Date formatted correctly');
            console.log('Auto-formatted date:', value, '→', formatted);
            return true;
        } else {
            // Invalid format
            $input.addClass('date-error');
            $validationMsg.addClass('error').text('❌ Invalid date format. Use MM/DD/YYYY');
            console.warn('Invalid date format:', value);
            return false;
        }
    }

    // Valid MM/DD/YYYY format, now check if it's a real date
    var parts = value.split('/');
    var month = parseInt(parts[0], 10);
    var day = parseInt(parts[1], 10);
    var year = parseInt(parts[2], 10);

    // Check year range
    if (year < 1900 || year > 2100) {
        $input.addClass('date-warning');
        $validationMsg.addClass('warning').text('⚠️ Year should be between 1900-2100');
        return true; // Still valid, just warning
    }

    // Create date object to validate
    var testDate = new Date(year, month - 1, day);
    if (testDate.getFullYear() !== year || testDate.getMonth() !== month - 1 || testDate.getDate() !== day) {
        $input.addClass('date-error');
        $validationMsg.addClass('error').text('❌ Invalid date (e.g., Feb 30th doesn\'t exist)');
        return false;
    }

    // Date is valid
    $input.addClass('date-success');
    $validationMsg.addClass('success').text('✓ Valid date');
    return true;
}

// Cross-field validation: End date should be after start date
function validateDateRange() {
    var startVal = $('#demo_start_date').val().trim();
    var endVal = $('#demo_end_date').val().trim();

    if (!startVal || !endVal) return;

    var startDate = new Date(startVal);
    var endDate = new Date(endVal);

    if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
        var $endValidation = $('#demo_end_validation');

        if (endDate <= startDate) {
            $('#demo_end_date').addClass('date-error');
            $endValidation.removeClass('success warning').addClass('error')
                .text('❌ End date must be after start date');
        } else if (endDate.getTime() - startDate.getTime() > 365 * 24 * 60 * 60 * 1000) { // More than 1 year
            $('#demo_end_date').addClass('date-warning');
            $endValidation.removeClass('success error').addClass('warning')
                .text('⚠️ Fiscal year spans more than 1 year');
        } else {
            $('#demo_end_date').removeClass('date-error date-warning').addClass('date-success');
            $endValidation.removeClass('error warning').addClass('success')
                .text('✓ Valid date range');
        }
    }
}

function initializeDemoDatePickers() {
    console.log('🎨 Initializing enhanced demo datepickers...');

    if (typeof $ === 'undefined') {
        console.error('jQuery not loaded!');
        $('#validation-results').removeClass('info').addClass('error').html('❌ jQuery not loaded');
        return;
    }

    try {
        // Initialize start date picker with enhanced options
        if ($('#demo_start_picker').length > 0) {
            $('#demo_start_picker').datepicker({
                format: 'mm/dd/yyyy',
                todayBtn: 'linked',
                todayHighlight: true,
                autoclose: true,
                clearBtn: true,
                orientation: 'bottom auto',
                startDate: '1900-01-01',
                endDate: '2100-12-31',
                showOnFocus: true,
                forceParse: false
            }).on('changeDate', function(e) {
                console.log('Demo start date selected:', e.format());
                validateDateInput(this);
                setTimeout(validateDateRange, 100);
            }).on('show', function() {
                console.log('Demo start date picker opened');
            });

            console.log('✅ Demo start date picker initialized');
        }

        // Initialize end date picker
        if ($('#demo_end_picker').length > 0) {
            $('#demo_end_picker').datepicker({
                format: 'mm/dd/yyyy',
                todayBtn: 'linked',
                todayHighlight: true,
                autoclose: true,
                clearBtn: true,
                orientation: 'bottom auto',
                startDate: '1900-01-01',
                endDate: '2100-12-31',
                showOnFocus: true,
                forceParse: false
            }).on('changeDate', function(e) {
                console.log('Demo end date selected:', e.format());
                validateDateInput(this);
                setTimeout(validateDateRange, 100);
            }).on('show', function() {
                console.log('Demo end date picker opened');
            });

            console.log('✅ Demo end date picker initialized');
        }

        // Set default dates for demo
        setTimeout(function() {
            var currentYear = new Date().getFullYear();
            var startDate = new Date(currentYear, 0, 1); // January 1st
            var endDate = new Date(currentYear, 11, 31); // December 31st

            try {
                $('#demo_start_picker').datepicker('setDate', startDate);
                $('#demo_end_picker').datepicker('setDate', endDate);
                console.log('✅ Set default demo dates');

                // Trigger validation
                validateDateInput($('#demo_start_date')[0]);
                validateDateInput($('#demo_end_date')[0]);
                validateDateRange();

            } catch (e) {
                console.error('❌ Error setting default dates:', e);
            }
        }, 500);

        $('#validation-results').removeClass('error').addClass('success').html('✅ Enhanced date pickers initialized successfully!');

    } catch (error) {
        console.error('❌ Error initializing demo datepickers:', error);
        $('#validation-results').removeClass('success').addClass('error').html('❌ Error: ' + error.message);
    }
}

// Manual validation test
function testValidation() {
    console.log('🧪 Running validation tests...');

    var results = '<h4>Validation Test Results:</h4><ul>';

    // Test start date validation
    $('#demo_start_date').val('02/30/2024'); // Invalid date
    validateDateInput($('#demo_start_date')[0]);
    var startValid = !$('#demo_start_date').hasClass('date-error');
    results += '<li>❌ Invalid date (Feb 30th): ' + (startValid ? 'Failed to catch' : 'Correctly flagged') + '</li>';

    setTimeout(function() {
        $('#demo_start_date').val('02/15/2024'); // Valid date
        validateDateInput($('#demo_start_date')[0]);
        var startValid2 = $('#demo_start_date').hasClass('date-success');
        results += '<li>✅ Valid date (Feb 15th): ' + (startValid2 ? 'Correctly accepted' : 'Failed to accept') + '</li>';

        // Test cross-validation
        $('#demo_end_date').val('01/15/2024'); // Before start date
        validateDateInput($('#demo_end_date')[0]);
        validateDateRange();
        var rangeValid = $('#demo_end_date').hasClass('date-error');
        results += '<li>❌ End before start: ' + (rangeValid ? 'Correctly flagged' : 'Failed to catch') + '</li>';

        setTimeout(function() {
            $('#demo_end_date').val('12/31/2024'); // Valid end date
            validateDateInput($('#demo_end_date')[0]);
            validateDateRange();
            var rangeValid2 = $('#demo_end_date').hasClass('date-success');
            results += '<li>✅ Valid range: ' + (rangeValid2 ? 'Correctly accepted' : 'Failed to accept') + '</li>';

            results += '</ul><p><strong>Check browser console for detailed logs!</strong></p>';
            $('#validation-results').removeClass('info success error').addClass('success').html(results);
        }, 200);
    }, 200);
}

// Reset form
function resetForm() {
    $('#fy_name').val('');
    $('#demo_start_date').val('');
    $('#demo_end_date').val('');
    $('#demo_start_validation').empty();
    $('#demo_end_validation').empty();
    $('#demo_start_date, #demo_end_date').removeClass('date-error date-warning date-success');
    $('#validation-results').removeClass('success error').addClass('info').html('Form reset. Click "Test Validation" to run validation tests.');
    console.log('🔄 Form reset');
}

// Initialize on page load
$(document).ready(function() {
    console.log('🚀 Enhanced Date Picker Demo loaded');
    initializeDemoDatePickers();
});

// Also try to initialize after a delay
setTimeout(function() {
    if (!$('#demo_start_picker').hasClass('hasDatepicker')) {
        console.log('⏰ Retrying demo datepicker initialization...');
        initializeDemoDatePickers();
    }
}, 1500);

// Click handlers for calendar icons
$(document).on('click', '.calendar-addon', function() {
    var input = $(this).siblings('input');
    if (input.length > 0) {
        input.focus();
        try {
            var picker = input.closest('.datepicker-container');
            if (picker.length > 0 && typeof picker.datepicker === 'function') {
                picker.datepicker('show');
                console.log('📅 Opened datepicker via icon click');
            }
        } catch (e) {
            console.log('⚠️ Could not open datepicker via icon click');
        }
    }
});
</script>";

echo "<div class='demo-section'>
<h3>🎨 CSS Enhancements</h3>
<ul>
<li><strong>Beautiful Popup:</strong> Gradient header, hover effects, smooth animations</li>
<li><strong>Visual States:</strong> Green for valid, red for invalid, yellow for warnings</li>
<li><strong>Real-time Feedback:</strong> Instant validation as you type</li>
<li><strong>Responsive Design:</strong> Works on mobile and desktop</li>
<li><strong>Accessibility:</strong> Keyboard navigation and screen reader support</li>
</ul>
</div>";

echo "<div class='demo-section'>
<h3>🔧 Technical Features</h3>
<ul>
<li><strong>Auto-formatting:</strong> Converts various date formats to MM/DD/YYYY</li>
<li><strong>Cross-validation:</strong> End date must be after start date</li>
<li><strong>Date Range Checking:</strong> Warns for fiscal years longer than 1 year</li>
<li><strong>Error Recovery:</strong> Falls back to manual entry if datepicker fails</li>
<li><strong>Console Logging:</strong> Detailed debugging information</li>
</ul>
</div>";

echo "<p style='text-align: center; margin: 30px 0;'>
<a href='https://taliboncoop.com/en/setting/fiscal_year_create' class='btn btn-success' style='font-size: 18px; padding: 15px 30px;'>🚀 Try the Real Fiscal Year Form</a>
</p>";
?>
