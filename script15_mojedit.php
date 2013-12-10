<?php


global $ext_user, $country_list;


$site_url = $this->conf->get_uri_by_site(_SITE);


$user_id = $_REQUEST['user_id'] + 0;
if ($user_id <= 0) $user_id = $ext_user->id;

if ($ext_user->is_logged() && $user_id != $ext_user->id && $ext_user->data['incognito_mode'] != 'Y')
{
	$ext_user->inc_counter($user_id);
	$ext_user->watch_history($user_id);
}

if ($user_id > 0)
{
	if ($_REQUEST['act'] == 'profile_photo' && $_REQUEST['image'] + 0 > 0)
		$ext_user->exchange_profile_photo($_REQUEST['image'], $user_id);

	$user_data = ($ext_user->is_logged() && $user_id == $ext_user->id ? $ext_user->data : $ext_user->get_profile_data($user_id));

// wall
	if (isset($_REQUEST['view'])) $_SESSION['ext_user_wall_view'.$user_id] = $_REQUEST['view'];

	$datetime_tmp = strtotime(date('Y-m-d 00:00:00'));

	if ($_SESSION['ext_user_wall_view'.$user_id] == 'last_week') $limit_date = strtotime('-7 days', $datetime_tmp);
	elseif ($_SESSION['ext_user_wall_view'.$user_id] == 'last_month') $limit_date = strtotime('-1 month', $datetime_tmp);
	elseif ($_SESSION['ext_user_wall_view'.$user_id] == 'last_6months') $limit_date = strtotime('-6 months', $datetime_tmp);
	elseif ($_SESSION['ext_user_wall_view'.$user_id] == 'last_year') $limit_date = strtotime('-1 year', $datetime_tmp);
	elseif ($_SESSION['ext_user_wall_view'.$user_id] == 'all') $limit_date = 0;
	else $limit_date = -5;

	$user_wall = $ext_user->get_wall_posts('', $user_id, $limit_date);


	$user_images = $ext_user->get_images(0, $user_id);
	$user_videos = $ext_user->get_videos(0, $user_id);

	$user_friend_list = array();
	$user_friends = $ext_user->get_friends('(confirm_id>0) and (status=1)', $user_id);

	if (count($user_friends) > 0)
	{
		$temp_list = array();

		foreach ($user_friends as $val)
			$temp_list[] = ($val['id_user_from'] == $user_id ? $val['id_user_to'] : $val['id_user_from']);

		if (count($temp_list) > 0)
			$user_friend_list = $ext_user->get_users(' and (ext_user.id in ('.implode(', ', $temp_list).'))');
	}

	if ($ext_user->has_right($user_data, 'user', 'visits'))
	{
		$temp_list = array();

		if (is_array($user_data['last_viewed_by_user']))
			foreach ($user_data['last_viewed_by_user'] as $key => $val)
				$temp_list[] = $key + 0;

		$user_visit_list = (count($temp_list) > 0 ? $ext_user->get_users(' and (ext_user.id in ('.implode(', ', $temp_list).'))') : array());
	}
}

$tab_selected = $_REQUEST['tab'] + 0;


echo "<h2>";
echo "<span style='float: left;'>".upper_diacritic(lang('Profil používateľa')).': <strong>'.upper_diacritic($user_data['user'])."</strong></span>";

if ($user_id == $ext_user->id)
{
	echo "<div id='profile-header-number'>";

	if (strlen($user_data['phone']) > 0)
	{
		if ($user_data['phone'] == $user_data['phone_verified'])
		{
			echo "<h3>".lang('Všetci ti môžu poslať sms v tvare AYMI medzera %1% na 7774', '', array(upper_diacritic($user_data['user'])))."</h3>";

//			echo "<p>".lang('Pošli SMSku na číslo %1% v tvare', '', array('<strong>'.lang('6669').'</strong>')).':<br/> <b>'.lang('AYMI medzera MENO medzera tvoj text', '', array('<strong>'.hsc(upper_diacritic($user_data['user'])).'</strong>'))."</b></p>";
		}
		else
		{
			$title = lang('Pre overenie pošli SMSku na číslo 7774 z tel. čísla %1% v tvare', '', array(substr($user_data['phone'], 0, -9).' '.substr($user_data['phone'], -9, 3).' '.substr($user_data['phone'], -6, 3).' '.substr($user_data['phone'], -3))).': '.lang('AYMI medzera %1%', '', array(upper_diacritic($user_data['user'])));
			echo "<h3 style='text-decoration: underline; cursor: pointer;' onclick=\"alert('".$title."');\">".lang('Over svoje mobilné číslo')."</h3>";

//			echo "<p>".lang('Pre overenie pošli SMSku na číslo %1% z tel. čísla %2% v tvare', '', array('<strong>'.lang('6669').'</strong>', '<br/><strong>'.substr($user_data['phone'], 0, -9).' '.substr($user_data['phone'], -9, 3).' '.substr($user_data['phone'], -6, 3).' '.substr($user_data['phone'], -3).'</strong>')).': <b>'.lang('AYMI medzera %1%', '', array(strtoupper($user_data['user'])))."</b></p>";
		}
	}
	else
	{
		echo "<h3 onclick=\"\$.fancybox(\$('.fancybox-html').html());\" style='text-decoration: underline; cursor: pointer;'>".lang('Pre SMS zadaj svoj mobil')."</h3>";
	}

	echo "</div>";
}
else
{
	echo "<div id='profile-header-number'>";

	if (strlen($user_data['phone']) > 0 && $user_data['phone'] == $user_data['phone_verified'])
	{
		$title = lang('Pošli hneď teraz SMSku na číslo 7774 v tvare').': '.lang('AYMI medzera %1% medzera tvoj text', '', array(upper_diacritic($user_data['user'])));
		echo "<h3 style='text-decoration: underline; cursor: pointer;' onclick=\"alert('".$title."');\">".lang('%1% čaká na tvoju SMS', '', array(hsc($user_data['user'])))."</h3>";

//		echo "<p>".lang('Pošli hneď teraz SMSku na číslo %1% v tvare', '', array('<strong>'.lang('6669').'</strong>')).':<br/> <b>'.lang('AYMI medzera %1% medzera tvoj text', '', array('<strong>'.hsc(upper_diacritic($user_data['user'])).'</strong>'))."</b></p>";
	}
	else
	{
		echo "<h3>".lang('%1% ešte nezadal svoj mobil', '', array(upper_diacritic($user_data['user'])))."</h3>";
	}

	echo "</div>";

	echo "<div id='profile-header-message'><a href='".$this->conf->get_uri_by_site(4)."?user_id=".$user_id."' class='button-msg'>".lang('Poslať správu cez AYMI')."</a></div>";
}

