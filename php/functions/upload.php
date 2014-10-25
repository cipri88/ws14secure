<?php
 if(!isset($_SESSION)) 
    {        
	session_start(); //start session only if it is not already started
    }
ini_set('display_errors', 'On');
$error=''; // Variable To Store Error Message

$uploaddir = '/var/www/ws14secure/php/uploads/';
//$uploadfile = $uploaddir . basename($_FILES['uploadFile']['name']);


header('Content-Type: text/plain; charset=utf-8');

try {
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
      !isset($_FILES['uploadFile']['error']) ||
       is_array($_FILES['uploadFile']['error'])
    ) {
       throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['uploadFile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here. 
    if ($_FILES['uploadFile']['size'] > 1000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['uploadFile']['tmp_name']),
        array(
            'text' => 'text/plain',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
  //  if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
  //  echo "File is valid, and was successfully uploaded.\n";
//} else {
 //   echo "Possible file upload attack!\n";
//}

    if (!move_uploaded_file(
        $_FILES['uploadFile']['tmp_name'],
        sprintf('/var/www/ws14secure/php/uploads/%s.%s',
            sha1_file($_FILES['uploadFile']['tmp_name']).uniqid(),
            $ext
        )
    )) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    echo 'File is uploaded successfully.';

} catch (RuntimeException $e) {

    echo $e->getMessage();

}
?>