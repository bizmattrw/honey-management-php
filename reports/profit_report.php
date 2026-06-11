<?php include("../includes/layout.php"); ?>

<div class="container mt-4">

<h4>📊 Financial Dashboard</h4>

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
    <div class="col-md-3">
        <button onclick="printReport()" class="btn btn-success w-100">Print</button>
    </div>
</div>

<!-- SUMMARY CARDS -->
<div class="row text-center mb-4">
    <div class="col-md-2"><div class="card p-3 bg-success text-white">Revenue<br><span id="cRevenue"></span></div></div>
    <div class="col-md-2"><div class="card p-3 bg-danger text-white">Expenses<br><span id="cExpense"></span></div></div>
    <div class="col-md-2"><div class="card p-3 bg-warning text-dark">Purchases<br><span id="cPurchases"></span></div></div>
    <div class="col-md-2"><div class="card p-3 bg-info text-white">VAT<br><span id="cVAT"></span></div></div>
    <div class="col-md-2"><div class="card p-3 bg-secondary text-white">Tax<br><span id="cTax"></span></div></div>
    <div class="col-md-2"><div class="card p-3 bg-dark text-white">Profit<br><span id="cProfit"></span></div></div>
</div>

<!-- LINE CHART -->
<canvas id="trendChart" height="100"></canvas>

<!-- BAR CHART -->
<canvas id="summaryChart" height="100" class="mt-4"></canvas>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let trendChart, summaryChart;

function loadDashboard(){

    $.post("profit_data.php", {
        from: $('#from').val(),
        to: $('#to').val()
    }, function(res){

        let d = res.summary;

        // CARDS
        $('#cRevenue').text(d.revenue);
        $('#cExpense').text(d.expense);
        $('#cPurchases').text(d.purchases);
        $('#cVAT').text(d.vat);
        $('#cTax').text(d.tax);
        $('#cProfit').text(d.profit);

        // =========================
        // BAR CHART
        // =========================
        if(summaryChart) summaryChart.destroy();

        summaryChart = new Chart(document.getElementById('summaryChart'), {
            type: 'bar',
            data: {
                labels: ['Revenue','Expenses','Purchases','VAT','Tax','Profit'],
                datasets: [{
                    data: [
                        d.revenue,
                        d.expense,
                        d.purchases,
                        d.vat,
                        d.tax,
                        d.profit
                    ]
                }]
            }
        });

        // =========================
        // LINE CHART (MONTHLY TREND)
        // =========================
        if(trendChart) trendChart.destroy();

        trendChart = new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: res.trend.labels,
                datasets: [{
                    label: "Profit Trend",
                    data: res.trend.data
                }]
            }
        });

    }, 'json');
}

$('#filterBtn').click(loadDashboard);
loadDashboard();

// PRINT REPORT
function printReport(){
    window.print();
}
</script>

<?php include("../includes/footer.php"); ?>