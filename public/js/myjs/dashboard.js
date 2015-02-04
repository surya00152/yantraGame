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
    
    $('.flipTimer').flipTimer({ 
//        direction: 'down', 
//        //initDate: 'January 5, 2015 08:00:30',
//        date: 'January 5, 2015 08:30:30',
        seconds: true,
        minutes: true,
        hours: false,
        days: false,
        date: '08:30:30',
        initDate: '08:30:30',
        direction: 'down',
        callback: function() { 
                alert('times up!'); 
            },
        digitTemplate: '' +
        '<div class="digit">' +
        '  <div class="digit-top">' +
        '    <span class="digit-wrap"></span>' +
        '  </div>' +
        '  <div class="shadow-top"></div>' +
        '  <div class="digit-bottom">' +
        '    <span class="digit-wrap"></span>' +
        '  </div>' +
        '  <div class="shadow-bottom"></div>' +
        '</div>'
    });
    
});