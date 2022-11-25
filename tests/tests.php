<?php declare(strict_types=1);
require '../includes' . DIRECTORY_SEPARATOR . 'functions.php';

if(empty($argv[1])) {
    die('Please provide host base path where the project is installed. Examples: localhost, localhost/myx, localhost:8000/myx, etc' . PHP_EOL);
}

$basePath = $argv[1];
$testFiles = ['file4.jpeg', 'file3.JPG', 'file1.png', 'file2.JPG', 'test.jpg'];
$hasError = false;

echo 'Started tests' . PHP_EOL;

/*
* START testing post-json.php
*/
echo 'POSTing files...' . PHP_EOL;
foreach($testFiles as $fileName) {
    echo 'POST file ' . $fileName . '... ';
    $ch = curl_init($basePath . '/post-json.php');
    # Setup request to send json via POST.
    $payload = json_encode(['file_name' => $fileName, 'base64' => imgToBase64('images' . DIRECTORY_SEPARATOR . $fileName)]);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    # Return response instead of printing.
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    # Send request.
    $result = curl_exec($ch);
    curl_close($ch);
    
    if (json_decode($result, true)['message'] === 'Success'){
        echo 'Success' . PHP_EOL;
    } else {
        echo 'Error POSTing file ' . $fileName . PHP_EOL;
        $hasError = true;
    }
}
echo 'POSTing finished' . PHP_EOL;
/*
* END testing post-json.php
*/

/*
* START testing get.php
*/
echo 'GETting the files we just posted' . PHP_EOL;
foreach($testFiles as $fileName) {
    echo 'GET file ' . $fileName . '... ';
    $ch = curl_init($basePath . '/get.php?img_name=' . $fileName);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $jsonArr = json_decode($result, true);

    if ($jsonArr['base64'] === imgToBase64('images' . DIRECTORY_SEPARATOR . $fileName)){
        echo 'verifying base64 integrity - Success' . PHP_EOL;
    } else {
        echo 'Error: base64 mismatch!';
        $hasError = true;
    }
}
echo 'GETting finished' . PHP_EOL;
/*
* END testing get.php
*/

/*
* START testing get-zone.php with url_only=1
* GET only files with  min_lat=-52.1  max_lat=53.9  min_lon=-2.5  max_lon=-1.1
* These should be only file3.JPG and file4.jpeg
*/
echo 'GETting file URLs with location min_lat=-52.1  max_lat=53.9  min_lon=-2.5  max_lon=-1.1' . PHP_EOL;

$ch = curl_init($basePath . '/get-zone.php?min_lat=-52.1&max_lat=53.9&min_lon=-2.5&max_lon=-1.1&url_only=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$jsonArr = json_decode($result, true);
print_r($jsonArr);
if (count($jsonArr) === 2 &&
    $jsonArr[0]['url'] === $basePath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'file3.JPG' &&
    $jsonArr[1]['url'] === $basePath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'file4.jpeg'){
    echo 'Success testing get-zone.php' . PHP_EOL;
} else {
    echo 'Error testing get-zone.php!';
    $hasError = true;
}
/**
 * END testing get-zone.php
 */


/**
 * START testing delete.php
 * This will naturally perform a clean up after the tests
 */
foreach($testFiles as $fileName) {
    echo 'DELETE file ' . $fileName . '... ';
    $ch = curl_init($basePath . '/delete.php');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    $json = json_encode(['img_name' => $fileName]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 204){
        echo 'Success' . PHP_EOL;
    } else {
        echo 'Error deleting file ' . $fileName . PHP_EOL;
        $hasError = true;
    }
}
/**
 * END testing delete.php
 */


if(!$hasError) {
    echo 'ALL TESTS COMPLETED SUCCESSFULLY!' . PHP_EOL;
} else {
    echo 'AN ERROR OCCURED DURING TESTS EXECUTION!' . PHP_EOL;
}