echo "<span style='float: right; font-size: 50%;'>";

	$onclick_edit = "\$('#profile-header-content').load('ajax.php?act=user_pass_form&id=".$user_id."');";
	echo "<span style='text-decoration: underline; cursor: pointer;' onclick=\"".$onclick_edit."\">".lang('Zmeniť heslo')."</span>&nbsp;&nbsp;&nbsp;";

	if ($ext_user->has_right($user_data, 'user', 'status'))
	{
		echo "<input type='checkbox' name='form_user_status' id='form_user_status' value='1' onclick=\"\$.post('/ajax.php?act=user_save&id=".$user_id."&form_user_status=' + (this.checked ? '1' : '0') + '&ts=' + Number(new Date()), fnAjaxEvaluate);\"".($user_data['status'] == '1' ? ' checked' : '')." />&nbsp;&nbsp;&nbsp;";
		echo lang('Zapnutý');
	}

echo "</span>";
echo "<div style='clear: both;'></div>\n";
echo "</h2>\n";

if (!$ext_user->is_logged())
{
	if (($_REQUEST['act'] == 'login' || $_REQUEST['login'] == 'login') && isset($ext_user->error[3]))
	{
		echo "<p style='font-weight: bold; text-align: center;'>";
		echo lang('Tvoje konto zatiaľ nie je aktívne.')."<br/><br/>\n";
		echo lang('Pre aktiváciu svojho konta klikni na aktivačný link, ktorý bol poslaný na tvoj email %1%.', '', array($ext_user->data['email']))." <br/><br/>\n";
		echo lang('V prípade, že neaktivuješ svoje konto do 2 hodín, tak bude vymazané.')." <br/><br/>\n";
		echo "</p>\n";

		if ($_REQUEST['step'] == '1')
		{
			echo "<div id='login-error-script'>";

			if ($ext_user->check_field('email', $_REQUEST['email']))
			{
				echo lang('Email s aktivačným linkom bol odoslaný na adresu "%1%".', '', array($_REQUEST['email']));

				$activate_link = 'http://'._CMS_SERVER.$this->conf->get_uri_by_site(2).'?act=activate&amp;h='.$ext_user->data['hash'];

				$subject = lang('Dokončenie registrácie používateľa na').' '._CMS_DOMAIN;

				$text.= lang('Ahoj').", <br/><br/>\n";
				$text.= lang('pre aktiváciu svojho konta prosím klikni na nasledovný link').": <br/><br/>\n";
				$text.= "<a href='".$activate_link."' target='_blank'>".$activate_link."</a><br/><br/>\n";
				$text.= lang('S pozdravom')."<br/>\n";
				$text.= ucfirst(_CMS_DOMAIN)."<br/><br/>\n";

				$mail = new htmlMimeMail5();

				$mail->setHeadCharset($this->charset);
				$mail->setHTMLCharset($this->charset);

				$mail->setFrom('no-reply@'._CMS_DOMAIN);
				$mail->setBcc('register@'._CMS_DOAMIN);
				$mail->setSubject(rm_diacritic($subject));
				$mail->setHTML(rm_diacritic($text));

				$result = $mail->send(array($_REQUEST['email']));
			}
			else
				echo hsc($ext_user->error[107]);

			echo "</div>\n";
		}

		echo "<form id='resend-form' method='post' action='".hsc($this->conf->get_uri_by_site(3))."'>\n";

		echo "<input type='submit' name='resend_button' value='".lang('PREPOSLAŤ EMAIL')."' id='resend_button' class='button-blue' />\n";
		echo "<input type='text' name='email' value='".hsc(isset($_REQUEST['email']) ? $_REQUEST['email'] : $ext_user->data['email'])."' id='email' placeholder='".lang('Email')."' />\n";
		echo "<input type='hidden' name='user' value='".hsc($_REQUEST['user'])."' id='user' />\n";
		echo "<input type='hidden' name='password' value='".hsc($_REQUEST['password'])."' id='password' />\n";
		echo "<input type='hidden' name='step' value='1' id='step' />\n";
		echo "<input type='hidden' name='act' value='login' id='act' />\n";
		echo "<div style='clear: both;'></div>\n";

		echo "</form>\n";
	}
	else
	{
		echo "<p style='text-align: center;'>";
		echo lang('Pre zobrazenie profilu používateľa sa musíte prihlásiť.')."<br/><br/>\n";
		echo lang('Ak ešte nie ste u nás zaregistrovaný, neváhajte a %1%pripojte sa k nám%2%.', '', array("<a href='".$this->conf->get_uri_by_site(2)."'>", '</a>'))."<br/><br/>\n";
		echo "</p>\n";

		$mod_temp = new cms_mod_script(16);
		$mod_temp->show();
	}

	echo str_repeat('<br/>', 8)."\n";

	return;
}


echo "<div id='profile-header'>";
echo "<div id='profile-header-content'>";

$onclick_edit = "\$('#profile-header-content').load('ajax.php?act=user_profile_photo&id=".$user_id."');";

