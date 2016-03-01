<?php

require __DIR__ . '/vendor/autoload.php';

echo("Hello from PHP / Nginx container (with Composer)<br>");

//////////////////////////////// MySQL

$servername = "172.17.0.6";
$username = "root";
$password = "topsecret";
$dbname = "sys";

echo("<h2>Testing MySQL</h2>");
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo("Connection to MySQL failed: " . $conn->connect_error);
}

echo "Connected successfully to MySQL at $servername<br><br>";

echo "Tables in DB: $dbname<br><br>";
$sysTables = $conn->query("show tables;");

foreach ($sysTables as $sysTable) {

    echo($sysTable["Tables_in_sys"] . "<br>");
}

$conn->close();

//////////////////////////////// Cassandra
echo("<h2>Testing Cassandra</h2>");
$cassNodeAddress = '172.17.0.3';
$nodes = [$cassNodeAddress];
$cassconn = new Cassandra\Connection($nodes, 'system');

try {
    $cassconn->connect();
    echo("Connected successfully to Cassandra node: $cassNodeAddress<br>");
} catch (Cassandra\Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
    exit;//if connect failed it may be good idea not to continue
}


try {
    echo("Waiting for Cassandra to query...<br>");
    $response = $cassconn->querySync('SELECT keyspace_name, table_name FROM system_schema.columns;');
    $casrows = $response->fetchAll();
    $viewResults = array();
    foreach ($casrows as $casrow) {
        //var_dump($casrow);
        $keyspaceName = $casrow["keyspace_name"];
        $tableName = $casrow["table_name"];
        if (isset($viewResults[$keyspaceName]) === false) {
            $viewResults[$keyspaceName] = array($tableName);
        }
        else
        {
            if(!in_array($tableName, $viewResults[$keyspaceName])){
                array_push($viewResults[$keyspaceName], $tableName);
            }

        }
    }

    foreach($viewResults as $keyspace)
    {
        echo("<b>$keyspace[0]</b><br>");
        foreach($keyspace as $table)
        {
            echo("<ul>");
            echo("<li>$table </li>");
            echo("</ul>");
        }
    }
    echo("Done querying Cassandra.");
} catch (Cassandra\Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
    exit;
}

