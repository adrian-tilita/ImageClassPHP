<?php 
/** 
 *
 *  Image Manipulation Class
 *
 *  Author: Adrian Florin Tilita <adrian@tilita.ro>
 *  Date:   21.04.2011
 *
 *  Please feel free to leave feedback or bug reports
 *
 **/


/** --------------------------------------------------------------------------------------------------------------------------
 *  Basic usage
 *  --------------------------------------------------------------------------------------------------------------------------

	1) Using the class to resize/resize and crop/crop/display and store image

    $image_operator = new Image;

	$my_image         = 'test.jpg';
	$logo             = 'logo_test.png';

	$resize_size      = array('width'=>200);
	$resize_crop_size = array('width'=>500, 'height'=>500);
	$resize_crop_bg   = '#fff';

	$crop_coordinates = array('x1' => 100, 'y1' => 10);

	$scale_size      = array('width'=>600);
	$opacity         = 40;

	if(!$image_operator) {
		echo 'Not a valid image file!';
		exit();
	}


	// Resize
	$resized_image = $image_operator->resize($my_image, $resize_size);
	if( isset($resized_image['status']) && $resized_image['status'] == 'error' ) {
		echo $resized_image['message'];
		exit();
	}
	$image_operator->store($resized_image, 'write/test_resized.jpg','jpg',90);
	$image_operator->destroy($resized_image);

	// Resize and crop by cutting the image
	$resized_croped_image = $image_operator->resizeAndCrop($my_image, $resize_crop_size, true);
	if( isset($resized_croped_image['status']) && $resized_croped_image['status'] == 'error' ) {
		echo $resized_croped_image['message'];
		exit();
	}
	$image_operator->store($resized_croped_image, 'write/test_res_croped_cut.jpg','jpg',90);
	$image_operator->destroy($resized_croped_image);

	// Resize and crop by filling with background
	$resized_croped_image = $image_operator->resizeAndCrop($my_image, $resize_crop_size, false, $resize_crop_bg );
	if( isset($resized_croped_image['status']) && $resized_croped_image['status'] == 'error' ) {
		echo $resized_croped_image['message'];
		exit();
	}
	$image_operator->store($resized_croped_image, 'write/test_res_croped_bg.jpg','jpg',90);


	// Crop image
	$croped_image = $image_operator->crop($resized_croped_image, $crop_coordinates );
	if( isset($croped_image['status']) && $croped_image['status'] == 'error' ) {
		echo $croped_image['message'];
		exit();
	}
	$image_operator->store($croped_image, 'write/test_croped.jpg','jpg',90);
	$image_operator->store($croped_image, 'write/test_res_croped_full.jpg','jpg',90);


	// Watermark
	$watermark = $image_operator->addWatermark($my_image, $logo, 'top left', '-40px 0 0 -10px', $opacity, $scale_size);
	if( isset($watermark['status']) && $watermark['status'] == 'error' ) {
		echo $watermark['message'];
		exit();
	}
	$image_operator->store($watermark, 'write/watermark_image.jpg','jpg',90);


	// Rotate - Flip Image
	$flip_rotate = $image_operator->editImage($my_image, 'rotate', '90');
	if( isset($flip_rotate['status']) && $flip_rotate['status'] == 'error' ) {
		echo $flip_rotate['message'];
		exit();
	}
	$flip_rotate = $image_operator->editImage($flip_rotate, 'flip', 'vertical');
	if( isset($flip_rotate['status']) && $flip_rotate['status'] == 'error' ) {
		echo $flip_rotate['message'];
		exit();
	}
	$image_operator->store($flip_rotate, 'write/flip_rotate.jpg','jpg',90);


	// Get Colors Predominant coulours of an image
	$colors = $image_operator->getImageColors($my_image, false, 30, array('#000','#fff','#b7adac') );
	if( isset($colors['status']) && $colors['status'] == 'error' ) {
		echo $colors['message'];
		exit();
	}
	$image_operator->store($colors, 'write/predominant.jpg','jpg',90);


	$colors = $image_operator->getImageColors($my_image, true, 30, array('#000','#fff','#b7adac') );
	if( isset($colors['status']) && $colors['status'] == 'error' ) {
		echo $colors['message'];
		exit();
	}
	foreach($colors as $value)
		echo $value . '<br />';


	// Creeate Gradient Image
	$gradient_block = $image_operator->createGradinet(300,300,'#000','#df0000');
	if( isset($gradient_block['status']) && $gradient_block['status'] == 'error' ) {
		echo $gradient_block['message'];
		exit();
	}
	$image_operator->store($gradient_block, 'write/gradient.jpg','jpg',90);


	// Creeate Text Box
	$text = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.<br>Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown<br>printer took a galley of type and scrambled it to make a type specimen book. It has survived not only<br>five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.<br>It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages,<br>and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

	$size = 5;
	$addString = $image_operator->textBox($text, '#456', $size);
	$image_operator->store($addString, 'write/string.png','png');


	// Adjust image - add sepia effect
	$filter = $image_operator->adjustImage($my_image, 'vintage');
	$image_operator->store($filter, 'write/sepia.png','png');

 *  --------------------------------------------------------------------------------------------------------------------------
 **/


/** --------------------------------------------------------------------------------------------------------------------------
 *  Image Manipulation Class Content
 *  --------------------------------------------------------------------------------------------------------------------------
 * 1) _verifyGD() - Verify if GD is loaded and active
 *    = returns error if GD is not loaded
 *
 * 2) _verifyFT - Verify if Free Font Type Supported
 *    = returns error if Free Type Font is not loaded
 *
 * 3) getImageInfo($image) - Fetch the image details
 *    @ $image - a valid image path or a $_FILES var
 *    = returns false if it is not an image/image resource or an array with:
 *		a. width - width in pixels
 *		b. height - height in pixels
 *		c. aspect_ratio
 *		d*. mime - image myme-type 
 *		e*. memory_size - the amount of memory consumpted if loaded in memory (in bytes)
 *		f*. file_size - the image filesize (in bytes)
 *      g. image_handler - the image file path and name or $_FILES['tmp_name']
 *      * - if $image is a GD resource, the values will not be returned
 *
 * 4) getAvailableMemory() - Get available memory that can be used for image resource
 *    = returns the available memory (in bytes) that can be used for script memory consumption
 *
 * 5) getImageMemory($size, $plus = false) - Get the amount of memory consumpted by loading a image in memory
 *    @ $size - an array with the image width and height. Ex: array('width'=>200, 'height'=>400)
 *    = returns true if the image can be loaded in memory, false if the image size is to big
 *
 * 6) isImage($image, $type = false) - Verify if the the file is a valid suported image (jpg/png/gif/bmp)
 *    @ $image - a valid image path or a $_FILES var
 *    @ $type - an array with valid images (Ex: $type = array('jpg','png') - will exclude bmp & gif as valid images)
 *    = returns false if it not an image or image short type name if it is an accepted image (jpg/png/gif/bmp)
 *
 * 7) _hex2rgb($hex, $array = false) - Convert HEX color value to RGB
 *    @ $hex - a hex value (ex: '#fff', '#000000', 'cecece')
 *    @ $array - true/false
 *    = returns a rgb value (Ex: white = 255,255,255). If $array is true then it will return an array('red_value','green_value','blue_value');
 *
 * 8) _rgb2hex($rgb) - Convert RGB color value to HEX
 *    @ $rgb - array with RGB values. Ex: array('200','255',0)
 *    = returns a full hex value (Ex: white = #ffffff). If $array is true then it will return an array('red_value','green_value','blue_value');
 *
 * 9) word2bin($string) - Convert string to 32bit unsigned integer (DWORD)
 *    @ $string - hex string
 * 	  = returns a 32bit unsigned integer (DWORD)
 *
 * 10) imagecreatefrombmp($image) - Create an image resource from 24-bit bitmaps
 *    = returns image handler on succes or an array on error - array('status' => 'error',  'message' => message)
 *
 * 11) _getSize($size, $image_info) - Convert a given size to a parameters type size
 *    @ $size - a size given: 70%, array( 'width' => 300, 'height' => 50% ), array( 'height' => 100 )
 *    @ $image_info - array returned by getImageInfo($image)
 *    = return an array with width and height keys.
 *
 * 12) _getCropSize($size, $image_info, $cut = true) - Convert a given size to a parameters type size
 *    @ $size - a size given: 70%, array( 'width' => 300, 'height' => 50% ), array( 'height' => 100 )
 *    @ $image_info - array returned by getImageInfo($image)
 *    @ $cur - true/false - if the size will cut the image or if it will fill space
 *    = return an array with width and height keys.
 *
 * 13) _output($image, $parameters) - Output Image
 *    @ $image      - image resource
 *    @ $parameters - an array of the form $parameters = array('type' => 'jpg', 'quality' => 100, 'path' => 'test.jpg');
 *    = output image resource: to disk or on page (on store returns true or false) *
 *
 * 14) _resample($image, $parameters) - Resample Image
 *    @ $image - a valid image path/GD image resource or a $_FILES var
 *    @ $parameters - an array with the parameters needed for resampling the image image
 *                  - array('coordinates => array('x1'=>value,'y1'=>value,'x2'=>value,'y2'=>value),
 *                          'size' => array('width'=>value, 'height'=>value,'o_width'=> original_img_width, 'o_height'=>original_img_height),
 *                          'background' => optional_background_color_used_when_cropped);
 *    = returns image resource
 *
 * 15) resize($image, $size) - Resize Image
 *    @ $image - a valid image path or a $_FILES var
 *    @ $size - an array with the form $array('width'=> value,'height'=> value)* or a % value
 *               * the array can be written with one value (ex: $array('width'=200) where
 *                 the height will be automatically calculated
 *    = returns image resource
 *
 * 16) resizeAndCrop($image, $size, $cut = true, $bg = false) - Resize and crop image
 *    @ $image/$size - same as resize()
 *    @ $cut - true/false - cut the image or fill spaces with a background color
 *    @ $bg - background color in hex format. Ex: #fff; c4c4c4;
 *    = returns image resource
 *
 * 17) crop($image, $coordinates) - 100% Crop Image
 *    @ $image - same as resize()
 *    @ $coordinates - an array like: array('x1'=>0,'y1'=>200,'x2'=>300, 'y2' => 400)
 *    = returns image resource
 *
 * 18) addWatermark($image, $watermark, $position = false, $margin = false, $opacity = false, $scale_watermak = false) - Add Watermark on Image
 *    @ $image/$watermark - image file path, $_FILES var or GD image resource
 *    @ $position - css like position (top left): 'top left', 'top middle', 'bottom right' etc.
 *    @ $margin - css like margin (with 2 or 4 values, in order 'top right bottom left'). Ex: $margin = '10px 0 0 20px'
 *    @ $opacity - $opacity value from 0 (full transparent) - 100 (opaque) or false
 *    @ $opacity - $opacity value from 0 (full transparent) - 100 (opaque) or false
 *    @ $scale_watermak - false or $size var like in resize();
 *    = returns image resource
 *
 * 19) editImage($image, $action, $value, $background = false) - Edit Image
 *    @ $image  - image file path, $_FILES var or GD image resource
 *    @ $action - rotate/flip
 *    @ $value  - degree/horizontal - vertical
 *    @ $background - background color if rotating procces makes blank spaces - transparent if not set
 *    = returns image resource
 *
 * 20) adjustImage($image, $filter, $value = false) - Adjust - Add effect to image
 *    @ $image  - image file path, $_FILES var or GD image resource
 *    @ $filter* - available filter
 *    @ $value  - filter value if needed
 *    = returns image resource

 *
 * *Available filters/effects:
 *    - brightness;  value: -100 to 100
 *    - contrast;    value: -100 to 100
 *    - adjust_red;  value: -100 to 100
 *    - adjust_gree; value: -100 to 100
 *    - adjust_blue; value: -100 to 100
 *    - adjust_colors; value: array('red'=>100,'green'=>40,'blue=>-10) value from -255 to 255
 *    - invert_colors
 *    - grayscale
 *    - edge_detect
 *    - emboss
 *    - soften
 *    - sharpen
 *    - sketchy
 *    - blur
 *    - sepia
 *    - sepia2
 *    - vintage
 *    - vignette
 *    - old_photo
 *
 * 21) getImageColors($image, $return_array = false, $limit_results = 10, $exclude = false) - Get image colors - order by density
 *    @ $image  - image file path, $_FILES var or GD image resource
 *    @ $return_array - true/false - if false, will return image resource
 *    @ $limit_results - number of colors to return
 *    @ $exclude - an array with colors (in hex) to exclude from list. Ex: array('#df0000','#000','#fff')
 *    = returns image resource or an array('#ffffff','#000000','#ececec')
 *
 * 22) createGradinet($width, $height, $startColor, $endColor, $vertical = true) - Create image gradient
 *    @ $width - image gradient container width
 *    @ $height - image gradient container height
 *    @ $startColor* - gradient start color in hex. Ex: #fff, #df0000, ececec OR transparent
 *    @ $endColor* - gradient end color in hex. Ex: #fff, #df0000, ececec OR transparent
 *    * there can be only one transparent color
 *    @ $vertical - vertical or horizontal direction (true if vertical, false if horizontal)
 *    = return image resource
 *
 * 23) textBox($string, $color = '#000', $size = 3, $align = 'left', $lineheight = false, $fontfile = false) - Create text box
 *    @ $string - a string (that can contain <br/>, <br>, \n, \r for wraping long lines)
 *    @ $color - text color in hex format. Ex: #000
 *    @ $size - either 1-5 for normal GD font or other size for loading otf/ttf fonts
 *    @ $align - left/center/rigth
 *    @ $line-height - text line-height
 *    @ $font-file - OTF or TTF font files
 *    = return image resource
 *
 * 24) display($image, $type = false, $quality = false) - Output Image
 *    @ $image - image object returned by resize() or crop()
 *    @ $type - JPG/PNG/GIF - optional, default is jpg
 *    @ $quality - a value between 0 to 100 (only for JPEG and PNG)
 *    = return image
 *
 * 25) store($image, $path, $type, $quality = false) - Store Image
 *    @ $image - image object returned by resize() or crop()
 *    @ $path - path and filename
 *    @ $type - JPG/PNG/GIF
 *    @ $quality - a value between 0 to 100 (only for JPEG and PNG)
 *    = return true/false
 *
 * 26) destroy($image)- Destroy image
 *    @ $image - image object returned by resize() or crop()
 **/

