<?php
require '/var/www/html/vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$result = $rds->describeDBInstances([
    'DBInstanceIdentifier' => 'fabdelsa-mp1',
]);
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
print "============\n". $endpoint . "================\n";
$link = mysqli_connect($endpoint,"fabdelsa","fabdelsa","farah") or die("Error " . mysqli_error($link));
$sql = "CREATE TABLE users 
(
ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(20),
email VARCHAR(20),
phone VARCHAR(20), 
file VARCHAR(256),
raws3url VARCHAR(256),
finisheds3url VARCHAR(256),
state TINYINT(3),
subscibe TINYINT(2),
datetime VARCHAR(256)  
)";
$link->query($sql);
shell-exec("chmod 600 setup.php");

#creating the sns topic
$sns = new Aws\Sns\SnsClient([
'version' => 'latest',
'region' => 'us-east-1'
]);

$result = $sns->createTopic([
'Name' => 'mp2',
]);

#adding the topic attributes
'AttributeName' => 'DisplayName',
'AttributeValue' => 'mp2',
'TopicArn' => $result['TopicArn']
]);

header("Location: index.php");

?>