echo "<div id='profile-photo'".($ext_user->has_right($user_data, 'user', 'edit') ? " class='editable'" : '').">";

	if (is_file($user_data['photo_large']))
		echo "<a href='".$user_data['photo'].'?'.$user_data['photo_profile']."' title='".lang('Hlavná profilová fotka používateľa').' '.hsc($user_data['user'])."' rel='profile' class='fancybox' style=\"background-image: url('".$user_data['photo_large'].'?'.$user_data['photo_profile']."');\" title='".lang('Hlavná profilová fotka používateľa').' '.hsc($user_data['user'])."'>&nbsp;</a>";
	else
		echo "<img src='images/profile_photo.png' alt='".lang('Nezadaná profilová fotka používateľa').' '.$user_data['user']."' />";

	if ($ext_user->has_right($user_data, 'user', 'edit'))
		echo "<p><span class='link-action' style='display: none;' onclick=\"".$onclick_edit."\">".lang('Upraviť')."</span>&nbsp;</p>\n";

echo "</div>\n";

$onclick_edit = "\$('#profile-header-content').load('ajax.php?act=user_basic_form&id=".$user_id."');";

echo "<div id='profile-basic'".($ext_user->has_right($user_data, 'user', 'edit') ? " class='editable' onclick=\"".$onclick_edit."\"" : '').">";

	$value = $user_data['user'];
	echo "<p><strong>".lang('Meno').':</strong> <em>'.hsc($value)."</em></p>\n";

	$value = get_age_by_date($user_data['birthday']);
	echo "<p><strong>".lang('Vek').':</strong> <em>'.hsc($value)."</em></p>\n";

	$value = get_birthday_by_date($user_data['birthday']);
	echo "<p><strong>".lang('Dátum narodenia').':</strong> '.hsc($value)."</p>\n";

	$value = get_zodiac_by_date($user_data['birthday']);
	echo "<p><strong>".lang('Znamenie').':</strong> '.hsc($value)."</p>\n";

	$value = $user_data['city'].', '.$country_list[$user_data['country']];
	echo "<p><strong>".lang('Nachádza sa').':</strong> '.hsc($value)."</p>\n";

	if ($user_data['gender'] == 'M') $value1 = lang('muž');
	elseif ($user_data['gender'] == 'F') $value1 = lang('žena');
	else $value1 = '';

	if ($user_data['looking_for'] == 'M') $value2 = lang('muža');
	elseif ($user_data['looking_for'] == 'F') $value2 = lang('ženu');
	elseif ($user_data['looking_for'] == 'B') $value2 = lang('kamarátstvo');
	elseif ($user_data['looking_for'] == 'C') $value2 = lang('šancu');
	elseif ($user_data['looking_for'] == 'L') $value2 = lang('šťastie');
	elseif ($user_data['looking_for'] == 'N') $value2 = lang('nikoho');
	else $value2 = lang('nezáleží');
	echo "<p><strong>".lang('Je').':</strong> <em>'.hsc($value1)."</em> <strong>".lang(' a hľadá').':</strong> <em>'.hsc($value2)."</em></p>\n";

	$value = get_elapsed_time($user_data['login_date'] != '0000-00-00 00:00:00' ? $user_data['login_date'] : $user_data['register_date']);
	echo "<p><strong>".lang('Posl. prihlásenie').':</strong> '.hsc($value)."</p>\n";

	$value = ($user_data['incognito_mode'] == 'Y' ? lang('Áno') : lang('Nie'));
	echo "<p><strong>".lang('Prezerať profily ostatných inkognito').':</strong> '.hsc($value)."</p>\n";

	if ($ext_user->has_right($user_data, 'user', 'edit'))
		echo "<p><span class='link-action' style='display: none;' onclick=\"".$onclick_edit."\">".lang('Upraviť')."</span>&nbsp;</p>\n";

echo "</div>\n";

echo "<div id='profile-banner'>";
	$temp = new cms_mod_banners(38);
	$temp->show();
echo "</div>\n";

/*
echo "<div id='profile-sms'>";

if ($user_id == $ext_user->id)
{
	if (strlen($user_data['phone']) > 0)
	{
		if ($user_data['phone'] == $user_data['phone_verified'])
		{
			echo "<h3>".lang('Noví ľudia čakajú na tvoju SMS')."</h3>";

			echo "<p>".lang('Pošli SMSku na číslo %1% v tvare', '', array('<strong>'.lang('6669').'</strong>')).':<br/> <b>'.lang('AYMI medzera MENO medzera tvoj text', '', array('<strong>'.hsc(upper_diacritic($user_data['user'])).'</strong>'))."</b></p>";
		}
		else
		{
			echo "<h3>".lang('Over svoj mobil a začni komunikovať cez SMS')."</h3>";

			echo "<p>".lang('Pre overenie pošli SMSku na číslo %1% z tel. čísla %2% v tvare', '', array('<strong>'.lang('6669').'</strong>', '<br/><strong>'.substr($user_data['phone'], 0, -9).' '.substr($user_data['phone'], -9, 3).' '.substr($user_data['phone'], -6, 3).' '.substr($user_data['phone'], -3).'</strong>')).': <b>'.lang('AYMI medzera %1%', '', array(strtoupper($user_data['user'])))."</b></p>";
		}
	}
	else
	{
		echo "<h3>".lang('Pridaj svoj mobil, noví ľudia ti chcú písať')."</h3>";

		echo "<p><strong onclick=\"\$.fancybox(\$('.fancybox-html').html());\" style='text-decoration: underline; cursor: pointer;'>".lang('Zadať telefónne číslo na môj mobil')."</strong></p>";

	}
}
else
{
	if (strlen($user_data['phone']) > 0 && $user_data['phone'] == $user_data['phone_verified'])
	{
		echo "<h3>".lang('%1% čaká na tvoju SMS', '', array('<strong>'.hsc($user_data['user']).'</strong><br/>'))."</h3>";

		echo "<p>".lang('Pošli hneď teraz SMSku na číslo %1% v tvare', '', array('<strong>'.lang('6669').'</strong>')).':<br/> <b>'.lang('AYMI medzera %1% medzera tvoj text', '', array('<strong>'.hsc(upper_diacritic($user_data['user'])).'</strong>'))."</b></p>";
	}
	else
	{
		echo "<h3>".lang('%1% zatiaľ nepridal svoj mobil', '', array('<strong>'.hsc($user_data['user']).'</strong> '))."</h3>";
	}

	echo "<div style='text-align: center; margin: 20px 0 0;'><a href='".$this->conf->get_uri_by_site(4)."?user_id=".$user_id."' class='button-msg'>".lang('Poslať správu cez AYMI')."</a></div>";
}

echo "</div>\n";
*/

