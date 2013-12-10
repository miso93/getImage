<?php
		// include_once 'simple_html_dom.php';
		// require 'fastimage/Fastimage.php';
// error_reporting(E_ALL);

		// require('unicode/lib/Unicode.php');

	    $images = array();
	    $title = '';
	    $description = '';

		if(isset($_POST['url'])){
			$url = $_POST['url'];
			
			if(strpos($url, 'http://') !== 0)
			{
				$url = "http://".$url;
			}
			$parse_url = parse_url($url);

			if(isset($parse_url['scheme'])){
				$url = $parse_url['scheme'].'://'.$parse_url['host'];
			} else {
				$url = 'http://';		
			}
			$host = $url;

			$sub = isset($parse_url['path']) ? $parse_url['path'] : '';

			if(isset($parse_url['path'])){
				$url .= $parse_url['path'];
			}
			if(isset($parse_url['query'])){
				$url .= '?' . $parse_url['query'];
			}

			@$html = file_get_contents_curl($url);

			$meta_og_title = null;
			$meta_og_description = null;
			$meta_og_img = null;
			$title = null;
			$description = null;

			if(!$html) {
				die();
			}

				@$doc = new DOMDocument();
				@$doc->loadHTML($html);
				@$tags = get_meta_tags($url);

				//prva metoda
				foreach($doc->getElementsByTagName('meta') as $meta) {

					// var_dump($meta->getAttribute('property'));
				    if($meta->getAttribute('property') == 'og:image'){ 
				        $meta_og_img = $meta->getAttribute('content');
				        // $images[] = $meta_og_img;
				    } else
				    if($meta->getAttribute('property') == 'og:title'){ 
				        $meta_og_title = $meta->getAttribute('content');
				    } else
				    if($meta->getAttribute('property') == 'og:description'){ 
				        $meta_og_description = $meta->getAttribute('content');
				    }
				}

				//druha metoda
				//title
				if(!$meta_og_title){
					$nodes = $doc->getElementsByTagName('title');
					$title = $nodes->item(0)->nodeValue;
				} else {
					$title = $meta_og_title; //inicializacia title
				}
				//description
				if(!$meta_og_description){
					$metas = $doc->getElementsByTagName('meta');
					for ($i = 0; $i < $metas->length; $i++)
					{
					    $meta = $metas->item($i);
					    if($meta->getAttribute('name') == 'description')
					        $description = $meta->getAttribute('content');
					}
				} else {
					$description = $meta_og_description; //inicializacia title
				}
				

				// var_dump(mb_check_encoding($html, 'UTF-8'));
				mb_internal_encoding("UTF-8");

				// $enc = mb_detect_encoding($title, "UTF-8, ISO-8859-2");
				// $newtitle = autoUTF($title);
				// echo 'From '.$title."<br />";
				// echo 'Fixed result: '.$newtitle."<br />";

				// $newdescription = autoUTF($description);
				// echo 'From '.$description."<br />";
				// echo 'Fixed result: '.$newdescription."<br />";
				// $string = new Unicode_String();
				// $string->fromUTF8($title);
				// $title = $string->toUTF8();

				// $title = cp1250_to_utf2($title);
				// $description = cp1250_to_utf2($description);
				// $title = (mb_check_encoding($html, 'UTF-8') ? utf8_decode($title) : $title);
				// $description = (mb_check_encoding($html, 'UTF-8') ? utf8_decode($description) : $description);


			$image_regex = '/<img[^>]*'.'src=[\"|\'](.*)[\"|\']/Ui';
			preg_match_all($image_regex, $html, $img, PREG_PATTERN_ORDER);
			$images_array = $img[1];

			$count = 0;

			$output = preg_split('|(?<!\\\)/|', $sub);
			$sub = '';
			for($i = 1; $i < count($output) - 1; $i++){
				$sub .= '/'.$output[$i];
			}

			foreach($images_array as $src){

				$parse_url = parse_url($src);
				if(!isset($parse_url['host'])){
					// var_dump($parse_url['path']);
					if(strpos($parse_url['path'], '\/') === 0){

						$src = $host.$parse_url['path'];
					}
					else {		
						$src = $host.$sub.'/'.$parse_url['path'];
					}
				} else {

					$src = 'http://'.$parse_url['host'].$parse_url['path'];
				}

				// echo $src.'<br>';
				if(deleteWaste($src)){
						// echo "som tu".'<br>';

						// $image = new FastImage($src);
						// var_dump($image);
						// list($width, $height) = $image->getSize();
						// var_dump($image->getSize());
						@$info = getimagesize($src);
						// var_dump($info);
						$width  = $info[0];
						$height = $info[1];
						// echo $width.' '.$height.'<br>';
						if($width > 95 && $height > 70 && $width < 1500 && $height < 1200 && !($width > (3*$height) || $height > (3*$width) ) ){

							$temp['src'] =  $src;
							$temp['obsah'] = $width * $height;
							if(!in_array($temp, $images)){
								$images[$count]['src'] = $src;
								$images[$count]['obsah'] = $width * $height;

								$count++;
							}
							
						}

				}
				// if($count >= 5){ // ak mam viac ako 5 images, mam dost
				// 	break; 
				// }
			}
			if(count($images) > 1){
				aasort($images, 'obsah');
			}
			if(isset($meta_og_img)){
				$og_image['src'] = $meta_og_img;
				array_unshift($images, $og_image);
			}
			$images = array_slice($images, 0, 5);

			// var_dump($images);
			createWindow($title, $url, $description, $images);


	    	
		}

		function aasort (&$array, $key) {
		    $sorter=array();
		    $ret=array();
		    reset($array);
		    foreach ($array as $ii => $va) {
		        $sorter[$ii]=$va[$key];
		    }
		    arsort($sorter);
		    foreach ($sorter as $ii => $va) {
		        $ret[$ii]=$array[$ii];
		    }
		    $array=$ret;
		}

		function cp1250_to_utf2($text){
	        $dict  = array(chr(225) => 'á', chr(228) =>  'ä', chr(232) => 'č', chr(239) => 'ď',
	            chr(233) => 'é', chr(236) => 'ě', chr(237) => 'í', chr(229) => 'ĺ', chr(229) => 'ľ',
	            chr(242) => 'ň', chr(244) => 'ô', chr(243) => 'ó', chr(154) => 'š', chr(248) => 'ř',
	            chr(250) => 'ú', chr(249) => 'ů', chr(157) => 'ť', chr(253) => 'ý', chr(158) => 'ž',
	            chr(193) => 'Á', chr(196) => 'Ä', chr(200) => 'Č', chr(207) => 'Ď', chr(201) => 'É',
	            chr(204) => 'Ě', chr(205) => 'Í', chr(197) => 'Ĺ',    chr(188) => 'Ľ', chr(210) => 'Ň',
	            chr(212) => 'Ô', chr(211) => 'Ó', chr(138) => 'Š', chr(216) => 'Ř', chr(218) => 'Ú',
	            chr(217) => 'Ů', chr(141) => 'Ť', chr(221) => 'Ý', chr(142) => 'Ž',
	            chr(150) => '-');
	        return strtr($text, $dict);
	    }

		function autoUTF($s)
		{
		    // detect UTF-8
		    if (preg_match('#[\x80-\x{1FF}\x{2000}-\x{3FFF}]#u', $s))
		        return $s;

		    // detect WINDOWS-1250
		    if (preg_match('#[\x7F-\x9F\xBC]#', $s))
		        return iconv('WINDOWS-1250', 'UTF-8', $s);

		    // assume ISO-8859-2
		    return iconv('ISO-8859-2', 'UTF-8', $s);
		}

	function file_get_contents_curl($url)
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	    $data = curl_exec($ch);
		$info = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		
		//checking mime types
		if(strstr($info,'text/html')) {
			curl_close($ch);
	    	return $data;
		} else {
			return false;
		}
	}

	function deleteWaste($src){
		$target = $src;
		if ( !preg_match("/\.(jpg|jpeg|png|gif)$/",parse_url($target, PHP_URL_PATH)) ){
			// echo 'nepreslo validaciou koli typu<br>';
		    return false;
		}
		$target = $src;
		if(preg_match('/googleads/',$target) ){
			// echo 'nepreslo validaciou koli google reklame<br>';
			return false;
		}
		$target = $src;
		if(preg_match('/ /',$target) ){
			// echo 'nepreslo validaciou koli medzere v URL<br>';
			return false;
		}
		$target = $src;
		$file = $src;
		@$file_headers = get_headers($file);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == '404 Not Found') {
		    // echo 'nepreslo validaciou pretoze src neexistuje<br>';
		    return false;
		}

		return true;
	}
	function createWindow($title, $url, $description, $images){
		if(isset($title)){
			?>
			<div id="create-post">
				<p class="create-post-title"> <?php echo $title ?></p>
				<?php if(isset($url)){ ?>
					<p class="create-post-domain"><?php echo $url ?></p>
				<?php } ?>
				<?php if(isset($description)){ ?>
					<p class="create-post-description" ><?php echo $description ?></p>
				<?php } ?>	
				<?php
				if(count($images)){
					?>
					<div class="create-post-button">
						<a class="left" href="#" ></a>
						<a class="right" href="#" ></a>
					</div>
					<?php
					$id = 1;
					foreach($images as $src){
						
						echo "<div id=".$id." class='create-post-images' >";
						echo "<img src=".$src['src']." />";
						echo "</div>";
						
						$id++;
					}
				} else {
					echo "<div class='create-post-images' >";
					echo "<img src='images/noimage.jpg' />";
					echo "</div>";
				}
			?>
			</div>
			<?php
		}
	}

	function convert_to ( $source, $target_encoding, $encoding )
    {

	    if( !strcmp($encoding, $target_encoding)){
	    	return $source;

	    }
	    $target = $source;
	    $target = iconv($encoding, $target_encoding, $target); 
	  
	    return $target;
    }
	function getTitle($str){
		if(strlen($str)>0){
		    preg_match("/\<title\>(.*)\<\/title\>/",$str,$title);
		    if(isset($title[1])){
		    	return $title[1];
		    }
		    else {
		    	return '';
		    }
		}
	}
	function getCharset($str){
		preg_match('~charset=([-a-z0-9_]+)~i', $str, $charset);
		return isset($charset[0]) ? $charset[0] : $charset;
	}

	function exists($url){
		// if (!$fp = curl_init($url)) return false;
		$file_headers = @get_headers($url);
		if($file_headers == false || $file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == '404 Not Found') {
		    return false;
		}
		return true;
	}
?>
