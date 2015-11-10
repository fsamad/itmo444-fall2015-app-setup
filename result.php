<?php

//Starting the session 
session_start();
// In PHP versions earlier than 4.1.0, $HTTP_POST_FILES should be used instead
// of $_FILES


echo $_POST['email'];
$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['file']['name']);
echo '<pre>';
if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n";
} else {
    echo "Possible file upload attack!\n";
}
echo 'Here is some more debugging info:';
print_r($_FILES);
print "</pre>";
require 'vendor/autoload.php';
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$bucket = uniqid("php-fabdelsa-",false);
# AWS PHP SDK version 3 create bucket
$result = $s3->createBucket([
    'ACL' => 'public-read',
    'Bucket' => $bucket
]);
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
   'Key' => $uploadfile
]);  
$url = $result['ObjectURL'];
echo $url;
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$result = $rds->describeDBInstances([
    'DBInstanceIdentifier' => 'mp1-fabdelsa',
]);
$endpoint = $result['DBInstances'][0]['Endpoint']['Address']
    echo "============\n". $endpoint . "================";^M
//echo "begin database";^M
$link = mysqli_connect($endpoint,"fabdelsa","fabdelsa","farah") or die("Error " . mysqli_error($link));
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
if (!($stmt = $link->prepare("INSERT INTO Table (name, email,phone,file,s3rawurl,s3finishedurl,state,datetime) VALUES (?,?,?,?,?,?,?,?)"))) {
    echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$s3rawurl = $url; //  $result['ObjectURL']; from above
$file = basename($_FILES['file']['name']);
$s3finishedurl = "none";
$state =0;
$datetime=0;
$stmt->bind_param("ssssssii",$name,$email,$phone,$file,$s3rawurl,$s3finishedurl,$state,$datetime);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
printf("%d Row inserted.\n", $stmt->affected_rows);
/* explicit close recommended */
$stmt->close();
$link->real_query("SELECT * FROM Table");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . " " . $row['name'] . " " . $row['email']. " " . $row['phone'];
}
$link->close();
?>
