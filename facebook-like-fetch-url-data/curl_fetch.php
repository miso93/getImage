<?php
$url = trim($_REQUEST['url']);
$url = check_url($url);

function check_url($value)
{
	$value = trim($value);
	if (get_magic_quotes_gpc()) 
	{
		$value = stripslashes($value);
	}
	$value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
	$value = strip_tags($value);
	$value = htmlspecialchars($value);
	return $value;
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

//fetching url data via curl
$html = file_get_contents_curl($url);

if($html) {
//parsing begins here:
$doc = new DOMDocument();
@$doc->loadHTML($html);
$nodes = $doc->getElementsByTagName('title');

//get and display what you need:
$title = $nodes->item(0)->nodeValue;
$metas = $doc->getElementsByTagName('meta');

for ($i = 0; $i < $metas->length; $i++)
{
    $meta = $metas->item($i);
    if($meta->getAttribute('name') == 'description')
        $description = $meta->getAttribute('content');
}

// fetch images
$image_regex = '/<img[^>]*'.'src=[\"|\'](.*)[\"|\']/Ui';
preg_match_all($image_regex, $html, $img, PREG_PATTERN_ORDER);
$images_array = $img[1];
var_dump($images_array);
?>
<div class="images">
	<?php
	$k=1;
	for ($i=0;$i<sizeof($images_array);$i++)
	{
		if($images_array[$i])
		{
			if(strstr($images_array[$i],'http')) {
				echo "<img src='".$images_array[$i]."' width='100' id='".$k."' >";
				$k++;
			} 
		}
	}
	?>
	<input type="hidden" name="total_images" id="total_images" value="<?php echo --$k?>" />
	</div>
	<div class="info">
		
		<label class="title">
			<?php  echo $title; ?>
		</label>
		<br clear="all" />
		<label class="url">
			<?php  echo substr($url ,0,35); ?>
		</label>
		<br clear="all" /><br clear="all" />
		<label class="desc">
			<?php  echo $description; ?>
		</label>
		<br clear="all" /><br clear="all" />
		
		<label style="float:left"><img src="prev.png" id="prev" alt="" /><img src="next.png" id="next" alt="" /></label>
		
		<label class="totalimg">
			Total <?php echo $k?> images
		</label>
		<br clear="all" />
		
	</div>
<?php
} else {
echo "Please enter a valid url";
}
?>
		
	