<?php

require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$result = $rds->createDBInstance([
    'AllocatedStorage' => 10,
    'DBInstanceClass' => 'db.t1.micro', // REQUIRED
    'DBInstanceIdentifier' => 'mp1-fabdelsa', // REQUIRED
    'DBName' => 'users',
    'Engine' => 'MySQL', // REQUIRED
    'EngineVersion' => '5.5.41',
  'MasterUserPassword' => 'fabdelsa',
    'MasterUsername' => 'fabdelsa',
    'PubliclyAccessible' => true,
]);
print "Create RDS DB results: \n";
$result = $rds->waitUntil('DBInstanceAvailable',['DBInstanceIdentifier' => 'mp1-fabdelsa',
]);
// table creation 
$result = $rds->describeDBInstances([
    'DBInstanceIdentifier' => 'mp1-fabdelsa',
]);
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
print "============\n". $endpoint . "================\n";
$link = mysqli_connect($endpoint,"fabdelsa","fabdelsa","3306") or die("Error " . mysqli_error($link)); 
echo "Here is the result: " . $link;
$sql = "CREATE TABLE User 
(
ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(20),
useremail VARCHAR(20),
telephone VARCHAR(20), 
raws3url VARCHAR(256),
finisheds3url VARCHAR(256),
filename VARCHAR(256),
state TINYINT(3),
date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
)";
$con->query($sql);
?>