class Image {



	public $error_message;
	public $memory;
	public $status;
	public $exif_explication;



	/**
	 * __construct for defining error messages
	 **/
	function __construct() {


		/*
		 * General Script Setup
		 */
		$this->memory = array();
		$this->memory['script_memory'] = 1 * pow(2,20);   // 1 MB - aproximate the php script not including the resize operation
		$this->memory['available_ram'] = 1024 * pow(2,20); // 512 MB of RAM - used when memory limit is set to -1	
		$this->memory['used_memory']   = 0; // Add Memory Usage at each operation	


		/*
		 * General Error Messages
		 */
		$this->error_message = array();
		$this->error_message['gd_not_loaded']= 'GD Library not loaded!';
		$this->error_message['no_freetype'] = 'FreeType is not suported!';

		$this->error_message['inexistent']   = 'Image file doesn\'t exists!';
		//$this->error_message['not_valid']    = 'Not a valid or accepted image type!';
		$this->error_message['no_memory']    = 'Not enough memory available for image manipulation operation!';
		$this->error_message['no_mime_type'] = 'Undefined image mime-type!';
		$this->error_message['no_resource']  = 'No resource image given!';
		$this->error_message['not_valid_bmp']= 'Not a valid 24-bit bitmap image!';
		$this->error_message['empty_size']   = 'Width or height not set!';
		$this->error_message['wrong_coordinates'] = 'Wrong coordinates!';


		/*
		 * Exif Tag Values "Translated"
		 */
		$this->exif_explication = array();
		$this->exif_explication['MeteringMode'] = array(
			0 => 'Unknown',
			1 => 'Average',
			2 => 'CenterWeightedAverage',
			3 => 'Spot',
			4 => 'MultiSpot',
			5 => 'Pattern',
			6 => 'Partial',
			255 => 'Other'
		);
		$this->exif_explication['WhiteBalance'] = array(
			0 => 'Auto white balance',
			1 => 'Manual white balance'
		);
		$this->exif_explication['ExposureMode'] = array(
			0 => 'Auto exposure',
			1 => 'Manual exposure',
			2 => 'Auto bracket'
		);
		$this->exif_explication['Contrast'] = array(
			0 => 'Normal',
			1 => 'Soft',
			2 => 'Hard'
		);
		$this->exif_explication['Sharpness'] = array(
			0 => 'Normal',
			1 => 'Soft',
			2 => 'Hard'
		);
		$this->exif_explication['Saturation'] = array(
			0 => 'Normal',
			1 => 'Low saturation',
			2 => 'High saturation'
		);
		$this->exif_explication['SubjectDistanceRange'] = array(
			0 => 'Unknown',
			1 => 'Macro',
			2 => 'Close view',
			3 => 'Distant view'
		);
		$this->exif_explication['ExposureProgram'] = array(
			0 => 'Not defined',
			1 => 'Manual',
			2 => 'Normal program',
			3 => 'Aperture priority',
			4 => 'Shutter priority',
			5 => 'Creative program (biased toward depth of field)',
			6 => 'Action program (biased toward fast shutter speed)',
			7 => 'Portrait mode (for closeup photos with the background out of focus)',
			8 => 'Landscape mode (for landscape photos with the background in focus)'
		);
		$this->exif_explication['Flash'] = array(
			0 => 'Flash did not fire.',
			1 => 'Flash fired.',
			5 => 'Strobe return light not detected.',
			7 => 'Strobe return light detected.',
			9 => 'Flash fired, compulsory flash mode',
			13 => 'Flash fired, compulsory flash mode, return light not detected',
			15 => 'Flash fired, compulsory flash mode, return light detected',
			16 => 'Flash did not fire, compulsory flash mode',
			24 => 'Flash did not fire, auto mode',
			25 => 'Flash fired, auto mode',
			29 => 'Flash fired, auto mode, return light not detected',
			31 => 'Flash fired, auto mode, return light detected',
			32 => 'No flash function',
			65 => 'Flash fired, red-eye reduction mode',
			69 => 'Flash fired, red-eye reduction mode, return light not detected',
			71 => 'Flash fired, red-eye reduction mode, return light detected',
			73 => 'Flash fired, compulsory flash mode, red-eye reduction mode',
			77 => 'Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected',
			79 => 'Flash fired, compulsory flash mode, red-eye reduction mode, return light detected',
			89 => 'Flash fired, auto mode, red-eye reduction mode',
			93 => 'Flash fired, auto mode, return light not detected, red-eye reduction mode',
			95 => 'Flash fired, auto mode, return light detected, red-eye reduction mode'
		);
		$this->exif_explication['LightSource'] = array(
			0 => 'unknown',
			1 => 'Daylight',
			2 => 'Fluorescent',
			3 => 'Tungsten (incandescent light)',
			4 => 'Flash',
			9 => 'Fine weather',
			10 => 'Cloudy weather',
			12 => 'Daylight fluorescent (D 5700 – 7100K)',
			13 => 'Day white fluorescent (N 4600 – 5400K)',
			14 => 'Cool white fluorescent (W 3900 – 4500K)',
			15 => 'White fluorescent (WW 3200 – 3700K)',
			17 => 'Standard light A',
			18 => 'Standard light B',
			19 => 'Standard light C',
			20 => 'D55',
			21 => 'D65',
			22 => 'D75',
			23 => 'D50',
			24 => 'ISO studio tungsten',
			255 => 'other light source'
		);


		/*
		 * Verify if GD is loaded
		 */
		$gd_loaded = $this->_verifyGD();
		if($gd_loaded != NULL) {
			echo $gd_loaded;
			exit();
		}


	} // __construct()



	/**
	 * Verify if GD is loaded and active
	 **/
	private function _verifyGD() {


		if( !extension_loaded('gd') || !function_exists('gd_info') )
			return $this->status = $this->error_message['gd_not_loaded'];


	} // _verifyGD()



	/**
	 * Verify if Free Font Type Supported
	 **/
	private function _verifyFT() {


		$gd_info = gd_info();
		$accepted_ftl = array('with freetype','with TTF library');


		if($gd_info['FreeType Support'] != 1)
			return $this->status = $this->error_message['no_freetype'];


		if(!in_array($gd_info['FreeType Linkage'],$accepted_ftl))
			return $this->status = $this->error_message['no_freetype'];


	} // _verifyFT()	