echo "<div style='clear: both;'></div>\n";

echo "<div class='tab-buttons'>";

	echo "<p id='tab-button0' onclick='fnChangeTab(0);' class='tab-button".($tab_selected == 0 ? ' selected' : '')."'>".upper_diacritic(lang('O mne'))."</p>\n";
	echo "<p id='tab-button1' onclick='fnChangeTab(1);' class='tab-button".($tab_selected == 1 ? ' selected' : '')."'>".upper_diacritic(lang('Moje fotky')).' ('.(count($user_images) > 99 ? '99+' : count($user_images)).")</p>\n";
	echo "<p id='tab-button2' onclick='fnChangeTab(2);' class='tab-button".($tab_selected == 2 ? ' selected' : '')."'>".upper_diacritic(lang('Moje videá')).' ('.(count($user_videos) > 99 ? '99+' : count($user_videos)).")</p>\n";
	echo "<p id='tab-button3' onclick='fnChangeTab(3);' class='tab-button".($tab_selected == 3 ? ' selected' : '')."'>".upper_diacritic(lang('Priatelia')).' ('.(count($user_friends) > 99 ? '99+' : count($user_friends)).")</p>\n";
if ($ext_user->has_right($user_data, 'user', 'visits'))
	echo "<p id='tab-button4' onclick='fnChangeTab(4);' class='tab-button".($tab_selected == 4 ? ' selected' : '')."'>".upper_diacritic(lang('Návštevy')).' ('.(count($user_visit_list) > 99 ? '99+' : count($user_visit_list)).")</p>\n";

echo "</div>\n";

echo "<div id='tab-content0' class='tab-content' style='display: ".($tab_selected == 0 ? '' : 'none').";'>";

	$onclick_edit = "\$('#profile-header-content').load('ajax.php?act=user_detail_form1&id=".$user_id."');";

	echo "<div id='profile-detail1'".($ext_user->has_right($user_data, 'user', 'edit') ? " class='editable' onclick=\"".$onclick_edit."\"" : '').">";

		$value = ($user_data['height'] + 0 > 0 ? ($i == 139 ? '&gt;' : ($i == 201 ? '&lt;' : '')).$user_data['height'].' '.lang('cm') : '');
		echo "<p><strong>".lang('Výška').':</strong> <em>'.$value."</em></p>\n";

		$value = ($user_data['weight'] + 0 > 0 ? ($i == 39 ? '&gt;' : ($i == 141 ? '&lt;' : '')).$user_data['weight'].' '.lang('kg') : '');
		echo "<p><strong>".lang('Váha').':</strong> <em>'.$value."</em></p>\n";

		$value = $user_data['figure'];
		echo "<p><strong>".lang('Postava').':</strong> '.hsc(lang($value))."</p>\n";

		$value = $user_data['hair_color'];
		echo "<p><strong>".lang('Farba vlasov').':</strong> '.hsc(lang($value))."</p>\n";

		$value = $user_data['eye_color'];
		echo "<p><strong>".lang('Farba očí').':</strong> '.hsc(lang($value))."</p>\n";

		$value = $user_data['glasses'];
		echo "<p><strong>".lang('Nosím okuliare').':</strong> '.hsc(lang($value))."</p>\n";

		if ($ext_user->has_right($user_data, 'user', 'edit'))
			echo "<p><span class='link-action' style='display: none;' onclick=\"".$onclick_edit."\">".lang('Upraviť')."</span>&nbsp;</p>\n";

	echo "</div>\n";

	$onclick_edit = "\$('#profile-header-content').load('ajax.php?act=user_detail_form2&id=".$user_id."');";

	echo "<div id='profile-detail2'".($ext_user->has_right($user_data, 'user', 'edit') ? " class='editable' onclick=\"".$onclick_edit."\"" : '').">";

		$value = $user_data['job'];
		echo "<p><strong>".lang('Zamestnanie').':</strong> '.hsc($value)."</p>\n";

		$value = $user_data['earnings'];
		echo "<p><strong>".lang('Zárobok').':</strong> '.lang($value)."</p>\n";

		$value = $user_data['living'];
		echo "<p><strong>".lang('Bývam').':</strong> '.lang($value)."</p>\n";

		$value = $user_data['education'];
		echo "<p><strong>".lang('Vzdelanie').':</strong> '.lang($value)."</p>\n";

		$value = array();
		$temp = explode('|', $user_data['languages']);
		foreach ($temp as $val) $value[] = lang($val);

		echo "<p><strong>".lang('Cudzie jazyky').':</strong> '.implode(', ', $value)."</p>\n";

		if ($ext_user->has_right($user_data, 'user', 'edit'))
			echo "<p><span class='link-action' style='display: none;' onclick=\"".$onclick_edit."\">".lang('Upraviť')."</span>&nbsp;</p>\n";

	echo "</div>\n";

	$onclick_edit = "\$('#profile-header-content').load('ajax.php?act=user_detail_form3&id=".$user_id."');";

	echo "<div id='profile-detail3'".($ext_user->has_right($user_data, 'user', 'edit') ? " class='editable' onclick=\"".$onclick_edit."\"" : '').">";

		$value = $user_data['family'];
		echo "<p><strong>".lang('Rodinný stav').':</strong> '.lang($value)."</p>\n";

		$value = $user_data['children'];
		echo "<p><strong>".lang('Deti').':</strong> '.lang($value)."</p>\n";

		$value = $user_data['faith'];
		echo "<p><strong>".lang('Viera').':</strong> '.lang($value)."</p>\n";

		$value = $user_data['sex_orientation'];
		echo "<p><strong>".lang('Sexuálna orientácia').':</strong> '.lang($value)."</p>\n";

		$value = $user_data['smoking'];
		echo "<p><strong>".lang('Fajčenia').':</strong> '.lang($value)."</p>\n";

		$value = $user_data['drinking'];
		echo "<p><strong>".lang('Alkohol').':</strong> '.lang($value)."</p>\n";

		if ($ext_user->has_right($user_data, 'user', 'edit'))
			echo "<p><span class='link-action' style='display: none;' onclick=\"".$onclick_edit."\">".lang('Upraviť')."</span>&nbsp;</p>\n";

	echo "</div>\n";

	$onclick_edit = "\$('#profile-header-content').load('ajax.php?act=user_detail_form4&id=".$user_id."');";

	echo "<div id='profile-detail4'".($ext_user->has_right($user_data, 'user', 'edit') ? " class='editable' onclick=\"".$onclick_edit."\"" : '').">";

		if ($user_data['looking_for'] == 'M') $value = lang('muža');
		elseif ($user_data['looking_for'] == 'F') $value = lang('ženu');
		elseif ($user_data['looking_for'] == 'B') $value = lang('kamarátstvo');
		elseif ($user_data['looking_for'] == 'C') $value = lang('šancu');
		elseif ($user_data['looking_for'] == 'L') $value = lang('šťastie');
		elseif ($user_data['looking_for'] == 'N') $value = lang('nikoho');
		else $value = lang('nezáleží');
		echo "<p><strong>".lang('Hľadám').':</strong> <em>'.$value."</em></p>\n";

		$value = array();
		$temp = explode('|', $user_data['looking_for_height']);
		foreach ($temp as $val) $value[] = lang($val);

		echo "<p><strong>".lang('Výška').':</strong> '.implode(', ', $value)."</p>\n";

		$value = array();
		$temp = explode('|', $user_data['looking_for_figure']);
		foreach ($temp as $val) $value[] = lang($val);

		echo "<p><strong>".lang('Postava').':</strong> '.implode(', ', $value)."</p>\n";

		$value = array();
		$temp = explode('|', $user_data['looking_for_education']);
		foreach ($temp as $val) $value[] = lang($val);

		echo "<p><strong>".lang('Vzdelanie').':</strong> '.implode(', ', $value)."</p>\n";

		$value = array();
		$temp = explode('|', $user_data['looking_for_smoking']);
		foreach ($temp as $val) $value[] = lang($val);

		echo "<p><strong>".lang('Fajčiar').':</strong> '.implode(', ', $value)."</p>\n";

		$value = array();
		$temp = explode('|', $user_data['looking_for_faith']);
		foreach ($temp as $val) $value[] = lang($val);

		echo "<p><strong>".lang('Viera').':</strong> '.implode(', ', $value)."</p>\n";

		if ($ext_user->has_right($user_data, 'user', 'edit'))
			echo "<p><span class='link-action' style='display: none;' onclick=\"".$onclick_edit."\">".lang('Upraviť')."</span>&nbsp;</p>\n";

	echo "</div>\n";

	echo "<div style='clear: both;'></div>\n";

