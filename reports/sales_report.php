<?php include("../includes/layout.php"); ?>

<div class="container mt-4">

<div class="card shadow">
<div class="card-header bg-dark text-white">
    <h4>📊 Sales Report</h4>
</div>

<div class="card-body">
<div class="row mb-3">
    <div class="col-md-3">
        <label>From</label>
        <input type="date" id="from" class="form-control">
    </div>

    <div class="col-md-3">
        <label>To</label>
        <input type="date" id="to" class="form-control">
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button id="filterBtn" class="btn btn-primary w-100">
            <i class="fas fa-filter"></i> Filter
        </button>
    </div>
</div>
<table id="salesTable" class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
<th>ID</th>
<th>Customer</th>
<th>Total</th>
<th>Paid</th>
<th>Balance</th>
<th>Status</th>
<th>Date</th>
</tr>
</thead>

<tfoot>
<tr>
<th colspan="2">TOTAL</th>
<th id="totalSales"></th>
<th id="totalPaid"></th>
<th id="totalBalance"></th>
<th colspan="2"></th>
</tr>
</tfoot>

</table>

</div>
</div>

</div>

<script>
$(document).ready(function(){

let table = $('#salesTable').DataTable({

    processing: true,
    serverSide: true,

    ajax: {
        url: 'sales_data.php',
        type: 'POST',
        data: function(d){
            d.from = $('#from').val();
            d.to   = $('#to').val();
        }
    },

    columns: [
        { data: 'SaleID' },
        { data: 'Name' },
        { data: 'TotalAmount' },
        { data: 'PaidAmount' },
        { data: 'Balance' },
        { data: 'PaymentStatus' },
        { data: 'SaleDate' }
    ],

    dom: 'Blfrtip',

    lengthMenu: [
        [10,25,50,100,500,1000],
        [10,25,50,100,500,1000]
    ],

    buttons: ['excel','pdf'],

    drawCallback: function(settings){
        let json = settings.json;

        if(json){
            $('#totalSales').html(json.totalSales);
            $('#totalPaid').html(json.totalPaid);
            $('#totalBalance').html(json.totalBalance);
        }
    }

});

// FILTER BUTTON
$('#filterBtn').click(function(){
    table.ajax.reload();
});

});
</script>

<?php include("../includes/footer.php"); ?>