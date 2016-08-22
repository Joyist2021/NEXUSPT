<?php
require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
loggedinorreturn();

$id = 0 + $_GET['id'];
if (!$id)
	die('没有种子ID');

$res = sql_query("SELECT torrents.*, categories.mode as cat_mode FROM torrents LEFT JOIN categories ON category = categories.id WHERE torrents.id = $id");
$row = mysql_fetch_array($res);
if (!$row) die('没有种子');

if ($enablespecial == 'yes' && (get_user_class() >= $movetorrent_class||$CURUSER["picker"] == 'yes'))
	$allowmove = true; //enable moving torrent to other section
else {$allowmove = false;
}

if ($enablespecial2 != 'yes'|| get_user_class() < $movetorrent_class)$specialcatmode2=0;


$sectionmode = $row['cat_mode'];

if ($sectionmode == $browsecatmode)
{
	$othermode = $specialcatmode;
	$othermode2 = $specialcatmode2;
	$movenote = $lang_edit['text_move_to_special'];
}
elseif($sectionmode == $specialcatmode)
{
	$othermode = $browsecatmode;
	$othermode2 = $specialcatmode2;
	$movenote = $lang_edit['text_move_to_browse'];
}
else
{
	$othermode = $browsecatmode;
	$othermode2 = $specialcatmode;
	$movenote = $lang_edit['text_move_to_browse'];
}











$showsource = (get_searchbox_value($sectionmode, 'showsource') || ($allowmove && (get_searchbox_value($othermode, 'showsource')||get_searchbox_value($othermode2, 'showsource')))); //whether show sources or not
$showmedium = (get_searchbox_value($sectionmode, 'showmedium') || ($allowmove &&(get_searchbox_value($othermode, 'showmedium')||get_searchbox_value($othermode2, 'showmedium')))); //whether show media or not
$showcodec = (get_searchbox_value($sectionmode, 'showcodec') || ($allowmove && (get_searchbox_value($othermode, 'showcodec')||get_searchbox_value($othermode2, 'showcodec')))); //whether show codecs or not
$showstandard = (get_searchbox_value($sectionmode, 'showstandard') || $allowmove && (get_searchbox_value($othermode, 'showstandard')||get_searchbox_value($othermode2, 'showstandard'))); //whether show standards or not
$showprocessing = (get_searchbox_value($sectionmode, 'showprocessing') || $allowmove && (get_searchbox_value($othermode, 'showprocessing')||get_searchbox_value($othermode2, 'showprocessing'))); //whether show processings or not
$showteam = (get_searchbox_value($sectionmode, 'showteam') || ($allowmove && (get_searchbox_value($othermode, 'showteam')||get_searchbox_value($othermode2, 'showteam')))); //whether show teams or not
$showaudiocodec = (get_searchbox_value($sectionmode, 'showaudiocodec') || $allowmove &&(get_searchbox_value($othermode, 'showaudiocodec')||get_searchbox_value($othermode, 'showaudiocodec'))); //whether show audio codecs or not

stdhead($lang_edit['head_edit_torrent'] . "\"". $row["name"] . "\"");
?> <script type="text/javascript" src="common.php<?php $cssupdatedate=($cssdate_tweak ? "?".htmlspecialchars($cssdate_tweak) : "");echo $cssupdatedate?>"></script> <?

