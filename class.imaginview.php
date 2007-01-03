<?
/**
* ImaginView - A Photo Gallery script in PHP
*
* ImaginView (class.imaginview.php) is a php class to build a photo gallery.
* It's small, simple, and easy to implement.
*
* @package ImaginView
* @author Frank Rosquin <frank@nullsense.net>
* @link http://www.nullsense.net/development/imaginview/ ImaginView HomePage
* @version V0.6beta-8
* @copyright Frank Rosquin
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
*/

// Last commit by: $Author: cnf $
// Last commit done on: $Date: 2005-03-10 18:33:40 +0100 (Thu, 10 Mar 2005) $

/**
* ImaginView Class
* 
* Main class. This is where it all happenes baby!
*
* @package ImaginView
* @author Frank Rosquin <frank@nullsense.net>
* @access public
*/
class imaginView
{
	// You should not edit these variables.
	// They are just defaults. 
	// Please use your index php file for changing settings.
	/**
	* @var string $version ImaginView version
	*/
	var $version = '0.6-svn $Rev: 71 $';
	
	/**
	* @var string $photo_dir Full path to the Gallery dir (where the pictures are)
	*/
	var $photo_dir = "/please/read/the/documentation";

	/**
	* @var string $cache_dir Full path to the cache dir (where cached thumbnails are kept)
	*/
	var $cache_dir = "/tmp/";
	
	/**
	* @var string $photo_url URL to the Gallery dir. $folder_pic should be kept here.
	* this can be the full url (http://yourdomain.com/Gallery) or just the path part (/Gallery)
	* @see $folder_pic
	* @see $photo_dir
	*/
	var $photo_url = "/Gallery";

	/**
	* @var string $folder_pic the name of the picture used to represent a folder in the gallery view.
	* This picture MUST reside in $photo_url
	* @see $photo_url
	* @see $photo_dir
	*/
	var $folder_pic = "folder.jpg";

	/**
	* @var string $outline_color Color of the thumbnail background.
	* @see $outline_border
	*/
	var $outline_color = "#FFCCCC";

	/**
	* @var string $outline_border The color of the 1 pixel border around the thumbnail. Set this to FALSE to disable border creation.
	* @see $outline_color
	*/
	var $outline_border = FALSE;

	/**
	* @var integer $pics_per_row The number of pictures there are in 1 row (left to right).
	* @see $rows
	*/
	var $pics_per_row = 4;

	/**
	* @var integer $rows The number of rows there are.
	* @see $pics_per_row
	*/
	var $rows = 4;

	/**
	* @var integer $thumb_max The size of the thumbnails. thumbnails are created as squares. This sets the dimention of said squares.
	*/
	var $thumb_max = 120;

	/**
	* @var integer $photo_max The maximum size of the image in detail view. 
	* ImaginView will calculate which side is the largest, and set that one to $photo_max. No rescaleing is actually done, 
	* it uses plain html tags. It's only use is so large pictures won't upset your sites layout. Unset to disable.
	*/
	var $photo_max = 600;

	/**
	* @var string $exif Unused at this time.
	*/
	var $exif = "off";

	/**
	* @var string $header Name of your header file.
	* @see $footer
	*/
	var $header = "";

	/**
	* @var string $footer Name of your footer file.
	*/
	var $footer = "";

	/**
	* @var string $template_power Path to the TemplatePower class.
	*/
	var $template_power = "class.TemplatePower.inc.php";

	/**
	* @var string $template Path to the ImaginView template.
	*/
	var $template = "imaginview.tpl";