	/**
	 * Fetch the image details
	 *    @ $image - a valid image path or a $_FILES var
	 *    = returns false if it is not an image/image resource or an array with:
	 *		a. width - width in pixels
	 *		b. height - height in pixels
	 *		c. aspect_ratio
	 *		d*. mime - image myme-type 
	 *		e*. memory_size - the amount of memory consumpted if loaded in memory (in bytes)
	 *		f*. file_size - the image filesize (in bytes)
	 *      g. image_handler - the image file path and name or $_FILES['tmp_name']
	 *      * - if $image is a GD resource, the values will not be returned
	 **/
	function getImageInfo($image) {


		/*
		 * Fetch $image type
		 */
		$imageHandler = gettype($image);


		/*
		 * If $image is a filename or a $_FILES var
		 */
		if($imageHandler != 'resource') {

			if($imageHandler == 'array')
				$image = $image['tmp_name'];

			/*
			 * If the file does not exists on disk
			 */
			if(!file_exists($image)) 
				return $this->error_message['inexistent'];

			$imageInfo = getimagesize($image);
			$imageInfo = array( 'width'        => $imageInfo[0],
								'height'       => $imageInfo[1],
								'aspect_ratio' => $imageInfo[0] / $imageInfo[1],
								'mime'         => $imageInfo['mime'],
								'memory_size'  => $this->getImageMemory(array('width'=>$imageInfo[0],'height'=> $imageInfo[1]), true),
								'file_size'    => filesize($image),
								'image_handler'=> $image
			);

		} else {
		/*
		 * If $image as a GD resource
		 */
			$imageInfo['width']        = (@imagesx($image) !== false) ? imagesx($image) : 0;
			$imageInfo['height']       = (@imagesy($image) !== false) ? imagesy($image) : 0;
			$imageInfo['aspect_ratio'] = ($imageInfo['width'] != 0 && $imageInfo['height'] != 0) ? $imageInfo['width'] / $imageInfo['height'] : 0;
			$imageInfo['image_handler']= $image;
			if($imageInfo['width'] == 0 && $imageInfo['height'] == 0) {
				echo $this->error_message['no_resource'];
				exit();
			}

		}


		return $imageInfo;


	} // getImageInfo()



	/**
	 * Get available memory that can be used for image resource
	 *    = returns the available memory (in bytes) that can be used for script memory consumption
	 **/
	public function getAvailableMemory() {


		/**
		 * Modify if needed
		 **/
		$script_memory = $this->memory['script_memory'];
		$available_ram = $this->memory['available_ram'];


		$available_memory = ini_get('memory_limit');
		if($available_memory != -1) {

			$get_type = substr($available_memory, -1);
			$get_type = (is_numeric($get_type)) ? '' : strtolower($get_type);
			switch($get_type) {
				case('k'): $available_memory = substr($available_memory,0,-1) * 1024;               break;
				case('m'): $available_memory = substr($available_memory,0,-1) * 1024 * 1024;        break;
				case('g'): $available_memory = substr($available_memory,0,-1) * 1024 * 1024 * 1024; break;
				default:   $available_memory = $available_memory;
			}
			return $available_memory - $script_memory;

		} // If it is not set to full server memory


		return $available_ram - $script_memory;


	} // End of getAvailableMemory()




	/**
	 * Get the amount of memory consumpted by loading a image in memory
	 *    @ $size - an array with the image width and height. Ex: array('width'=>200, 'height'=>400)
	 *    = returns true if the image can be loaded in memory, false if the image size is to big
	 **/
	public function getImageMemory($size, $calculate = false) {


		/*
		 * Memory factor for image load into memory
		 */
		$factor= 1.7;


		/*
		 * Calculating image memory use
		 */
		$size  = $size['width'] * $size['height'];
		$memory_use = ($size * 3 + 40) * $factor;


		if($calculate === true)
			return $memory_use;

		$this->memory['used_memory'] += $memory_use;

		if($this->memory['used_memory'] >= $this->getAvailableMemory())
			return false;
		else
			return true;


	} // End of getAvailableMemory()



	/**
	 * Verify if the the file is a valid suported image (jpg/png/gif/bmp)
	 *    @ $image - a valid image path or a $_FILES var
	 *    @ $type - an array with valid images (Ex: $type = array('jpg','png') - will exclude bmp & gif as valid images)
	 *    = returns false if it not an image or image short type name if it is an accepted image (jpg/png/gif/bmp)
	 **/
	function isImage($image, $type = false) {


		/* Setting allowed images */
		if( !$type )
			$type = array('jpg','png','gif','bmp');


		/* Fetch the image */
		$imageHandler = gettype($image);
		if($imageHandler == 'array')
			$image = $image['tmp_name'];


		/* Get The Image MimeType */
		$imageInfo = getimagesize($image);
		if(!$imageInfo)
			return false;


		/* Translating the mime-type in short type image type */
		$imageInfo = $imageInfo['mime'];
		switch($imageInfo) {
			/* Jpeg Image */
			case('image/jpeg'):
			case('image/pjpeg'):
				$imageInfo = 'jpg';
				break;

			/* PNG Image */
			case('image/png'):
				$imageInfo = 'png';
				break;

			/* GIF Image */
			case('image/gif'):
				$imageInfo = 'gif';
				break;

			/* Bitmap Image */
			case('image/x-windows-bmp'):
			case('image/bmp'):
			case('image/x-ms-bmp'):
				$imageInfo = 'bmp';
				break;
		}


		/* Verify if the file is an accepted image type */
		if( !in_array($imageInfo, $type) )
			return false;

		return $imageInfo;


	} // isImage()



	/**
	 * Convert HEX color value to RGB
	 *    @ $hex - a hex value (ex: '#fff', '#000000', 'cecece')
	 *    @ $array - true/false
	 *    = returns a rgb value (Ex: white = 255,255,255). If $array is true then it will return an array('red_value','green_value','blue_value');
	 **/
	private function _hex2rgb($hex, $array = false) {


		/*
		 * Escaping "#" symbol
		 */
		$hex = str_replace('#','', $hex);


		/*
		 * If it is a shor value hex. (Ex: #fff)
		 */
		if(strlen($hex) == 3) {

			$hexR = substr($hex,0,1);
			$hexR = $hexR . $hexR;

			$hexG = substr($hex,1,1);
			$hexG = $hexG . $hexG;

			$hexB = substr($hex,2,1);
			$hexB = $hexB . $hexB;

		/*
		 * If it is a normal value hex. (Ex: #ffffff)
		 */
		} elseif(strlen($hex) == 6) {

			$hexR = substr($hex,0,2);
			$hexG = substr($hex,2,2);
			$hexB = substr($hex,4,2);

		/*
		 * If it is an incorect hex value returns white background
		 */
		} else {

			$hexR = 'ff';
			$hexG = 'ff';
			$hexB = 'ff';

		}

		$hexR = hexdec($hexR);
		$hexG = hexdec($hexG);
		$hexB = hexdec($hexB);

		if($array)
			return array($hexR, $hexG, $hexB);

		return $hexR . ',' . $hexG . ',' . $hexB;


	} // _hex2rgb()



	/**
	 * Convert RGB color value to HEX
	 *    @ $rgb - array with RGB values. Ex: array('200','255',0)
	 *    = returns a full hex value (Ex: white = #ffffff). If $array is true then it will return an array('red_value','green_value','blue_value');
	 **/
	private function _rgb2hex($rgb) {


		$red   = ( isset($rgb[0]) ) ? $rgb[0] : 0;
		$green = ( isset($rgb[1]) ) ? $rgb[1] : 0;
		$blue  = ( isset($rgb[2]) ) ? $rgb[2] : 0;

		$red   = dechex($red);
		$green = dechex($green);
		$blue  = dechex($blue);

		$red   = (strlen($red)  == 1) ? "0{$red}"   : $red;
		$green = (strlen($green)== 1) ? "0{$green}" : $green;
		$blue  = (strlen($blue) == 1) ? "0{$blue}"  : $blue;

		return "#{$red}{$green}{$blue}";


	} // _rgb2hex()



	/**
	 * Convert string to 32bit unsigned integer (DWORD)
	 *    @ $string - hex string
	 *    = returns a 32bit unsigned integer (DWORD)
	 **/
	function word2bin($string) {


		$part_1 = ord($string[0]);
		$part_2 = ord($string[1]);
		$part_3 = ord($string[2]);

		return $part_3 * 256 * 256 + $part_2 * 256 + $part_1;


	} // word2bin()



	/**
	 * Create an image resource from 24-bit bitmaps
	 *    = returns image handler on succes or an array on error - array('status' => 'error',  'message' => message)
	 **/
	public function imagecreatefrombmp($image) {


		/*
		 * Open the bitmap file
		 */
		$image_content = file_get_contents($image);


		/*
		 * Read bitmap header
		 */
		$image_header = substr($image_content,0,54);
		$image_header = unpack( 'c2identifier/Vfile_size/Vreserved/Vbitmap_data/Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
							    '/Vcompression/Vdata_size/Vh_resolution/Vv_resolution/Vcolors/Vimportant_colors', $image_header);


		/*
		 * Verify if the file is a valid 24bit bitmap image
		 */
		if($image_header['identifier1'] != 66 || $image_header['identifier2'] != 77 || $image_header['bits_per_pixel'] != 24)
			return array('status'=>'error','message'=> $this->error_message['not_valid_bmp']);


		/*
		 * Establishing row length
		 */
		$row_length = ceil(($image_header['width'] * 3) / 4) * 4;


		/*
		 * Verify available memory for resizing image
		 */
		$memory_usage = $this->getImageMemory($image_header);
		if(!$memory_usage)
			return array('status'=>'error','message'=> $this->error_message['no_memory']);


		unset($image);
		$image = imagecreatetruecolor($image_header['width'], $image_header['height']);


		/*
		 * Read and write image data, pixel by pixel, line by line, from top to bottom
		 */
		$pointer = 54;
		for($y_height = $image_header['height']-1; $y_height >= 0; --$y_height) {

			$y_line = substr($image_content, $pointer, $row_length);
			$pixels = str_split($y_line, 3);

			for($x_width = 0; $x_width < $image_header['width']; ++$x_width )
				imagesetpixel( $image, $x_width, $y_height, $this->word2bin($pixels[$x]) );

			$pointer = $pointer + $row_length;

		}


		return $image;


	} // imagecreatefrombmp()



	/**
	 * Convert a given size to a parameters type size
	 *    @ $size - a size given: 70%, array( 'width' => 300, 'height' => 50% ), array( 'height' => 100 )
	 *    @ $image_info - array returned by getImageInfo($image)
	 *    = return an array with width and height keys.
	 **/
	private function _getSize($size, $image_info) {


		/*
		 * If the given size is an array
		 */
		if(is_array($size)) {

			/* Getting height when is not set */
			if( isset($size['width']) && !isset($size['height']) ) {

				if( strpos($size['width'],'%') === false) {
					// rez.
					$size['width'] = $size['width'];
					$size['height']= $image_info['height'] / ($image_info['width'] / $size['width']);

				} else {

					$size['width'] = $image_info['width'] * substr($size['width'],0,-1) / 100;
					$size['height']= $image_info['height'] / ($image_info['width'] / $size['width']);

				}

			}
			/* Getting width when is not set */
			if( !isset($size['width']) && isset($size['height']) ) {

				if( strpos($size['height'],'%') === false) {

					$size['height']= $size['height'];
					$size['width'] = $image_info['width'] / ($image_info['height'] / $size['height']);

				} else {

					$size['height']= $image_info['height'] * substr($size['height'],0,-1) / 100;
					$size['width'] = $image_info['width'] / ($image_info['height'] / $size['height']);

				}

			}
			/* Getting width when is not set */
			if( isset($size['width']) && isset($size['height']) ) {

				if( strpos($size['height'],'%') === false) {

					$size['height']= $size['height'];
					$size['width'] = $size['width'];

				} else {

					$size['height']= $image_info['height'] / ($image_info['width'] / $size['width']);
					$size['width'] = $image_info['width'] / ($image_info['height'] / $size['height']);

				}

			}

		} else {

			$o_size = substr($size,0,-1);
			$size = array();
			$size['width'] = $image_info['width'] * $o_size / 100;
			$size['height']= $image_info['height']* $o_size / 100;

		}

		$size['width'] = round($size['width']);
		$size['height']= round($size['height']);


		return $size;


	} // _getSize();



