$(document).ready(function() {
    
    //Local user list
    $('#purchaseRepoTbl').DataTable({
        responsive: true,
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": [0, 1, 2, 3, 4, 5]},
        ],
        "aaSorting": [[6, "desc"]],
        "fnDrawCallback": function(oSettings, json) {
            //Hide last column Header & data. 
            $("#purchaseRepoTbl th:last-child, #purchaseRepoTbl td:last-child").hide();
        },
        'iDisplayLength':100
    });
    
    $("#date").datepicker({
        dateFormat: 'dd-mm-yy'
    });
    
});