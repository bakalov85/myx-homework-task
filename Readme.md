# MYX back-end task
## Background
The fastest way to POST/GET images is through the multipart/form-data content type. It is binary and is native to the HTTP protocol. But it is not quite RESTful - there is no JSON.
Another way is to use Base64 encoding. It is a string representation of an image. It increases the transferred file size by 33%. It can be put in a JSON and is RESTful.
About getting images, the fastest way is to provide only image URL and let the client lazy load the images.
## My solution
In my solution I showcase all of the above methods - multipart, JSON with Base64 and URLs only.
## Requirements
- PHP >= 8.1 both for the web server and for the CLI (for the tests)
- I use images from your archive and they are quite big, so you'll have to increase post_max_size in your web server's php.ini to at least 16M
## Installation
- Clone the project into your web server's document root
- Make sure the uploads/ and thumbnails/ directories are writabe by the web server's user
## Endpoints
### POST
When POST-ing an image, it is put into the uploads/ directory. A smaller thumbnail is generated and put into the thumbnails/ direcotry. The width of the thumbnail is 256px, the height varies because I preserve the aspect ratio.
- post-multipart.php - post image in multipart/form-data format<br />
    Example usage: `curl -F 'image=@images/test.jpg' localhost/post-multipart.php`
- post-json.php - post JSON with Base64 encoded image<br />
    Example usage can be found in tests/post-json.sh
### GET
- get.php - get the original image or if you send thumb=1 GET parameter, a thumbail will be returned<br />
    Example usage: `curl -X GET 'localhost/get.php?img_name=test.jpg&thumb=1'`
- get-zone.php - get images whose location fits within a geographical rectangle of min/max latitude/longitude. This endpoint can return Base64 representations of images, or URLs only, depending on the GET parameter 'url_only'.<br />
    Example usage: `curl -X GET 'localhost/get-zone.php?min_lat=-52.1&max_lat=53.9&min_lon=-2.5&max_lon=-1.1&url_only=1'`<br />
    Return data: a JSON containing the following fields: 'img_name', 'lat', 'lon', and either 'url' or 'base64'.<br />
    The above example will return images with lat/lon 52.643144722222225/-2.0572403055555553 and 53.876443888888886/-1.9046100833333333, but will NOT return an image with lat/lon 19.951848666666667/73.96449494444445<br />
    I represent coordinates as floats for easier comparison. Google Maps also does this so don't worry ;)
### DELETE
- delete.php - send DELETE request with JSON in its body, containing 'img_name' parameter<br />
    Example usage: `curl -X DELETE -d '{"img_name":"test.jpg"}' -H 'Content-Type: application/json' localhost/myx/delete.php`<br />
    Returns status 204 (No content) on success
## Testing
- To run the tests, go to the tests/ directory and run `php tests.php <BASE_PATH>`<br />
    The BASE_PATH includes the hostname and path to the project, for example localhost, localhost/myx, localhost:8000/myx, etc<br />
    Here is the successful run of the tests:<br />
    ```
    vladimir@vladimir-VirtualBox:/var/www/html/myx/tests$ php tests.php localhost/myx
    Started tests
    POSTing files...
    POST file file4.jpeg... Success
    POST file file3.JPG... Success
    POST file file1.png... Success
    POST file file2.JPG... Success
    POST file test.jpg... Success
    POSTing finished
    GETting the files we just posted
    GET file file4.jpeg... verifying base64 integrity - Success
    GET file file3.JPG... verifying base64 integrity - Success
    GET file file1.png... verifying base64 integrity - Success
    GET file file2.JPG... verifying base64 integrity - Success
    GET file test.jpg... verifying base64 integrity - Success
    GETting finished
    GETting file URLs with location min_lat=-52.1  max_lat=53.9  min_lon=-2.5  max_lon=-1.1
    Array
    (
        [0] => Array
            (
                [img_name] => file3.JPG
                [lat] => 53.876443888889
                [lon] => -1.9046100833333
                [url] => localhost/myx/uploads/file3.JPG
            )

        [1] => Array
            (
                [img_name] => file4.jpeg
                [lat] => 52.643144722222
                [lon] => -2.0572403055556
                [url] => localhost/myx/uploads/file4.jpeg
            )

        )
    Success testing get-zone.php
    DELETE file file4.jpeg... Success
    DELETE file file3.JPG... Success
    DELETE file file1.png... Success
    DELETE file file2.JPG... Success
    DELETE file test.jpg... Success
    ALL TESTS COMPLETED SUCCESSFULLY!
    ```

## Notes
I work with MIME types, because they are more reliable than file extensions. My project supports JPEG and PNG files.
