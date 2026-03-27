<?php
include("../config/db.php");

$from=$_POST['from']??'';
$to=$_POST['to']??'';

$where="WHERE 1=1";
if($from && $to) $where.=" AND ProcessingDate BETWEEN '$from' AND '$to'";

$q="SELECT * FROM processingbatch $where";
$total=count($conn->query($q)->fetchAll());

$q.=" LIMIT ".$_POST['start'].",".$_POST['length'];
$data=$conn->query($q)->fetchAll();

$res=[];
$tInput=0;$tOutput=0;$tLoss=0;

foreach($data as $r){
    $loss=$r['InputQuantityKg']-$r['OutputQuantityKg'];

    $tInput+=$r['InputQuantityKg'];
    $tOutput+=$r['OutputQuantityKg'];
    $tLoss+=$loss;

    $res[]=[
        "Input"=>$r['InputQuantityKg'],
        "Output"=>$r['OutputQuantityKg'],
        "Loss"=>$loss,
        "Date"=>$r['ProcessingDate']
    ];
}

echo json_encode([
    "draw"=>intval($_POST['draw']),
    "recordsTotal"=>$total,
    "recordsFiltered"=>$total,
    "data"=>$res,
    "totalInput"=>$tInput,
    "totalOutput"=>$tOutput,
    "totalLoss"=>$tLoss
]);