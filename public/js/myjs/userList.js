$(document).ready(function() {
    
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