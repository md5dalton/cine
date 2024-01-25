<?php
/*ini_set('max_execution_time', '0');
ini_set('post_max_size', '0');
ini_set('upload_max_filesize', '0');
ini_set('max_file_uploads', '0');
*/
echo "<pre>";

$uploads_directory = 'uploads/';

if (!is_dir($uploads_directory)) mkdir($uploads_directory);

if (isset($_FILES)) {


	foreach ($_FILES as $f) {

		$f = (object) $f;
		
		$destination_file = $uploads_directory . $f->name;

		if (file_exists($destination_file)) $destination_file  = $uploads_directory . time() . $f->name;

		if (move_uploaded_file($f->tmp_name, $destination_file)) echo "Uploaded\n\n"; else echo "Uploaded\n\n"; 

		print_r($f);

	}
}



?>