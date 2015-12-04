<!DOCTYPE html>
<html>
<head>
<title> Gallery page </title>
<body>
<div class="container" style="border-style: solid; border-color: #003333; border-width: 25px; width:500px; margin-left:450px">

<h1 style="color: maroon" align='center'> Gallery </h1>

<?php
session_start();
$email = $_POST["email"];
if (empty($_POST["email"])){
    $email = $_SESSION["email"];
}
require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
'version' => 'latest',
'region' => 'us-east-1'
]);
$result = $rds->describeDBInstances([
'DBInstanceIdentifier' => 'fabdelsa-mp1'
]);
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
$link = mysqli_connect($endpoint,"fabdelsa","fabdelsa","farah");
if (mysqli_connect_errno()) {
printf("Connection faild: %s\n", mysqli_connect_error());
exit();
}
$sql = "SELECT * FROM users WHERE email = '$email'";

$link->real_query($sql);
if ($result = $link->use_result()) {
            while ($row = $result->fetch_assoc()) {
if ($row['finisheds3url'])
{
echo "<h3> This is the raw image </h3><img src =\" " . $row['raws3url'] . "\" height='200' width='200' /> </h3>";
echo "<h3> This is the rendered image<img src =\" " . $row['finisheds3url'] . "\" height='100' width='100' /> </h3>";

}else{
echo "<img src =\" " . $row['raws3url'] . "\" height='200' width='200' />";

}
            }
            $result->close();
        }
session_destroy();
?>

</div>
</body>
</html>

