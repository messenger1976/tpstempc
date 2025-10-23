/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    var amount = $(".amountformat").val();

    $(".amountformat").val(function(index, value) {

        var x = value.replace(/[^0-9\.]/g, '');
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
        return  parts.join(".");


    });

    $(".amountformat").on("keypress keyup blur", function(event) {
        //this.value = this.value.replace(/[^0-9\.]/g,'');


        var x = $(this).val().replace(/[^0-9\.]/g, '');
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        $(this).val(parts.join("."));

        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
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
