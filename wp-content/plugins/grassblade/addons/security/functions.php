<?php
function grassblade_security_check() {

	if(empty($_REQUEST['grassblade_security_check']) || empty($_REQUEST['file']))
	{
		return;
	}
	$request_uri = rawurldecode($_SERVER["REQUEST_URI"]);
	$file_path_part = explode("wp-content", $request_uri, 2);
	$file_with_query_strings = WP_CONTENT_DIR.(@$file_path_part[1]);
	$file_parts = parse_url($file_with_query_strings);
	$file = @$file_parts["path"];
	$user = wp_get_current_user();
	if(!file_exists($file))
	{
		header(' ', true, 404);
		//echo "Invalid Request.";
		exit;
	}
	if(!empty($user->ID))
	{
		$mimetype = mime_content_type($file);
		grassblade_serveFile($file, null, $mimetype);
	}
	else {
		header(' ', true, 403);
		//echo "Access Denied.";
	}
	exit;
}
add_action("parse_request", "grassblade_security_check");

add_action( 'save_post', 'grassblade_security_check_gb_xapi_content_box_save', 11, 1);
function grassblade_security_check_gb_xapi_content_box_save($post_id) {
	$post = get_post( $post_id);
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

	if ( !isset($_POST['gb_xapi_content_box_content_nonce']) || !wp_verify_nonce( $_POST['gb_xapi_content_box_content_nonce'], "grassblade/addons/contentuploader/functions.php" ) )
		return;
	

	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return;
	}

	if( 'gb_xapi_content' != $_POST["post_type"] || !empty($post->post_type) && $post->post_type != "gb_xapi_content" )
		return;
	
	grassblade_security_secure_content($post_id);
}
function grassblade_security_secure_content($post_id) {
    $params = grassblade_xapi_content::get_params($post_id);
    if(empty($params["src"]))
            return;

    $src = $params["src"];
    $siteurl = get_bloginfo('url');
    $siteurl_without_http = str_replace(array("http://", "https://"), array("", ""), $siteurl);

    if(strpos($src, $siteurl_without_http) === false)
            return;

    $grassblade_settings = grassblade_settings();
    $security_enable = $grassblade_settings["security_enable"];
    if(empty($security_enable))
    $add_htaccess = false;
    else
    {
	    if(@$params["guest"] == "0" || @$params["guest"] == 1 || @$params["guest"] == 2)
	    $add_htaccess = empty($params["guest"]);
	    else
	    {
	            $track_guest = $grassblade_settings["track_guest"];
	            $security_enable = $grassblade_settings["security_enable"];
	            $add_htaccess = empty($track_guest) && $security_enable;
	    }
    }
    $file_path_part = explode("wp-content", $src, 2);
    if(empty($file_path_part[1]))
            return;

    $path_diff = dirname($file_path_part[1]).DIRECTORY_SEPARATOR;//WP_CONTENT_DIR.(@$file_path_part[1]);

    $slashes = preg_replace("/[^\\/]/", "", $path_diff);
    $wordpress_index_file = realpath(get_home_path());//str_replace("/", "../", $slashes)."index.php";
    $wordpress_index_file = implode("/", array_map( function() {return "..";}, explode(DIRECTORY_SEPARATOR, trim(str_replace($wordpress_index_file, "", realpath(WP_CONTENT_DIR.$path_diff)), DIRECTORY_SEPARATOR)) ))."/"; //Calculate Relative Path
	$htaccess_file = realpath(WP_CONTENT_DIR.$path_diff).DIRECTORY_SEPARATOR.".htaccess";
    grassblade_security_add_htaccess($add_htaccess, $wordpress_index_file, $htaccess_file, $post_id);
} 
function grassblade_security_add_htaccess($add_htaccess, $wordpress_index_file, $htaccess_file, $post_id) {
	$htaccess_file_exists = file_exists($htaccess_file);

	if(empty($add_htaccess) && $htaccess_file_exists) {
		$deleted = @unlink($htaccess_file);
		if(empty($deleted)) {
			$notice = sprintf(__("Could not delete .htaccess file: %s. ", "grassblade"), $htaccess_file);
			if(!is_writable($htaccess_file)) {
				$notice = $notice . " " . __("File is not writable.", "grassblade");
			}
			grassblade_admin_notice($notice);
		}
	}

	if($add_htaccess && empty($htaccess_file_exists))
	{
		$htaccess_file_template = apply_filters("grassblade_security_htaccess_file_template", dirname(__FILE__)."/htaccess.txt", $post_id);
		$htaccess_file_content = file_get_contents($htaccess_file_template);
		$htaccess_file_content = str_replace("[WORDPRESS_INDEX_FILE]", $wordpress_index_file, $htaccess_file_content);
		$fh = @fopen($htaccess_file, "w");
		$error = false;
		if(!$fh)
			$error = true;
		else
		{
			$written = fwrite($fh, $htaccess_file_content);
			if(empty($written))
				$error = true;
			fclose($fh);
		}	
		if($error) {
			$notice = sprintf(__("Could not add .htaccess file: %s. ", "grassblade"), $htaccess_file);			
			$notice = $notice . " " . __("File is not writable.", "grassblade");
			grassblade_admin_notice($notice);				
		}
	}
}

