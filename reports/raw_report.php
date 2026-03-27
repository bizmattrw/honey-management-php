<?php include("../includes/layout.php"); ?>

<div class="container mt-4">
<h4>🍯 Raw Honey Report</h4>

<!-- FILTER -->
<div class="row mb-3">
    <div class="col-md-3">
        <input type="date" id="from" class="form-control">
    </div>

    <div class="col-md-3">
        <input type="date" id="to" class="form-control">
    </div>

    <div class="col-md-3">
        <button id="filterBtn" class="btn btn-primary w-100">Filter</button>
    </div>
</div>

<!-- TABLE -->
<table id="table" class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
    <th>Supplier</th>
    <th>Phone</th>
    <th>District</th>
    <th>Sector</th>
    <th>Cell</th>
    <th>Village</th>
    <th>Quantity (Kg)</th>
    <th>Price/Kg</th>
    <th>Total Amount</th>
   
    <th>Date</th>
</tr>
</thead>

<tbody></tbody>
<tfoot>
<tr>
    <th colspan="6" class="text-end">Totals:</th>
    <th id="totalQty"></th>
    <th></th>
    <th id="totalAmount"></th>
    <th></th>
</tr>
</tfoot>
</table>
</div>

<script>
let table = $('#table').DataTable({

    processing: true,
    serverSide: true,

    ajax: {
        url: 'raw_honey_data.php',
        type: 'POST',
        data: function(d){
            d.from = $('#from').val();
            d.to = $('#to').val();
        }
    },

    columns: [
        { data: 'supplier' },
        { data: 'phone' },
        { data: 'district' },
        { data: 'sector' },
        { data: 'cell' },
        { data: 'village' },
        { data: 'qty' },
        { data: 'price' },    
        { data: 'amount' }, 
        { data: 'date' }
    ],

    dom: 'Blfrtip',
    buttons: ['excel','pdf'],

  drawCallback: function(settings){
    let json = settings.json;

    $('#totalQty').html(json.totalQty);
    $('#totalAmount').html(json.totalAmount);
}
});

$('#filterBtn').click(function(){
    table.ajax.reload();
});
</script>

<?php include("../includes/footer.php"); ?>