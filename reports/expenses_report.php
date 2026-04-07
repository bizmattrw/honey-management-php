<?php include("../includes/layout.php");
 include("../config/db.php"); ?>

<div class="container mt-4">

<h4>💸 Expenses Report</h4>

<!-- FILTERS -->
<div class="row mb-3">

    <div class="col-md-3">
        <input type="date" id="from" class="form-control">
    </div>

    <div class="col-md-3">
        <input type="date" id="to" class="form-control">
    </div>

    <div class="col-md-3">
        <select id="category" class="form-control">
            <option value="">All Categories</option>
            <option>Transport</option>
            <option>Salary</option>
            <option>Fuel</option>
            <option>Maintenance</option>
            <option>Other</option>
        </select>
    </div>

    <div class="col-md-3">
        <button id="filterBtn" class="btn btn-primary w-100">Filter</button>
    </div>

</div>

<table id="table" class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
    <th>Title</th>
    <th>Category</th>
    <th>Amount</th>
    <th>Date</th>
    <th>Description</th>
</tr>
</thead>

<tfoot>
<tr>
    <th colspan="2">Total</th>
    <th id="tAmount"></th>
    <th colspan="2"></th>
</tr>
</tfoot>

</table>

</div>
<script>
$(document).ready(function(){

let table = $('#table').DataTable({

    processing:true,
    serverSide:true,

    ajax:{
        url:'expenses_data.php',
        type:'POST',
        data:function(d){
            d.from = $('#from').val();
            d.to = $('#to').val();
            d.category = $('#category').val();
        }
    },

    columns:[
        {data:'title'},
        {data:'category'},
        {data:'amount'},
        {data:'date'},
        {data:'desc'}
    ],

    dom:'Blfrtip',
    buttons:['excel','pdf'],

    drawCallback:function(settings){
        $('#tAmount').html(settings.json.totalAmount);
    }
});

$('#filterBtn').click(()=>{
    table.ajax.reload();
});

});
</script>

<?php include("../includes/footer.php"); ?>