//	$onclick_edit = "\$('#profile-header-content').load('ajax.php?act=user_about_form&id=".$user_id."');";
//
//	echo "<div id='profile-about'".($ext_user->has_right($user_data, 'user', 'edit') ? " class='editable' onclick=\"".$onclick_edit."\"" : '').">";
//
//		echo "<h3>".hsc(upper_diacritic(lang('Niečo o mne')))."</h3>\n";
//		echo "<p>".nl2br(hsc($user_data['about']))."</p>\n";
//
//		if ($ext_user->has_right($user_data, 'user', 'edit'))
//			echo "<p><span class='link-action' style='display: none;' onclick=\"".$onclick_edit."\">".lang('Upraviť')."</span>&nbsp;</p>\n";
//
//	echo "</div>\n";

	echo "<div id='profile-wall'>\n";
	echo "<div id='profile-wall-content'>\n";
	echo "<a name='wall'></a>\n";

	$writeable = ($user_id == $ext_user->id || in_array($ext_user->id, array_keys($user_friend_list)) || $ext_user->is_admin());

	echo "<form id='form-wall-post' action='' method='post' onsubmit='return false;'>\n";
	echo "<input type='hidden' name='id' value='".$user_id."' />\n";
	echo "<input type='hidden' name='from' value='".$ext_user->id."' />\n";

	echo "<div class='form-input' style='float: left; width: ".($writeable ? '91' : '85')."%;'>";
		$placeholder = ($writeable ? ($user_id == $ext_user->id ? lang('Napíš na svoju nástenku') : lang('Napíš na nástenku používateľovi %1%', '', array($user_data['user']))) : lang('%1% musí byť tvoj priateľ, aby si mohol napísať na nástenku', '', array($user_data['user'])));
		echo "<textarea name='form_text' placeholder='".hsc($placeholder)."' style='height: 44px;'".($writeable ? " onkeyup=\"if (Math.abs(parseInt(this.style.height) - parseInt(this.scrollHeight)) > 8) this.style.height = (this.scrollHeight > 44 ? this.scrollHeight : 44) + 'px';\"" : ' disabled')."></textarea>\n";
		echo "<textarea name='form_text_special' style='display: none'></textarea>\n";
	echo "</div>\n";

	echo "<div style='float: right; width: ".($writeable ? '8' : '14')."%;'>";
		if ($writeable)
			echo "<input type='button' name='send' id='send' value='&nbsp;' 
			onclick=\"
			sAjaxObj = 'profile-wall'; 
			sAjaxUrl = ''; 
			var specialTextarea = \$('div.form-input textarea[name=form_text_special]');
			if(specialTextarea.length == 0){
				\$.post('/ajax.php?act=wall_add_post&ts=' + Number(new Date()), 
					\$('#form-wall-post').serializeArray(), fnAjaxEvaluateReload);
			} else {
				\$.post('/ajax.php?act=wall_add_special_post&ts=' + Number(new Date()), 
					\$('#form-wall-post').serializeArray(), fnAjaxEvaluateReload);
			} 
			\" class='button-ok' />&nbsp;";
		
		else
			echo "<input type='button' name='send' id='send' value='".lang('Pridať priateľa')."' onclick=\"fnRedirect('".$this->conf->get_uri_by_site(13).'?user_id='.$user_id."&act=ask'); return false;\" class='button' />&nbsp;";
	echo "</div>\n";

	echo "<div style='clear: both;'></div>\n";

	echo "</form>\n";

	if (count($user_wall) > 0)
	{
		echo "<div id='profile-history'>";
		echo lang('Zobraziť príspevky').': ';
		echo "<a href='".$site_url.'?user_id='.$user_id."&amp;view=#wall'".($_SESSION['ext_user_wall_view'.$user_id] == '' ? " class='selected'" : '').'>'.lang('posledných 5')."</a> - ";
		echo "<a href='".$site_url.'?user_id='.$user_id."&amp;view=last_week#wall'".($_SESSION['ext_user_wall_view'.$user_id] == 'last_week' ? " class='selected'" : '').'>'.lang('posledný týždeň')."</a> - ";
		echo "<a href='".$site_url.'?user_id='.$user_id."&amp;view=last_month#wall'".($_SESSION['ext_user_wall_view'.$user_id] == 'last_month' ? " class='selected'" : '').'>'.lang('posledný mesiac')."</a> - ";
//			echo "<a href='".$site_url.'?user_id='.$user_id."&amp;view=last_6months#wall'".($_SESSION['ext_user_wall_view'.$user_id] == 'last_6months' ? " class='selected'" : '').'>'.lang('posledných 6 mesiacov')."</a> - ";
		echo "<a href='".$site_url.'?user_id='.$user_id."&amp;view=last_year#wall'".($_SESSION['ext_user_wall_view'.$user_id] == 'last_year' ? " class='selected'" : '').'>'.lang('posledný rok')."</a> - ";
		echo "<a href='".$site_url.'?user_id='.$user_id."&amp;view=all#wall'".($_SESSION['ext_user_wall_view'.$user_id] == 'all' ? " class='selected'" : '').'>'.lang('všetky')."</a><br/><br/>";
		echo "</div>\n";

		$link_pattern = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

		foreach ($user_wall as $val)
		{
			$link_from = "/profile".$val['id_user_from'].'/'.str_replace(array('.', '_'), '-', cms_format_alturl($val['user_from']));

			echo "<div class='wall-post-header'>";
				echo "<a name='wall-post".$val['id']."'></a>\n";
				echo date('j.n.Y - H:i', strtotime($val['insert_date']));
				if ($val['id_user_from'] == $ext_user->id || $val['id_user'] == $ext_user->id)
					echo "&nbsp;&nbsp;&nbsp;<span onclick=\"sAjaxObj = 'profile-wall'; sAjaxUrl = ''; \$.post('/ajax.php?act=wall_delete_post&ts=' + Number(new Date()), { 'id': ".$val['id']."}, fnAjaxEvaluateReload);\" class='button-delete'>".lang('Zmazať').'</span>';
				elseif ($val['status'] != '1')
					echo "&nbsp;&nbsp;&nbsp;<span style='color: red;'>".lang('Zmazané').'</span>';
			echo "</div>\n";

			echo "<div class='wall-post-photo'>";
				echo "<a href='".$link_from."'>";
				if (is_file($val['photo_small']))
					echo "<img src='".$val['photo_small'].'?'.$val['photo_profile']."' alt='".$val['user']."' />";
				else
					echo "<img src='images/profile_photo.png' alt='".$val['user']."' />";
				echo "</a>";
			echo "</div>\n";
			echo "<div class='wall-post-content'>";
				if ($val['id_user'] == $val['id_user_from'])
					echo "<a href='".$link_from."'>".$val['user_from']."</a>";
				else
					echo "<a href='".$link_from."'>".$val['user_from'].'</a> '.lang('napísal(a) na nástenku používateľa')." <a href='/profile".$user_id.'/'.str_replace(array('.', '_'), '-', cms_format_alturl($val['user']))."'>".$val['user'].'</a>';

				$text = str_replace(array("\r\n", "\n\r", "\n", "\r"), ' <br/>', hsc($val['text']));

				if (preg_match_all($link_pattern, $text, $url) > 0)
				{
					foreach ($url[0] as $link)
						$text = str_replace($link, "<a href='".$link."' target='_blank'>".$link.'</a> ', $text);

					echo '<p>'.$text.'</p>';

					$link = $url[0][0];

					if (strlen($link) > 0)
					{
						if (stripos($link, 'youtube.com') !== FALSE)
							echo "<a href='".hsc(str_replace('watch?v=', 'v/', $link))."?autoplay=1' target='_blank' class='fancybox-video' rel='wall'><img src='".$ext_user->get_video_image_path($link, $user_id)."' /></a>";
						else
//						elseif (preg_match('/(\\.png|\\.gif|\\.jpg|\\.jpeg|photo|image|img)/i', $link) > 0)
							echo "<a href='".$link."' target='_blank'><img src='".$link."' style='max-width: 800px;' onerror=\"this.style.display='none';\" /></a>";
					}
				}
				else
					echo '<p>'.$text.'</p>';

				echo "<div id='profile-wall-comment".$val['id']."'>\n";
				echo "<div id='profile-wall-comment".$val['id']."-content'>\n";

				if ($writeable)
				{
					echo "<form id='form-wall-comment".$val['id']."' action='' method='post' onsubmit='return false;'>\n";
					echo "<input type='hidden' name='id' value='".$val['id']."' />\n";
					echo "<input type='hidden' name='from' value='".$ext_user->id."' />\n";

					echo "<div class='form-input' style='float: left; width: 90%;'>";
						$placeholder = lang('Komentuj príspevok').' ...';
						echo "<textarea name='form_text' placeholder='".hsc($placeholder)."' style='height: 18px;'".($writeable ? " onkeyup=\"if (Math.abs(parseInt(this.style.height) - parseInt(this.scrollHeight)) > 8) this.style.height = (this.scrollHeight > 18 ? this.scrollHeight : 18) + 'px';\"" : ' disabled')."></textarea>\n";
					echo "</div>\n";

					echo "<div style='float: right; width: 9%;'>";
						echo "<input type='button' name='send' id='send' value='&nbsp;' onclick=\"sAjaxObj = 'profile-wall-comment".$val['id']."'; sAjaxUrl = ''; \$.post('/ajax.php?act=wall_add_comment&ts=' + Number(new Date()), \$('#form-wall-comment".$val['id']."').serializeArray(), fnAjaxEvaluateReload);\" class='button-ok-small' />&nbsp;";
					echo "</div>\n";

					echo "<div style='clear: both;'></div>\n";

					echo "</form>\n";
				}

				if (is_array($val['comment_list']) && count($val['comment_list']) > 0)
				{
					foreach ($val['comment_list'] as $val2)
					{
						$link_from = "/profile".$val2['id_user_from'].'/'.str_replace(array('.', '_'), '-', cms_format_alturl($val2['user_from']));

						echo "<div class='wall-comment-photo'>";
							echo "<a href='".$link_from."'>";
							if (is_file($val['photo_small']))
								echo "<img src='".$val2['photo_small'].'?'.$val2['photo_profile']."' alt='".$val2['user']."' />";
							else
								echo "<img src='images/profile_photo.png' alt='".$val['user']."' />";
							echo "</a>\n";
						echo "</div>\n";
						echo "<div class='wall-comment-content'>";
							echo "<a href='".$link_from."'>".$val2['user_from']."</a>";

							if ($val2['id_user_from'] == $ext_user->id || $val2['id_user'] == $ext_user->id)
								echo "<span onclick=\"sAjaxObj = 'profile-wall-comment".$val['id']."'; sAjaxUrl = ''; \$.post('/ajax.php?act=wall_delete_comment&ts=' + Number(new Date()), { 'id': ".$val2['id']."}, fnAjaxEvaluateReload);\" class='button-delete-small' style='float: right;'>&nbsp;</span>";

							$text = str_replace(array("\r\n", "\n\r", "\n", "\r"), ' <br/>', hsc($val2['text']));

							if (preg_match_all($link_pattern, $text, $url) > 0)
							{
								foreach ($url[0] as $link)
									$text = str_replace($link, "<a href='".$link."' target='_blank'>".$link.'</a> ', $text);
							}
							else
								echo '<p>'.$text.'</p>';
						echo "</div>";

						echo "<div style='clear: both;'></div>\n";
					}
				}

				echo "</div>\n";
				echo "</div>\n";

			echo "</div>\n";

			echo "<div style='clear: both;'></div>\n";
		}
	}
	else
	{
		echo '<br/><br/>'.lang('Nie sú žiadne príspevky na nástenke').'<br/><br/><br/>';
	}

	echo "</div>\n";
	echo "</div>\n";

	echo "<div id='profile-showed'>";
		echo lang('Počet zobrazení').': '.($user_data['showed_count'] + 1).'x';
	echo "</div>\n";