	/**
	* Constructor.
	*
	* function that sets everything up.
	*
	* @param array $config array with config settings
	* @access public
	* @return void
	*/
	function imaginView($config)
	{
		session_start();

		// Detect if we are running windows
		define( "_CUR_OS", substr( php_uname( ), 0, 7 ) == "Windows" ? "Win" : "_Nix" );
		if (_CUR_OS == "Win") {
			define("_SLASH", '\\');
		} else {
			define("_SLASH", '/');
		}

		//////////////////////////////////////////
		// Enable/Disable debugging
		if (!isset($config['debug']) || $config['debug'] == "" || $config['debug'] == "none") {
			error_reporting(0);
		} else if ($config['debug'] == 'all') {
			$this->debug = $config['debug'];
			error_reporting(E_ALL);
		} else {
			$this->debug = $config['debug'];
			error_reporting(E_ALL ^ E_NOTICE);
		}

		//////////////////////////////////////////
		// Build config options
		foreach ($config as $key => $value) {
			if (isset($this->$key))
				$this->$key = $value;
		}

		//////////////////////////////////////////
		// Defines for img type detection (need to check where i got these)
		define('GIF_SIG', "\x47\x49\x46");
		define('JPG_SIG', "\xFF\xD8\xFF");
		define('PNG_SIG', "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A");
		define('RD_BUF', 512);           /* amount of data to initially read */
		

		//////////////////////////////////////////
		// Call correct action
		if (isset($_GET['thumbnail'])) {
			$_GET['thumbnail'] = ereg_replace('^/', '', $_GET['thumbnail']);
			$this->__thumbNail($this->__urldecode($_GET['thumbnail']));
		} else if (isset($_GET['image'])) {
			$this->__showImage($this->__urldecode($_GET['image']));
		} else {
			if (!isset($_GET['dir'])) {
				$_GET['dir'] = "";
			}
			$this->__showDir($this->__urldecode($_GET['dir']));
		}


		//////////////////////////////////////////
		// Devel debugging
		if ($this->debug == 'all') {
			echo "<pre>\n";
				print_r($_SESSION);
				print_r($_SERVER);
				print_r($this);
			echo "</pre>\n";
		} else if ($this->debug == 'debug') {
			echo "<pre>\n";
				print_r($this);
			echo "</pre>\n";
		}
		
	}