	/**
	 * Convert a given size to a parameters type size
	 *    @ $size - a size given: 70%, array( 'width' => 300, 'height' => 50% ), array( 'height' => 100 )
	 *    @ $image_info - array returned by getImageInfo($image)
	 *    @ $cur - true/false - if the size will cut the image or if it will fill space
	 *    = return an array with width and height keys.
	 **/
	private function _getCropSize($size, $image_info, $cut = true) {


		/*
		 * If the image should be cropped
		 */
		if($cut === true) {

			$size_tmp   = array('width' => $size['width']);
			$get_size = $this->_getSize($size_tmp, $image_info);
	
			if($get_size['height'] < $size['height']) {
		
				$size_tmp = array('height' => $size['height']);
				$get_size = $this->_getSize($size_tmp, $image_info);
	
			}

		/*
		 * If the image would be filled with background
		 */
		} else {

			$size_tmp   = array('width' => $size['width']);
			$get_size = $this->_getSize($size_tmp, $image_info);
	
			if($get_size['height'] > $size['height']) {
		
				$size_tmp = array('height' => $size['height']);
				$get_size = $this->_getSize($size_tmp, $image_info);
	
			}

		}

		$x = ($get_size['width'] != $size['width'])   ? floor(( $get_size['width'] - $size['width'] ) / 2) : 0;
		$y = ($get_size['height'] != $size['height']) ? floor(( $get_size['height']- $size['height']) / 2) : 0;

		return array('width' => $get_size['width'], 'height' => $get_size['height'], 'x' => $x, 'y' => $y);


	} // _getCropSize();



	/**
	 * Output Image
	 *    @ $image      - image resource
	 *    @ $parameters - an array of the form $parameters = array('type' => 'jpg', 'quality' => 100, 'path' => 'test.jpg');
	 *    = output image resource: to disk or on page (on store return true or false)
	 **/
	private function _output($image, $parameters) {


		/*
		 * Translate Parameters
		 */
		$parameters['path'] = (isset($parameters['path'])) ? $parameters['path'] : null;

		switch($parameters['type']) {
			case('jpg'):
				$quality = (!$parameters['quality']) ? 100 : $parameters['quality'];
				if(!$parameters['quality'])
					header('Content-Type: image/jpeg');
				$store = imagejpeg($image, $parameters['path'], $quality);
				break;
			case('png'):
				$quality = (!$parameters['quality']) ? 8 : ( ($parameters['quality'] > 10) ? round($parameters['quality'] / 10) : $parameters['quality']);
				$quality = ($quality > 9) ? 9 : $quality;
				if(!$parameters['quality'])
					header('Content-Type: image/png');
				$store = imagepng($image, $parameters['path'], $quality);
				break;
			case('gif'):
				if(!$parameters['quality'])
					header('Content-Type: image/gif');
				$store = imagegif($image, $parameters['path']);
				break;
			default:
				$quality = (!$parameters['quality']) ? 100 : $parameters['quality'];
				if(!$parameters['quality'])
					header('Content-Type: image/jpeg');
				$store = imagejpeg($image, $parameters['path'], $quality);
		}


		/*
		 * If the operation is store then return true or false
		 */
		if($parameters['path'] != 'null')
			return $store;


	} // _output()



	/**
	 * _resample($image, $parameters) - Resample Image
	 *    @ $image - a valid image path or a $_FILES var
	 *    @ $parameters - an array with the parameters needed for resampling the image image
	 *                  - array('coordinates => array('x1'=>value,'y1'=>value,'x2'=>value,'y2'=>value),
	 *                          'size' => array('width'=>value, 'height'=>value,'o_width'=> original_img_width, 'o_height'=>original_img_height),
	 *                          'background' => optional_background_color_used_when_cropped);
	 *    = returns image resource
	 **/
	private function _resample($image, $parameters) {


		/*
		 * Output Image
		 */
		$image_output = imagecreatetruecolor($parameters['size']['width'], $parameters['size']['height']);


		/*
		 * Establishing source type
		 */
		if(gettype($image) == 'resource') {

			$image_source = $image;

		} else {
			/*
			 * Create Destination Image
			 */
			switch($parameters['mime']) {
	
				/* Jpeg Image */
				case('image/jpeg'):
				case('image/pjpeg'):
					$image_source = imagecreatefromjpeg($image);
					break;
				/* PNG Image */
				case('image/png'):
					$image_source = imagecreatefrompng($image);

					imagealphablending($image_output, false);
					$color = imagecolorallocatealpha($image_output, 0, 0, 0, 127);
					imagefill($image_output, 0, 0, $color);
        			imagesavealpha($image_output, true);

					break;
				/* GIF Image */
				case('image/gif'):
					$image_source = imagecreatefromgif($image);

					imagealphablending($image_output, false);
					$color = imagecolorallocatealpha($image_output, 0, 0, 0, 127);
					imagefill($image_output, 0, 0, $color);
        			imagesavealpha($image_output, true);

					break;
				/* Bitmap Image */
				case('image/x-windows-bmp'):
				case('image/bmp'):
				case('image/x-ms-bmp'):
					$image_source = $this->imagecreatefrombmp($image);
					if( isset($image_source['status']) )
						return $image_source;
					break;
				default:
					return array('status' => 'error', 'message' => $this->error_message['no_mime_type']);
	
			}

		}

		imagecopyresampled($image_output,
						   $image_source,
						   $parameters['coordinates']['x1'],
						   $parameters['coordinates']['y1'],
						   $parameters['coordinates']['x2'],
						   $parameters['coordinates']['y2'],
						   $parameters['size']['width'],
						   $parameters['size']['height'],
						   $parameters['size']['o_width'],
						   $parameters['size']['o_height']);

		/*
		 * If a background color isset
		 */
		if(isset($parameters['background'])) {

			$background = imagecolorallocate($image_output, $parameters['background'][0], $parameters['background'][1], $parameters['background'][2]);


			if($parameters['coordinates']['x2'] < 0) {
	
				$patch_fill[0] = array('x1' => 0,
									   'y1' => 0,
									   'x2' => $parameters['coordinates']['x2'] * -1,
									   'y2' => $parameters['size']['height']);
	
				$patch_fill[1] = array('x1' => $parameters['size']['width'] - ($parameters['coordinates']['x2'] * -1),
									   'y1' => 0,
									   'x2' => $parameters['size']['width'],
									   'y2' => $parameters['size']['height']);

			} elseif($parameters['coordinates']['y2'] < 0) {
	
				$patch_fill[0] = array('x1' => 0,
									   'y1' => 0,
									   'x2' => $parameters['size']['width'],
									   'y2' => $parameters['coordinates']['y2'] * -1);
	
				$patch_fill[1] = array('x1' => 0,
									   'y1' => $parameters['size']['height'] - ($parameters['coordinates']['y2'] * -1),
									   'x2' => $parameters['size']['width'],
									   'y2' => $parameters['size']['height']);

			}
			imagefilledrectangle($image_output, $patch_fill[0]['x1'], $patch_fill[0]['y1'], $patch_fill[0]['x2'], $patch_fill[0]['y2'], $background);
			imagefilledrectangle($image_output, $patch_fill[1]['x1'], $patch_fill[1]['y1'], $patch_fill[1]['x2'], $patch_fill[1]['y2'], $background);

		} // isset background coor
		

		/*
		 * Return Image Object
		 */
		return $image_output;


	} // _resample()



	/**
	 * Resize Image
	 *    @ $image - a valid image path or a $_FILES var, or a GD resource
	 *    @ $size - an array with the form $array('width'=> value,'height'=> value)* or a % value
	 *               * the array can be written with one value (ex: $array('width'=200) where
	 *                 the height will be automatically calculated
	 *    = returns image resource
	 **/
	public function resize($image, $size) {


		/*
		 * Get Image Info
		 */
		$image_info = $this->getImageInfo($image);
		if(!is_array($image_info))
			return $image_info;


		/*
		 * Get the size of the image
		 */
		$size = $this->_getSize($size, $image_info);


		/*
		 * Verify available memory for resizing image
		 */
		$memory_usage = $this->getImageMemory($image_info);
		$memory_usage = $this->getImageMemory($size);
		if(!$memory_usage)
			return array('status'=>'error','message'=> $this->error_message['no_memory']);


		$parameters = array('coordinates' => array('x1'=>0,
												   'y1'=>0,
												   'x2'=>0,
												   'y2'=>0),
							'size' => array('width'   => $size['width'],
											'height'  => $size['height'],
											'o_width' => $image_info['width'],
											'o_height'=> $image_info['height']));
		if(isset($image_info['mime']))
			$parameters['mime'] = $image_info['mime'];

		/*
		 * Return resampled image resource
		 */
		return $this->_resample($image_info['image_handler'], $parameters);


	} // resize()



