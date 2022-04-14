<?php
if(!isset($_POST['download_image']))
{
    getTheFileForum();
}else{
    global $inserCounetr;
    global $updateCounter;
    $inserCounetr = 0;
    $updateCounter = 0 ;
    $storagename = "images.csv";
    move_uploaded_file($_FILES["image_download"]["tmp_name"], "uploads/files/" . $storagename);
    $loc = 'uploads/files/' . $storagename ;
	$file = fopen($loc,"r");
	while(! feof($file))
	{
		set_time_limit ( 60 );
		$row = fgetcsv($file);
		$rowSize = count($row);		
		for($j = 0 ; $j < $rowSize; $j++ )
		{
			$url = trim($row[$j]);
			$url = strstr($url, 'http');
			if($url !== '' && !empty($url))
			{
				if(!downloadImage( $url, $updateCounter, $j )){
					echo "Not Downloaded $updateCounter : $j -> $url<br>";
				}
			}
		}		
		$updateCounter++;
	}
	fclose($file);
	echo "Operation Finish";
}

function downloadImage( $image_url, $index, $subIndex )
{
	$download = false;
	$content = file_get_contents($image_url);	
	
	$imgExtension = explode("?",pathinfo($image_url, PATHINFO_EXTENSION));
	$imgExtension = $imgExtension[0];
	if($imgExtension == '')
	{
		$imgExtension = "jpg";
	}
	$destination = 'uploads/images/image-'.$index.'-'.$subIndex.'.'.$imgExtension;
	$result = array('url' => false,'destination' => $destination);
	if($content)
	{
		$isimgdownlaod = file_put_contents($destination, $content);
		if($isimgdownlaod != false)
		{
			$download	= true;
		}
	}
	return $download;
}

function getTheFileForum(){
?>
    <div class="form-group col-lg-12 col-md-12 col-lg-12 col-xs-12">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
            <input class="form-field" type="file" name="image_download" id="image_download">
            <input type="submit" class="btn btn-add" name="download_image" value="Download Images">
        </form>
    </div>
<?php
}
?>