	/**
	* Called when loading a thumbnail.
	*
	* Function to output a thumbnail. Calls __makeTypeThumbl() when no cached thumbnail exists.
	* 
	* @param string $get_thumbnail Contains $_GET['thumbnail'], passed by imaginView()
	* @access private
	* @return void
	* @see imaginView()
	* @see __makeTypeThumbl()
	*/
	function __thumbNail($get_thumbnail) 
	{
		$photo = $this->photo_dir . "/" . stripslashes($get_thumbnail);
		if (_CUR_OS == "Win")
			$photo = str_replace("/", "\\", $photo);

		if (realpath($photo) != $photo) 
			exit;
	
		$thumbnail = str_replace("/", "_", $get_thumbnail);

		header("Content-type: image/png");

		// Cache control
		header("Pragma: public");
		$offset = 60 * 60 * 24 * 3;
		Header("Cache-Control: max-age=$offset, s-maxage=$offset, public, must-revalidate");
		$expires = gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		header('Expires: ' . $expires);

		$cache_file = $this->cache_dir . "/" . "$thumbnail";
		if (file_exists($cache_file)) {
			header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($cache_file)) . " GMT");
			header("ETag: \"". md5($cache_file) ."\"");
			header('Content-Length: ' . filesize($cache_file));
		}
		header('Content-Disposition: inline; filename="tn_'.$thumbnail.'"');

		if ((!file_exists($cache_file)) || 
			(filemtime($cache_file) < filemtime($photo))) {
				$this->__makeTypeThumbl($photo, $get_thumbnail);
		}
	
		readfile($cache_file);
		exit;
	}

	/**
	* Checks image type, and calls __createThumb().
	*
	* @param string $photo Full path to the image we want a thumbnail for.
	* @param string $get_thumbnail Contains $_GET['thumbnail']
	* @access private
	* @return void
	* @see __createThumb()
	* @see __thumbNail()
	* @see __getImgType()
	* @see imaginView()
	*/
	function __makeTypeThumbl($photo, $get_thumbnail) 
	{
		$type = $this->__getImgType($photo);
		$size = getimagesize($photo);

		if ($type == "JPG") {
			$src_img = imagecreatefromjpeg($photo);
			$this->__createThumb($src_img, $size, $get_thumbnail);
		} elseif ($type == "GIF") {
			$src_img = imagecreatefromgif($photo);
			$this->__createThumb($src_img, $size, $get_thumbnail);
		} elseif ($type == "PNG") {
			$src_img = imagecreatefrompng($photo);
			$this->__createThumb($src_img, $size, $get_thumbnail);
		} else {
			$this->createERRORThumb($photo, $get_thumbnail);
		}
	}

	/**
	* Creates the actual thumbnail, and writes it to disl.
	*
	* @param binary $src_img Output from imagecreatefrom*()
	* @param string $size Output from getimagesize()
	* @param string $get_thumbnail Contains $_GET['thumbnail']
	* @access private
	* @return void
	* @see __makeTypeThumbl()
	* @see imaginView()
	*/
	function __createThumb($src_img, $size, $get_thumbnail)
	{
		//$src_img = imagecreatefromjpeg($photo);

		//$size = getimagesize($photo);
		$origw = $size[0];
		$origh = $size[1];
		
		if ($origw > $origh) {
			$neww = $this->thumb_max - 2;
			$diff = $origw / $neww;
			$newh = $origh / $diff;
		} else {
			$newh = $this->thumb_max - 2;
			$diff = $origh / $newh;
			$neww = $origw / $diff;
		}

		$dst_img = imagecreatetruecolor($neww, $newh);
	
		if (function_exists('imagecopyresampled')) {
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $neww, $newh, $origw, $origh);
		} else {
			imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $neww, $newh, $origw, $origh);
		}
		imagedestroy($src_img);
	
		if ($neww > $newh) {
			$startx = 1;
			$starty = ($this->thumb_max / 2) - ($newh / 2);
		} else {
			$startx = ($this->thumb_max / 2) - ($neww / 2);
			$starty = 1;
		}

		$outline_img = imagecreatetruecolor($this->thumb_max, $this->thumb_max);
		$this->outline_color = ereg_replace("#", "", $this->outline_color);
		sscanf($this->outline_color, "%2x%2x%2x", $red, $green, $blue);
		$bg_color = imagecolorallocate($outline_img, $red, $green, $blue);
		imagefill($outline_img, 0, 0, $bg_color);

		if ($this->outline_border != FALSE && $this->outline_border != $this->outline_color) {
			$this->outline_border = ereg_replace("#", "", $this->outline_border);
			sscanf($this->outline_border, "%2x%2x%2x", $red, $green, $blue);
			$border_color = imagecolorallocate($outline_img, $red, $green, $blue);
			imageline($outline_img, 0, 0, 0, 250, $border_color);
			imageline($outline_img, 0, 0, 250, 0, $border_color);
			imageline($outline_img, 0, $this->thumb_max - 1, $this->thumb_max - 1, $this->thumb_max - 1, $border_color);
			imageline($outline_img, $this->thumb_max - 1, 0, $this->thumb_max - 1, $this->thumb_max - 1, $border_color);
		}
		
		imagecopy($outline_img, $dst_img, $startx, $starty, 0, 0, $neww, $newh);
		imagedestroy($dst_img);

		$thumbnail = str_replace("/", "_", $get_thumbnail);
		$cache_file = $this->cache_dir . "/" . "$thumbnail";

		$cret = imagepng($outline_img, $cache_file);
		if (!$cret) echo "Error: Could not write cache file";
		imagedestroy($outline_img);

	}

	/**
	* Determines the type of an image by examineing the first x bytes of the file.
	*
	* @param string $URLData Full path to the image you want to type
	* @access private
	* @return string $Type The type of image (GIF, PNG, JPG or Unknown)
	* @see __makeTypeThumbl()
	*/
	function __getImgType( $URLData )
	{
	   /* initialize variables */
	   $Type = "Unknown";
  
	   /* if a filename or URL */
	   if ( (strlen($URLData) < 200 && is_file($URLData) ) || substr(strtolower($URLData), 0, 7) == 'http://' ) {
	      $FP = @fopen($URLData, 'rb');
	      if( $FP )
	         $imgData = fread( $FP, RD_BUF );
	   } else { /* is a string */
	      $imgData = $URLData;
	   }
	
		if ( $imgData ) {
			if ( substr($imgData, 0, 3) == GIF_SIG ) {
				$Type = "GIF";
			} elseif ( substr($imgData, 0, 8) == PNG_SIG ) {
				$Type = "PNG";
			} elseif ( substr($imgData, 0, 3) == JPG_SIG ) {
				$Type = "JPG";
			} else {
				$Type = "Unknown";
			}
		}
		if( $FP ) fclose ($FP); /* close file */
		return $Type;
	} 

	/**
	* Shows one image in detailed view.
	*
	* @param string $get_image Contains $_GET['image'], passed by imaginView()
	* @access private
	* @return void
	* @see imaginView()
	* @see __urlencode()
	*/
	function __showImage($get_image)
	{
		include_once($this->template_power);
		if (isset($this->header) && ($this->header != "") )
			include_once($this->header);
		$tpl = new TemplatePower("$this->template");
		$tpl->prepare();

		if ($get_image != "") {
			$path = stripslashes($get_image);

			if (substr($path, -1, 1) == "/") {
				$path = substr($path, 0, -1);
			}
 
			$fullpath = realpath($this->photo_dir . "/" . $path);
			if ($fullpath != realpath($fullpath)) exit;
		} else {
			$path = ""; 
			$fullpath = $this->photo_dir;
		}

		if ((!is_dir($fullpath)) && ($fullpath == "")) exit;

		$parts = explode("/", $path);
		$img_name = array_pop($parts);
		foreach ($parts as $part) {
			$ppath .= $part . "/";
		}

		$cur_dir = preg_replace("#^/#", "", $ppath);
		$cur_dir = preg_replace("#/$#", "", $cur_dir);
		$index_file = $this->cache_dir . "/" . str_replace("/", "_", $cur_dir) . "_index";
            
		if (!$handle = fopen($index_file, 'r')) {
		    echo "1";
		    //do other;
		}           
		while (!feof($handle)) {
			$contents .= fread($handle, 8192);
		}
		$index = unserialize($contents);
		fclose($handle);

		$first_img = $ppath . $index[0];
		$last_img = $ppath . $index[count($index) -1];
		foreach ($index as $value) {
			if ($index[0] != $img_name) {
				$prev_img = $ppath . array_shift($index);
			} else {
				if (isset($index[1])) {
					$next_img = $ppath . $index[1];
				}
				break;
			}
		}

		$size = getimagesize($fullpath);
		$origw = $size[0];
		$origh = $size[1];
		
		if ( ( ($origw > $this->photo_max) || ($origh > $this->photo_max) ) && ($this->photo_max != 0) ) {
			if ($origw > $origh) {
				$neww = $this->photo_max;
				$diff = $origw / $neww;
				$newh = $origh / $diff;
			} else {
				$newh = $this->photo_max;
				$diff = $origh / $newh;
				$neww = $origw / $diff;
			}
		} else {
			$neww = $origw;
			$newh = $origh;
		}

		$nice_name = ereg_replace("_", " ", $img_name);
		$nice_name = ereg_replace("\.(gif|GIF)", " (gif)", $nice_name);
		$nice_name = ereg_replace("\.(jpg|JPG|jpeg|JPEG)", " (jpg)", $nice_name);
		$nice_name = ereg_replace("\.(png|PNG)", " (png)", $nice_name);



		$tpl->newBlock("image");
		$tpl->assign("img_src", $this->photo_url . "/" . $this->__urlencode($get_image));
		$tpl->assign("img_width", $neww);
		$tpl->assign("img_height", $newh);
		$tpl->assign("img_orig_size", "$size[0]x$size[1]");
		$tpl->assign("img_nice_name", $nice_name);
		$tpl->assign("img_name", $img_name);
		if (is_file($fullpath.".txt")) {
			$handle = fopen($fullpath.".txt", "r");
			$descript = '';
			while (!feof($handle)) {
				$descript .= fread($handle, 1024);
			}
			fclose($handle);
			$tpl->assign("img_description", $descript);
		}

		if ($this->exif == "on") {
			$exif = exif_read_data($this->photo_dir . "/" . $path);
			if ($exif == false) {
				echo "no exif";
				break;
			}
			$tpl->newBlock("img_exif");
		}

		$tpl->newBlock("navigation");
		$tpl->assign("span", 1);
		if (isset($prev_img)) {
			$tpl->newBlock("go_prev");
			$tpl->assign("action", "image");
			$tpl->assign("argument", $this->__urlencode($prev_img));
			$tpl->assign("fargument", $this->__urlencode($first_img));
		} else {
			$tpl->newBlock("no_prev");
		}
		$tpl->newBlock("go_home");
		//$tpl->assign("home_link", $_SERVER['PHP_SELF'] . "?dir=$ppath");
		$tpl->assign("home_link", $_SESSION['imaginview']['last_path']);
		if (isset($next_img)) {
			$tpl->newBlock("go_next");
			$tpl->assign("action", "image");
			$tpl->assign("argument", $this->__urlencode($next_img));
			$tpl->assign("fargument", $this->__urlencode($last_img));
		} else {
			$tpl->newBlock("no_next");
		}
		$tpl->gotoBlock( "_ROOT" );
		$tpl->assign("iv_version", $this->version);
		$tpl->printToScreen();
		if (isset($this->footer) && ($this->footer != "") )
			include_once($this->footer);


		
	}

	/**
	* Shows a table with thumbnails.
	*
	* @param string $get_dir Contains $_GET['dir'], passed by imaginView()
	* @access private
	* @return void
	* @see imaginView()
	* @see __urlencode()
	*/
	function __showDir($get_dir)
	{

		$_SESSION['imaginview']['last_path'] = stripslashes($_SERVER['REQUEST_URI']);
		include_once($this->template_power);
		if (isset($this->header)  && ($this->header != "") ) 
			include_once($this->header);
		$tpl = new TemplatePower("$this->template");
		$tpl->prepare();
		$tpl->assignGlobal( "PHP_SELF", $_SERVER['PHP_SELF']);
		$tpl->newBlock("table_start");


		if ($_GET['dir'] != "") {
			$path = stripslashes($_GET['dir']);
			
			if (substr($path, -1, 1) == "/") {
				$path = substr($path, 0, -1);
			}

			$fullpath = realpath($this->photo_dir . "/" . $path);
			if ($fullpath != realpath($fullpath)) exit;
		} else {
			$path = "";
			$fullpath = $this->photo_dir;
		}

		if ((!is_dir($fullpath)) && ($fullpath == "")) exit;

		$i = 1;
		$ppr = 0;
		$row = 0;

		if ($dh = opendir($fullpath)) {
			while (($file = readdir($dh)) !== false) {
				if ( ($file != ".") && ($file != "..") &&
				!( ($file == $this->folder_pic) && ($path != "/") ) &&
				!(ereg('^\..*', $file) || ereg('\.(php|html|htm|txt)$', $file)) ) {
					if (is_dir($fullpath."/".$file)) {
						$dirlist[] = $file;
					} else {
						$filelist[] = $file;
					}
				}
			}
			if (is_array($filelist)) sort($filelist);
			if (is_array($dirlist)) sort($dirlist);
			if (is_array($dirlist) && is_array($filelist)) {
				$entrylist = array_merge($dirlist, $filelist);
			} elseif (is_array($dirlist) && !is_array($filelist)) {
				$entrylist = $dirlist;
			} elseif (!is_array($dirlist) && is_array($filelist)) {
				$entrylist = $filelist;
			}
			
			$cur_dir = preg_replace("#^/#", "", $get_dir);
			$cur_dir = preg_replace("#/$#", "", $cur_dir);
			$index_file = $this->cache_dir . "/" . str_replace("/", "_", $cur_dir) . "_index";

			if (!$handle = fopen($index_file, 'w+')) {              
				echo "1";
				//do other;
			}
			if (fwrite($handle, serialize($filelist)) === FALSE) {
				echo "2";
				//do other;
			}
			fclose($handle);


			$parts = explode("/", $path);

			if ($parts[0] != "") {
				array_unshift($parts, "");
			}
			
			if (count($parts) == 0 || ($parts[0] == "" && $parts[1] == "/")) {
				$parent = "";
			} else {
				for ($j = 0; $j < count($parts)-1; $j++) {
					$parent .= $parts[$j] . "/";
				}
			}

			$td_width = 100 / $this->pics_per_row;
			$ppp = $this->pics_per_row * $this->rows;

			$floffset = 0;
			while ($floffset < (count($entrylist) - $ppp)) {
				$floffset += $ppp;
			}

			if ( isset($_GET['offset']) && 
			((count($entrylist) - $_GET['offset']) > 0) && 
			(is_int($_GET['offset'] / $ppp)) && 
			($_GET['offset'] != "0") ) {
				$offset = $_GET['offset'];
				for ($c = $_GET['offset']; $c > 0; $c--) {
					array_shift($entrylist);
				}
				$i += $_GET['offset'];
			}

			if (is_file($fullpath."/.names")) {
				$desc_contents = file($fullpath."/.names");
				foreach ($desc_contents as $description) {
					$tmpdesc = explode(":", $description);
					$desc[$tmpdesc[0]]['pname'] = $tmpdesc[1];
				}
			}

			if ($entrylist)
			foreach ($entrylist as $file) {
					if ($ppr == $this->pics_per_row) {
						$tpl->newBlock("new_row");
						$row++;
						$ppr = 0;
					}

					if ($row == $this->rows) {
						break;
					}

					if (filetype($fullpath . "/" . $file) == "dir") {
						$nice_name = ereg_replace("_", " ", $file);
						$tpl->newBlock("thumb_nail");
						$tpl->assign("thumbnail", $this->photo_url . "/" . $this->folder_pic );
						$tpl->assign("td_width", $td_width);
						$tpl->assign("tn_link", "dir=" . $this->__urlencode($path . "/" . $file));
						$tpl->assign("tn_caption", $nice_name);
						$i++;
						$ppr++;
					} else if ($this->__getImgType($fullpath . "/" . $file) != "Unknown") {
						$nice_name = ereg_replace("_", " ", $file);
						$nice_name = ereg_replace("\.(gif|GIF)", " (gif)", $nice_name);
						$nice_name = ereg_replace("\.(jpg|JPG|jpeg|JPEG)", " (jpg)", $nice_name);
						$nice_name = ereg_replace("\.(png|PNG)", " (png)", $nice_name);
						$tpl->newBlock("thumb_nail");
						$tpl->assign("thumbnail", $_SERVER['PHP_SELF'] . "?thumbnail=" . $this->__urlencode($path . "/" . $file));
						$tpl->assign("td_width", $td_width);
						$tpl->assign("thumb_max", ($this->thumb_max + 4));
						$tpl->assign("tn_link", "image=" . $this->__urlencode($path . "/" . $file));
						$tpl->assign("tn_filename", $file);
						if (is_array($desc[$file]) and !empty($desc[$file]['pname'])) {
							$tpl->assign("tn_caption", $desc[$file]['pname']);
						} else {
							$tpl->assign("tn_caption", $nice_name);
						}
						$i++;
						$ppr++;
					} else {
						//echo "$file<br />\n";
					}


			}

			$entrycount = count($entrylist);
			$tpl->newBlock("table_end");
			$tpl->newBlock("navigation");
			$tpl->assign("span", $this->pics_per_row);
			if (isset($offset)) {
				$loffset = $offset - $ppp;
				$tpl->newBlock("go_prev");
				$tpl->assign("action", "dir");
				$tpl->assign("argument", $path);
				$tpl->assign("fargument", $path);
				$tpl->assign("loffset", "&offset=" . $loffset);
				$tpl->assign("floffset", "&offset=" . 0);
			} else {
				$tpl->newBlock("no_prev");
			}

			if (isset($parent)) {
				$tpl->newBlock("go_home");
				$tpl->assign("home_link", $_SERVER['PHP_SELF'] . "?dir=" . $this->__urlencode($parent));
			} else {
				$tpl->newBlock("no_home");
			}

			if ( ( (($entrycount - $ppp) > 0) || (($entrycount - $offset) > 0) ) && ($entrycount > $ppp) ) {
				if (isset($offset)) {
					$loffset = $offset + $ppp;
				} else {
					$loffset = $ppp;
				}
				$tpl->newBlock("go_next");
				$tpl->assign("action", "dir");
				$tpl->assign("argument", $path);
				$tpl->assign("fargument", $path);
				$tpl->assign("loffset", "&offset=" . $loffset);
				$tpl->assign("floffset", "&offset=" . $floffset);
			} else {
				$tpl->newBlock("no_next");
			}
			$tpl->gotoBlock( "_ROOT" );
			$tpl->assign("iv_version", $this->version);
			$tpl->printToScreen();
			if (isset($this->footer) && ($this->footer != "") )
				include_once($this->footer);
		}

	}

	/**
	* Unused
	*
	* @access private
	* @return integer 1/0
	*/
	function __authUser()
	{
		if (foo) {return 1;}
		return 1;
	}

	/**
	* rawurlencode(), but with %2F converted back to /
	*
	* @param string $string Any string you want to urlencode.
	* @access private
	* @return string urlencoded string of $string
	*/
	function __urlencode($string)
	{
		return str_replace("%2F", "/", rawurlencode($string));
	}

	/**
	* Replaces / with %2F, then rawurldecode()
	*
	* @param string $string Any string that was __urlencode()ed
	* @access private
	* @return string urldecoded string of $string
	*/
	function __urldecode($string)
	{
		return stripslashes(rawurldecode(str_replace("/", "%2F", $string)));
	} 

}
?>
