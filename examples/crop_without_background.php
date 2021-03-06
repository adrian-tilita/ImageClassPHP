<?php
require '../src/Image.php';

$image      = new Image();
$my_image   = '../img/example_image.jpg';
$resize_crop_size = array('width'=>300, 'height'=>300);

// Resize
$resized_image = $image->resizeAndCrop($my_image, $resize_crop_size, true);
if( isset($resized_image['status']) && $resized_image['status'] == 'error' ) {
    echo $resized_image['message'];
    exit();
}
$image->display($resized_image);
//$image->store($resized_image, 'file_path.jpg','jpg',90);
//$image->destroy($resized_image);
?>