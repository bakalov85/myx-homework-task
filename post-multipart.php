<?php declare(strict_types=1);
require 'includes' . DIRECTORY_SEPARATOR . 'functions.php';

// Content-Type multipart/form-data
if (!empty($_FILES['image'])) {
    /*
    * START VALIDATING MIME TYPE
    */
    $mimeType = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
        returnResponse('Only files with image/jpeg and image/png MIME types are supported', 400);
    }
    /*
    * END VALIDATING MIME TYPE
    */

    $fileName = $_FILES['image']['name'];

    /**
     * START CREATING MAIN FILE 
     */
    $successUploadImg = move_uploaded_file($_FILES['image']['tmp_name'], 'uploads' . DIRECTORY_SEPARATOR . $fileName);
    /**
     * END CREATING MAIN FILE 
     */

    
    /**
     * START CREATING THUMBNAIL 
     */
    $successCreateThumb = false;
    if ($successUploadImg) {
        $fileType = substr($mimeType, strpos($mimeType, "/") + 1);   
        $successCreateThumb = createThumb('uploads' . DIRECTORY_SEPARATOR . $fileName, 'thumbnails' . DIRECTORY_SEPARATOR . $fileName, $fileType);
    }
    /**
     * END CREATING THUMBNAIL 
     */

    if ($successCreateThumb) {
        returnResponse('Successfully uploaded file', 201);
    } else {
        returnResponse('Internal server error', 500);
    }
} else {
    returnResponse("Please send a file according to this example: curl -F 'image=@tests/images/test.jpg' localhost/post-multipart.php", 400);
}