<?php
/* Local Database*/

$servername = "pkkhospital.mysql.database.azure.com";
$username = "mpaikdqxza";
$password = "P@ssword1234";
$dbname = "clinic_db";


// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



?> 
