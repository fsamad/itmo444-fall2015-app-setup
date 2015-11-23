<?php
session_start();
$uname = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$allowed =  array('gif','png' ,'jpg');
$filename = $_FILES['file']['name'];
date_default_timezone_set('America/Chicago');
$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['file']['name']);
echo '<pre>';
if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n";
} else {
    echo "Possible file upload attack!\n";
}
require 'vendor/autoload.php';
#use Aws\S3\S3Client;
#$client = S3Client::factory();
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$bucket = uniqid("php-fabdelsa-",false);
//createing a bucket
$result = $s3->createBucket([
    'ACL' => 'public-read',
    'Bucket' => $bucket
]);
//wait until bucket exists
$s3->waitUntil('BucketExists',[
        'Bucket' => $bucket
]);
//uploading a file
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
   'Key' => $bucket,
   'SourceFile' => $uploadfile
]);
$url = $result['ObjectURL'];
echo $url;
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$result = $rds->describeDBInstances([
    'DBInstanceIdentifier' => 'fabdelsa-mp1'
]);
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
//echo "begin database";
$link = mysqli_connect($endpoint,"fabdelsa","fabdelsa","farah",3306) or die("Error " . mysqli_error($link));
/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
/* Prepared statement, stage 1: prepare */
if (!($stmt = $link->prepare("INSERT INTO users(name,email,phone,file,raws3url,finisheds3url,state,datetime) VALUES (?,?,?,?,?,?,?,?)"))){
 echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}
$uname = $_POST['name'];
$email = $_POST['email'];
$_SESSION["email"] = $email;
$phone = $_POST['phone'];
$s3url = $url; //  $result['ObjectURL']; from above
$filename = basename($_FILES['file']['name']);
$fs3url = "none";
$state =0;
$date = date("d M Y - h:i:s A");
$stmt->bind_param("ssssssis",$uname,$email,$phone,$filename,$s3url,$fs3url,$status,$date);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
printf("%d Row inserted.\n", $stmt->affected_rows);
/* explicit close recommended */
$stmt->close();
$link->real_query("SELECT * FROM users");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {
    echo $row['ID'] . " " . $row['name'] . " " . $row['email']. " " . $row['phone'];
}
$link->close();
header("Location: gallery.php");
