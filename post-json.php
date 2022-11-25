<?php declare(strict_types=1);
require 'includes' . DIRECTORY_SEPARATOR . 'functions.php';

$json = file_get_contents('php://input');
$jsonArr = json_decode($json, true);

if (json_last_error() === JSON_ERROR_NONE) {
    /*
    * START VALIDATING MIME TYPE
    */
    $imgData = base64_decode($jsonArr['base64']);
    $f = finfo_open();
    $mimeType = finfo_buffer($f, $imgData, FILEINFO_MIME_TYPE);

    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
        returnJsonResponse('Only files with image/jpeg and image/png MIME types are supported', 400);
    }
    /*
    * END VALIDATING MIME TYPE
    */


    /**
     * START CREATING MAIN FILE 
     */
    $filePath = 'uploads' . DIRECTORY_SEPARATOR . $jsonArr['file_name'];
    $ifp = fopen($filePath, 'wb'); // open the output file for writing
    $successCreateMainImage = fwrite($ifp, $imgData);
    fclose($ifp); // clean up the file resource
    /**
     * END CREATING MAIN FILE 
     */


    /**
     * START CREATING THUMBNAIL 
     */
    $successCreateThumb = false;
    if ($successCreateMainImage) {
        $fileType = substr($mimeType, strpos($mimeType, "/") + 1);   
        $successCreateThumb = createThumb($filePath, 'thumbnails' . DIRECTORY_SEPARATOR . $jsonArr['file_name'], $fileType);
    }
    /**
     * END CREATING THUMBNAIL 
     */

    if ($successCreateThumb) {
        returnJsonResponse('Success', 201);
    } else {
        returnJsonResponse('Error', 400);
    }
} else {
    returnJsonResponse('JSON error: ' . json_last_error_msg(), 400);
}