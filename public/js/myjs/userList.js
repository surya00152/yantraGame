$(document).ready(function() {
    
    $("#createAgentFrm").validate({
        submitHandler: function(form) {
            return true;
        },
        rules: {
            phoneNo: {number:true,
                required:true,
                minlength: 6,
                maxlength: 10},
            password: {required: true}
        },
        messages: {
            phoneNo: {required: "Please Enter Agent Id.",
                number: "Agent Id Allow ony numeric value.",
                minlength: "Please enter at least 6 digits no.",
                maxlength: "Agent Id is to long."},
            password: {required: "Please Enter Password."}
        }
    });
    
    $(".confirm").button().click(function(e) {
        e.preventDefault();
        var target = $(this).attr("link");
        var title = 'Change User Account status';
        var content = 'Are you sure want to change account status?';

        $('<div>' + content + '</div>'). dialog({
            draggable: false,
            modal: true,
            resizable: false,
            width: 'auto',
            title: title,
            buttons: {
                "Confirm": function() {
                    window.location.href = target;
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });

    });
  
    $('[data-toggle=popover]').popover({
        html: true,
        trigger: 'manual'
    }).click(function(e) {
        $('[data-toggle=popover]').not(this).popover('hide');
        $(this).popover('toggle');
    });
    
    $(document).click(function(e) {
        if (!$(e.target).is('[data-toggle=popover], .popover-title, .popover-content')) {
            $('[data-toggle=popover]').popover('hide');
        }
    });
    
    $('.tool-tip').tooltip({
        selector: "[data-toggle=tooltip]",
        container: "body"
    });
    
    //Local user list
    $('#localUserTbl').DataTable({
        responsive: true
    });
    
    //Agent user list
    $('#agentUserTbl').DataTable({
        responsive: true
    });
});

function changeCredit(userId) {
    $('#chageUserCredit #userId').val(userId);
}

function changePassword(userId) {
    $('#changeUserPassword #userId').val(userId);
}