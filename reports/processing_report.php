<?php include("../includes/layout.php"); ?>

<div class="container mt-4">
<h4>🏭 Processing Report</h4>

<input type="date" id="from">
<input type="date" id="to">
<button id="filterBtn" class="btn btn-primary">Filter</button>

<table id="table" class="table table-bordered">
<thead>
<tr>
<th>Input</th>
<th>Output</th>
<th>Loss</th>
<th>Date</th>
</tr>
</thead>

<tfoot>
<tr>
<th id="tInput"></th>
<th id="tOutput"></th>
<th id="tLoss"></th>
<th></th>
</tr>
</tfoot>
</table>
</div>

<script>
let table = $('#table').DataTable({
    processing:true,
    serverSide:true,
    ajax:{
        url:'processing_data.php',
        type:'POST',
        data: d => {
            d.from = $('#from').val();
            d.to = $('#to').val();
        }
    },
    columns:[
        {data:'Input'},
        {data:'Output'},
        {data:'Loss'},
        {data:'Date'}
    ],
    dom:'Blfrtip',
    buttons:['excel','pdf'],
    drawCallback:function(settings){
        let j = settings.json;
        $('#tInput').html(j.totalInput);
        $('#tOutput').html(j.totalOutput);
        $('#tLoss').html(j.totalLoss);
    }
});

$('#filterBtn').click(()=>table.ajax.reload());
</script>

<?php include("../includes/footer.php"); ?>