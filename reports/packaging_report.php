<?php include("../includes/layout.php"); ?>

<div class="container mt-4">
<h4>📦 Packaging Report</h4>

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

<table id="table" class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
    <th>Product</th>
    <th>Size</th>
    <th>Qty Produced</th>
    <th>Processed Used</th>
    <th>Date</th>
</tr>
</thead>

<tbody></tbody>

<tfoot>
<tr>
    <th></th>
    <th>Total</th>
    <th id="tQty"></th>
    <th id="tUsed"></th>
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
        url: 'packaging_data.php',
        type: 'POST',
        data: function(d){
            d.from = $('#from').val();
            d.to = $('#to').val();
        }
    },

    columns: [
        { data: 'Product' },
        { data: 'Size' },   // ✅ NEW
        { data: 'Qty' },
        { data: 'Used' },
        { data: 'Date' }
    ],

    dom: 'Blfrtip',

    buttons:[
{
 extend:'excel',
 customize:function(xlsx){
  let sheet=xlsx.xl.worksheets['sheet1.xml'];
  $('sheetData',sheet).append(
   `<row>
     <c t="inlineStr"><is><t>Total</t></is></c>
     <c></c>
     <c><v>${$('#tQty').text()}</v></c>
   </row>`
  );
 }
},
{
 extend:'pdf',
 customize:function(doc){
  doc.content[1].table.body.push([
    'TOTAL','',
    $('#tQty').text()
  ]);
 }
}
],

    drawCallback: function(settings){

        let json = settings.json;

        $('#tQty').html(json.totalQty);
        $('#tUsed').html(json.totalUsed);
    }
});

$('#filterBtn').click(function(){
    table.ajax.reload();
});
</script>

<?php include("../includes/footer.php"); ?>