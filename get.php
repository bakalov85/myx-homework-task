<?php declare(strict_types=1);
require 'includes' . DIRECTORY_SEPARATOR . 'functions.php';

if(!empty($_GET['img_name']) && file_exists('uploads' . DIRECTORY_SEPARATOR . $_GET['img_name'])) {
    $imgName = $_GET['img_name'];
    $result = [
        'img_name' => $imgName,
    ];

    // you can request a smaller thumbnail image or the original big image
    if(!empty($_GET['thumb'])) {
        $result['base64_thumb'] = imgToBase64('thumbnails' . DIRECTORY_SEPARATOR . $imgName);
    } else {
        $result['base64'] = imgToBase64('uploads' . DIRECTORY_SEPARATOR . $imgName);
    }
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
} else {
    returnJsonResponse('File not found', 404);
}