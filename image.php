<html lang="sk" xml:lang="sk" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8">
	<meta content="sk" http-equiv="Content-Language">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<link href="http://aymi.eu/styles/default.css?1382708919" type="text/css" rel="Stylesheet">
	<title>Testovanie vyberanie obrazkov z url</title>
</head>
<body>
	<form method="POST" action="">
		<!-- <input id="url" type="text" name="url" > -->
		<input type="submit" name="submit" value="Vlozit post">
		<?php
		// echo phpinfo();
		echo "<div class='form-input' >";
		echo "<textarea name='form_text' style='height: 44px;'></textarea>\n";
		echo "<textarea name='form_text_special'></textarea>\n";
		?>
		<div id="result">
		
		</div>
		<?php
		echo "</div>";
		?>
	</form>
</body>

	
	<style type="text/css">
		div#create-post {
			width: 400px;
		}
		div#create-post p {
			margin: 0;
			padding: 0;
		}
		div#create-post p.create-post-title {
			font-size: 12px;
			font-weight: bold;
		}
		div#create-post p.create-post-domain {
			color: #CCCCCC;
		}
		div#create-post p.create-post-description {
			font-style: italic;
		}
		div#create-post div.create-post-images {
			border-left: 1px solid #CCCCCC;
			float: left;
			padding: 5px;
			width: 120px;
			height: 80px;
			/*position: absolute;*/
		}
		div#create-post div.create-post-images img {
			width: 100%;

		}
		div#create-post div.create-post-button{
			/*width: 10px;
			height: 10px;
			display: block;*/
		}
		div#create-post div.create-post-button a {
			padding: 0;
			margin: 0;
		}
		div#create-post div.create-post-button a.left.hidden {
			background: url(images/button-left.png) 0 0 no-repeat;
		}
		div#create-post div.create-post-button a.right.hidden {
			background: url(images/button-right.png) 0 0 no-repeat;
		}
		div#create-post div.create-post-button a.left {
			width: 15px;
			height: 22px;
			border: 0 solid #000;
			background: url(images/button-left-active.png) 0 0 no-repeat;
			display: inline-block;
		}
		div#create-post div.create-post-button a.right {
			width: 15px;
			height: 22px;
			border: 0 solid #000;
			background: url(images/button-right-active.png) 0 0 no-repeat;
			display: inline-block;
		}
	</style>
	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script type="text/javascript">
		$( document ).ready(function() {
			
			var textArea;
			var specialTextarea;
			var result;

			//trim funkcia pre IE7 a IE8
			if(typeof String.prototype.trim !== 'function') {
			  	String.prototype.trim = function() {
			    return this.replace(/^\s+|\s+$/g, ''); 
			  }
			}

			var insertToSpecialTextarea = function() {
				var src;
				var title;
				var domain;
				var description;
				result.find("div.create-post-images").each(function (){
				  if($(this).css("display") == "block"){
				    src = $(this).find('img').attr('src'); 
				  }
				});
				domain = result.find("p.create-post-domain").html();
				src = '<a href="' + domain + '" ><img src="' + src + '" /></a>';			
				title = '<p class="special-post-title"><a href="' + domain + '" >' + result.find("p.create-post-title").html() + '</a></p>';
				domain = '<p class="special-post-domain"><a href="' + domain + '" >' + result.find("p.create-post-domain").html() + '</a></p>';
				description = '<p class="special-post-description">' + result.find("p.create-post-description").html() + '</p>\n';
				specialTextarea.text('<div class="special_post">' + src + title + domain + description + '</div');

			}
			var reload = function() {
				var allImages = $("div.create-post-images");
				var active = allImages.first();
				var left = $("div#create-post div.create-post-button a.left");
				var right = $("div#create-post div.create-post-button a.right");

				allImages.hide();

				active.show();
				left.addClass("hidden");
				if(allImages.length <= 1){
					right.hide();
					left.hide();
				}
				left.click(function() {
					if (active.prev().is(allImages)){
						active.hide();
						active = active.prev();
						active.show();
						right.removeClass("hidden");

						insertToSpecialTextarea()

						if (!active.prev().is(allImages)){
							left.addClass("hidden");
						}
					}
					return false;
				});

				right.click(function() {
					if (active.next().is(allImages)){
						active.hide();
						active = active.next();
						active.show();

						left.removeClass("hidden");
						insertToSpecialTextarea()

						if (!active.next().is(allImages)){

							right.addClass("hidden");
						}
					} 
					return false;
				});

			}

			// detect paste to input
			

			$('body').on('keyup', 'textarea[name=form_text]', function(event) {
			// $('body').delegate('form#form-wall-post textarea[name=form_text]','keyup', function(event) {
				specialTextarea = $("div.form-input textarea[name=form_text_special]");
				result = $("div.form-input #result");
				loading = $("div.form-input").find("img.load");
				var self = $(this);
				var url = self.val();
				
				// if(event.keyCode == 32 || (event.keyCode == 86 && event.ctrlKey == true)){
				if(event.keyCode == 32){
					getContent(url);
				}
			});

			$('body').on('paste', 'textarea[name=form_text]', function (event) {
			// $('body').delegate('form#form-wall-post textarea[name=form_text]','paste', function (event) {
				specialTextarea = $("div.form-input textarea[name=form_text_special]");
				result = $("div.form-input #result");
				loading = $("div.form-input").find("img.load");

				var self = $(this);
				console.log('paste');
				setTimeout(function() {
				      var url = self.val();
				      getContent(url);
				}, 200);

			});

			function getContent(url){
				url = url.trim().split(' ');
				url = url[url.length - 1];
				
				if(validateURL(url)){
					if(/* url.indexOf(".jpg") === -1 &&*/ url.indexOf(".jpeg") === -1 && url.indexOf(".png") === -1 && url.indexOf(".gif") === -1){
						loading.show();
						$.ajax({   
	                        type: "POST",
	                        data : 'url=' + url,
	                        cache: false,  
	                        url: "get_page_info.php",   
	                        success: function(data){
	                            result.html(data);  
	                            insertToSpecialTextarea()
	                            reload();  
	                            loading.hide();            
	                        }   
	                    });
					} else {
						specialTextarea.val('');
						result.html('');
					}
                }
			}
			function validateURL(textval) {
			  var urlregex = new RegExp(
			        "^(http:\/\/www.|https:\/\/www.|http:\/\/|www.){1}([0-9A-Za-z]+\.)");
			  return urlregex.test(textval);
			}
		});
	</script>
</html>