<html>
<head>
	<title>ImageClass</title>
	<style type="text/css">
	html,body { left:0; top:0; }
	body {
		font:font:13px/20px "Trebuchet MS", Arial, Helvetica, sans-serif;
		color:#222;
	}
	.limit { width:1000px; }
	.center{ margin:0 auto;}
	h1 {
		font-size:34px;
		line-height:40px;
	}
	h2 {
		font-size:24px;
		line-height:30px;
		border-bottom:1px solid #222;
	}
	.code {
		margin:10px 0;
		border:1px solid #d8d8d8;
		background:#f3f3f3;
		padding:10px;
	}
	</style>
</head>
<body>
	<div class="limit center">
		<h1>ImageClass Examples</h1>
		<p>You can find all the examples in the comments inside the <em>ImageClass.php</em> files.</p>
		<p>Please feel free to participate with anything you thing it will help (even on completing this example page).</p>

		<p><strong>Original Image:</strong></p>
		<img src="../img/example_image.jpg" class=limit>

		<h2>Resizing and Image</h2>
		<div>
			<p class="code">
			<?php
			$file = file_get_contents('resize.php');
			echo highlight_string($file, true);
			?>
			</p>
			<p><strong>Result:</strong></p>
			<img src="resize.php">
		</div>

		<h2>Resizing and Croping Image without background</h2>
		<p>If the image aspect ratio and the new size aspect ratio are not the same, some of the image will be cropped</p>
		<div>
			<p class="code">
			<?php
			$file = file_get_contents('crop_without_background.php');
			echo highlight_string($file, true);
			?>
			</p>
			<p><strong>Result:</strong></p>
			<img src="crop_without_background.php">
		</div>
		<h2>Resizing and Croping Image with background</h2>
		<p>If the image aspect ratio and the new size aspect ratio are not the same, the difference will be filled with a background color</p>
		<div>
			<p class="code">
			<?php
			$file = file_get_contents('crop_with_background.php');
			echo highlight_string($file, true);
			?>
			</p>
			<p><strong>Result:</strong></p>
			<img src="crop_with_background.php">
		</div>

	</div>
</body>
</html>