add_action("grassblade_settings_update", "grassblade_security_grassblade_settings_update", 5, 2);
function grassblade_security_grassblade_settings_update($grassblade_settings_old, $grassblade_settings_new) {
        if($grassblade_settings_old['track_guest'] != $grassblade_settings_new['track_guest'] || @$grassblade_settings_old['security_enable'
] != @$grassblade_settings_new['security_enable']) {
		$posts = get_posts('post_type=gb_xapi_content&posts_per_page=-1');
		foreach ($posts as $post) {
			grassblade_security_secure_content($post->ID);
		}
	}
}

function grassblade_security_fields($fields) {
	$updated_fields = array();
	foreach ($fields as $key => $value) {
		if($value["id"] == "content_settings_end")
		{
			$updated_fields[] = array( 'id' => 'security_enable', 'label' => __( 'Enable Content Security', 'grassblade' ),  'placeholder' => '', 'type' => 'checkbox', 'values'=> '', 'never_hide' => true ,	'help' => __('Disables direct url access to key static files in the content to users who are not logged in. Disabled for content with guest access. Protected file types: gif,jpeg,jpg,png,mp4,mp3,mpg,mpeg,avi,html.', 'grassblade'));
		}
		$updated_fields[] = $value;

	}
	return $updated_fields;
}
add_filter("grassblade_settings_fields", "grassblade_security_fields", 1, 1);

if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $filename_exploded = explode('.',$filename);
        $ext = strtolower(array_pop($filename_exploded));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}

function grassblade_serveFile($fileName, $fileTitle = null, $contentType = 'application/octet-stream')
{
	if( !file_exists($fileName) )
		throw New \Exception(sprintf('File not found: %s', $fileName));

	if( !is_readable($fileName) )
		throw New \Exception(sprintf('File not readable: %s', $fileName));


	### Remove headers that might unnecessarily clutter up the output
	//header_remove('Cache-Control');
	//header_remove('Pragma');


	### Default to send entire file
	$byteOffset = 0;
	$byteLength = $fileSize = filesize($fileName);

	header('Accept-Ranges: bytes', true);
	header(sprintf('Content-Type: %s', $contentType), true);

	if( $fileTitle )
		header(sprintf('Content-Disposition: attachment; filename="%s"', $fileTitle));

	### Parse Content-Range header for byte offsets, looks like "bytes=11525-" OR "bytes=11525-12451"
	if( isset($_SERVER['HTTP_RANGE']) && preg_match('%bytes=(\d+)-(\d+)?%i', $_SERVER['HTTP_RANGE'], $match) )
	{
		### Offset signifies where we should begin to read the file
		$byteOffset = (int)$match[1];


		### Length is for how long we should read the file according to the browser, and can never go beyond the file size
		if( isset($match[2]) ){
			$finishBytes = (int)$match[2];
	        	$byteLength = $finishBytes + 1;
		} else {
			$finishBytes = $fileSize - 1;
		}
	
		$cr_header = sprintf('Content-Range: bytes %d-%d/%d', $byteOffset, $finishBytes, $fileSize);
	
		header("HTTP/1.1 206 Partial content");
		header($cr_header);  ### Decrease by 1 on byte-length since this definition is zero-based index of bytes being sent
	}

	$byteRange = $byteLength - $byteOffset;

	header(sprintf('Content-Length: %d', $byteRange));

//	header(sprintf('Expires: %s', date('D, d M Y H:i:s', time() + 60*60*24*90) . ' GMT'));


	$buffer = ''; 	### Variable containing the buffer
	$bufferSize = 512 * 16; ### Just a reasonable buffer size
	$bytePool = $byteRange; ### Contains how much is left to read of the byteRange

	if( !$handle = fopen($fileName, 'r') )
		throw New \Exception(sprintf("Could not get handle for file %s", $fileName));

	if( fseek($handle, $byteOffset, SEEK_SET) == -1 )
		throw New \Exception(sprintf("Could not seek to byte offset %d", $byteOffset));


	while( $bytePool > 0 )
	{
		$chunkSizeRequested = min($bufferSize, $bytePool); ### How many bytes we request on this iteration

		### Try readin $chunkSizeRequested bytes from $handle and put data in $buffer
		$buffer = fread($handle, $chunkSizeRequested);

		### Store how many bytes were actually read
		$chunkSizeActual = strlen($buffer);

		### If we didn't get any bytes that means something unexpected has happened since $bytePool should be zero already
		if( $chunkSizeActual == 0 )
		{
			### For production servers this should go in your php error log, since it will break the output
			trigger_error('Chunksize became 0', E_USER_WARNING);
			break;
		}

		### Decrease byte pool with amount of bytes that were read during this iteration
		$bytePool -= $chunkSizeActual;

		### Write the buffer to output
		print $buffer;

		### Try to output the data to the client immediately
		flush();
	}

	exit();
}