if (!isset($CURUSER) || $CURUSER["id"] != $row["owner"] && (get_user_class() < $torrentmanage_class&&$CURUSER["picker"] != 'yes')) {
	print("<h1 align=\"center\">".$lang_edit['text_cannot_edit_torrent']."</h1>");
	print("<p>".$lang_edit['text_cannot_edit_torrent_note']."</p>");
}
elseif(($CURUSER["id"] == $row["owner"]||get_user_class() >= $torrentmanage_class)) {
	print("<form method=\"post\" id=\"compose\" name=\"edittorrent\" action=\"takeedit.php\" enctype=\"multipart/form-data\">");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\" />");
	if (isset($_GET["returnto"]))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />");
	print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"98%\">\n");
	print("<tr><td class='colhead' colspan='2' align='center'>".htmlspecialchars($row["name"])."</td></tr>");
	tr($lang_edit['row_torrent_name']."<font color=\"red\">*</font>", "<input type=\"text\" style=\"width: 650px;\" name=\"name\" id=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" /><br><b><font class=\"medium\" id=\"texttorrentnamenote\" ></font></b>", 1);
	if ($smalldescription_main == 'yes')
		tr($lang_edit['row_small_description'], "<input type=\"text\" style=\"width: 650px;\" name=\"small_descr\" value=\"" . htmlspecialchars($row["small_descr"]) . "\" /><br><b><font class=\"medium\" id=\"texttorrentsmaillnamenote\"></font></b>", 1);

	get_external_tr($row["url"],$row["urltype"]);

	if ($enablenfo_main=='yes')
		tr($lang_edit['row_nfo_file'], "<font class=\"medium\">
	<input type=\"radio\" name=\"nfoaction\" value=\"remove\" />".$lang_edit['radio_remove'].
	"<input id=\"nfoupdate\" type=\"radio\" name=\"nfoaction\" value=\"update\" checked=\"checked\" />".$lang_edit['radio_update']."
	
	</font><input type=\"file\" name=\"nfo\" onchange=\"document.getElementById('nfoupdate').checked=true\" />", 1);
	
	
	print("<tr><td class=\"rowhead\">".$lang_edit['row_description']."<font color=\"red\">*</font></td><td class=\"rowfollow\">");
	textbbcode("edittorrent","descr",($row["descr"]), false);
	print("</td></tr>");
	$s = "<select name=\"type\" id=\"browsecat\" onchange=\"javascript:secondtype();notechange()\" >";
	//$s = "<select name=\"type\" id=\"oricat\"  >";
	$cats = genrelist($sectionmode);
	foreach ($cats as $subrow) {
		$s .= "<option value=\"" . $subrow["id"] . "\"";
		if ($subrow["id"] == $row["category"])
		$s .= " selected=\"selected\"";
		$s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
	}
	
		if ($allowmove){
		
		
		$cats2 = genrelist($othermode);
		foreach ($cats2 as $subrow) {
			$s .= "<option value=\"" . $subrow["id"] . "\"";
			if ($subrow["id"] == $row["category"])
			$s .= " selected=\"selected\"";
			$s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
		}
		
				$cats3 = genrelist($othermode2);
		foreach ($cats3 as $subrow) {
			$s .= "<option value=\"" . $subrow["id"] . "\"";
			if ($subrow["id"] == $row["category"])
			$s .= " selected=\"selected\"";
			$s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
		}
		

		
		
		
		}

	$s .= "</select>\n";
	if ($allowmove){
		$s2 = "<select name=\"type\" id=newcat disabled>\n";
		$cats2 = genrelist($othermode);
		foreach ($cats2 as $subrow) {
			$s2 .= "<option value=\"" . $subrow["id"] . "\"";
			if ($subrow["id"] == $row["category"])
			$s2 .= " selected=\"selected\"";
			$s2 .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
		}
		$s2 .= "</select>\n";
		$movecheckbox = "<input type=\"checkbox\" id=movecheck name=\"movecheck\" value=\"1\" onclick=\"disableother2('oricat','newcat')\" />";
	}
	//tr($lang_edit['row_type']."<font color=\"red\">*</font>", $s.($allowmove ? "&nbsp;&nbsp;".$movecheckbox.$movenote.$s2 : ""), 1);
	//if ($showsource || $showmedium || $showcodec || $showaudiocodec || $showstandard || $showprocessing)
	{
	

	
	
	
		if ($showsource){
			$source_select = torrent_selection($lang_edit['text_source'],"source_sel","sources",$row["source"]);
		}
		else $source_select = "";

		if ($showmedium){
			$medium_select = torrent_selection($lang_edit['text_medium'],"medium_sel","media",$row["medium"]);
		}
		else $medium_select = "";

		if ($showcodec){
			$codec_select = torrent_selection($lang_edit['text_codec'],"codec_sel","codecs",$row["codec"]);
		}
		else $codec_select = "";

		if ($showaudiocodec){
			$audiocodec_select = torrent_selection("","audiocodec_sel","audiocodecs",$row["audiocodec"],true);
		}
		else $audiocodec_select = "";

		if ($showstandard){
			$standard_select = torrent_selection($lang_edit['text_standard'],"standard_sel","standards",$row["standard"]);
		}
		else $standard_select = "";

		if ($showprocessing){
			$processing_select = torrent_selection($lang_edit['text_processing'],"processing_sel","processings",$row["processing"]);
		}
		else $processing_select = "";
		
		if ($showteam){
			$team_select = torrent_selection($lang_edit['text_team'],"team_sel","teams",$row["team"]);
		}
		else $showteam = "";

		tr($lang_edit['row_quality']."<font color=\"red\">*</font>","<b>".$lang_edit['row_type'].":&nbsp;</b>". $s.$audiocodec_select."<b><font class=\"medium\" id=\"texttorrentsecondnote\" ></font></b><br />" .$source_select . $medium_select . $codec_select .  $standard_select .$team_select. $processing_select, 1);

	}

	/*if ($showteam){
		if ($showteam){
			$team_select = torrent_selection($lang_edit['text_team'],"team_sel","teams",$row["team"]);
		}
		else $showteam = "";

		tr($lang_edit['row_content'],$team_select,1);
	}*/
	tr($lang_edit['row_check'], "<input type=\"checkbox\" name=\"visible\"" . ($row["visible"] == "yes" ? " checked=\"checked\"" : "" ) . " value=\"1\" /> ".$lang_edit['checkbox_visible']."&nbsp;&nbsp;&nbsp;".(get_user_class() >= $beanonymous_class || get_user_class() >= $torrentmanage_class ? "<input type=\"checkbox\" name=\"anonymous\"" . ($row["anonymous"] == "yes" ? " checked=\"checked\"" : "" ) . " value=\"1\" />".$lang_edit['checkbox_anonymous_note']."&nbsp;&nbsp;&nbsp;" : "").(get_user_class() >= $torrentmanage_class ? "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"yes\" /> ".$lang_edit['checkbox_banned'] : ""), 1);
	//if (get_user_class()>= $torrentsticky_class || (get_user_class() >= $torrentmanage_class && $CURUSER["picker"] == 'yes')){
	if (get_user_class()>= $torrentsticky_class||$CURUSER["picker"] == 'yes'){
		$pickcontent = "";
	
		//if(get_user_class()>=$torrentsticky_class)
		{
			if(get_user_class()>=$torrentonpromotion_class){
			$pickcontent .= "<b>".$lang_edit['row_special_torrent'].":&nbsp;</b>"."<select name=\"sel_spstate\" style=\"width: 100px;\">" .promotion_selection($row["sp_state"], 0). "</select>&nbsp;&nbsp;&nbsp;";
			
			$timeout=($row["promotion_time_type"]!=2)?date("Y-m-d H:i:s",TIMENOW):$row["promotion_until"];
			
						$pickcontent .= "<select name=\"promotion_time_type\" style=\"width: 100px;\">" .
			"<option" . (($row["promotion_time_type"] == 0) ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_use_global_setting']."</option>" .
			"<option" . (($row["promotion_time_type"] == 1) ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_forever']."</option>" .
			"<option" . (($row["promotion_time_type"] == 2) ? " selected=\"selected\"" : "" ) . " value=\"2\">".$lang_edit['select_until']."</option>" .
			"</select>&nbsp;&nbsp;&nbsp;<input type=\"text\" style=\"width: 200px\" name=\"promotionuntil\" value=\"" . $timeout . "\" />".$lang_edit['text_promotion_until_note']. "<br />";
			}
			
			if(get_user_class()>=$torrentsticky_class){
			
			$pickcontent .= "<b>".$lang_edit['row_torrent_position'].":&nbsp;</b>"."<select name=\"sel_posstate\" style=\"width: 100px;\">" .
			"<option" . (($row["pos_state"] == "normal") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_normal']."</option>" .
			"<option" . (($row["pos_state"] == "sticky") ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_sticky']."</option>" .
			"</select>&nbsp;&nbsp;&nbsp;";}
		}
		if(get_user_class()>=$torrentmanage_class || $CURUSER["picker"] == 'yes')
		{
			$pickcontent .= "<b>".$lang_edit['row_recommended_movie'].":&nbsp;</b>"."<select name=\"sel_recmovie\" style=\"width: 100px;\">" .
			"<option" . (($row["picktype"] == "normal") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_normal']."</option>" .
			"<option" . (($row["picktype"] == "hot") ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_hot']."</option>" .
			"<option" . (($row["picktype"] == "classic") ? " selected=\"selected\"" : "" ) . " value=\"2\">".$lang_edit['select_classic']."</option>" .
			"<option" . (($row["picktype"] == "recommended") ? " selected=\"selected\"" : "" ) . " value=\"3\">".$lang_edit['select_recommended']."</option>" .
			"</select>";
		}
		tr($lang_edit['row_pick'], $pickcontent, 1);
	}

	print("<tr><td class=\"toolbox\" colspan=\"2\" align=\"center\"><input id=\"qr\" type=\"submit\" value=\"".$lang_edit['submit_edit_it']."\" onclick='javascript:{closealltags();this.disabled=true;this.form.submit()}' /> <input type=\"reset\" value=\"".$lang_edit['submit_revert_changes']."\" /></td></tr>\n");
	print("</table>\n");
	print("</form>\n");
	
	if($row['added']==$row["last_action"]){
	
	print("<br /><br />");

	print("<form method=\"post\" action=\"delete.php\">\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\" />\n");
	if (isset($_GET["returnto"]))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
	print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
	print("<tr><td class=\"colhead\" align=\"left\" style='padding-bottom: 3px' colspan=\"2\">".$lang_edit['text_delete_torrent']."</td></tr>");
	print("<input type=\"hidden\" style=\"width: 200px\" name=\"reason[]\" />");
	tr("<input name=\"reasontype\" type=\"radio\"  checked=\"checked\" value=\"3\" />&nbsp;".$lang_edit['radio_nuked'], "<input type=\"text\" style=\"width: 200px\"  value=\"做种失败\" name=\"reason[]\" />", 1);
	print("<tr><td class=\"toolbox\" colspan=\"2\" align=\"center\"><input type=\"submit\" style='height: 25px' value=\"".$lang_edit['submit_delete_it']."\" /></td></tr>\n");
	print("</table>");
	print("</form>\n");
	
	}
	elseif(get_user_class() >= $torrentmanage_class||$CURUSER["picker"] == 'yes'){
	print("<br /><br />");

	print("<form method=\"post\" action=\"delete.php\">\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\" />\n");
	if (isset($_GET["returnto"]))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
	print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
	print("<tr><td class=\"colhead\" align=\"left\" style='padding-bottom: 3px' colspan=\"2\">".$lang_edit['text_delete_torrent']."</td></tr>");
	tr("<input name=\"reasontype\" type=\"radio\" value=\"1\" />&nbsp;".$lang_edit['radio_dead'], $lang_edit['text_dead_note'], 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"2\" />&nbsp;".$lang_edit['radio_dupe'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />", 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"3\" />&nbsp;".$lang_edit['radio_nuked'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />", 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"4\" />&nbsp;".$lang_edit['radio_rules'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />".$lang_edit['text_req'], 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"5\" checked=\"checked\" />&nbsp;".$lang_edit['radio_other'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />".$lang_edit['text_req'], 1);
	print("<tr><td class=\"toolbox\" colspan=\"2\" align=\"center\"><input type=\"submit\" style='height: 25px' value=\"".$lang_edit['submit_delete_it']."\" /></td></tr>\n");
	print("</table>");
	print("</form>\n");
	}
}elseif($CURUSER["picker"] == 'yes'){

	print("<form method=\"post\" id=\"compose\" name=\"edittorrent\" action=\"takeedit.php\" enctype=\"multipart/form-data\">");
	print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"940\">\n");
	print("<tr><td class='colhead' colspan='2' align='center'>".htmlspecialchars($row["name"])."</td></tr>");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\" />");
		$pickcontent = "";
		/*$pickcontent .= "<b>".$lang_edit['row_torrent_position'].":&nbsp;</b>"."<select name=\"sel_posstate\" style=\"width: 100px;\">" .
		"<option" . (($row["pos_state"] == "normal") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_normal']."</option>" .
		"<option" . (($row["pos_state"] == "sticky") ? " selected=\"selected\"" : "" ) . " value=\"1\">".$lang_edit['select_sticky']."</option>" .
		"</select>&nbsp;&nbsp;&nbsp;";*/

		//if(get_user_class()>=$torrentmanage_class && $CURUSER["picker"] == 'yes')

			$pickcontent .= "<select name=\"sel_recmovie\" style=\"width: 100px;\">" .
			"<option" . (($row["picktype"] == "normal") ? " selected=\"selected\"" : "" ) . " value=\"0\">".$lang_edit['select_normal']."</option>" .
			
			"<option" . (($row["picktype"] == "classic") ? " selected=\"selected\"" : "" ) . " value=\"2\">".$lang_edit['select_classic']."</option>" .
			"<option" . (($row["picktype"] == "recommended") ? " selected=\"selected\"" : "" ) . " value=\"3\">".$lang_edit['select_recommended']."</option>" .
			"</select>";
		//tr($lang_edit['row_pick'], $pickcontent, 1);
		print("<tr><td   class=rowhead><b>".$lang_edit['row_recommended_movie'].":&nbsp;</b></td><td align=\"left\" style='padding: 0px;'>&nbsp;&nbsp;".$pickcontent."</td></tr>");
		get_external_tr2($row["url"],$row["urltype"]);
		tr($lang_edit['row_check'], "<input type=\"checkbox\" name=\"visible\"" . ($row["visible"] == "yes" ? " checked=\"checked\"" : "" ) . " value=\"1\" /> ".$lang_edit['checkbox_visible']."&nbsp;&nbsp;&nbsp;".( "<input type=\"checkbox\" name=\"anonymous\"" . ($row["anonymous"] == "yes" ? " checked=\"checked\"" : "" ) . " value=\"1\" />".$lang_edit['checkbox_anonymous_note']."&nbsp;&nbsp;&nbsp;").("<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"yes\" /> ".$lang_edit['checkbox_banned'] ), 1);

		
		
	$s = "<select name=\"type\" id=\"oricat\"  >";
	$cats = genrelist($sectionmode);
	foreach ($cats as $subrow) {
		$s .= "<option value=\"" . $subrow["id"] . "\"";
		if ($subrow["id"] == $row["category"])
		$s .= " selected=\"selected\"";
		$s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
	}

	$s .= "</select>\n";
	if ($allowmove){
		$s2 = "<select name=\"type\" id=newcat disabled>\n";
		$cats2 = genrelist($othermode);
		foreach ($cats2 as $subrow) {
			$s2 .= "<option value=\"" . $subrow["id"] . "\"";
			if ($subrow["id"] == $row["category"])
			$s2 .= " selected=\"selected\"";
			$s2 .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
		}
		
		$cats3 = genrelist($othermode2);
		foreach ($cats3 as $subrow) {
			$s2 .= "<option value=\"" . $subrow["id"] . "\"";
			if ($subrow["id"] == $row["category"])
			$s2 .= " selected=\"selected\"";
			$s2 .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
		}
		

		$s2 .= "</select>\n";
		$movecheckbox = "<input type=\"checkbox\" id=movecheck name=\"movecheck\" value=\"1\" onclick=\"disableother2('oricat','newcat')\" />";
	}
	tr($lang_edit['row_type']."<font color=\"red\">*</font>", $s.($allowmove ? "&nbsp;&nbsp;".$movecheckbox.$movenote.$s2 : ""), 1);
	
	
	
	print("<tr><td class=\"toolbox\" colspan=\"2\" align=\"center\"><input id=\"qr\" type=\"submit\" value=\"".$lang_edit['submit_edit_it']."\" /> <input type=\"reset\" value=\"".$lang_edit['submit_revert_changes']."\" /></td></tr>\n");
	print("</table>\n");
	print("</form>\n");
	
	print("<br /><br />");

	print("<form method=\"post\" action=\"delete.php\">\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\" />\n");
	if (isset($_GET["returnto"]))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
	print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
	print("<tr><td class=\"colhead\" align=\"left\" style='padding-bottom: 3px' colspan=\"2\">".$lang_edit['text_delete_torrent']."</td></tr>");
	tr("<input name=\"reasontype\" type=\"radio\" value=\"1\" />&nbsp;".$lang_edit['radio_dead'], $lang_edit['text_dead_note'], 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"2\" />&nbsp;".$lang_edit['radio_dupe'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />", 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"3\" />&nbsp;".$lang_edit['radio_nuked'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />", 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"4\" />&nbsp;".$lang_edit['radio_rules'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />".$lang_edit['text_req'], 1);
	tr("<input name=\"reasontype\" type=\"radio\" value=\"5\" checked=\"checked\" />&nbsp;".$lang_edit['radio_other'], "<input type=\"text\" style=\"width: 200px\" name=\"reason[]\" />".$lang_edit['text_req'], 1);
	print("<tr><td class=\"toolbox\" colspan=\"2\" align=\"center\"><input type=\"submit\" style='height: 25px' value=\"".$lang_edit['submit_delete_it']."\" /></td></tr>\n");
	print("</table>");
	print("</form>\n");




















}
print("<script>javascript:secondtype();notechange();</script> ");
stdfoot();
?>