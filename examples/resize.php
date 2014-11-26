<?php
require '../src/Image.php';

$image      = new Image();
$my_image   = '../img/example_image.jpg';
$resize_size= array('width'=>200);

// Resize
$resized_image = $image->resize($my_image, $resize_size);
if( isset($resized_image['status']) && $resized_image['status'] == 'error' ) {
    echo $resized_image['message'];
    exit();
}
$image->display($resized_image);
//$image->store($resized_image, 'file_path.jpg','jpg',90);
//$image->destroy($resized_image);
?>