echo "</div>\n";

echo "<div id='tab-content1' class='tab-content' style='display: ".($tab_selected == 1 ? '' : 'none').";'>";
	if ($ext_user->has_right($user_data, 'user', 'edit'))
		echo "<p class='bottom-line'><span class='link-action' onclick=\"\$('#profile-header-content').load('ajax.php?act=user_image&id=".$user_id."&image=-1');\">".lang('Pridať novú fotku')."</span>&nbsp;</p>\n";

	if (!$ext_user->is_admin() && $user_id != $ext_user->id && !is_file($ext_user->get_profile_photo_path()))
	{
		echo '<br/><br/>'.lang('Aby si mohol vidieť fotky ostatných používateľov, musíš zadať svoju profilovú fotku').'<br/><br/><br/>';
	}
	elseif (count($user_images) > 0)
	{
		$limited = (!$ext_user->is_admin() && $user_id != $ext_user->id && count($user_images) > 3 && count($ext_user->get_images()) < 3);

		$ind = 0;

		foreach ($user_images as $val)
		{
			if ($limited && $ind >= 3)
			{
				echo "<div class='profile-image' style='height: 220px;'>";
				echo '<br/><br/><br/>'.lang('Aby si mohol vidieť všetky fotky ostatných používateľov, musíš pridať aspoň 3 fotky vo svojom profile v záložke "Moje fotky"');
				echo "</div>\n";

				continue;
			}

			if ($ind > 0 && $ind % 4 == 0) echo "<div style='clear: both;'></div>\n";

			echo "<div class='profile-image".($ext_user->has_right($user_data, 'user', 'edit') ? ' editable' : '')."'>";
			echo "<div>";

			if (is_file($val['path_large']))
				echo "<a href='".$val['path'].'?'.$val['hash']."' title='".hsc($val['title'])."' class='fancybox' rel='profile'><img src='".$val['path_large'].'?'.$val['hash']."' alt='".hsc($val['title'])."' /></a>";
			else
				echo "<img src='images/no_image.png' alt='".lang('Nenájdená fotka')."' />";

			echo "</div>\n";

			if ($ext_user->has_right($user_data, 'user', 'edit'))
			{
				echo "<p>";
				echo "<span class='link-action' style='display: none;' onclick=\"\$('#profile-header-content').load('ajax.php?act=user_image&id=".$user_id."&image=".$val['id']."');\">".lang('Upraviť')."</span>&nbsp;";
				echo "<span class='link-action' style='display: none;' onclick=\"\$('#profile-header').load('".$this->conf->get_uri_by_site(_SITE)."?act=profile_photo&id=".$user_id."&image=".$val['id']."&tab=1 #profile-header-content');\">".lang('Dať do profilu')."</span>&nbsp;";
				echo "</p>\n";
			}
			else
				echo get_elapsed_time($val['insert_date']);

			echo "</div>\n";

			$ind ++;
		}

		echo "<div style='clear: both;'></div>\n";
	}
	else
	{
		echo '<br/><br/>'.lang('Nie sú pridané žiadne obrázky').'<br/><br/><br/>';
	}
