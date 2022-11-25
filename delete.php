<?php declare(strict_types=1);
require 'includes' . DIRECTORY_SEPARATOR . 'functions.php';

$json = file_get_contents('php://input');
$jsonArr = json_decode($json, true);

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' || empty($jsonArr['img_name'])) {
    returnResponse('Please send DELETE request with JSON body {"img_name":"<THE_IMAGE_TO_DELETE>"}', 400);
}

if (!file_exists('uploads' . DIRECTORY_SEPARATOR . $jsonArr['img_name'])) {
    returnResponse('File not found', 404);
}

unlink('uploads' . DIRECTORY_SEPARATOR . $jsonArr['img_name']);

if (file_exists('thumbnails' . DIRECTORY_SEPARATOR . $jsonArr['img_name'])) {
    unlink('thumbnails' . DIRECTORY_SEPARATOR . $jsonArr['img_name']);
}

returnResponse('', 204);