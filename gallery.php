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
$link = mysqli_connect($endpoint,"fabdelsa","fabdelsa","farah");
if (mysqli_connect_errno()) {
printf("Connection faild: %s\n", mysqli_connect_error());
exit();
}
else {
echo "Success";
}
$link->real_query("SELECT * FROM Table WHERE email = '$email'");
$res = $link->use_result();
echo "Result set order.\n";
while ($row = $res->fetch_assoc()) {
echo "<img src =\" " . $row['raws3url'] . "\" /><img src =\"" . $row['finisheds3url'] . "\"/>";
echo $row['id'] . "email: " . $row['email'];
}
$link->close();
?>
</div>
</body>
</html>
   
