$(document).ready(function() {
    
    //Local user list
    $('#daywiseRepoTbl').DataTable({
        responsive: true
    });
    
    $("#startDate,#endDate").datepicker({
        dateFormat: 'dd-mm-yy'
    });
    
});