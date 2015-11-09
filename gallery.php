<!DOCTYPE html>
<html>
<head>
<title> Gallery page <title>
<body>
<div class="container" style="border-style: solid; border-color: #003333; border-width: 25px; width:500px; margin-left:450px">

<h1 style="color: maroon" align='center'> Gallery </h1>

<?php
session_start();
$email = $_POST["email"];
echo $email;
require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
'version' => 'latest',
'region' => 'us-east-1'
]);

$result = $rds->describeDBInstances(array(
'DBInstanceIdentifier' => 'fabdelsa-mp1'
));

$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
echo"============\n". $endpoint . "===============";
   
