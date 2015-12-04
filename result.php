<?php
session_start();
require 'vendor/autoload.php';
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
if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n";
} else {
    echo "Possible file upload attack!\n";
}
$image = @file_get_contents($uploadfile);
echo "got image contents";
if($image) {
    $im = new Imagick();
    echo $im;
    $im->readImageBlob($image);
    $im->setImageFormat("png24");
    $geo=$im->getImageGeometry();
    $width=$geo['width'];
    $height=$geo['height'];
        echo $height. $width;
    if($width > $height)
    {
        $scale = ($width > 200) ? 200/$width : 1;
    }
    else
    {
        $scale = ($height > 200) ? 200/$height : 1;
    }
    $newWidth = $scale*$width;
    $newHeight = $scale*$height;
 echo $newWidth.$newHeight;
    $im->setImageCompressionQuality(85);
    $im->resizeImage($newWidth,$newHeight,Imagick::FILTER_LANCZOS,1.1);
}
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

unlink($uploadfile);
$im->writeImage($uploadfile);
//uploading the thumbnail image
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
   'Key' => $bucket,
   'SourceFile' => $uploadfile
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

echo "done";
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
echo "2222";
#subscribe options
if ($subscribe == "option1" || $subscribe == "option2"){
$subs = 1;
echo "if";
if ($subscribe == "option1"){;
$result = $sns-> subscribe([
        'Endpoint'=> $phone,
        'Protocol'=> 'sms',
        'TopicArn'=> 'arn:aws:sns:us-east-1:697950492524:mp2-1'
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
echo "3333333";
$response = $sns->publish([
'TopicArn'=>'arn:aws:sns:us-east-1:697950492524:mp2-1',
'Message'=>'Hello user, the image was successfuly added to your gallery'
]);
$link->close();
header("Location: gallery.php");

?>

