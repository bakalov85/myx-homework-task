<?php declare(strict_types=1);

function imgToBase64(string $fileName): string
{
    $data = file_get_contents($fileName);
    $base64 = '' . base64_encode($data);

    return $base64;
}

/**
 * @param string $fileType Must be 'jpeg' or 'png'
 */
function createThumb(string $sourceFilePath, string $destImagePath, string $fileType, int $thumbWidth = 256): bool
{
    // call imagecreatefromjpeg or imagecreatefrompng with parameter $sourceFilePath
    $sourceImage = call_user_func('imagecreatefrom' . $fileType, $sourceFilePath);
    $orgWidth = imagesx($sourceImage);
    $orgHeight = imagesy($sourceImage);
    $thumbHeight = (int)floor($orgHeight * ($thumbWidth / $orgWidth));
    $destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
    imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $orgWidth, $orgHeight);
    // call imagejpeg or imagepng with parameters $destImage, $destImagePath
    $success = call_user_func('image' . $fileType, $destImage, $destImagePath);
    imagedestroy($sourceImage);
    imagedestroy($destImage);

    return $success;
}

/**
 * getImageLocation
 * Returns an array of latitude and longitude from the Image file
 * @param $image file path
 * @return multitype:array|boolean
 */
function getImageLocation($image): array|false
{
    $exif = @exif_read_data($image, null, true);
    if($exif && isset($exif['GPS'])){
        $GPSLatitudeRef = $exif['GPS']['GPSLatitudeRef'];
        $GPSLatitude    = $exif['GPS']['GPSLatitude'];
        $GPSLongitudeRef= $exif['GPS']['GPSLongitudeRef'];
        $GPSLongitude   = $exif['GPS']['GPSLongitude'];
        
        $lat_degrees = count($GPSLatitude) > 0 ? gps2Num($GPSLatitude[0]) : 0;
        $lat_minutes = count($GPSLatitude) > 1 ? gps2Num($GPSLatitude[1]) : 0;
        $lat_seconds = count($GPSLatitude) > 2 ? gps2Num($GPSLatitude[2]) : 0;
        
        $lon_degrees = count($GPSLongitude) > 0 ? gps2Num($GPSLongitude[0]) : 0;
        $lon_minutes = count($GPSLongitude) > 1 ? gps2Num($GPSLongitude[1]) : 0;
        $lon_seconds = count($GPSLongitude) > 2 ? gps2Num($GPSLongitude[2]) : 0;
        
        $lat_direction = ($GPSLatitudeRef == 'W' or $GPSLatitudeRef == 'S') ? -1 : 1;
        $lon_direction = ($GPSLongitudeRef == 'W' or $GPSLongitudeRef == 'S') ? -1 : 1;
        
        $latitude = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60*60)));
        $longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60*60)));

        return array('lat'=>$latitude, 'lon'=>$longitude);
    }else{
        return false;
    }
}

function gps2Num($coordPart)
{
    $parts = explode('/', $coordPart);
    if(count($parts) <= 0)
    return 0;
    if(count($parts) == 1)
    return $parts[0];
    return floatval($parts[0]) / floatval($parts[1]);
}

function returnResponse(string $message, int $code): void
{
    echo $message . PHP_EOL;
    http_response_code($code);
    exit;
}

function returnJsonResponse(string $message, int $code): void
{
    $return = ['message' => $message];
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($return);
    exit;
}