	/**
	 * Resize And Crop Image
	 *    @ $image/$size - same as resize()
	 *    @ $cut - true/false - cut the image or fill spaces with a background color
	 *    @ $bg - background color in hex format. Ex: #fff; c4c4c4;
	 *    = returns image resource
	 **/
	public function resizeAndCrop($image, $size, $cut = true, $bg = false) {


		/*
		 * Verify if the full parameters are written
		 */
		if(!isset($size['width']) || !isset($size['height']) )
			return array('status' => 'error', 'message' => $this->error_message['empty_size'] );


		/*
		 * Get Image Info
		 */
		$image_info = $this->getImageInfo($image);
		if(!is_array($image_info))
			return $image_info;


		/*
		 * Get the size of the image
		 */
		$new_size = $this->_getCropSize($size, $image_info, $cut);


		/*
		 * Verify available memory for resizing image
		 */
		$memory_usage = $this->getImageMemory($image_info);
		$memory_usage = $this->getImageMemory($new_size);
		if(!$memory_usage)
			return array('status'=>'error','message'=> $this->error_message['no_memory']);


		$parameters = array('coordinates' => array('x1'=>0,
												   'y1'=>0,
												   'x2'=>0,
												   'y2'=>0),
							'size' => array('width'   => $new_size['width'],
											'height'  => $new_size['height'],
											'o_width' => $image_info['width'],
											'o_height'=> $image_info['height']));
		if(isset($image_info['mime']))
			$parameters['mime'] = $image_info['mime'];


		/*
		 * First resize image
		 */
		$image_obj = $this->_resample($image_info['image_handler'], $parameters);


		$memory_usage = $this->getImageMemory($size);
		if(!$memory_usage)
			return array('status'=>'error','message'=> $this->error_message['no_memory']);


		$parameters = array('coordinates' => array('x1'=>0,
												   'y1'=>0,
												   'x2'=>$new_size['x'],
												   'y2'=>$new_size['y']),
							'size' => array('width'   => $size['width'],
											'height'  => $size['height'],
											'o_width' => $size['width'],
											'o_height'=> $size['height'])
							);
		/*
		 * If isset background color
		 */
		if($bg)
			$parameters['background'] = $this->_hex2rgb($bg, true);

		/*
		 * Return Image
		 */
		return $this->_resample($image_obj, $parameters);


	} // resizeAndCrop()



	/**
	 * Crop Image
	 *    @ $image - same as resize()
	 *    @ $coordinates - an array like: array('x1'=>0,'y1'=>200,'x2'=>300, 'y2' => 400)
	 *    = returns image resource
	 **/
	public function crop($image, $coordinates) {


		/*
		 * Get Image Info
		 */
		$image_info = $this->getImageInfo($image);
		if(!is_array($image_info))
			return $image_info;


		/*
		 * Calculate new image size
		 */
		if(!isset($coordinates['x1']))
			$coordinates['x1'] = 0;

		if(!isset($coordinates['y1']))
			$coordinates['y1'] = 0;

		if(!isset($coordinates['x2']))
			$coordinates['x2'] = $image_info['width'];

		if(!isset($coordinates['y2']))
			$coordinates['y2'] = $image_info['height'];

		$size = array('width' => $coordinates['x2'] - $coordinates['x1'],
					  'height'=> $coordinates['y2'] - $coordinates['y1']);



		/*
		 * Verify available memory for resizing image
		 */
		$memory_usage = $this->getImageMemory($size);
		$memory_usage = $this->getImageMemory($image_info);
		if(!$memory_usage)
			return array('status'=>'error','message'=> $this->error_message['no_memory']);


		/*
		 * Verify if the coordinates are correct
		 */
		if( ($size['width'] + $coordinates['x1'] > $image_info['width']) ||
			($size['height']+ $coordinates['y1'] > $image_info['height']) ||
			($coordinates['x1'] >= $coordinates['x2']) || ($coordinates['y1'] >= $coordinates['y2']) )
			return array('status' => 'error', 'message' => $this->error_message['wrong_coordinates'] );


		$parameters = array('coordinates' => array( 'x1' => 0,
													'y1' => 0,
													'x2' => $coordinates['x1'],
													'y2' => $coordinates['y1']),
							'size' => array('width'   => $size['width'],
											'height'  => $size['height'],
											'o_width' => $size['width'],
											'o_height'=> $size['height']));
		if(isset($image_info['mime']))
			$parameters['mime'] = $image_info['mime'];


		/*
		 * Return Image
		 */
		return $this->_resample($image_info['image_handler'], $parameters);


	} // crop()



	/**
	 * Add Watermark on Image
	 *    @ $image/$watermark - image file path, $_FILES var or GD image resource
	 *    @ $position - css like position (top left): 'top left', 'top middle', 'bottom right' etc.
	 *    @ $margin - css like margin (with 2 or 4 values, in order 'top right bottom left'). Ex: $margin = '10px 0 0 20px'
	 *    @ $opacity - $opacity value from 0 (full transparent) - 100 (opaque) or false
	 *    @ $opacity - $opacity value from 0 (full transparent) - 100 (opaque) or false
	 *    @ $scale_watermak - false or $size var like in resize();
	 *    = returns image resource
	 **/
	public function addWatermark($image, $watermark, $position = false, $margin = false, $opacity = false, $scale_watermak = false) {


		/*
		 * Get Image Info
		 */
		$image_info = $this->getImageInfo($image);
		if(!is_array($image_info))
			return $image_info;


		/*
		 * Get Watermark Info
		 */
		$watermark_info = $this->getImageInfo($watermark);
		if(!is_array($watermark_info))
			return $watermark_info;


		/*
		 * Create Watermark Resource
		 */
		if(isset($watermark_info['mime'])) {

			switch($watermark_info['mime']) {
				/* Jpeg Image */
				case('image/jpeg'):
				case('image/pjpeg'): $watermark_img = imagecreatefromjpeg($watermark_info['image_handler']); break;
				/* PNG Image */
				case('image/png'):   $watermark_img = imagecreatefrompng($watermark_info['image_handler']); break;
				/* GIF Image */
				case('image/gif'):   $watermark_img = imagecreatefromgif($watermark_info['image_handler']); break;
				default:
					return array('status' => 'error', 'message' => $this->error_message['no_mime_type']);
			}
			$memory_usage = $this->getImageMemory($watermark_info);
			if(!$memory_usage)
				return array('status'=>'error','message'=> $this->error_message['no_memory']);

		} else {

			$watermark_img = $watermark;

		}


		/*
		 * Create Base Image
		 */
		if(isset($image_info['mime'])) {

			switch($image_info['mime']) {
				/* Jpeg Image */
				case('image/jpeg'):
				case('image/pjpeg'): $base_img = imagecreatefromjpeg($image_info['image_handler']); break;
				/* PNG Image */
				case('image/png'):   $base_img = imagecreatefrompng($image_info['image_handler']); break;
				/* GIF Image */
				case('image/gif'):   $base_img = imagecreatefromgif($image_info['image_handler']); break;
				default:
					return array('status' => 'error', 'message' => $this->error_message['no_mime_type']);
			}
			$memory_usage = $this->getImageMemory($image_info);
			if(!$memory_usage)
				return array('status'=>'error','message'=> $this->error_message['no_memory']);

		} else {

			$base_img = $image;

		}
		imagealphablending($base_img, true);


		/*
		 * Establishing new watermark width and height
		 */
		if($scale_watermak) {

			$watermark_img = $this->resize($watermark, $scale_watermak);
			if( isset($watermark_img['status']) && $watermark_img['status'] == 'error' ) {
				echo $watermark_img['message'];
				exit();
			}
			$watermark_info = $this->getImageInfo($watermark_img);

		}

		/*
		 * Establshing position
		 */
		$watermark_x = 'center';
		$watermark_y = 'center';
		if($position !== false) {
			$position = str_replace('middle','center',$position);
			$position = explode(' ',$position);
			if(count($position) == 2) {
				$watermark_x = $position[1];
				$watermark_y = $position[0];
			}
		}
		$accepted_x_positions = array('left','right','center');
		$accepted_y_positions = array('top','bottom','center');

		$watermark_x = (in_array($watermark_x, $accepted_x_positions)) ? $watermark_x : 'center';
		$watermark_y = (in_array($watermark_y, $accepted_y_positions)) ? $watermark_y : 'center';


		/*
		 * Translating position in pixels
		 */
		switch($watermark_x) {
			case('left'):
				$watermark_x = 0;
				break;
			case('right'):
				$watermark_x = round($image_info['width'] - $watermark_info['width']);
				break;
			case('center'):
				$watermark_x = round(($image_info['width'] - $watermark_info['width'])/2);
				break;
		}
		switch($watermark_y) {
			case('top'):
				$watermark_y = 0;
				break;
			case('bottom'):
				$watermark_y = round($image_info['height'] - $watermark_info['height']);
				break;
			case('center'):
				$watermark_y = round(($image_info['height'] - $watermark_info['height'])/2);
				break;
		}


		/*
		 * Establishing margins
		 */
		$margins = array('top' => 0, 'left' => 0, 'bottom' => 0, 'right' => 0);
		if($margin) {
			$margin = str_replace('px','',$margin);
			$margin = explode(' ', $margin);
			if(count($margin) == 2) {
				$margins = array('top'    => $margin[0],
								 'right'  => $margin[1],
								 'bottom' => $margin[0],
								 'left'   => $margin[1]);
			}
			if(count($margin) == 4) {
				$margins = array('top'    => $margin[0],
								 'right'  => $margin[1],
								 'bottom' => $margin[2],
								 'left'   => $margin[3]);
			}
		}
		$watermark_x = $watermark_x + $margins['left'] - $margins['right'];
		$watermark_y = $watermark_y + $margins['top'] - $margins['bottom'];


		/*
		 * Create combined image
		 */
		imagecopy($base_img, $watermark_img, $watermark_x, $watermark_y, 0, 0, $watermark_info['width'], $watermark_info['height']);
		imagesavealpha($base_img, true);


		/*
		 * Establishing opacity
		 */
		$opacity = ($opacity) ? $opacity * 1 : 100;
		if($opacity != 100) {

			$opacity = $opacity * 1.27;

			$memory_usage = $this->getImageMemory($image_info);
			if(!$memory_usage)
				return array('status'=>'error','message'=> $this->error_message['no_memory']);


			$new_image = imagecreatetruecolor($image_info['width'],$image_info['height']);
			imagecopyresampled($new_image, $base_img, 0, 0, 0, 0, $image_info['width'], $image_info['height'], $image_info['width'], $image_info['height']);


			if(isset($image_info['mime'])) {
	
				switch($image_info['mime']) {
					/* Jpeg Image */
					case('image/jpeg'):
					case('image/pjpeg'): $return_image = imagecreatefromjpeg($image_info['image_handler']); break;
					/* PNG Image */
					case('image/png'):   $return_image = imagecreatefrompng($image_info['image_handler']); break;
					/* GIF Image */
					case('image/gif'):   $return_image = imagecreatefromgif($image_info['image_handler']); break;
					default:
						return array('status' => 'error', 'message' => $this->error_message['no_mime_type']);
				}
				$memory_usage = $this->getImageMemory($image_info);
				if(!$memory_usage)
					return array('status'=>'error','message'=> $this->error_message['no_memory']);
	
			} else {
	
				$return_image = $image;
	
			}

			imagecopymerge($return_image, $new_image, 0, 0, 0, 0, $image_info['width'], $image_info['height'], $opacity);
			return $return_image;

		}

		/*
		 * Return image resource
		 */
		return $base_img;


	} // addWatermark()



