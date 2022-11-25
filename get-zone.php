<?php declare(strict_types=1);
require 'includes' . DIRECTORY_SEPARATOR . 'functions.php';

if(!empty($_GET['min_lat']) && !empty($_GET['max_lat']) && !empty($_GET['min_lon']) && !empty($_GET['max_lon'])) {
    $files = scandir('uploads');
    $result = [];
    
    for($i = 2; $i < count($files); $i++) {
        $filePath = 'uploads' . DIRECTORY_SEPARATOR . $files[$i];
        
        // skip files that are not jpg or png
        $mimeType = mime_content_type($filePath);
        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            continue;
        }

        $imgLocation = getImageLocation($filePath);
        
        if($imgLocation) {
            if($imgLocation['lat'] >= $_GET['min_lat'] && $imgLocation['lat'] <= $_GET['max_lat'] &&
            $imgLocation['lon'] >= $_GET['min_lon'] && $imgLocation['lon'] <= $_GET['max_lon']) {
                $resultData = [
                    'img_name' => $files[$i],
                    'lat' => $imgLocation['lat'],
                    'lon' => $imgLocation['lon'],
                ];

                if(!empty($_GET['url_only'])) {
                    $url = dirname($_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $files[$i];
                    $resultData['url'] = $url;
                } else {
                    $resultData['base64'] = imgToBase64($filePath);
                }

                $result[] = $resultData;
            }
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
} else {
    returnJsonResponse('Insufficient parameters', 400);
}