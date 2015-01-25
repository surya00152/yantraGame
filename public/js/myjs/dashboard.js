$(document).ready(function() {
    $('#drawMode').change(function(){
        if($(this).val() == 1) {
            $('#jackpotMode').attr('disabled',false).closest('.checkbox').removeClass('disabled');
            /* min Mode*/
            $('#manualContent').hide();
            $('#percentageContent').hide();
        } else if($(this).val() == 2) {
            $('#jackpotMode').attr('disabled',true).closest('.checkbox').addClass('disabled');
            /* percentage Mode*/
            $('#manualContent').hide();
            $('#percentageContent').show();
        } else if($(this).val() == 3) {
            $('#jackpotMode').attr('disabled',false).closest('.checkbox').removeClass('disabled');
            /* manual Mode*/
            $('#manualContent').show();
            $('#percentageContent').hide();
        }
    });
    
    $('#jackpotMode').change(function(){
       if($(this).is(':checked') == true) {
           $('#jackpotValueContent').show();
       } else {
           $('#jackpotValueContent').hide();
       }
    });
});