	/**
	 * Edit Image
	 *    @ $image  - image file path, $_FILES var or GD image resource
	 *    @ $action - rotate/flip
	 *    @ $value  - degree/horizontal - vertical
	 *    @ $background - background color if rotating procces makes blank spaces - transparent if not set
	 *    = returns image resource
	 **/
	public function editImage($image, $action, $value, $background = false) {


		/*
		 * Get Image Info
		 */
		$image_info = $this->getImageInfo($image);
		if(!is_array($image_info))
			return $image_info;


		/*
		 * Create Image Resource
		 */
		if(isset($image_info['mime'])) {

			switch($image_info['mime']) {
				/* Jpeg Image */
				case('image/jpeg'):
				case('image/pjpeg'): $return_img = imagecreatefromjpeg($image_info['image_handler']); break;
				/* PNG Image */
				case('image/png'):   $return_img = imagecreatefrompng($image_info['image_handler']); break;
				/* GIF Image */
				case('image/gif'):   $return_img = imagecreatefromgif($image_info['image_handler']); break;
				default:
					return array('status' => 'error', 'message' => $this->error_message['no_mime_type']);
			}
			$memory_usage = $this->getImageMemory($image_info);
			if(!$memory_usage)
				return array('status'=>'error','message'=> $this->error_message['no_memory']);

		} else {

			$return_img = $image;

		}
		imagealphablending($return_img, true);

		/*
		 * Get Action
		 */
		switch($action) {
			/*
			 * Rotate Image
			 */
			case('rotate'):

				if($background) {
					$background = $this->_hex2rgb($background, true);
					$bg_color = imagecolorallocate($return_img, $background[0], $background[1], $background[2]);
				} else {
					$bg_color = imagecolorallocatealpha($return_img, 255, 255, 255, 100);			
				}
				$return_img = imagerotate($return_img, $value, $bg_color);
				break;
			/*
			 * Flip Image
			 */
			case('flip'):
				$value = ($value == 'horizontal') ? 'horizontal' : 'vertical';

				$memory_usage = $this->getImageMemory($image_info);
				if(!$memory_usage)
					return array('status'=>'error','message'=> $this->error_message['no_memory']);

				$flipped_image = imagecreatetruecolor($image_info['width'], $image_info['height']);
				imagealphablending($flipped_image, false);
				$color = imagecolorallocatealpha($flipped_image, 0, 0, 0, 127);
				imagefill($flipped_image, 0, 0, $color);

				$src_x_pos = 0;
				$src_y_pos = 0;

				$src_width  = $image_info['width'];
				$src_height = $image_info['height'];

				switch($value) {
					case('horizontal'):
						$src_x_pos = $src_width - 1;
						$src_width = 0 - $src_width;
						break;
					case('vertical'):
						$src_y_pos = $src_height - 1;
						$src_height = 0 - $src_height;
						break;
				}
				imagecopyresampled($flipped_image, $return_img, 0, 0, $src_x_pos, $src_y_pos, $image_info['width'], $image_info['height'], $src_width, $src_height);	
				$return_img = $flipped_image;
			break;
		}

		imagesavealpha($return_img, true);

		/*
		 * Return image resource
		 */
		return $return_img;


	} // editImage()



	/**
	 * Adjust - Add effect to image
	 *    @ $image  - image file path, $_FILES var or GD image resource
	 *    @ $filter* - available filter
	 *    @ $value  - filter value if needed
	 *    = returns image resource
	 *
	 * *Available filters/effects:
	 *    - brightness;  value: -100 to 100
	 *    - contrast;    value: -100 to 100
	 *    - adjust_red;  value: -100 to 100
	 *    - adjust_gree; value: -100 to 100
	 *    - adjust_blue; value: -100 to 100
	 *    - adjust_colors; value: array('red'=>100,'green'=>40,'blue=>-10) value from -255 to 255
	 *    - invert_colors
	 *    - grayscale
	 *    - edge_detect
	 *    - emboss
	 *    - soften
	 *    - sharpen
	 *    - sketchy
	 *    - blur
	 *    - sepia
	 *    - sepia2
	 *    - vignette
	 *    - old_photo
	 **/

	public function adjustImage($image, $filter, $value = false) {



		/*
		 * Get Image Info
		 */
		$image_info = $this->getImageInfo($image);


		/*
		 * Create Watermark Resource
		 */
		if(isset($image_info['mime'])) {

			switch($image_info['mime']) {
				/* Jpeg Image */
				case('image/jpeg'):
				case('image/pjpeg'): $return_image = imagecreatefromjpeg($image_info['image_handler']); break;
				/* PNG Image */
				case('image/png'):   $return_image = imagecreatefrompng($image_info['image_handler']); break;
				/* GIF Image */
				case('image/gif'):   $return_image = imagecreatefromgif($image_info['image_handler']); break;
				default:
					return array('status' => 'error', 'message' => $this->error_message['no_mime_type']);
			}
			$memory_usage = $this->getImageMemory($image_info);
			if(!$memory_usage)
				return array('status'=>'error','message'=> $this->error_message['no_memory']);

		} else {

			$return_image = $image;

		}

		imagealphablending($return_image, false);
		$transparent_color = imagecolorallocatealpha($return_image, 0, 0, 0, 127);
		imagefill($return_image, 0, 0, $transparent_color);


		switch($filter) {
			/**
			 * Brightness
			 **/
			case('brightness'):
				$value = ($value) ? $value : 5;
				$value = $value * 2.55;
				imagefilter($return_image, IMG_FILTER_BRIGHTNESS, $value);
				break;

			/**
			 * Contrast
			 **/
			case('contrast'):
				$value = ($value) ? $value : 5;
				$value = $value * 2.55 * -1;
				imagefilter($return_image, IMG_FILTER_CONTRAST, $value);
				break;

			/**
			 * Adjust Colors - Red
			 **/
			case('adjust_red'):
				$red = ($value != false) ? $value * 2.55 : 0;
				imagefilter($return_image, IMG_FILTER_COLORIZE, $red, 0, 0);
				break;

			/**
			 * Adjust Colors - Green
			 **/
			case('adjust_green'):
				$green = ($value != false) ? $value * 2.55 : 0;
				imagefilter($return_image, IMG_FILTER_COLORIZE, 0, $green, 0);
				break;

			/**
			 * Adjust Colors - Blue
			 **/
			case('adjust_blue'):
				$blue = ($value != false) ? $value * 2.55 : 0;
				imagefilter($return_image, IMG_FILTER_COLORIZE, 0, 0, $blue);
				break;

			/**
			 * Adjust Colors - Advanced
			 **/
			case('adjust_color'):
				$red   = (isset($value['red']))   ? $value['red'] : 0;
				$green = (isset($value['green'])) ? $value['green'] : 0;
				$blue  = (isset($value['blue']))  ? $value['blue'] : 0;
				imagefilter($return_image, IMG_FILTER_COLORIZE, $red, $green, $blue);
				break;				

			/**
			 * Invert all colors
			 **/
			case('invert_colors'):
				imagefilter($return_image, IMG_FILTER_NEGATE);
				break;

			/**
			 * Grayscale image
			 **/
			case('grayscale'):
				imagefilter($return_image, IMG_FILTER_GRAYSCALE);
				break;

			/**
			 * Show image edges
			 **/
			case('edge_detect'):
				imagefilter($return_image, IMG_FILTER_EDGEDETECT);
				break;

			/**
			 * Emboss image
			 **/
			case('emboss'):
				imagefilter($return_image, IMG_FILTER_EMBOSS);
				break;

			/**
			 * Soften the image
			 **/
			case('soften'):
				imagefilter($return_image, IMG_FILTER_SMOOTH, 6);
				break;

			/**
			 * Sharpen the image
			 **/
			case('sharpen'):
				imagefilter($return_image, IMG_FILTER_SMOOTH, -10);
				break;


			/**
			 * Sketchy
			 **/
			case('sketchy'):
				imagefilter($return_image, IMG_FILTER_SMOOTH, -9);
				break;

			/**
			 * Gaussian Blur the image
			 **/
			case('blur'):
				imagefilter($return_image, IMG_FILTER_GAUSSIAN_BLUR);
				break;

			/**
			 * Sepia
			 **/
			case('sepia'):
				imagefilter($return_image, IMG_FILTER_GRAYSCALE);
				imagefilter($return_image, IMG_FILTER_CONTRAST,  -24);
				imagefilter($return_image, IMG_FILTER_BRIGHTNESS,-8);
				imagefilter($return_image, IMG_FILTER_COLORIZE, 100, 50, 0);
				break;

			/**
			 * Sepia 2
			 **/
			case('sepia2'):
				imagefilter($return_image, IMG_FILTER_GRAYSCALE);
				imagefilter($return_image, IMG_FILTER_CONTRAST,  -25.5);
				imagefilter($return_image, IMG_FILTER_BRIGHTNESS,-30.6);
				imagefilter($return_image, IMG_FILTER_COLORIZE, 90, 60, 40);
				break;

			/**
			 * Vintage Camera Effect
			 **/
			case('vintage'):
				imagefilter($return_image, IMG_FILTER_SMOOTH, -10);
				imagefilter($return_image, IMG_FILTER_CONTRAST,  -5);
				imagefilter($return_image, IMG_FILTER_COLORIZE, 40, 30, 100);
				break;


			/**
			 * Vignette Effect
			 **/
			case('vignette'):
				/**
				 * Verify if a duplicate can be made
				 **/
				$memory_usage = $this->getImageMemory($image_info);
				if(!$memory_usage)
					return array('status'=>'error','message'=> $this->error_message['no_memory']);

				$vig_width  = $image_info['width'];
				$vig_height = $image_info['height'];

				$factor_size = array($vig_width, $vig_height);
				$factor_size = max($factor_size);

				$factor_width  = round($factor_size / 1000) * 2;
				$opacity_factor= round($factor_size / 1000) * 2;

				$center_x = round($vig_width / 2);
				$center_y = round($vig_height / 2);

				$start_x = $vig_width  * 1.15;
				$start_y = $vig_height * 1.15;

				$base_image = imagecreatetruecolor($vig_width, $vig_height);
				imagealphablending($base_image, false);
				$transparent_color = imagecolorallocatealpha($base_image, 0, 0, 0, 127);
				imagefill($base_image, 0, 0, $transparent_color);

				for($i = 0; $i <= (127 * $factor_width); $i++) {
			
					$opacity = round(127 - $i / $opacity_factor);
					$opacity = ($opacity < 0) ? 0 : $opacity;

					$fill = imagecolorallocatealpha($base_image, 0, 0, 0, $opacity);
			
					imagesetthickness($base_image, 3);
					imagearc($base_image, $center_x, $center_y, $start_x + $i, $start_y + $i, 0,   180,$fill);
					imagearc($base_image, $center_x, $center_y, $start_x + $i, $start_y + $i, 180, 360,$fill);
			
				}
				imagesavealpha($base_image, true);
				imageantialias($base_image, true);

				$return_image = $this->addWatermark($image, $base_image, 'center center');
				imagefilter($return_image, IMG_FILTER_CONTRAST, -2);
				break;


		} // End of switch

		/**
		 * Return Image
		 **/
		return $return_image;


	} // adjustImage()



