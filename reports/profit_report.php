<?php include("../includes/layout.php"); ?>

<div class="container mt-4">

<h4>📊 Profit Report</h4>

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

<table id="table" class="table table-bordered">

<thead class="table-dark">
<tr>
    <th>Revenue</th>
    <th>Expenses</th>
    <th>Profit</th>
</tr>
</thead>

<tfoot>
<tr>
    <th id="tRevenue"></th>
    <th id="tExpense"></th>
    <th id="tProfit"></th>
</tr>
</tfoot>

</table>

</div>

<script>
  $(document).ready(function(){

let table = $('#table').DataTable({

    processing: true,
    serverSide: true,

    ajax: {
        url: "profit_data.php", // CHECK PATH
        type: "POST",
        data: function(d){
            d.from = $('#from').val();
            d.to = $('#to').val();
        }
    },

    columns: [
        { data: 'revenue' },
        { data: 'expense' },
        { data: 'profit' }
    ],

    drawCallback: function(settings){
        let json = settings.json;

        $('#tRevenue').html(json.totalRevenue);
        $('#tExpense').html(json.totalExpense);
        $('#tProfit').html(json.totalProfit);
    }
});

/* FILTER */
$('#filterBtn').click(function(){
    table.ajax.reload();
});

});
</script>

<?php include("../includes/footer.php"); ?>