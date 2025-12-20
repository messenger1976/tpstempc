/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    $(".amountformat").on("keypress", function(event) {
        var currentVal = $(this).val();
        var cursorPos = this.selectionStart || 0;
        
        // Allow arrow keys, backspace, delete, tab, escape, enter
        if (event.which >= 37 && event.which <= 40 || event.which == 8 || event.which == 46 || event.which == 9 || event.which == 27 || event.which == 13) {
            return true;
        }
        
        // Allow minus sign only at the beginning
        if (event.which == 45) {
            if (cursorPos !== 0 || currentVal.indexOf('-') === 0) {
                event.preventDefault();
                return false;
            }
            return true;
        }
        
        // Allow decimal point only once
        if (event.which == 46) {
            if (currentVal.indexOf('.') != -1) {
                event.preventDefault();
                return false;
            }
            return true;
        }
        
        // Allow digits (0-9)
        if (event.which >= 48 && event.which <= 57) {
            return true;
        }
        
        // Block all other keys
        event.preventDefault();
        return false;
    });

    $(".amountformat").on("keyup blur", function(event) {
        // Format the value while preserving negative sign
        var currentVal = $(this).val();
        var isNegative = currentVal.indexOf('-') === 0;
        var x = currentVal.replace(/[^0-9\.]/g, '');
        
        if (x === '' || x === '.') {
            $(this).val('');
            return;
        }
        
        var parts = x.toString().split(".");
        // Only format the integer part (before decimal)
        if (parts[0] !== '') {
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        
        var formatted = (isNegative ? '-' : '') + parts.join(".");
        $(this).val(formatted);
    });


    /* $('.amountformat').keyup(function(event) {
     
     // skip for arrow keys
     if(event.which >= 37 && event.which <= 40){
     event.preventDefault();
     }
     
     $(this).val(function(index, value) {
     return value
     .replace(/\D/g, '')
     .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
     ;
     });
     });*/
});