	/**
	 * Get image colors - order by density
	 *    @ $image  - image file path, $_FILES var or GD image resource
	 *    @ $return_array - true/false - if false, will return image resource
	 *    @ $limit_results - number of colors to return
	 *    @ $exclude - an array with colors (in hex) to exclude from list. Ex: array('#df0000','#000','#fff')
	 *    = returns image resource or an array('#ffffff','#000000','#ececec')
	 **/
	public function getImageColors($image, $return_array = false, $limit_results = 10, $exclude = false) {


		/*
		 * Get Image Info
		 */
		$image_info = $this->getImageInfo($image);
		if(!is_array($image_info))
			return $image_info;


		/*
		 * Create Image Resource
		 */
		if(isset($image_info['mime'])) {

			switch($image_info['mime']) {
				/* Jpeg Image */
				case('image/jpeg'):
				case('image/pjpeg'): $return_img = imagecreatefromjpeg($image_info['image_handler']); break;
				/* PNG Image */
				case('image/png'):   $return_img = imagecreatefrompng($image_info['image_handler']); break;
				/* GIF Image */
				case('image/gif'):   $return_img = imagecreatefromgif($image_info['image_handler']); break;
				default:
					return array('status' => 'error', 'message' => $this->error_message['no_mime_type']);
			}
			$memory_usage = $this->getImageMemory($image_info);
			if(!$memory_usage)
				return array('status'=>'error','message'=> $this->error_message['no_memory']);

		} else {

			$return_img = $image;

		}
		imagealphablending($return_img, true);


		/*
		 * Get Colors From Pixel by Pixel
		 */
		$x_axis = 1;
		$y_axis = 1;
		$total_pixels = ($image_info['width']-1) * ($image_info['height']-1);

		$colors = array();
		for($i=0; $i<$total_pixels; $i++) {

			$get_colors = imagecolorat($return_img, $x_axis, $y_axis);
			$r = ($get_colors >> 16) & 0xFF;
			$g = ($get_colors >> 8) & 0xFF;
			$b = $get_colors & 0xFF;

			$colors[] = $r . '|' . $g . '|' . $b;
			if($x_axis == ($image_info['width'] - 1)) {
				$x_axis = 0;
				++$y_axis;
			}
			$x_axis++;

		}


		/*
		 * If the response is an array

		 */
		if($exclude) {

			$exclude_list = array();
			foreach($exclude as $value) {
				$value = $this->_hex2rgb($value);
				$exclude_list[] = str_replace(',','|',$value);
			}

		}


		/*
		 * Rearange pixel colors
		 */
		$return_colors = array();
		foreach($colors as $value) {

			if(array_key_exists($value, $return_colors))
				++$return_colors[$value];
			else
				$return_colors[$value] = 1;

		}
		arsort($return_colors);

		$total_colors = array_sum($return_colors);
		$limit_pallete = $limit_results;
		$start_pallete = 0;

		$print_color = array();
		foreach($return_colors as $key=>$value) {

			if(isset($exclude_list) && !in_array($key, $exclude_list)) {

				$print_color[$start_pallete]['color']      = explode('|',$key);
				$print_color[$start_pallete]['percentage'] = round($value * 100 / $total_colors, 2);
	
				$start_pallete++;
				if($start_pallete == $limit_pallete)
					break;

			}

		}


		/*
		 * If $return_array not false
		 */
		if($return_array) {


			$return_array = array();
			foreach($print_color as $key=>$value)
				$return_array[] = $this->_rgb2hex($value['color']);

			return $return_array;

			
		}


		/**
		 * Create Color Image
		 **/
		$block_size = 50;
		$return_image_height = $block_size * $limit_results;
		$memory_usage = $this->getImageMemory(array('width'=>200, 'height'=>$return_image_height));
		if(!$memory_usage)
			return array('status'=>'error','message'=> $this->error_message['no_memory']);

		$return_image = imagecreatetruecolor(200,$return_image_height);

		$y_fill = 0;
		$textcolor_white = imagecolorallocate($return_image, 255, 255, 255);
		$textcolor_black = imagecolorallocate($return_image, 0, 0, 0);


		/*
		 * Write Return Image
		 */
		foreach($print_color as $key=>$value) {


			$current_color = imagecolorallocate($return_image, $value['color'][0], $value['color'][1], $value['color'][2]);
			$current_color_hex = $this->_rgb2hex($value['color']);

			imagefilledrectangle($return_image, 0, $y_fill, 200, $y_fill + $block_size, $current_color);


			if( ($value['color'][0] + $value['color'][1] + $value['color'][2]) <= 255 )
				imagestring($return_image, 20, 70, $y_fill + 10, $current_color_hex, $textcolor_white);
			else
				imagestring($return_image, 20, 70, $y_fill + 10, $current_color_hex, $textcolor_black);
			
			$y_fill = $y_fill + $block_size;


		}

		return $return_image;


	} // getImageColors()



	/**
	 * Create image gradient
	 *    @ $width - image gradient container width
	 *    @ $height - image gradient container height
	 *    @ $startColor* - gradient start color in hex. Ex: #fff, #df0000, ececec OR transparent
	 *    @ $endColor* - gradient end color in hex. Ex: #fff, #df0000, ececec OR transparent
	 *    * there can be only one transparent color
	 *    @ $vertical - vertical or horizontal direction (true if vertical, false if horizontal)
	 *    = return image resource
	 **/
	public function createGradinet($width, $height, $startColor, $endColor, $vertical = true) {


		/*
		 * Verify memory usage
		 */
		$memory_usage = $this->getImageMemory(array('width'=>$width, 'height'=>$height));
		if(!$memory_usage)
			return array('status'=>'error','message'=> $this->error_message['no_memory']);


		/*
		 * Creating transparent base image
		 */
		$base_image = imagecreatetruecolor($width, $height);
		imagealphablending($base_image, false);
		$transparent_color = imagecolorallocatealpha($base_image, 0, 0, 0, 127);
		imagefill($base_image, 0, 0, $transparent_color);


		/*
		 * Setting line numbers and image width (row width)
		 */
		if($vertical) {
			$lines      = $height;
			$line_width = $width;
		} else {
			$lines      = $width;
			$line_width = $height;
		}


		$start_rgb = ($startColor != 'transparent') ? $this->_hex2rgb($startColor, true) : 'transparent';
		$stop_rgb  = ($endColor != 'transparent')   ? $this->_hex2rgb($endColor, true)   : 'transparent';


		/*
		 * Creating image
		 */
        for($i = 0; $i < $lines; $i++) {


			if($start_rgb == 'transparent' || $stop_rgb == 'transparent') {


				$transparent_unit = intval($lines / 127);
				$transparent_unit = ($transparent_unit == 0) ? 1 : $transparent_unit;

				if($start_rgb == 'transparent') {

					$opacity = 127 - $i / $transparent_unit;
					$opacity = ($opacity <= 0 ) ? 0 : $opacity;

					$red_color   = $stop_rgb[0];
					$green_color = $stop_rgb[1];
					$blue_color  = $stop_rgb[2];

				} else {

					$opacity = 0 + $i / $transparent_unit;
					$opacity = ($opacity >= 127 ) ? 127 : $opacity;

					$red_color   = $start_rgb[0];
					$green_color = $start_rgb[1];
					$blue_color  = $start_rgb[2];

				}

				$current_color = imagecolorallocatealpha($base_image, $red_color, $green_color, $blue_color, $opacity);

			
			} else {

				$red_color   = ($stop_rgb[0] - $start_rgb[0] != 0) ? intval( $start_rgb[0] + ($stop_rgb[0] - $start_rgb[0]) * ($i / $lines) ) : $start_rgb[0];
				$green_color = ($stop_rgb[1] - $start_rgb[1] != 0) ? intval( $start_rgb[1] + ($stop_rgb[1] - $start_rgb[1]) * ($i / $lines) ) : $start_rgb[1];
				$blue_color  = ($stop_rgb[2] - $start_rgb[2] != 0) ? intval( $start_rgb[2] + ($stop_rgb[2] - $start_rgb[2]) * ($i / $lines) ) : $start_rgb[2];

				$current_color = imagecolorallocate($base_image, $red_color, $green_color, $blue_color);

			}
			if($vertical)
				imagefilledrectangle($base_image, 0, $i, $line_width, $i, $current_color);
			else
				imagefilledrectangle($base_image, $i, 0, $i, $line_width, $current_color);

		}


		imagesavealpha($base_image, true);
		return $base_image;


	} // createGradinet()



