var interval;
$(document).ready(function() {
    startTimer();
    //CounterInit(100);
    $('#drawMode').change(function(){
        if($(this).val() == 1) {
            $('#jackpotMode').attr('checked',false);
            $('#jackpotMode').attr('disabled',false).closest('.checkbox').removeClass('disabled').show();
            
            /* min Mode*/
            $('#manualContent').hide();
            $('#percentageContent').hide();
        } else if($(this).val() == 2) {
            $('#jackpotMode').attr('checked',false);
            $('#jackpotMode').attr('disabled',true).closest('.checkbox').addClass('disabled').hide();
            $('#jackpotmodeVal').val('0');
            /* percentage Mode*/
            $('#jackpotValueContent').hide();
            $('#manualContent').hide();
            $('#percentageContent').show();
        } else if($(this).val() == 3) {
            $('#jackpotMode').attr('checked',false);
            $('#jackpotMode').attr('disabled',false).closest('.checkbox').removeClass('disabled').show();
            
            /* manual Mode*/
            $('#manualContent').show();
            $('#percentageContent').hide();
        }else if($(this).val() == 4) {
            $('#jackpotMode').attr('checked',false);
            $('#jackpotMode').attr('disabled',true).closest('.checkbox').addClass('disabled').hide();
            $('#jackpotmodeVal').val('0');
            /* percentage Mode*/
            $('#jackpotValueContent').hide();
            $('#manualContent').hide();
            $('#percentageContent').hide();
        }
    });
    
    $('#jackpotMode').change(function(){
       if($(this).is(':checked') == true) {
           $('#jackpotmodeVal').val('1');
           $('#jackpotValueContent').show();
           $('.toggle').removeClass('off');
       } else {
           $('#jackpotmodeVal').val('0');
           $('#jackpotValueContent').hide();
           $('.toggle').addClass('off');
       }
    });    
});

function refreshTable(ele) {
    $(ele).attr('disabled',true);
    //Refresh Table
    $.ajax({
        url: '/public/admin/dashboard',
        type: 'GET',
        success: function(data)
        {
            $('#totalQnt').html('0');
            $('#totalPrice').html('0');            
            for (i=1;i<=10;i++) {
                $('#PL-'+i).html('0');
                $('#jackpotPL-'+i).html('0');
                $('#quantity-'+i).html('0');
                $('#winPrice-'+i).html('0');
                $('#jackpotWinPrice-'+i).html('0');
                $('#price-'+i).html('0');
                
            }
            arrayIndex = [];
            var json = jQuery.parseJSON(data);
            if (Object.keys(json.data).length > 0) {
                $.each(json.data,function(key,val){
                    $.each(val,function(vKey,vVal) {
                        $('#'+vKey+'-'+key).html(vVal);
                        arrayIndex[key] = true;
                    });
                });
            }
            for (i=1;i<=10;i++) {
                if (typeof arrayIndex[i] == 'undefined') {
                    $('#PL-'+i).html(json.totalPrice);
                    $('#jackpotPL-'+i).html(json.totalPrice);
                }
            }            
            $('#totalQnt').html(json.totalQnt);
            $('#totalPrice').html(json.totalPrice);
            $('#timer').html(json.remainigTime);
            clearInterval(interval);
            startTimer();
            //CounterInit(json.remainigTime);
            $(ele).attr('disabled',false);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            $(ele).attr('disabled',false);
            alert('Table refresh fail.Please try again.');
        }
    });
}

function startTimer() {
    interval = setInterval(function() {
        var timer = $('#timer').html().split(':');
        //by parsing integer, I avoid all extra string processing
        var minutes = parseInt(timer[0],10);
        var seconds = parseInt(timer[1],10);
        --seconds;
        minutes = (seconds < 0) ? --minutes : minutes;
        if (minutes < 0) clearInterval(interval);
        seconds = (seconds < 0) ? 59 : seconds;
        seconds = (seconds < 10) ? '0' + seconds : seconds;
        minutes = (minutes < 10) ?  '0' + minutes : minutes;
        if (minutes + ':' + seconds == '00:00') {
            $('#timer').html('00:00');
            clearInterval(interval);
        } else {
            $('#timer').html(minutes + ':' + seconds);
        }        
    }, 1000);
}