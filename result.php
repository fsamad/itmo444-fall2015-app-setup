<?php
session_start();
$uname = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$subscribe = $_POST['subscribe'];
echo $subscribe;
$subs = 0;
$allowed =  array('gif','png' ,'jpg');
$filename = $_FILES['file']['name'];
date_default_timezone_set('America/Chicago');

$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['file']['name']);

#the upload thumb 
$uploadthumb = '/tmp/thumb/';
$uploadfilethumb = $uploadthumb . basename($_FILES['file']['name']);


echo '<pre>';
if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile) && move_uploaded_file($_FILES['file']['tmp_name'], $uploadfilethumb)) {
    echo "File is valid, and was successfully uploaded.\n";
} else {
    echo "Possible file upload attack!\n";
}
require 'vendor/autoload.php';

var_dump($filename);
$imagick = new Imagick(realpath($uploaddir));
$imagick -> thumbnailImage(100, 100, true, true);
$imagick -> writeImage($uploadthumb);

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

//uploading the thumbnail image
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
   'Key' => $bucket,
   'SourceFile' => $uploadthumb
]);
$thumburl = $result['ObjectURL'];
echo $thumburl;

//creating the rds client
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
if (!($stmt = $link->prepare("INSERT INTO users(name,email,phone,file,raws3url,finisheds3url,state,datetime,subscribe) VALUES (?,?,?,?,?,?,?,?,?)"))){
 echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}
$uname = $_POST['name'];
$email = $_POST['email'];
$_SESSION["email"] = $email;
$phone = $_POST['phone'];
$s3url = $url; //  $result['ObjectURL']; from above
$filename = basename($_FILES['file']['name']);
$fs3url = $thumburl;
$state =0;
$date = date("d M Y - h:i:s A");
$sns = new Aws\Sns\SnsClient([
'version' => 'latest',
'region' => 'us-east-1'
]);

#subscribe options
if ($subscribe == "option1" || $subscribe == "option2"){
$subc = 1;
if ($subscribe == "option1"){
$result = $sns->subscribe([
'Endpoint'=>$phone,
'Protocol'=> 'sms',
'TopicArn'=> 'arn:aws:sns:us-east-1:697950492524:mp2'
]);
}
}

$stmt->bind_param("ssssssisi",$uname,$email,$phone,$filename,$s3url,$fs3url,$status,$date,$subs);
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

$response = $sns->publish([
'TopicArn'=>'arn:aws:sns:us-east-1:697950492524:mp2',
'Message'=>'Hello user, the image was successfuly added to your gallery'
]);

$link->close();
header("Location: gallery.php");

?>