	/**
	 * Create text box
	 *    @ $string - a string (that can contain <br/>, <br>, \n, \r for wraping long lines)
	 *    @ $color - text color in hex format. Ex: #000
	 *    @ $size - either 1-5 for normal GD font or other size for loading otf/ttf fonts
	 *    @ $align - left/center/rigth
	 *    @ $line-height - text line-height
	 *    @ $font-file - OTF or TTF font files
	 *    = return image resource
	 **/
	public function textBox($string, $color = '#000', $size = 3, $align = 'left', $lineheight = false, $fontfile = false) {


		/*
		 * Verify if Free Type is supported
		 */
		$ft_support = $this->_verifyFT();
		if($ft_support != NULL) {
			echo $ft_support;
			exit();
		}


		/*
		 * Parse string
		 */
		$string = str_replace(array('<br />','<br/>',"\n","\r","\n\r",'<BR />','<BR/>','<BR>',"\R","\N","\N\R"), '<br>', $string);
		$string = explode('<br>',$string);


		/*
		 * Calculating parameters
		 */
		if($fontfile) {
			/*
			 * If font is a OTF or TTF Font
			 */
			$string_width = array();
			$string_heigth= array();
		
			$left_position = array();
			$top_position  = array();
		
			foreach($string as $value) {
		
				$stringbox = imagettfbbox($size, $angle, $fontfile, $value); 
			
				$minX = min( array($stringbox[0], $stringbox[2], $stringbox[4], $stringbox[6]) ); 
				$maxX = max( array($stringbox[0], $stringbox[2], $stringbox[4], $stringbox[6]) ); 
				$minY = min( array($stringbox[1], $stringbox[3], $stringbox[5], $stringbox[7]) ); 
				$maxY = max( array($stringbox[1], $stringbox[3], $stringbox[5], $stringbox[7]) ); 
			
				$string_width[]  = ($maxX - $minY); 
				$string_height[] = ($maxY - $minY); 

				$left_position[] = abs($minX);
				$top_position[]  = abs($minY);
		
			}

			/*
			 * Calculate largest width string, single lineheight
			 */
			$single_line_height = max($string_height);
			$max_width    = max($string_width);
			$top_position = max($top_position);

		} else {

			/*
			 * If base GD font
			 */

			/*
			 * Calculate largest width string, single lineheight
			 */
			$single_line_height = imagefontheight($size);
			$top_position = 0;

			$max_length = 0;
			foreach($string as $value)
				$max_length = (strlen($value) > $max_length) ? strlen($value) : $max_length;
	
			$font_width = imagefontwidth($size);
			$max_width = $font_width * $max_length;

		}

		/*
		 * Full Line-heiht
		 */
		$single_line_full_height = ($lineheight) ? ($single_line_height * ($lineheight / $size)) : $single_line_height;

		/*
		 * Lineheight parameters
		 */
		$line_height_top    = ($lineheight) ? round(($single_line_full_height - $single_line_height) / 2) : 0;
		$line_height_bottom = ($lineheight) ? ($single_line_full_height - $single_line_height - $line_height_top) : 0;


		/*
		 * Establishing base image width and heigth and creating base image
		 */
		$width = $max_width;
		$height= $single_line_full_height * count($string);	

		$base_image = imagecreatetruecolor($width, $height);
		imagealphablending($base_image, false);
		$transparent_color = imagecolorallocatealpha($base_image, 255, 255, 255, 0); //127);
		imagefill($base_image, 0, 0, $transparent_color);


		/*
		 * Setting text color
		 */
		$color = $this->_hex2rgb($color, true);
		$textcolor = imagecolorallocate($base_image, $color[0], $color[1], $color[2]);


		if($fontfile) {

			/*
			 * Calculating $x_pos for text align
			 */
			foreach($string as $key=>$value) {

				switch($align) {
					case('left'):
						$x_pos = 0;
						break;
					case('center'):
						$x_pos = round($width - $string_width[$key]) / 2;
						break;
					case('rigth'):
						$x_pos = $width - $string_width[$key];
						break;
				}
				$y_pos = ($start_iteration == 0) ? ($line_height_top + $single_line_height - ($single_line_height - $top_position)) :
													(($line_height_top + $line_height_bottom + $single_line_height) * $start_iteration) + ($line_height_top + $single_line_height - ($single_line_height - $top_position));
				imagettftext($base_image, $size, 0, $x_pos, $y_pos, $textcolor, $fontfile, $value);
				++$start_iteration;

			}

		} else {
			/*
			 * Calculating $x_pos for text align
			 */
			$start_iteration = 0;
			foreach($string as $value) {
	
				switch($align) {
					case('left'):
						$x_pos = 0;
						break;
					case('center'):
						$x_pos = $max_length - strlen($value);
						$x_pos = ($x_pos == 0) ? 0 : round($x_pos / 2) * $font_width;
						break;
					case('rigth'):
						$x_pos = $max_length - strlen($value);
						$x_pos = ($x_pos == 0) ? 0 : $font_width * $x_pos;
						break;
				}
				$y_pos = ($start_iteration == 0) ? $line_height_top : (($line_height_top + $line_height_bottom + $single_line_height) * $start_iteration) + $line_height_top;
	
				imagestring($base_image, $size, $x_pos, $y_pos, $value, $textcolor);
				++$start_iteration;
			}
		}


		imagesavealpha($base_image, true);


		/*
		 * Return image
		 */
		return $base_image;


	} // textBox()



	/**
	 * Display Image
	 *    @ $image - image object returned by resize() or crop()
	 *    @ $type - JPG/PNG/GIF
	 *    @ $quality - a value between 0 to 100 (only for JPEG and PNG)
	 *    = return image
	 **/
	public function display($image, $type = false, $quality = false) {


		/*
		 * Verify if $image is a resource
		 */
		$image_type = gettype($image);
		if($image_type != 'resource')
			return $this->error_message['no_resource'];

		$parameters = array('type'    => strtolower($type),
							'quality' => $quality);

		return $this->_output($image, $parameters);


	} // display()


	/**
	 * Store Image
	 *    @ $image - image object returned by resize() or crop()
	 *    @ $path - path and filename
	 *    @ $type - JPG/PNG/GIF
	 *    @ $quality - a value between 0 to 100 (only for JPEG and PNG)
	 *    = return true/false
	 **/
	public function store($image, $path, $type, $quality = false) {


		/*
		 * Verify if $image is a resource
		 */
		$image_type = gettype($image);
		if($image_type != 'resource')
			return $this->error_message['no_resource'];


		$parameters = array('type'    => strtolower($type),
							'quality' => $quality,
							'path'    => $path);
		return $this->_output($image, $parameters);


	} // store()



	/**
	 * Destroy Image
	 *    @ $image - image object returned by resize() or crop()
	 **/
	public function destroy($image) {


		$imageInfo = $this->getImageInfo($image);
		$memory_usage = $this->getImageMemory($imageInfo, true);
		$this->memory['used_memory'] = $this->memory['used_memory'] - $memory_usage;
		@imagedestroy($image);


	} // destroy()



    
	/**
	 * Read Exif From Image
	 * ---------------------------
	 * @image - image file path, $_FILES var or GD image resource
	 * 
	 **/
	/**
	 * Read Exif From Image
	 *    @ $image - image object returned by resize() or crop()
	 *    @ $detailed - translated values
	 *    @ $show_empty - wheather to hide or not empty values
	 *    = return array
	 **/
	function readExif($image, $detailed = false, $show_empty = false) {


		/*
		 * Get Exif Explications
		 */
		$tmp = $this->exif_explication;

		/*
		 * Read Exif Data From Image
		 */
		$exif_data = exif_read_data($image);

		$exif = array();

		$exif['Make']     = isset($exif_data['Make'])  ? trim($exif_data['Make'])  : '';
		$exif['Model']    = isset($exif_data['Model']) ? trim($exif_data['Model']) : '';
		$exif['Model']    = isset($exif_data['Model']) ? trim($exif_data['Model']) : '';
		if(isset($exif_data['DateTime'])) {
			$exif_data['DateTime'] = explode(' ', $exif_data['DateTime']);
			$exif['Date'] = $exif_data['DateTime'][0];
			$exif['Time'] = $exif_data['DateTime'][1];
		} else {
			$exif['Date'] = '';
			$exif['Time'] = '';
		}

		$exif['Shutter Speed'] = isset($exif_data['ExposureTime']) ? trim($exif_data['ExposureTime']) : '';
		$exif['Aperture'] = isset($exif_data['FNumber']) ? 'F' . trim($exif_data['FNumber'] / 10) : '';
		$exif['Focal Length'] = '';
		if(isset($exif_data['FocalLength'])) {
			$exif_data['FocalLength'] = trim($exif_data['FocalLength']);
			$exif_data['FocalLength'] = explode('/',$exif_data['FocalLength']);
			if(count($exif_data['FocalLength']) == 2)
				$exif_data['FocalLength'] = $exif_data['FocalLength'][0] / $exif_data['FocalLength'][1];
			else
				$exif_data['FocalLength'] = '';
	
			$exif['Focal Length'] = ($exif_data['FocalLength'] != '') ? $exif_data['FocalLength'] . 'mm' : '';
		}
		$exif['ISO'] = isset($exif_data['ISOSpeedRatings']) ? trim($exif_data['ISOSpeedRatings']) : '';

		$exif['ExposureCompensation'] = '';
		if(isset($exif_data['ExposureBiasValue'])) {
			$exif['ExposureCompensation'] = explode('/',trim($exif_data['ExposureBiasValue']));
			$exif['ExposureCompensation'] = $exif['ExposureCompensation'][0] / $exif['ExposureCompensation'][1] . ' EV';
		}
		$exif['Flash']       = (isset($exif_data['Flash']) && array_key_exists($exif_data['Flash'], $tmp['Flash']) ) ? $tmp['Flash'][$exif_data['Flash']] : '';

		/*
		 * Detailed Information
		 */
		if($detailed) {

			$exif['35mmFocalLength'] = isset($exif_data['FocalLengthIn35mmFilm']) ? trim($exif_data['FocalLengthIn35mmFilm']) . 'mm' : '';

			$exif['XResolution'] = '';
			if(isset($exif_data['XResolution'])) {
				$exif['XResolution'] = explode('/',trim($exif_data['XResolution']));
				$exif['XResolution'] = $exif['XResolution'][0] / $exif['XResolution'][1];
			}
			$exif['YResolution'] = '';
			if(isset($exif_data['YResolution'])) {
				$exif['YResolution'] = explode('/',trim($exif_data['YResolution']));
				$exif['YResolution'] = $exif['YResolution'][0] / $exif['YResolution'][1];
			}

			$exif['Metering']        = isset($exif_data['MeteringMode']) ? $tmp['MeteringMode'][$exif_data['MeteringMode']] : '';
			$exif['WhiteBalance']    = isset($exif_data['WhiteBalance']) ? $tmp['WhiteBalance'][$exif_data['WhiteBalance']] : '';
			$exif['ExposureMode']    = isset($exif_data['ExposureMode']) ? $tmp['ExposureMode'][$exif_data['ExposureMode']] : '';
			$exif['Contrast']        = isset($exif_data['Contrast'])     ? $tmp['Contrast'][$exif_data['Contrast']] : '';
			$exif['Sharpness']       = isset($exif_data['Sharpness'])    ? $tmp['Sharpness'][$exif_data['Sharpness']] : '';
			$exif['Saturation']      = isset($exif_data['Saturation'])   ? $tmp['Saturation'][$exif_data['Saturation']] : '';
			$exif['SubjectDistance'] = isset($exif_data['SubjectDistanceRange'])   ? $tmp['SubjectDistanceRange'][$exif_data['SubjectDistanceRange']] : '';
			$exif['ExposureProgram'] = (isset($exif_data['ExposureProgram']) && $exif_data['ExposureProgram'] < 9) ? $tmp['ExposureProgram'][$exif_data['ExposureProgram']] : '';

			$exif['LigthSource'] = (isset($exif_data['LightSource']) && array_key_exists($exif_data['LightSource'], $tmp['LightSource']) ) ? $tmp['LightSource'][$exif_data['LightSource']] : '';
			$exif['Color'] = (isset($exif_data['ColorSpace']) && $exif_data['ColorSpace'] == 1) ? 'sRGB' : 'Other than sRGB';

		} // end of $detailed


		/*
		 * Clear Empty Values
		 */
		if(!$show_empty) {
			foreach($exif as $key=>$value) {
				if(empty($value))
					unset($exif[$key]);

			}
		} // $show_empty
		
		return $exif;

	}


} // End of Image class
?>