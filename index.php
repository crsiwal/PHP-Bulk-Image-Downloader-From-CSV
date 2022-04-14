<?php
if(!isset($_POST['download_image']))
{
    getTheFileForum();
}else{
    global $inserCounetr;
    global $updateCounter;
	define ('SITE_ROOT', realpath(dirname(__FILE__)));
    $inserCounetr = 0;
    $updateCounter = 0 ;
    $user = md5( uniqid(rand(), true) );
	$storagename = $user . ".csv";
	$directory = 'uploads/images/' . $user . '/';
	
	if (!file_exists($directory)) {
		mkdir($directory, 0777, true);
	}
    move_uploaded_file( $_FILES["image_download"]["tmp_name"], SITE_ROOT. "/uploads/files/" . $storagename );
    $loc = 'uploads/files/' . $storagename ;
	$file = fopen($loc,"r");
	while(! feof($file))
	{
		set_time_limit ( 60 );
		$row = fgetcsv($file);
		$rowSize = count( $row );
		for($j = 0 ; $j < $rowSize; $j++ )
		{
			$url = trim($row[$j]);
			$url = strstr($url, 'http');
			if($url !== '' && !empty($url))
			{
				if(!downloadImage( $url, $updateCounter, $j , $user )){
					echo "Not Downloaded $updateCounter : $j -> $url<br>";
				}
			}
		}
		$updateCounter++;
	}
	fclose($file);
	
/** Now compress this folder **/


$zip_name = $user;
$zip_directory = $directory;
$zip = new zip( $zip_name, $zip_directory );
$zip->add_directory( $directory );
$zip->save();
$zip_path = $zip->get_zip_path();

if (file_exists( $zip_path )) {
	header( "Pragma: public" );
	header( "Expires: 0" );
	header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
	header( "Cache-Control: public" );
	header( "Content-Description: File Transfer" );
	header( "Content-type: application/zip" );
	header( "Content-Disposition: attachment; filename=\"" . $zip_name . ".zip\"" );
	header( "Content-Transfer-Encoding: binary" );
	header( "Content-Length: " . filesize( $zip_path ) );
	readfile( $zip_path );
    exit;
}
}

function downloadImage( $image_url, $index, $subIndex, $location )
{
	$download = false;
	$content = file_get_contents($image_url);	
	
	$imgExtension = explode("?",pathinfo($image_url, PATHINFO_EXTENSION));
	$imgExtension = $imgExtension[0];
	if($imgExtension == '')
	{
		$imgExtension = "jpg";
	}
	$destination = 'uploads/images/'.$location.'/image-'.$index.'-'.$subIndex.'.'.$imgExtension;
	$result = array( 'url' => false,'destination' => $destination );
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



class zip
{
   private $zip;
   public function __construct( $file_name, $zip_directory)
    {
        $this->zip = new ZipArchive();
        $this->path = dirname( __FILE__ ) . '/uploads/compressed/' . $file_name . '.zip';
        $this->zip->open( $this->path, ZipArchive::CREATE );
    }
      
   /**
     * Get the absolute path to the zip file
     * @return string
     */
    public function get_zip_path()
    {
        return $this->path;
    }
       
    /**
     * Add a directory to the zip
     * @param $directory
     */
    public function add_directory( $directory )
    {
        if( is_dir( $directory ) && $handle = opendir( $directory ) )
        {
            $this->zip->addEmptyDir( 'images/' );
            while( ( $file = readdir( $handle ) ) !== false )
            {
				if (!is_file($directory . '/' . $file))
                {
					if (!in_array($file, array('.', '..')))
                    {
						$this->add_directory($directory . '/' . $file );
                    }
                }
                else
                {
					$this->add_file($directory . '/' . $file, 'images/' . $file );
				}
            }
        }
    }
   
    /**
     * Add a single file to the zip
     * @param string $path
     */
    public function add_file( $path , $filename )
    {
        $this->zip->addFile( $path, $filename );
    }
   
    /**
     * Close the zip file
     */
    public function save()
    {
        $this->zip->close();
    }
}




function getTheFileForum(){
?>
    <div class="form-group col-lg-12 col-md-12 col-lg-12 col-xs-12">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
            <input class="form-field" type="file" name="image_download" id="image_download" accept=".csv">
            <input type="submit" class="btn btn-add" name="download_image" value="Download Images">
        </form>
    </div>
<?php
}
?>