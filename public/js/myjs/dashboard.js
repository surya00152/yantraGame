
$(document).ready(function() {
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
            $('#jackpotMode').attr('checked',false).closest('.checkbox').removeClass('disabled').show();
            
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
       } else {
           $('#jackpotmodeVal').val('0');
           $('#jackpotValueContent').hide();
       }
    });    
});

function refreshTable() {
    //Refresh Table
    $.ajax({
        url: '/public/admin/dashboard',
        type: 'GET',
        success: function(data)
        {
            var json = jQuery.parseJSON(data);
            if (Object.keys(json.data).length > 0) {
                $.each(json.data,function(key,val){
                    $.each(val,function(vKey,vVal) {
                        $('#'+vKey+'-'+key).html(vVal);
                    });
                });
            }
            $('#totalQnt').html(json.totalQnt);
            $('#totalPrice').html(json.totalPrice);
            CounterInit(json.remainigTime);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            alert('Table refresh fail.Please try again.');
        }
    });
}