<?
/**
* ImaginView - A Photo Gallery script in PHP
*
* ImaginView (class.imaginview.php) is a php class to build a photo gallery.
* It's small, simple, and easy to implement.
*
* @author Frank Rosquin <frank@nullsense.net>
* @link http://www.nullsense.net/development/imaginview/ ImaginView HomePage
* @copyright Frank Rosquin
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
* @package ImaginView
*/

/**
* This is the index.php file. Here you set your config options, and call ImaginView itself.
*
* For further info, please read the documentation
*
* @author Frank Rosquin <frank@nullsense.net>
* @package ImaginView
*/


/** 
* @var string $conf['photo_dir'] Full path to the Gallery dir (where the pictures are)
*/
$conf['photo_dir'] = "/var/www/free/cnf/htdocs/nullsense.net/Gallery";

/** 
* @var string $conf['cache_dir'] Full path to the cache dir (where cached thumbnails are kept)
*/
$conf['cache_dir'] = "/var/www/free/cnf/htdocs/nullsense.net/Gallery.cache";

/**
* @var string $conf['photo_url'] URL to the Gallery dir. $folder_pic should be kept here.
* this can be the full url (http://yourdomain.com/Gallery) or just the path part (/Gallery)
* @see $conf['folder_pic']
* @see $conf['photo_dir']
*/
$conf['photo_url'] = "http://www.nullsense.net/Gallery";

/**
* @var string $conf['folder_pic'] The name of the picture used to represent a folder in the gallery view.
* This picture MUST reside in $photo_url
* @see $conf['photo_url']
* @see $conf['photo_dir']
*/
$conf['folder_pic'] = "folder.jpg";

/**
* @var string $conf['outline_color'] Color of the thumbnail background.
* @see $conf['outline_border']
*/
$conf['outline_color'] = "#414756";

/**
* @var string $conf['outline_border'] The color of the 1 pixel border around the thumbnail. Set this to FALSE to disable border creation.
* @see $conf['outline_color']
*/
$conf['outline_border'] = "#78CB50";

/**
* @var integer $conf['thumb_max'] The size of the thumbnails. thumbnails are created as squares. This sets the dimention of said squares.
*/
$conf['thumb_max'] = 120;

/**
* @var string $conf['template_power'] Path to the TemplatePower class.
*/
//$conf['template_power'] = "/var/www/free/cnf/htdocs/nullsense.net.inc/class_TemplatePower.inc.php";

/**
* @var string $conf['template'] Path to the ImaginView template.
*/
$conf['template'] = "imaginview.tpl";

/**
* @var string $conf['header'] Name of your header file.
*/
$conf['header'] = "header.php";

/**
* @var string $conf['footer'] Name of your footer file.
*/
$conf['footer'] = "footer.php";

/**
* @var string $conf['debug'] Set debugging options
* Valid options: on/debug/all/none
* on: no notices, basic info
* debug: debug info, no notices
* all: all debug info
* none: no debug info
*/
$conf['debug'] = "on";

if (isset($_GET['debug'])) { $conf['debug'] = "all"; };

/**
* include the ImaginView class file
*/
include("class.imaginview.php");

$gallery = new imaginView($conf);


?>
