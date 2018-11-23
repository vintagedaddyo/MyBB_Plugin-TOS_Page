<?php
/*
 * MyBB: TOS Page
 *
 * File: TOS_page.php
 * 
 * Authors: Chris Boulton, Samuel, Vintagedaddyo
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.0
 * 
 */
 
// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Plugin add hooks

$plugins->add_hook('global_start', 'TOS_page_toplink_start');
$plugins->add_hook('misc_start', 'TOS_page');
$plugins->add_hook('fetch_wol_activity_end', 'TOS_page_online_activity');
$plugins->add_hook('build_friendly_wol_location_end', 'TOS_page_online_location');

// Plugin info

function TOS_page_info()
{

    global $lang;

    $lang->load('TOS_page');
    
    $lang->TOS_page_Desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->TOS_page_Desc;

    return Array(
        'name' => $lang->TOS_page_Name,
        'description' => $lang->TOS_page_Desc,
        'website' => $lang->TOS_page_Web,
        'author' => $lang->TOS_page_Auth,
        'authorsite' => $lang->TOS_page_AuthSite,
        'version' => $lang->TOS_page_Ver,
        'codename' => $lang->TOS_page_CodeName,
        'compatibility' => $lang->TOS_page_Compat
    );
}

// Plugin activation

function TOS_page_activate()
{
	
	global $db, $lang;

// Plugin include language 

 $lang->load('TOS_page');
	
	$TOSpage = array(
		'name' => 'TOS_page',
  'title'          => ''.$lang->TOS_page_Setting_0_Title.'', 
  'description'    => ''.$lang->TOS_page_Setting_0_Description.'',
		'disporder' => '403',
		'isdefault' => '0'
		);
		
	$group['gid'] = $db->insert_query('settinggroups', $TOSpage);
	$gid = $db->insert_id();
	
	$TOSlist = array(
		'name' => 'TOS_list',
  'title'          => ''.$lang->TOS_page_Setting_1_Title.'', 
  'description'    => ''.$lang->TOS_page_Setting_1_Description.'',
		'optionscode' => 'textarea',
		'value' =>       ''.$lang->TOS_page_Setting_1_Value.'',
		'disporder' => '1',
		'gid' => intval($gid)
		);
		
	$db->insert_query('settings', $TOSlist);

	rebuild_settings();

	$insert_query[] = array("tid" => "0","title" => "misc_TOS", "template" => $db->escape_string("<head>\n<title>{\$mybb->settings['bbname']} - {\$lang->TOS_page_title}</title>\n{\$headerinclude}\n</head>\n<body>\n	{\$header}\n	<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" class=\"tborder\" style=\"clear: both;\">\n	<tr>\n			<td class=\"thead\"><strong>{\$mybb->settings['bbname']} {\$lang->TOS_page_title}</strong></td>\n		</tr>\n		<tr>\n			<td class=\"trow1\">{\$TOS_list}</td>\n		</tr>\n	</table>\n	{\$footer}\n</body>\n</html>"), "sid" => "-1");

	$db->insert_query_multiple("templates", $insert_query);

	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("header", "#".preg_quote('toplinks_help}</a></li>')."#i", 'toplinks_help}</a></li>
<li class="TOS_link"><a href="{\$mybb->settings[\'bburl\']}/misc.php?action=TOS" class="TOS" style="background: url(images/toplinks/TOS.png) no-repeat;" border="0" alt="" />{\$lang->TOS_page_link}</a></li>');

}

// Plugin deactivation

function TOS_page_deactivate()
{
	
	global $db;
	
	$db->delete_query("settinggroups", "name = 'TOS_page'");

	$db->delete_query('templates', 'title = "misc_TOS"');

	$db->write_query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN(
		'TOS_list'
	)");

	rebuild_settings();

	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("header", '#
<li class="TOS_link">(.*)</li>#', '', 0);

}

// Plugin page

function TOS_page()
{
	
	global $mybb, $templates, $theme, $header, $footer, $headerinclude, $TOS, $lang;

// Plugin include language 

 $lang->load('TOS_page');
  
	if($mybb->input['action'] != "TOS")
	{
		return;
	}

	add_breadcrumb("{$lang->TOS_page_breadcrumb}");

	$TOS_list = nl2br($mybb->settings['TOS_list']);

	eval("\$TOS = \"".$templates->get("misc_TOS")."\";");

	output_page($TOS);

}

// Plugin online activity

function TOS_page_online_activity($user_activity)
{
	
	global $parameters, $tid_list;
  	
	if($user_activity['activity'] == "misc" && $parameters['action'] == "TOS")
	{
		
		$user_activity['activity'] = "misc_TOS";
		
		return $user_activity;
	}

}

// Plugin online location

function TOS_page_online_location($plugin_array)
{
	
	global $lang, $threads, $mybb, $TOS;

// Plugin include language 

 $lang->load('TOS_page');
 	
	if($plugin_array['user_activity']['activity'] == "misc_TOS")
	{
		$plugin_array['location_name'] = "$lang->TOS_page_online_viewing <a href=\"misc.php?action=TOS\">$lang->TOS_page_online_link</a>";		
		
		return $plugin_array;
	}
	
}
	
function TOS_page_toplink_start()
{ 
	
	global $mybb, $templates, $theme, $header, $TOS, $lang;

// Plugin include language 

 $lang->load('TOS_page');
  
}

?>