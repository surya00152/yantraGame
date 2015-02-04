$(document).ready(function() {
    
    //Local user list
    $('#purchaseRepoTbl').DataTable({
        responsive: true
    });
    
    $("#date").datepicker({
        dateFormat: 'dd-mm-yy'
    });
    
});