echo "</div>\n";

echo "<div id='tab-content2' class='tab-content' style='display: ".($tab_selected == 2 ? '' : 'none').";'>";
	if ($ext_user->has_right($user_data, 'user', 'edit'))
		echo "<p class='bottom-line'><span class='link-action' onclick=\"\$('#profile-header-content').load('ajax.php?act=user_video&id=".$user_id."&video=-1');\">".lang('Pridať nové video')."</span>&nbsp;</p>\n";

	if (count($user_videos) > 0)
	{
		$ind = 0;

		foreach ($user_videos as $val)
		{
			if ($ind > 0 && $ind % 3 == 0) echo "<div style='clear: both;'></div>\n";

			echo "<div class='profile-video".($ext_user->has_right($user_data, 'user', 'edit') ? ' editable' : '')."'>";
			echo "<div>";

			if (strlen($val['link']) > 0)
				echo "<a href='".hsc(str_replace('watch?v=', 'v/', $val['link']))."?autoplay=1' target='_blank' title='".hsc($val['title'])."' class='fancybox-video' rel='profilevideo'><img src='".$ext_user->get_video_image_path($val['link'], $user_id)."' alt='".hsc($val['title'])."' /></a>";
			else
				echo "<img src='images/no_video.png' alt='".lang('Nenájdené video')."' />";

			echo "</div>\n";

			if ($ext_user->has_right($user_data, 'user', 'edit'))
				echo "<p><span class='link-action' style='display: none;' onclick=\"\$('#profile-header-content').load('ajax.php?act=user_video&id=".$user_id."&video=".$val['id']."');\">".lang('Upraviť')."</span>&nbsp;</p>\n";
			else
				echo get_elapsed_time($val['insert_date']);

			echo "</div>\n";

			$ind ++;
		}

		echo "<div style='clear: both;'></div>\n";
	}
	else
	{
		echo '<br/><br/>'.lang('Nie sú pridané žiadne videá').'<br/><br/><br/>';
	}
echo "</div>\n";

echo "<div id='tab-content3' class='tab-content' style='display: ".($tab_selected == 3 ? '' : 'none').";'>";
	if ($ext_user->has_right($user_data, 'friend', 'edit'))
		echo "<p class='bottom-line'><span class='link-action' onclick=\"fnRedirect('".$this->conf->get_uri_by_site(13)."'); return false;\">".lang('Pridať alebo zmazať priateľa')."</span>&nbsp;</p>\n";

	if (count($user_friends) > 0)
	{
		foreach ($user_friends as $val)
		{
			$key = ($val['id_user_from'] == $user_id ? $val['id_user_to'] : $val['id_user_from']);
			$row = $user_friend_list[$key];

			if (!is_array($row)) continue;

			echo "<div class='user-medium".($row['status'] == '1' ? '' : ' disabled')."' style='width: 12.5%;'>";

			echo "<a href='/profile".$row['id'].'/'.str_replace(array('.', '_'), '-', cms_format_alturl($row['user']))."' title='".$row['user']."'>";

			if (is_file($row['photo_medium']))
				echo "<img src='".$row['photo_medium'].'?'.$row['photo_profile']."' alt='".$row['user']."' />";
			else
				echo "<img src='images/profile_photo.png' alt='".$row['user']."' />";

			echo "</a>";

			echo "<h3>".$row['user']."</h3>";
			echo "<em>".get_age_by_date($row['birthday'])."</em><br/>";
			echo get_elapsed_time($val['datetime']);

			echo "</div>\n";
		}

		echo "<div style='clear: both;'></div>\n";
	}
	else
	{
		echo '<br/><br/>'.lang('Nie sú žiadni pridaní priatelia').'<br/><br/><br/>';
	}
echo "</div>\n";

if ($ext_user->has_right($user_data, 'user', 'visits'))
{
	echo "<div id='tab-content4' class='tab-content' style='display: ".($tab_selected == 4 ? '' : 'none').";'>";
		if (is_array($user_data['last_viewed_by_user']) && count($user_data['last_viewed_by_user']) > 0)
		{
			foreach ($user_data['last_viewed_by_user'] as $key => $val)
			{
				$row = $user_visit_list[$key];

				if (!is_array($row)) continue;

				echo "<div class='user-medium".($row['status'] == '1' ? '' : ' disabled')."' style='width: 12.5%;'>";

				echo "<a href='/profile".$row['id'].'/'.str_replace(array('.', '_'), '-', cms_format_alturl($row['user']))."' title='".$row['user']."'>";

				if (is_file($row['photo_medium']))
					echo "<img src='".$row['photo_medium'].'?'.$row['photo_profile']."' alt='".$row['user']."' />";
				else
					echo "<img src='images/profile_photo.png' alt='".$row['user']."' />";

				echo "</a>";

				echo "<h3>".$row['user']."</h3>";
				echo "<em>".get_age_by_date($row['birthday'])."</em><br/>";
				echo get_elapsed_time($val['datetime']);

				echo "</div>\n";
			}

			echo "<div style='clear: both;'></div>\n";
		}
		else
			echo '<br/><br/>'.lang('Nie sú žiadne návštevy profilu').'<br/><br/><br/>';
	echo "</div>\n";
}

echo "</div>\n";
echo "</div>\n";



echo "<script language='javascript' type='text/javascript'>\n";
echo "<!--\n";

echo "	function fnChangeTab(iTab)\n";
echo "	{\n";
echo "		$('.tab-button').filter(function(index) { return this.id != '#tab-button' + iTab; }).removeClass('selected');\n";
echo "		$('#tab-button' + iTab).addClass('selected');\n";

echo "		$('.tab-content').filter(function(index) { return this.id != '#tab-content' + iTab; }).hide();\n";
echo "		$('#tab-content' + iTab).show();\n";
echo "	}\n";

if ($tab_selected <= 0)
	echo "	\$('html,body').animate({scrollTop:$('#profile-wall').offset().top}, 1000);\n";

echo "-->\n";
echo "</script>\n";

