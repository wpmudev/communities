<?php
/*
Plugin Name: Communities
Plugin URI: http://premium.wpmudev.org/project/communities
Description: Create internal communities with their own discussion boards, wikis, news dashboards, user lists and messaging facilities
Author: Paul Menard (Incsub)
Version: 1.1.9.5
Author URI: http://premium.wpmudev.org/
WDP ID: 67
*/

/*
Copyright 2009-2011 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$communities_current_version = '1.1.9.5';
//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//
$communities_notifications_default = 'digest'; // 'digest', 'instant', OR 'none'
$communities_text_domain = 'communities'; // 'digest', 'instant', OR 'none'

include_once( dirname(__FILE__) . '/lib/dash-notices/wpmudev-dash-notification.php');

$COMMUNITIES_ALLOWED_CONTENT_TAGS = array(
	'a' 		=> 	array('href' => array(),'title' => array()),
  	'p'			=>	array(),
	'ul'		=>	array(),
	'li'		=>	array(),
	'br'		=>	array(),
	'strong'	=>	array(),
	'img'		=>	array()
);

//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//

if (preg_match('/mu\-plugin/', PLUGINDIR) > 0) {
    load_muplugin_textdomain($communities_text_domain, dirname(plugin_basename(__FILE__)).'/communities-languages/');
} else {
	load_plugin_textdomain( $communities_text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/communities-languages/' );
}

$communities_notifications_digest_subject = __("COMMUNITY_NAME newsletter", $communities_text_domain);
$communities_notifications_digest_content = change_email_body_to_html( __(
"Hi COMMUNITY_NAME subscriber,

The following new items have been posted to COMMUNITY_NAME over the
last 24 hours, click on the link next to each item to access it on the site.

TOPICS

PAGES

NEWS

Cheers, The SITE_NAME Team


To receive instant notifications or to turn off notifications please
visit: NOTIFCATIONS_URL", $communities_text_domain ) );

$communities_notifications_instant_news_subject = __("New news at COMMUNITY_NAME", $communities_text_domain);
$communities_notifications_instant_news_content = change_email_body_to_html(  __(
"Hi COMMUNITY_NAME subscriber,

There's a new news item called 'NEWS_ITEM_TITLE' at COMMUNITY_NAME.

Click on the following link to read it: NEWS_ITEM_URL

Cheers, The SITE_NAME Team


To receive daily digest notifications or to turn off notifications
please visit: NOTIFCATIONS_URL", $communities_text_domain ) );

$communities_notifications_instant_page_subject = __("New wiki page at COMMUNITY_NAME", $communities_text_domain);
$communities_notifications_instant_page_content = change_email_body_to_html( __(
"Hi COMMUNITY_NAME subscriber,

There's a new wiki page called 'PAGE_TITLE' at COMMUNITY_NAME.

Click on the following link to read it: PAGE_URL

Cheers, The SITE_NAME Team


To receive daily digest notifications or to turn off notifications
please visit: NOTIFCATIONS_URL", $communities_text_domain ) );

$communities_notifications_instant_topic_subject = __("New topic at COMMUNITY_NAME", $communities_text_domain);
$communities_notifications_instant_topic_content = change_email_body_to_html( __(
"Hi COMMUNITY_NAME subscriber,

There's a new topic called 'TOPIC_TITLE' at COMMUNITY_NAME.

Click on the following link to read it: TOPIC_URL

Cheers, The SITE_NAME Team


To receive daily digest notifications or to turn off notifications
please visit: NOTIFCATIONS_URL", $communities_text_domain ) );



//check for activating
if ((!isset($_GET['key'])) || (empty($_GET['key'])))  {
	add_action('admin_head', 'communities_make_current');
}

if ( (isset($_GET['action'])) && (sanitize_text_field($_GET['action']) == 'dashboard') ) {
	add_action('admin_head','communities_dashboard_css');
}

if ( (isset($_GET['action'])) && (sanitize_text_field($_GET['action']) == 'digest_notifications') ) {
	communities_digest_notifications();
}
add_action('admin_init', 'communities_admin_init');
add_action('admin_menu', 'communities_plug_pages');
add_action('wpabar_menuitems', 'communities_admin_bar');
add_action('communities_digest_notifications_cron', 'communities_digest_notifications');
register_activation_hook(__FILE__, 'communities_plugin_install');

//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//

//replaced \n \r endings on <br />
function change_email_body_to_html( $body ) {
    return preg_replace( '/\r\n|\n\r|\n|\r/', '<br />', $body );
}


function communities_make_current() {
	global $wpdb, $communities_current_version;
	if (get_site_option( "communities_version" ) == '') {
		add_site_option( 'communities_version', '0.0.0' );
	}

	if (get_site_option( "communities_version" ) == $communities_current_version) {
		// do nothing
	} else {
		//up to current version
		update_site_option( "communities_installed", "no" );
		update_site_option( "communities_version", $communities_current_version );
	}
	//communities_global_install();
	//--------------------------------------------------//
	if (get_option( "communities_version" ) == '') {
		add_option( 'communities_version', '0.0.0' );
	}

	if (get_option( "communities_version" ) == $communities_current_version) {
		// do nothing
	} else {
		//up to current version
		update_option( "communities_version", $communities_current_version );
		//communities_blog_install();
	}
}

function communities_blog_install() {
	global $wpdb, $communities_current_version;
	//$communities_table1 = "";
	//$wpdb->query( $communities_table1 );
}

function communities_global_install() {
	global $wpdb, $communities_current_version;
	if (get_site_option( "communities_installed" ) == '') {
		add_site_option( 'communities_installed', 'no' );
	}

	if (get_site_option( "communities_installed" ) == "yes") {
		// do nothing
	} else {

		$communities_table1 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "communities` (
  `community_ID` bigint(20) unsigned NOT NULL auto_increment,
  `community_owner_user_ID` int(11) NOT NULL default '0',
  `community_name` VARCHAR(255),
  `community_description` VARCHAR(255),
  `community_private` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`community_ID`)
) ENGINE=MyISAM;";
		$communities_table2 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "communities_members` (
  `member_ID` bigint(20) unsigned NOT NULL auto_increment,
  `community_ID` int(11) NOT NULL default '0',
  `member_moderator` tinyint(1) NOT NULL default '0',
  `member_notifications` VARCHAR(255) NOT NULL default 'digest',
  `member_user_ID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`member_ID`)
) ENGINE=MyISAM;";
		$communities_table3 = "CREATE TABLE `" . $wpdb->base_prefix . "communities_topics` (
  `topic_ID` bigint(20) unsigned NOT NULL auto_increment,
  `topic_community_ID` bigint(20) NOT NULL,
  `topic_title` TEXT NOT NULL,
  `topic_author` bigint(20) NOT NULL,
  `topic_last_author` bigint(20) NOT NULL,
  `topic_stamp` bigint(30) NOT NULL,
  `topic_last_updated_stamp` bigint(30) NOT NULL,
  `topic_closed` tinyint(1) NOT NULL default '0',
  `topic_sticky` tinyint(1) NOT NULL default '0',
  `topic_posts` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`topic_ID`)
) ENGINE=MyISAM;";
		$communities_table4 = "CREATE TABLE `" . $wpdb->base_prefix . "communities_posts` (
  `post_ID` bigint(20) unsigned NOT NULL auto_increment,
  `post_community_ID` bigint(20) NOT NULL,
  `post_topic_ID` bigint(20) NOT NULL,
  `post_author` bigint(20) NOT NULL,
  `post_content` TEXT,
  `post_stamp` bigint(30) NOT NULL,
  PRIMARY KEY  (`post_ID`)
) ENGINE=MyISAM;";
		$communities_table5 = "CREATE TABLE `" . $wpdb->base_prefix . "communities_pages` (
  `page_ID` bigint(20) unsigned NOT NULL auto_increment,
  `page_community_ID` bigint(20) NOT NULL,
  `page_parent_page_ID` bigint(20) NOT NULL default '0',
  `page_title` TEXT NOT NULL,
  `page_content` TEXT,
  `page_stamp` bigint(30) NOT NULL,
  PRIMARY KEY  (`page_ID`)
) ENGINE=MyISAM;";
		$communities_table6 = "CREATE TABLE `" . $wpdb->base_prefix . "communities_news_items` (
  `news_item_ID` bigint(20) unsigned NOT NULL auto_increment,
  `news_item_community_ID` bigint(20) NOT NULL,
  `news_item_title` TEXT NOT NULL,
  `news_item_content` TEXT,
  `news_item_stamp` bigint(30) NOT NULL,
  PRIMARY KEY  (`news_item_ID`)
) ENGINE=MyISAM;";
		$communities_table7 = "CREATE TABLE `" . $wpdb->base_prefix . "communities_notifications` (
  `notification_ID` bigint(20) unsigned NOT NULL auto_increment,
  `notification_community_ID` bigint(20) NOT NULL,
  `notification_user_ID` bigint(20) NOT NULL,
  `notification_item_title` TEXT NOT NULL,
  `notification_item_url` TEXT,
  `notification_item_type` VARCHAR(255) NOT NULL,
  `notification_stamp` bigint(30) NOT NULL,
  PRIMARY KEY  (`notification_ID`)
) ENGINE=MyISAM;";
		$wpdb->query( $communities_table1 );
		$wpdb->query( $communities_table2 );
		$wpdb->query( $communities_table3 );
		$wpdb->query( $communities_table4 );
		$wpdb->query( $communities_table5 );
		$wpdb->query( $communities_table6 );
		$wpdb->query( $communities_table7 );
		update_site_option( "communities_installed", "yes" );
	}
}

function communities_admin_init() {
	global $wp_roles;
	
	$role_names = $wp_roles->get_names();
	foreach($role_names as $role_name => $role_label ) {
		$role_object = get_role( $role_name );

		//$role_object->remove_cap('communities_manage');
		if (!isset($role_object->capabilities['communities_manage'])) {
			if ((isset($role_object->capabilities['read'])) && ($role_object->capabilities['read'] == 1))
				$role_object->add_cap('communities_manage', 1);
			else
				$role_object->add_cap('communities_manage', 0);
		}		

		//$role_object->remove_cap('communities_add');
		if (!isset($role_object->capabilities['communities_add'])) {
			if ((isset($role_object->capabilities['read'])) && ($role_object->capabilities['read'] == 1))
				$role_object->add_cap('communities_add', 1);
			else
				$role_object->add_cap('communities_add', 0);
		}		

		//$role_object->remove_cap('communities_view');
		if (!isset($role_object->capabilities['communities_view'])) {
			if ((isset($role_object->capabilities['read'])) && ($role_object->capabilities['read'] == 1))
				$role_object->add_cap('communities_view', 1);
			else
				$role_object->add_cap('communities_view', 0);
		}		
	}
}

function communities_plug_pages() {
	global $wpdb, $user_ID, $communities_text_domain;
	$owner_community_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = %d", $user_ID));

	//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	add_menu_page( 
		__('Communities', $communities_text_domain), 
		__('Communities', $communities_text_domain), 
		'communities_view', 
		'communities', 
		'communities_output'
	);

	if ((is_super_admin()) || ( $owner_community_count > 0 )) {

		add_submenu_page(
			'communities', 
			__('Communities', $communities_text_domain), 
			__('Manage Communities', $communities_text_domain), 
			'communities_manage', 
			'manage-communities', 
			'communities_manage_output' 
		);
	}

	add_submenu_page(
		'communities', 
		__('Add Community', $communities_text_domain), 
		__('Add Community', $communities_text_domain), 
		'communities_add', 
		'add-communities', 
		'communities_add_output' 
	);

	add_submenu_page(
		'communities', 
		__('Communities', $communities_text_domain), 
		__('Find Communities', $communities_text_domain), 
		'communities_view', 
		'find-communities', 
		'communities_find_output' 
	);
}

function communities_admin_bar( $menu ) {
	unset( $menu['communities'] );
	return $menu;
}


function communities_create_community($user_ID, $name, $description, $private = '0') {
	global $wpdb;
		
//	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities (community_owner_user_ID,community_name,community_description,community_private) VALUES ( '" . $user_ID . "','" . addslashes( $name ) . "','" . addslashes( $description ) . "','" . $private . "' )" );

	$wpdb->insert($wpdb->base_prefix . "communities", array(
		'community_owner_user_ID' 	=> 	intval($user_ID), 
		'community_name'	 		=>	stripslashes(sanitize_text_field($name)),
		'community_description'		=>	stripslashes(sanitize_text_field($description)),
		'community_private'			=>	intval($private)
		), array('%d', '%s', '%s', '%d')
	);

//	$community_ID = $wpdb->get_var("SELECT community_ID FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "' AND community_name = '" . addslashes( $name ) . "'");

	if ($wpdb->insert_id > 0) {
		$community_ID = $wpdb->insert_id;	
		communities_join_community($user_ID, $community_ID, '1');
	}
}

function communities_update_community($user_ID, $community_ID, $description, $private = '0') {
	global $wpdb;
	
	$user_ID 		= intval($user_ID);
	$community_ID 	= intval($community_ID);
	$description 	= stripslashes(sanitize_text_field($description));
	$private 		= intval($private);
	
	if ( is_super_admin() ) {
		//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities SET community_description = '" . $description . "' WHERE community_ID = '" . $community_ID . "'");
		//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities SET community_private = '" . $private . "' WHERE community_ID = '" . $community_ID . "'");
		$wpdb->update($wpdb->base_prefix . "communities", 
			array(
				'community_description'		=>	$description,
				'community_private'			=>	$private
			),
			array(
				'community_ID' 				=> 	$community_ID
			), array('%s', '%s'), array('%d')
		);
	} else {
		//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities SET community_description = '" . $description . "' WHERE community_ID = '" . $community_ID . "' AND community_owner_user_ID = '" . $user_ID . "'");
		//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities SET community_private = '" . $private . "' WHERE community_ID = '" . $community_ID . "' AND community_owner_user_ID = '" . $user_ID . "'");
		$wpdb->update($wpdb->base_prefix . "communities", 
			array(
				'community_description'		=>	$description,
				'community_private'			=>	$private
			),
			array(
				'community_ID' 				=> 	$community_ID, 
				'community_owner_user_ID'	=>	$user_ID
			), array('%s', '%s'), array('%d', '%d')
		);
	}
}

function communities_remove_community($community_ID) {
	global $wpdb;
	
	do_action('remove_community', $community_ID);

	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d", $community_ID ));
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_community_ID = %d", $community_ID ));
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_posts WHERE post_community_ID = %d", $community_ID ));
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d", $community_ID ));
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = %d", $community_ID ));
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_community_ID = %d", $community_ID ));
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $community_ID ));

}

function communities_join_community($user_ID, $community_ID, $moderator = '0') {
	global $wpdb, $communities_notifications_default;

	$user_ID 		= intval($user_ID);
	$community_ID 	= intval($community_ID);
	$moderator		= intval($moderator);
	
	if ( empty( $communities_notifications_default ) )
		$communities_notifications_default = 'digest';
	else
		$communities_notifications_default = stripslashes(sanitize_text_field($communities_notifications_default));
		
//	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_members (community_ID,member_moderator,member_notifications,member_user_ID) VALUES ( '" . $community_ID . "', '" . $moderator . "', '" . $communities_notifications_default . "', '" . $user_ID . "' )" );
	
	$wpdb->insert($wpdb->base_prefix . "communities_members", array(
		'community_ID' 				=> 	$community_ID, 
		'member_moderator'	 		=>	$moderator,
		'member_notifications'		=>	$communities_notifications_default,
		'member_user_ID'			=>	$user_ID
		), array('%d', '%d', '%s', '%d')
	);
}

function communities_leave_community($user_ID, $community_ID) {
	global $wpdb;
	
	$user_ID 		= intval($user_ID);
	$community_ID 	= intval($community_ID);
	
	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $community_ID, $user_ID ));
	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_community_ID = %d AND notification_user_ID = %d", $community_ID, $user_ID ));
}

function communities_add_moderator_privilege($user_ID, $community_ID) {
	global $wpdb;
	
	$community_ID 	= intval($community_ID);
	$user_ID 		= intval($user_ID);
	
	//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_members SET member_moderator = '1' WHERE community_ID = '" . $community_ID . "' AND member_user_ID = '" . $user_ID . "'");
	$wpdb->update($wpdb->base_prefix . "communities_members", 
		array(
			'member_moderator'		=>	'1'
		),
		array(
			'community_ID' 			=> 	$community_ID,
			'member_user_ID'		=>	$user_ID
		), array('%d'), array('%d', '$d')
	);
}

function communities_remove_moderator_privilege($user_ID, $community_ID) {
	global $wpdb;
	//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_members SET member_moderator = '0' WHERE community_ID = '" . $community_ID . "' AND member_user_ID = '" . $user_ID . "'");
	$wpdb->update($wpdb->base_prefix . "communities_members", 
		array(
			'member_moderator'		=>	'0'
		),
		array(
			'community_ID' 			=> 	intval($community_ID),
			'member_user_ID'		=>	intval($user_ID)
		), array('%d'), array('%d', '%d')
	);
}

function communities_update_notifications($user_ID, $community_ID, $notifications) {
	global $wpdb;

//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_members SET member_notifications = '" . $notifications . "' WHERE member_user_ID = '" . $user_ID . "' AND community_ID = '" . $community_ID . "'" );

	$wpdb->update($wpdb->base_prefix . "communities_members", 
		array(
			'member_notifications'		=>	$notifications
		),
		array(
			'community_ID' 				=> 	intval($community_ID),
			'member_user_ID'			=>	intval($user_ID)
		), array('%s'), array('%d', '%d')
	);

}

function communities_count_posts($topic_ID) {
	global $wpdb;
	
	$post_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_posts WHERE post_topic_ID = %d", $topic_ID));
	
	//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_posts = '" . $post_count . "' WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_topics", 
		array(
			'topic_posts'		=>	intval($post_count)
		),
		array(
			'topic_ID' 			=> 	intval($topic_ID)
		), array('%d'), array('%d')
	);
}

function communities_add_topic($community_ID, $user_ID, $title, $content, $sticky = '0') {
	global $wpdb, $COMMUNITIES_ALLOWED_CONTENT_TAGS;

	$community_ID 	= intval($community_ID);
	$user_ID 		= intval($user_ID);
	$title 			= stripslashes(sanitize_text_field($title));
	$content 		= stripslashes(wp_kses($content, $COMMUNITIES_ALLOWED_CONTENT_TAGS));
	$sticky			= intval($sticky);
	$time = time();

	//$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_topics (topic_community_ID, topic_title, topic_author, topic_last_author, topic_stamp, topic_last_updated_stamp, topic_sticky) VALUES ( '" . $community_ID . "', '" . addslashes( $title ) . "', '" . $user_ID . "', '" . $user_ID . "', '" . $time . "', '" . $time . "', '" . $sticky . "')" );

	$wpdb->insert($wpdb->base_prefix . "communities_topics", array(
		'topic_community_ID' 		=> 	$community_ID,
		'topic_title'	 			=>	$title,
		'topic_author'				=>	$user_ID,
		'topic_last_author'			=>	$user_ID,
		'topic_stamp'				=>	$time,
		'topic_last_updated_stamp'	=>	$time,
		'topic_sticky'				=>	$sticky,
		), array('%d', '%s', '%d', '%d', '%s', '%s', '%d')
	);

//	$topic_ID = $wpdb->get_var("SELECT topic_ID FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_stamp = '" . $time . "' AND topic_title = '" . addslashes( $title ) . "' AND topic_author = '" . $user_ID . "'");

	if ($wpdb->insert_id > 0) {
		$topic_ID = $wpdb->insert_id;	

		//$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_posts (post_community_ID, post_topic_ID, post_author, post_content, post_stamp) VALUES ( '" . $community_ID . "', '" . $topic_ID . "', '" . $user_ID . "', '" . addslashes( $content ) . "', '" . $time . "')" );

		$wpdb->insert($wpdb->base_prefix . "communities_posts", array(
			'post_community_ID' 		=> 	$community_ID,
			'post_topic_ID'				=>	$topic_ID,
			'post_author'				=>	$user_ID,
			'post_content'				=>	$content,
			'post_stamp'				=>	$time
			), array('%d', '%d', '%d', '%s', '%s')
		);

		communities_count_posts($topic_ID);

		communities_topic_notification($community_ID, $topic_ID, $title);
		
		return 	$topic_ID;
	}
}

function communities_add_post($community_ID, $topic_ID, $user_ID, $content) {
	global $wpdb, $COMMUNITIES_ALLOWED_CONTENT_TAGS;

	$community_ID 	= intval($community_ID);
	$topic_ID 		= intval($topic_ID);
	$user_ID 		= intval($user_ID);
	$content 		= stripslashes(wp_kses($content, $COMMUNITIES_ALLOWED_CONTENT_TAGS));

	$time = time();
//	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_posts (post_community_ID, post_topic_ID, post_author, post_content, post_stamp) VALUES ( '" . $community_ID . "', '" . $topic_ID . "', '" . $user_ID . "', '" . addslashes( $content ) . "', '" . $time . "')" );
	$wpdb->insert($wpdb->base_prefix . "communities_posts", array(
		'post_community_ID'		=> 	$community_ID,
		'post_topic_ID'			=>	$topic_ID, 
		'post_author'			=>	$user_ID, 
		'post_content'			=>	$content,
		'post_stamp'			=>	$time
		), array('%d', '%d', '%d', '%s', '%s')
	);

//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_last_author = '" . $user_ID . "' WHERE topic_ID = '" . $topic_ID . "'" );
//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_last_updated_stamp = '" . $time . "' WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_topics", 
		array(
			'topic_last_author'			=>	$user_ID,
			'topic_last_updated_stamp'	=>	$time
		),
		array(
			'topic_ID' 				=> 	$topic_ID
		), array('%d', '%s'). array('%d')
	);
	
	communities_count_posts($topic_ID);
}

function communities_update_post_content($post_ID, $content) {
	global $wpdb, $COMMUNITIES_ALLOWED_CONTENT_TAGS;

	$post_ID 	= intval($post_ID);
	$content 	= stripslashes(wp_kses($content, $COMMUNITIES_ALLOWED_CONTENT_TAGS));

//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_posts SET post_content = '" . addslashes( $content ) . "' WHERE post_ID = '" . $post_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_posts", 
		array(
			'post_content'		=>	$content
		),
		array(
			'post_ID' 			=> 	$post_ID
		), array('%s'), array('%d')
	);
}

function communities_update_topic_title($topic_ID, $title) {
	global $wpdb;

	$topic_ID 	= intval($topic_ID);
	$title 		= stripslashes(sanitize_text_field($title));

//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_title = '" . addslashes( $title ) . "' WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_topics", 
		array(
			'topic_title'		=>	$title
		),
		array(
			'topic_ID' 			=> 	$topic_ID
		), array('%s'), array('%d')
	);
}

function communities_close_topic($topic_ID) {
	global $wpdb;

	$topic_ID 	= intval($topic_ID);
	
//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_closed = '1' WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_topics", 
		array(
			'topic_closed'		=>	'1'
		),
		array(
			'topic_ID' 			=> 	$topic_ID
		), array('%d'), array('%d')
	);
}

function communities_open_topic($topic_ID) {
	global $wpdb;
	
	$topic_ID 	= intval($topic_ID);

//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_closed = '0' WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_topics", 
		array(
			'topic_closed'		=>	'0'
		),
		array(
			'topic_ID' 			=> 	$topic_ID
		), array('%d'), array('%d')
	);
}

function communities_stick_topic($topic_ID) {
	global $wpdb;

	$topic_ID 	= intval($topic_ID);

//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_sticky = '1' WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_topics", 
		array(
			'topic_sticky'		=>	'1'
		),
		array(
			'topic_ID' 			=> 	$topic_ID
		), array('%d'), array('%d')
	);
}

function communities_unstick_topic($topic_ID) {
	global $wpdb;

	$topic_ID 	= intval($topic_ID);

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_sticky = '0' WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_topics", 
		array(
			'topic_sticky'		=>	'0'
		),
		array(
			'topic_ID' 			=> 	$topic_ID
		), array('%d'), array('%d')
	);
}

function communities_delete_topic($topic_ID) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $topic_ID ));
	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->base_prefix . "communities_posts WHERE post_topic_ID = %d", $topic_ID ));
}

function communities_delete_post($topic_ID, $post_ID) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = %d", $post_ID ));

	communities_count_posts($topic_ID);

}

function communities_add_page($community_ID, $parent_page_ID, $title, $content) {
	global $wpdb, $COMMUNITIES_ALLOWED_CONTENT_TAGS;

	$community_ID 	= intval($community_ID);
	$parent_page_ID = intval($parent_page_ID);
	$title 			= stripslashes(sanitize_text_field($title));
	$content 		= stripslashes(wp_kses($content, $COMMUNITIES_ALLOWED_CONTENT_TAGS));
	$time 			= time();
	
//	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_pages (page_community_ID, page_parent_page_ID, page_title, page_content, page_stamp) VALUES ( '" . $community_ID . "', '" . $parent_page_ID . "', '" . addslashes( $title ) . "', '" . addslashes( $content ) . "', '" . $time . "')" );

	$wpdb->insert($wpdb->base_prefix . "communities_pages", array(
		'page_community_ID'		=> 	$community_ID, 
		'page_parent_page_ID'	=>	$parent_page_ID,
		'page_title'			=>	$title,
		'page_content'			=>	$content,
		'page_stamp'			=>	$time
		), array('%d', '%d', '%s', '%s', '%s')
	);
	
	//$page_ID = $wpdb->get_var("SELECT page_ID FROM " . $wpdb->base_prefix . "communities_pages WHERE page_stamp = '" . $time . "' AND page_title = '" . addslashes( $title ) . "'");
	if ($wpdb->insert_id > 0) {
		$page_ID = $wpdb->insert_id;	
	
		communities_page_notification($community_ID, $page_ID, $title);

		return 	$page_ID;
	}
}

function communities_update_page($page_ID, $title, $content) {
	global $wpdb, $COMMUNITIES_ALLOWED_CONTENT_TAGS;

	$title 		= stripslashes(sanitize_text_field($title));
	$content 	= stripslashes(wp_kses($content, $COMMUNITIES_ALLOWED_CONTENT_TAGS));

	//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_pages SET page_title = '" . addslashes( $title ) . "' WHERE page_ID = '" . $page_ID . "'" );
	//$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_pages SET page_content = '" . addslashes( $content ) . "' WHERE page_ID = '" . $page_ID . "'" );
	$wpdb->update($wpdb->base_prefix . "communities_pages", 
		array(
			'page_title'		=>	$title,
			'page_content'		=>	$content
		),
		array(
			'page_ID' 			=> 	$page_ID
		), array('%s', '%s'), array('%d')
	);
}

function communities_delete_page($page_ID) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = %d", $page_ID ));
	$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_pages WHERE page_parent_page_ID = %d", $page_ID ));
}

function communities_add_news_item($community_ID, $title, $content) {
	global $wpdb, $COMMUNITIES_ALLOWED_CONTENT_TAGS;

	$community_ID 	= intval($community_ID);
	$title 			= stripslashes(sanitize_text_field($title));
	$content 		= stripslashes(wp_kses($content, $COMMUNITIES_ALLOWED_CONTENT_TAGS));
	$time 			= time();

	//$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_news_items (news_item_community_ID, news_item_title, news_item_content, news_item_stamp) VALUES ( '" . $community_ID . "', '" . addslashes( $title ) . "', '" . addslashes( $content ) . "', '" . $time . "')" );

	$wpdb->insert($wpdb->base_prefix . "communities_news_items", array(
		'news_item_community_ID'	=>	$community_ID,
		'news_item_title'			=>	$title,
		'news_item_content'			=>	$content, 
		'news_item_stamp'			=>	$time
		)
	);

	//$news_item_ID = $wpdb->get_var("SELECT news_item_ID FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_stamp = '" . $time . "' AND news_item_title = '" . addslashes( $title ) . "'");
	if ($wpdb->insert_id > 0) {
		$news_item_ID = $wpdb->insert_id;	

		communities_news_notification($community_ID, $news_item_ID, $title);

		return 	$news_item_ID;
	}
}

function communities_update_news_item($news_item_ID, $title, $content) {
	global $wpdb, $COMMUNITIES_ALLOWED_CONTENT_TAGS;

	$news_item_ID	= intval($news_item_ID);
	$title 			= stripslashes(sanitize_text_field($title));
	$content 		= stripslashes(wp_kses($content, $COMMUNITIES_ALLOWED_CONTENT_TAGS));

//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_news_items SET news_item_title = '" . addslashes( $title ) . "' WHERE news_item_ID = '" . $news_item_ID . "'" );
//	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_news_items SET news_item_content = '" . addslashes( $content ) . "' WHERE news_item_ID = '" . $news_item_ID . "'" );
	
	$wpdb->update($wpdb->base_prefix . "communities_news_items", 
		array(
			'news_item_title'		=>	$title,
			'news_item_content'		=>	$content
		),
		array(
			'news_item_ID' 			=> 	$news_item_ID
		), array('%s', '%s'), array('%d')
	);	
}

function communities_delete_news_item($news_item_ID) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = %d", $news_item_ID ));
}

function communities_topic_notification($community_ID, $topic_ID, $title) {
	global $wpdb, $communities_notifications_instant_topic_subject, $communities_notifications_instant_topic_content, $current_site;

	$community_ID	= intval($community_ID);
	$topic_ID		= intval($topic_ID);
	$title 			= stripslashes(sanitize_text_field($title));


	$email_subject = $communities_notifications_instant_topic_subject;
	$email_content = $communities_notifications_instant_topic_content;

	$item_url = 'wp-admin?page=communities&action=topic&tid=' . $topic_ID . '&cid=' . $community_ID;

	// digest

	$query = $wpdb->prepare("SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = %s AND community_ID = %d", 'digest', $community_ID);
	$digest_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $digest_members ) > 0){
		$time = time();
		foreach ( $digest_members as $digest_member ) {
			$member_primary_blog = get_active_blog_for_user( $digest_member['member_user_ID'] );
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;

			//$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_notifications (notification_community_ID, notification_user_ID, notification_stamp, notification_item_title, notification_item_url, notification_item_type) VALUES ( '" . $community_ID . "', '" . $digest_member['member_user_ID'] . "', '" . $time . "', '" . addslashes( $title ) . "', '" . $notification_item_url . "', 'topic')" );
			
			$wpdb->insert($wpdb->base_prefix . "communities_notifications",
				array(
					'notification_community_ID'		=>	$community_ID, 
					'notification_user_ID'			=>	$digest_member['member_user_ID'], 
					'notification_stamp'			=>	$time, 
					'notification_item_title'		=>	$title, 
					'notification_item_url'			=>	$notification_item_url, 
					'notification_item_type'		=>	'topic'
				)
			);
		}
	}

	// instant

	$query = $wpdb->prepare("SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = %s AND community_ID = %d", 'instant', $community_ID);
	$instant_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $instant_members ) > 0) {
		$blog_charset = get_option('blog_charset');
		$community_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $community_ID));
		$email_subject = str_replace('COMMUNITY_NAME', stripslashes( sanitize_text_field($community_details->community_name) ), $email_subject);
		$email_content = str_replace('COMMUNITY_NAME', stripslashes( sanitize_text_field($community_details->community_name) ), $email_content);
		$email_content = str_replace('SITE_NAME', $current_site->site_name, $email_content);
		$email_content = str_replace('TOPIC_TITLE', $title, $email_content);
		foreach ( $instant_members as $instant_member ) {
			$loop_email_subject = $email_subject;
			$loop_email_content = $email_content;

			$member_primary_blog = get_active_blog_for_user( $instant_member['member_user_ID'] );
			$member_details = $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $instant_member['member_user_ID']) );
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$notifications_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . 'wp-admin?page=communities&action=notifications&cid=' . $community_ID;

			$loop_email_content = str_replace('TOPIC_URL', $notification_item_url, $loop_email_content);
			$loop_email_content = str_replace('NOTIFCATIONS_URL', $notifications_url, $loop_email_content);

			$from_email = 'noreply@' . $current_site->domain;
			$message_headers = "MIME-Version: 1.0\n" . "From: " . $current_site->site_name .  " <{$from_email}>\n" . "Content-Type: text/html; charset=\"" . $blog_charset . "\"\n";
			wp_mail($member_details->user_email, $loop_email_subject, $loop_email_content, $message_headers);
		}
	}
}

function communities_page_notification($community_ID, $page_ID, $title) {
	global $wpdb, $communities_notifications_instant_page_subject, $communities_notifications_instant_page_content, $current_site, $current_site;


	$community_ID	= intval($community_ID);
	$page_ID		= intval($page_ID);
	$title 			= stripslashes(sanitize_text_field($title));
	
	$email_subject = $communities_notifications_instant_page_subject;
	$email_content = $communities_notifications_instant_page_content;

	$item_url = 'wp-admin?page=communities&action=page&pid=' . $page_ID . '&cid=' . $community_ID;

	// digest

	$query = $wpdb->prepare("SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = %s AND community_ID = %d", 'digest', $community_ID);
	$digest_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $digest_members ) > 0){
		$time = time();
		foreach ( $digest_members as $digest_member ) {
			$member_primary_blog = get_active_blog_for_user( $digest_member['member_user_ID'] );
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			//$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_notifications (notification_community_ID, notification_user_ID, notification_stamp, notification_item_title, notification_item_url, notification_item_type) VALUES ( '" . $community_ID . "', '" . $digest_member['member_user_ID'] . "', '" . $time . "', '" . addslashes( $title ) . "', '" . $notification_item_url . "', 'page')" );
			$wpdb->insert('$wpdb->base_prefix . "communities_notifications', 
				array(
					'notification_community_ID'		=>	$community_ID, 
					'notification_user_ID'			=>	$digest_member['member_user_ID'], 
					'notification_stamp'			=>	$time, 
					'notification_item_title'		=>	$title, 
					'notification_item_url'			=>	$notification_item_url, 
					'notification_item_type'		=>	'page'
				)
			);
		}
	}

	// instant

	$query = $wpdb->prepare("SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = %s AND community_ID = %d", 'instant', $community_ID);
	$instant_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $instant_members ) > 0){
		$blog_charset = get_option('blog_charset');
		$community_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $community_ID));
		$email_subject = str_replace('COMMUNITY_NAME', stripslashes( sanitize_text_field($community_details->community_name) ), $email_subject);
		$email_content = str_replace('COMMUNITY_NAME', stripslashes( sanitize_text_field($community_details->community_name) ), $email_content);
		$email_content = str_replace('SITE_NAME', $current_site->site_name, $email_content);
		$email_content = str_replace('PAGE_TITLE', $title, $email_content);
		foreach ( $instant_members as $instant_member ) {
			$loop_email_subject = $email_subject;
			$loop_email_content = $email_content;

			$member_primary_blog = get_active_blog_for_user( $instant_member['member_user_ID'] );
			$member_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $instant_member['member_user_ID']));
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$notifications_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . 'wp-admin?page=communities&action=notifications&cid=' . $community_ID;

			$loop_email_content = str_replace('PAGE_URL', $notification_item_url, $loop_email_content);
			$loop_email_content = str_replace('NOTIFCATIONS_URL', $notifications_url, $loop_email_content);

			$from_email = 'noreply@' . $current_site->domain;
			$message_headers = "MIME-Version: 1.0\n" . "From: " . $current_site->site_name .  " <{$from_email}>\n" . "Content-Type: text/html; charset=\"" . $blog_charset . "\"\n";
			wp_mail($member_details->user_email, $loop_email_subject, $loop_email_content, $message_headers);
		}
	}
}

function communities_news_notification($community_ID, $news_item_ID, $title) {
	global $wpdb, $communities_notifications_instant_news_subject, $communities_notifications_instant_news_content, $current_site;

	$community_ID	= intval($community_ID);
	$news_item_ID	= intval($news_item_ID);
	$title 			= stripslashes(sanitize_text_field($title));

	$email_subject = $communities_notifications_instant_news_subject;
	$email_content = $communities_notifications_instant_news_content;

	$item_url = 'wp-admin?page=communities&action=news_item&niid=' . $news_item_ID . '&cid=' . $community_ID;

	// digest

	$query = $wpdb->prepare("SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = %s AND community_ID = %d", 'digest', $community_ID);
	$digest_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $digest_members ) > 0){
		$time = time();
		foreach ( $digest_members as $digest_member ) {
			$member_primary_blog = get_active_blog_for_user( $digest_member['member_user_ID'] );
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			//$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_notifications (notification_community_ID, notification_user_ID, notification_stamp, notification_item_title, notification_item_url, notification_item_type) VALUES ( '" . $community_ID . "', '" . $digest_member['member_user_ID'] . "', '" . $time . "', '" . addslashes( $title ) . "', '" . $notification_item_url . "', 'news')" );
			$wpdb->insert($wpdb->base_prefix . "communities_notifications",
				array(
					'notification_community_ID'		=>	$community_ID, 
					'notification_user_ID'			=>	$digest_member['member_user_ID'], 
					'notification_stamp'			=>	$time, 
					'notification_item_title'		=>	$title, 
					'notification_item_url'			=>	$notification_item_url, 
					'notification_item_type'		=>	'news'
				)
			);
		}
	}

	// instant

	$query = $wpdb->prepare("SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = %s AND community_ID = %d", 'instant', $community_ID );
	$instant_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $instant_members ) > 0){
		$blog_charset = get_option('blog_charset');
		$community_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $community_ID));
		$email_subject = str_replace('COMMUNITY_NAME', stripslashes( sanitize_text_field($community_details->community_name) ), $email_subject);
		$email_content = str_replace('COMMUNITY_NAME', stripslashes( sanitize_text_field($community_details->community_name) ), $email_content);
		$email_content = str_replace('SITE_NAME', $current_site->site_name, $email_content);
		$email_content = str_replace('NEWS_ITEM_TITLE', $title, $email_content);
		foreach ( $instant_members as $instant_member ) {
			$loop_email_subject = $email_subject;
			$loop_email_content = $email_content;

			$member_primary_blog = get_active_blog_for_user( $instant_member['member_user_ID'] );
			$member_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $instant_member['member_user_ID']));
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$notifications_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . 'wp-admin?page=communities&action=notifications&cid=' . $community_ID;

			$loop_email_content = str_replace('NEWS_ITEM_URL', $notification_item_url, $loop_email_content);
			$loop_email_content = str_replace('NOTIFCATIONS_URL', $notifications_url, $loop_email_content);

			$from_email = 'noreply@' . $current_site->domain;
			$message_headers = "MIME-Version: 1.0\n" . "From: " . $current_site->site_name .  " <{$from_email}>\n" . "Content-Type: text/html; charset=\"" . $blog_charset . "\"\n";
			wp_mail($member_details->user_email, $loop_email_subject, $loop_email_content, $message_headers);
		}
	}
}

function communities_digest_notifications() {
	global $wpdb, $communities_notifications_digest_subject, $communities_notifications_digest_content, $current_site;

	$email_subject = $communities_notifications_digest_subject;
	$email_content = $communities_notifications_digest_content;

	$query = $wpdb->prepare("SELECT member_user_ID, community_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = %s", 'digest');
	$digest_members = $wpdb->get_results( $query, ARRAY_A );

	if (count( $digest_members ) > 0){
		$blog_charset = get_option('blog_charset');
		foreach ( $digest_members as $digest_member ) {
			$notification_item_count = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_community_ID = %d AND notification_user_ID = %d", $digest_member['community_ID'], $digest_member['member_user_ID']));
			if ( $notification_item_count > 0 ) {
				unset( $topics );
				unset( $pages );
				unset( $news_items );
				unset( $notification_topics );
				unset( $notification_pages );
				unset( $notification_news_items );
				$loop_email_subject = $email_subject;
				$loop_email_content = $email_content;
				$community_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $digest_member['community_ID']));
				$member_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $digest_member['member_user_ID']));
				$member_primary_blog = get_active_blog_for_user( $digest_member['member_user_ID'] );
				$notifications_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . 'wp-admin?page=communities&action=notifications&cid=' . $digest_member['community_ID'];
				$loop_email_subject = str_replace('COMMUNITY_NAME', stripslashes( sanitize_text_field($community_details->community_name) ), $loop_email_subject);
				$loop_email_content = str_replace('COMMUNITY_NAME', stripslashes( sanitize_text_field($community_details->community_name) ), $loop_email_content);
				$loop_email_content = str_replace('SITE_NAME', $current_site->site_name, $loop_email_content);
				$loop_email_content = str_replace('NOTIFCATIONS_URL', $notifications_url, $loop_email_content);

				// topics

				$query = $wpdb->prepare("SELECT notification_item_title, notification_item_url FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = %s AND notification_community_ID = %d AND notification_user_ID = %d", 'topic', $digest_member['community_ID'], $digest_member['member_user_ID']);
				$topics = $wpdb->get_results( $query, ARRAY_A );
				if ( count( $topics ) > 0 ) {
					$notification_topics = __('New Topics', $communities_text_domain) . ":<br /><br />";
					foreach ( $topics as $topic ) {
						$notification_topics = $notification_topics . $topic['notification_item_title'] . "<br />";
						$notification_topics = $notification_topics . $topic['notification_item_url'] . "<br /><br />";
					}
					$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = %s AND notification_community_ID = %d AND notification_user_ID = %d",  'topic', $digest_member['community_ID'], $digest_member['member_user_ID']));
				} else {
					$loop_email_content = str_replace('TOPICS', '', $loop_email_content);
				}

				// pages

				$query = $wpdb->prepare("SELECT notification_item_title, notification_item_url FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = %s AND notification_community_ID = %d AND notification_user_ID = %d", 'page', $digest_member['community_ID'], $digest_member['member_user_ID']);
				$pages = $wpdb->get_results( $query, ARRAY_A );
				if ( count( $pages ) > 0 ) {
					$notification_pages = __('New Pages', $communities_text_domain) . ":<br /><br />";
					foreach ( $pages as $page ) {
						$notification_pages = $notification_pages . $page['notification_item_title'] . "<br />";
						$notification_pages = $notification_pages . $page['notification_item_url'] . "<br /><br />";
					}

					$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = %s AND notification_community_ID = %d AND notification_user_ID = %d", 'page', $digest_member['community_ID'], $digest_member['member_user_ID'] ));
				} else {
					$loop_email_content = str_replace('PAGES', '', $loop_email_content);
				}

				// news items

				$query = $wpdb->prepare("SELECT notification_item_title, notification_item_url FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = %s AND notification_community_ID = %d AND notification_user_ID = %d", 'news', $digest_member['community_ID'], $digest_member['member_user_ID']);
				$news_items = $wpdb->get_results( $query, ARRAY_A );
				if ( count( $news_items ) > 0 ) {
					$notification_news_items = __('New News', $communities_text_domain) . ":<br /><br />";
					foreach ( $news_items as $news_item ) {
						$notification_news_items = $notification_news_items . $news_item['notification_item_title'] . "<br />";
						$notification_news_items = $notification_news_items . $news_item['notification_item_url'] . "<br /><br />";
					}
					$wpdb->query( $wpdb->prepare("DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = %s AND notification_community_ID = %d AND notification_user_ID = %d",  'news', $digest_member['community_ID'], $digest_member['member_user_ID']));
				} else {
					$loop_email_content = str_replace('NEWS', '', $loop_email_content);
				}
				$loop_email_content = str_replace('PAGES', $notification_pages, $loop_email_content);
				$loop_email_content = str_replace('TOPICS', $notification_topics, $loop_email_content);
				$loop_email_content = str_replace('NEWS', $notification_news_items, $loop_email_content);
				if ( count( $news_items ) > 0 || count( $topics ) > 0 || count( $pages ) > 0 ) {
					$from_email = 'noreply@' . $current_site->domain;
					$message_headers = "MIME-Version: 1.0\n" . "From: " . $current_site->site_name .  " <{$from_email}>\n" . "Content-Type: text/html; charset=\"" . $blog_charset . "\"1";
					wp_mail($member_details->user_email, $loop_email_subject, $loop_email_content, $message_headers);
				}
			}
		}
	}
}

function communities_digest_notifications_schedule_cron() {
	if ( get_option('communities_digest_notifications_cron_scheduled') != '1' ) {
		$current_stamp = time();
		$current_hour = date('G', $current_stamp);
		if ( $current_hour == '23' ) {
			$schedule_time = $current_stamp;
		} else {
			$add_hours = 23 - $current_hour;
			$add_seconds = $add_hours * 3600;
			$schedule_time = $current_stamp + $add_seconds;
		}
		wp_schedule_event($schedule_time, 'daily', 'communities_digest_notifications_cron');
		add_option('communities_digest_notifications_cron_scheduled', '1');
	}
}

//------------------------------------------------------------------------//
//---Output Functions-----------------------------------------------------//
//------------------------------------------------------------------------//

function communities_dashboard_css() {
	global $current_site;
	?>
	<link rel='stylesheet' href='http://<?php echo $current_site->domain . $current_site->path; ?>wp-admin/css/dashboard.css' type='text/css' media='all' />
    <?php
}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//

function communities_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site, $communities_text_domain, $COMMUNITIES_ALLOWED_CONTENT_TAGS;

	if ((isset($_GET['updated'])) && (isset($_GET['updatedmsg']))) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field( $_GET['updatedmsg'] ) ) ?></p></div><?php
	}
	echo '<div class="wrap">';
	if (!isset($_GET[ 'action' ])) $_GET[ 'action' ] = '';
	else $_GET[ 'action' ] = sanitize_text_field($_GET[ 'action' ]);
	
	if (!isset($_GET['start'])) $_GET['start'] = 0;
	else $_GET['start'] = intval($_GET['start']);
	
	if (!isset($_GET['num'])) $_GET['num'] = 10;
	else $_GET['num'] = intval($_GET['num']);
	
	if (!isset($_GET['order'])) $_GET['order'] = "ASC";
	else $_GET['order'] = sanitize_text_field($_GET['order']);
	if (($_GET['order'] != "ASC") && ($_GET['order'] != "DESC")) $_GET['order'] = "ASC";
	
	if (!isset($_GET['orderby'])) $_GET['orderby'] = "community_name";
	else $_GET['orderby'] = sanitize_text_field($_GET['orderby']);
	
	switch( sanitize_text_field($_GET[ 'action' ]) ) {
		//---------------------------------------------------//
		case '':
		default:
			if ( is_super_admin() ) {
				$community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities");
			} else {
				$community_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = %d", $user_ID));
			}
			//echo "community_count=[". $community_count ."]<br />";
		
			?>
			<h2><?php _e('Communities', $communities_text_domain) ?></h2>
			<?php
			if( isset( $_GET[ 'start' ] ) == false ) {
				$start = 0;
			} else {
				$start = intval( $_GET[ 'start' ] );
			}
			if( isset( $_GET[ 'num' ] ) == false ) {
				$num = 30;
			} else {
				$num = intval( $_GET[ 'num' ] );
			}

//			$query = $wpdb->prepare("SELECT community_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_user_ID = '%d'", $user_ID);
			if ( is_super_admin() ) {
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities ORDER BY %s %s LIMIT %d,%d", $_GET['orderby'], $_GET['order'], $start,  $num);
			} else {
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = %d ORDER BY %s %s LIMIT %d,%d", $user_ID, $_GET['orderby'], $_GET['order'], $start,  $num);
			}
			//echo "query=[". $query ."]<br />";
			$communities = $wpdb->get_results( $query, ARRAY_A );
			if (count($communities) > 0){
					?>
                    <br />
                    <table><td>
					<fieldset>
					<?php

					$order_sort = "order=" . sanitize_text_field($_GET[ 'order' ]) . "&orderby=" . sanitize_text_field($_GET[ 'orderby' ]);

					if( $start == 0 ) {
						echo __('Previous Page', $communities_text_domain);
					} else if( $start > 0 ) {
						$start_prev = intval($start) - intval($num);
						echo '<a href="?page=communities&start='. $start_prev .'&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
					} else {
						echo '<a href="?page=communities&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
					}
					
					if( $community_count > ($start + $num) ) {
						echo '&nbsp;|&nbsp;<a href="?page=communities&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page', $communities_text_domain) . '</a>';
					} else {
						echo '&nbsp;|&nbsp;' . __('Next Page', $communities_text_domain);
					}

					?>
					</fieldset>
					</td></table>
					<?php
//				}
				?>
				<br />
				<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
				<thead><tr>
				<th scope='col'><?php _e('Name', $communities_text_domain); ?></th>
				<th scope='col'><?php _e('Owner', $communities_text_domain); ?></th>
				<th scope='col'><?php _e('Actions', $communities_text_domain); ?></th>
				<th scope='col'></th>
				<th scope='col'></th>
				<th scope='col'></th>
				<?php if (is_plugin_active('messaging/messaging.php')) {
					?><th scope='col'></th><?php
				}
				?>
				<th scope='col'></th>
				<th scope='col'></th>
				</tr></thead>
				<tbody id='the-list'>
				<?php
				//=========================================================//
					$class = '';
					foreach ($communities as $community){
					$community_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $community['community_ID']));
					//=========================================================//
					echo "<tr class='" . $class . "'>";
					echo "<td valign='top'><a href='?page=communities&action=dashboard&cid=" . $community['community_ID'] . "' style='text-decoration:none;'><strong>" . stripslashes( sanitize_text_field($community_details->community_name) ) . "</strong></a></td>";
					$owner_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $community_details->community_owner_user_ID));
					echo "<td valign='top'>" . $owner_details->display_name . "</td>";
					echo "<td valign='top'><a href='?page=communities&action=message_board&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Message Board', $communities_text_domain) . "</a></td>";
					echo "<td valign='top'><a href='?page=communities&action=wiki&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Wiki', $communities_text_domain) . "</a></td>";
					echo "<td valign='top'><a href='?page=communities&action=news&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('News', $communities_text_domain) . "</a></td>";
					$community_member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d", $community['community_ID']));
					echo "<td valign='top'><a href='?page=communities&action=member_list&cid=" . $community['community_ID'] . "' rel='permalink' class='delete'>" . __('Members', $communities_text_domain) . " (" . $community_member_count . ")</a></td>";

					if (is_plugin_active('messaging/messaging.php')) {
						echo "<td valign='top'><a href='admin.php?page=messaging_new&message_to=" . $owner_details->user_login . "' rel='permalink' class='edit'>" . __('Send Message to Owner', $communities_text_domain) . "</a></td>";
					}

					echo "<td valign='top'><a href='?page=communities&action=notifications&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Notifications', $communities_text_domain) . "</a></td>";
					if ( $community_details->community_owner_user_ID == $user_ID ) {
						echo "<td valign='top'>" . __('Leave', $communities_text_domain) . "</td>";
					} else {
						echo "<td valign='top'><a href='?page=communities&action=leave_community&cid=" . $community['community_ID'] . "' rel='permalink' class='delete'>" . __('Leave', $communities_text_domain) . "</a></td>";
					}
					echo "</tr>";
					$class = ('alternate' == $class) ? '' : 'alternate';
					//=========================================================//
					}
				//=========================================================//
				?>
				</tbody></table>
				<?php
			} else {
				?>
	            <p><?php _e('You currently are not a member of a community. Please visit the <a href="?page=find-communities">Find Communities</a> menu to search for communities to join.', $communities_text_domain) ?></p>
                <?php
			}

		break;
		//---------------------------------------------------//

		case "notifications":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				$member_notifications = $wpdb->get_var($wpdb->prepare("SELECT member_notifications FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = '%d'", $_GET['cid'], $user_ID));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=notifications&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Notifications', $communities_text_domain) ?></a></h2>
                <form name="notifications" method="POST" action="?page=communities&action=notifications_process&cid=<?php echo intval($_GET['cid']); ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Notifications', $communities_text_domain) ?></th>
                    <td><select name="notifications">
                        <option value="instant" <?php if ( $member_notifications == 'instant' ) { echo 'selected="selected"'; } ?> ><?php _e('Instant', $communities_text_domain); ?></option>
                        <option value="digest" <?php if ( $member_notifications == 'digest' ) { echo 'selected="selected"'; } ?> ><?php _e('Daily Digest', $communities_text_domain); ?></option>
                        <option value="none" <?php if ( $member_notifications == 'none' ) { echo 'selected="selected"'; } ?> ><?php _e('None', $communities_text_domain); ?></option>
                    </select>
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
                </p>
                </form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "notifications_process":
			communities_update_notifications($user_ID, intval($_GET['cid']), sanitize_text_field($_POST['notifications']));
			echo "
			<script type='text/javascript'>
			window.location='?page=communities&action=notifications&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.', $communities_text_domain)) . "';
			</script>
			";
		break;
		//---------------------------------------------------//
		case "member_list":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=member_list&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Members', $communities_text_domain) ?></a></h2>
				<?php
				if( isset( $_GET[ 'start' ] ) == false ) {
					$start = 0;
				} else {
					$start = intval( $_GET[ 'start' ] );
				}
				if( isset( $_GET[ 'num' ] ) == false ) {
					$num = 30;
				} else {
					$num = intval( $_GET[ 'num' ] );
				}
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d", $_GET['cid']);
				$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
				$members = $wpdb->get_results( $query, ARRAY_A );
				if( count( $members ) < $num ) {
					$next = false;
				} else {
					$next = true;
				}
				if (count($members) > 0){
					$members_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d", $_GET['cid']));
					if ($members_count > 30){
						?>
						<br />
						<table><td>
						<fieldset>
						<?php

						//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

						if( $start == 0 ) {
							echo __('Previous Page', $communities_text_domain);
						} elseif( $start <= 30 ) {
							echo '<a href="?page=communities&action=member_list&cid=' . intval($_GET['cid']) . '&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
						} else {
							echo '<a href="?page=communities&action=member_list&cid=' . intval($_GET['cid']) . '&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
						}
						if ( $next ) {
							echo '&nbsp;||&nbsp;<a href="?page=communities&action=member_list&cid=' . intval($_GET['cid']) . '&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page', $communities_text_domain) . '</a>';
						} else {
							echo '&nbsp;||&nbsp;' . __('Next Page', $communities_text_domain);
						}
						?>
						</fieldset>
						</td></table>
						<?php
					}
					?>
					<br />
					<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
					<thead><tr>
					<th scope='col'><?php _e('Name', $communities_text_domain); ?></th>
					<th scope='col'><?php _e('Avatar', $communities_text_domain); ?></th>
					<th scope='col'><?php _e('Type', $communities_text_domain); ?></th>
					<th scope='col'><?php _e('Actions', $communities_text_domain); ?></th>
					<?php if (is_plugin_active('messaging/messaging.php')) {
						?><th scope='col'></th><?php
					} ?>
					</tr></thead>
					<tbody id='the-list'>
					<?php
					//=========================================================//
						$class = '';
						foreach ($members as $member){
						//=========================================================//
						echo "<tr class='" . $class . "'>";
						$member_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $member['member_user_ID']));
						echo "<td valign='top'><strong>" . $member_details->display_name . "</strong></td>";
						echo "<td valign='top'>";
							
							if (is_plugin_active('avatars/avatars.php')) {
								echo "<img src='http://" . $current_site->domain . $current_site->path . "avatar/user-" . 
									$member['member_user_ID'] . "-32.png' />";
							} else {
								echo get_avatar('dummy@dummy.com', 32);
							}
						echo "</td>";
						
						$member_type = __('Member', $communities_text_domain);
						if ( $member['member_moderator'] == '1' ) {
							$member_type = __('Moderator', $communities_text_domain);
						}
						if ( $community_owner_user_ID == $member['member_user_ID'] ) {
							$member_type = __('Owner', $communities_text_domain);
						}
						echo "<td valign='top'>" . $member_type . "</td>";
						$member_primary_blog = get_active_blog_for_user( $member['member_user_ID'] );
						echo "<td valign='top'><a href='http://" . $member_primary_blog->domain . $member_primary_blog->path . "' rel='permalink' class='edit'>" . __('Visit Blog', $communities_text_domain) . "</a></td>";
						
						if (is_plugin_active('messaging/messaging.php')) {
						
							if ( $member['member_user_ID'] == $user_ID ) {
								echo "<td valign='top'>" . __('Send Message', $communities_text_domain) . "</td>";
							} else {
								echo "<td valign='top'><a href='admin.php?page=messaging_new&message_to=" . $member_details->user_login . "' rel='permalink' class='edit'>" . __('Send Message', $communities_text_domain) . "</a></td>";
							}
						}
						
						echo "</tr>";
						$class = ('alternate' == $class) ? '' : 'alternate';
						//=========================================================//
						}
					//=========================================================//
					?>
					</tbody></table>
					<?php
				}
			}
		break;
		//---------------------------------------------------//
		case "leave_community":
			$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			?>
			<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Leave', $communities_text_domain) ?></h2>
            <form name="leave_community" method="POST" action="?page=communities&action=leave_community_process">
                <input type="hidden" name="cid" value="<?php echo intval($_GET['cid']); ?>" />
                <input type="hidden" name="search_terms" value="<?php echo stripslashes(sanitize_text_field($_GET['search_terms'])); ?>" />
                <input type="hidden" name="return" value="<?php echo sanitize_text_field($_GET['return']); ?>" />
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Are you sure?', $communities_text_domain) ?></th>
                <td><select name="leave_community">
                    <option value="no" selected="selected" ><?php _e('No', $communities_text_domain); ?></option>
                    <option value="yes" ><?php _e('Yes', $communities_text_domain); ?></option>
                </select>
                </td>
                </tr>
                </table>
            <p class="submit">
            <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
            <input type="submit" name="Submit" value="<?php _e('Continue', $communities_text_domain) ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
		case "leave_community_process":
			if ( isset( $_POST['Cancel'] ) || sanitize_text_field($_POST['leave_community']) == 'no' ) {
				if ( sanitize_text_field($_POST['return']) == 'find_communities' ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=find-communities&search_terms=" . sanitize_text_field($_POST['search_terms']) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities';
					</script>
					";
				}
			} else {
				$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_POST['cid']));
				if ( $community_owner_user_ID == $user_ID ) {
					die();
				}
				communities_leave_community($user_ID, intval($_POST['cid']));
				if ( $_POST['return'] == 'find_communities' ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=find-communities&search_terms=" . sanitize_text_field($_POST['search_terms']) . "&updated=true&updatedmsg=" . urlencode(__('Successfully left.', $communities_text_domain)) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&updated=true&updatedmsg=" . urlencode(__('Successfully left.', $communities_text_domain)) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "message_board":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a></h2>
				<h3><?php _e('Topics', $communities_text_domain) ?></h3>
				<?php
				if( isset( $_GET[ 'start' ] ) == false ) {
					$start = 0;
				} else {
					$start = intval( $_GET[ 'start' ] );
				}
				if( isset( $_GET[ 'num' ] ) == false ) {
					$num = 30;
				} else {
					$num = intval( $_GET[ 'num' ] );
				}

				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_community_ID = %d", $_GET['cid']);
				$query .= " ORDER BY topic_sticky DESC, topic_last_updated_stamp DESC";
				$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
				$topics = $wpdb->get_results( $query, ARRAY_A );
				if( count( $topics ) < $num ) {
					$next = false;
				} else {
					$next = true;
				}
				if (count($topics) > 0){
					$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_community_ID = %d", $_GET['cid']));
					if ($topic_count > 30){
						?>
						<br />
						<table><td>
						<fieldset>
						<?php

						//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

						if( $start == 0 ) {
							echo __('Previous Page', $communities_text_domain);
						} elseif( $start <= 30 ) {
							echo '<a href="?page=communities&action=message_board&cid=' . intval($_GET['cid']) . '&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
						} else {
							echo '<a href="?page=communities&action=message_board&cid=' . intval($_GET['cid']) . '&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
						}
						if ( $next ) {
							echo '&nbsp;||&nbsp;<a href="?page=communities&action=message_board&cid=' . intval($_GET['cid']) . '&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page', $communities_text_domain) . '</a>';
						} else {
							echo '&nbsp;||&nbsp;' . __('Next Page', $communities_text_domain);
						}
						?>
						</fieldset>
						</td></table>
						<?php
					}
					echo "
					<br />
					<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
					<thead><tr>
					<th scope='col'>" . __('Topic', $communities_text_domain) . "</th>
					<th scope='col'>" . __('Posts', $communities_text_domain) . "</th>
					<th scope='col'>" . __('Last Poster', $communities_text_domain) . "</th>
					</tr></thead>
					<tbody id='the-list'>
					";
					//=========================================================//
						$class = '';
						foreach ($topics as $topic){
						if ( $topic['topic_sticky'] == '1' ) {
							$style = 'style="background-color:#D5EBEC;"';
						} else {
							$style = '';
						}
						//=========================================================//)
						echo "<tr class='" . $class . "' " . $style . " >";
						if ( $topic['topic_closed'] == '1' ) {
							$topic_closed = ' (' . __('Closed', $communities_text_domain) . ')';
						} else {
							$topic_closed = '';
						}
						if ( $topic['topic_sticky'] == '1' ) {
							$topic_sticky = ' (' . __('Sticky', $communities_text_domain) . ')';
						} else {
							$topic_sticky = '';
						}
						echo "<td valign='top'><strong><a href='?page=communities&action=topic&tid=" . $topic['topic_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($topic['topic_title']) ) . "</a>" . $topic_closed . $topic_sticky . "</strong></td>";
						echo "<td valign='top'>" . $topic['topic_posts'] . "</td>";
						$user_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $topic['topic_last_author']));
						echo "<td valign='top'>" . $user_details->display_name . "</td>";
						echo "</tr>";
						$class = ('alternate' == $class) ? '' : 'alternate';
						//=========================================================//
						}
					//=========================================================//
					?>
					</tbody></table>
					<?php
				} else {
					?>
					<p><?php _e("There currently aren't any topics on this message board. Use the form below to create the first topic!", $communities_text_domain) ?></p>
					<?php
				}
				?>
				<br />
				<h2><?php _e('New Topic', $communities_text_domain) ?></h2>
                <form name="new_topic" method="POST" action="?page=communities&action=new_topic&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
                    <td><input type="text" name="topic_title" id="topic_title" style="width: 95%" value="<?php echo (isset($_POST['topic_title'])) ? stripslashes(sanitize_text_field($_POST['topic_title'])) : ''; ?>" />
                    <br />
                    <?php _e('Required', $communities_text_domain) ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
                    <td><textarea name="topic_content" id="topic_content" style="width: 95%" rows="10"><?php echo (isset($_POST['topic_content'])) ? stripslashes(wp_kses($_POST['topic_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)) : ''; ?></textarea>
                    <br />
                    <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
                    </tr>
                    <?php
                    $member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = '%d'", $_GET['cid'], $user_ID));
					if (  $member_moderator == '1' || is_super_admin() ) {
					?>
                        <tr valign="top">
                        <th scope="row"><?php _e('Sticky', $communities_text_domain) ?></th>
                        <td><select name="topic_sticky">
							<?php 
							if (!isset($_POST['topic_sticky'])) $_POST['topic_sticky'] = ''; 
							else $_POST['topic_sticky'] = sanitize_text_field($_POST['topic_sticky']);
							?>
                            <option value="0" <?php if ($_POST['topic_sticky'] == '0' || $_POST['topic_sticky'] == '') echo 'selected="selected"'; ?>><?php _e('No', $communities_text_domain); ?></option>
                            <option value="1" <?php if ($_POST['topic_sticky'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $communities_text_domain); ?></option>
                        </select>
						</td>
                        </tr>
                    <?php
					}
					?>
                    </table>
                <p class="submit">
                <input type="submit" name="Submit" value="<?php _e('Post', $communities_text_domain) ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "new_topic":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				if ( isset( $_POST['Cancel'] ) ) {
					if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=message_board&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
						</script>
						";
					} else {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=message_board&cid=" . intval($_GET['cid']) . "';
						</script>
						";
					}
				} else {
					$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
					?>
					<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <?php _e('New Topic', $communities_text_domain) ?></h2>
					<?php
					if ( empty( $_POST['topic_title'] ) || empty( $_POST['topic_content'] ) ) {
						?>
                        <p><?php _e('Please fill in all fields.', $communities_text_domain); ?></p>
                        <form name="new_topic" method="POST" action="?page=communities&action=new_topic&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                            <table class="form-table">
                            <tr valign="top">
                            <th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
                            <td><input type="text" name="topic_title" id="topic_title" style="width: 95%" value="<?php echo stripslashes(sanitize_text_field($_POST['topic_title'])); ?>" />
                            <br />
                            <?php _e('Required', $communities_text_domain) ?></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
                            <td><textarea name="topic_content" id="topic_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses($_POST['topic_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)); ?></textarea>
                            <br />
                            <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
                            </tr>
                            <?php
                            $member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
                            if (  $member_moderator == '1' || is_super_admin() ) {
								if (!isset($_POST['topic_sticky'])) $_POST['topic_sticky'] == '0';
                            ?>
                                <tr valign="top">
                                <th scope="row"><?php _e('Sticky', $communities_text_domain) ?></th>
                                <td><select name="topic_sticky">
                                    <option value="0" <?php if ($_POST['topic_sticky'] == '0' || $_POST['topic_sticky'] == '') echo 'selected="selected"'; ?>><?php _e('No', $communities_text_domain); ?></option>
                                    <option value="1" <?php if ($_POST['topic_sticky'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $communities_text_domain); ?></option>
                                </select>
                                </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </table>
                        <p class="submit">
                        <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                        <input type="submit" name="Submit" value="<?php _e('Post', $communities_text_domain) ?>" />
                        </p>
                        </form>
                        <?php
					} else {
						$topic_ID = communities_add_topic(intval($_GET['cid']), $user_ID, stripslashes(sanitize_text_field($_POST['topic_title'])), stripslashes(wp_kses($_POST['post_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)), sanitize_text_field($_POST['topic_sticky']));
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . $topic_ID . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Topic added.', $communities_text_domain)) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "topic":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
				$date_format = get_option('date_format');
				$time_format = get_option('time_format');
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=topic&tid=<?php echo (isset($_GET['tid'])) ? intval($_GET['tid']) : ''; ?>&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field($topic_details->topic_title)); ?></a><?php if ( $topic_details->topic_closed == '1' ) { echo ' (' . __('Closed', $communities_text_domain) . ')'; }; ?></h2>
                <ul>
	                <li><strong><?php _e('Started', $communities_text_domain); ?>:</strong> <?php echo date_i18n($date_format . ' ' . $time_format,$topic_details->topic_stamp); ?></li>
	                <li><strong><?php _e('Last Updated', $communities_text_domain); ?>:</strong> <?php echo date_i18n($date_format . ' ' . $time_format,$topic_details->topic_last_updated_stamp); ?></li>
                    <?php
                    $last_poster_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $topic_details->topic_last_author));
                    $last_poster_primary_blog = get_active_blog_for_user( $topic_details->topic_last_author );
					?>
	                <li><strong><?php _e('Last Poster', $communities_text_domain); ?>:</strong> <?php echo $last_poster_details->display_name; ?> (<?php 

					if (is_plugin_active('messaging/messaging.php')) {
						?><a href="admin.php?page=messaging_new&message_to=<?php echo $last_poster_details->user_login; ?>" style="text-decoration:none;"><?php
						 _e('Send Message', $communities_text_domain); ?></a> | <?php } ?><a href="http://<?php echo  $last_poster_primary_blog->domain .  $last_poster_primary_blog->path; ?>" style="text-decoration:none;"><?php _e('View Blog', $communities_text_domain); ?></a>)</li>
                    <?php
					if ( is_super_admin() || $member_moderator == '1' ) {
						?>
		                <li><strong><?php _e('Actions', $communities_text_domain); ?>:</strong>
                        <?php
						if ( $topic_details->topic_closed == '1' ) {
							?>
							<a href="?page=communities&action=open_topic&tid=<?php echo (isset($_GET['tid'])) ? intval($_GET['tid']) : ''; ?>&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>&start=<?php echo (isset($_GET['start'])) ? intval($_GET['start']) : ''; ?>&num=<?php echo (isset($_GET['num'])) ? intval($_GET['num']) : ''; ?>" style="text-decoration:none;"><?php _e('Open', $communities_text_domain); ?></a> |
							<?php
						} else {
							?>
							<a href="?page=communities&action=close_topic&tid=<?php echo (isset($_GET['tid'])) ? intval($_GET['tid']) : ''; ?>&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>&start=<?php echo (isset($_GET['start'])) ? intval($_GET['start']) : ''; ?>&num=<?php echo (isset($_GET['num'])) ? intval($_GET['num']) : ''; ?>" style="text-decoration:none;"><?php _e('Close', $communities_text_domain); ?></a> |
							<?php
						}
						if ( $topic_details->topic_sticky == '1' ) {
							?>
							<a href="?page=communities&action=unstick_topic&tid=<?php echo (isset($_GET['tid'])) ? intval($_GET['tid']) : ''; ?>&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>&start=<?php echo (isset($_GET['start'])) ? intval($_GET['start']) : ''; ?>&num=<?php echo (isset($_GET['num'])) ? intval($_GET['num']) : ''; ?>" style="text-decoration:none;"><?php _e('Unstick', $communities_text_domain); ?></a> |
							<?php
						} else {
							?>
							<a href="?page=communities&action=stick_topic&tid=<?php echo (isset($_GET['tid'])) ? intval($_GET['tid']) : ''; ?>&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>&start=<?php echo (isset($_GET['start'])) ? intval($_GET['start']) : ''; ?>&num=<?php echo (isset($_GET['num'])) ? intval($_GET['num']) : ''; ?>" style="text-decoration:none;"><?php _e('Make Sticky', $communities_text_domain); ?></a> |
							<?php
						}
						?>
						<a href="?page=communities&action=edit_topic&tid=<?php echo (isset($_GET['tid'])) ? intval($_GET['tid']) : ''; ?>&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>&start=<?php echo (isset($_GET['start'])) ? intval($_GET['start']) : ''; ?>&num=<?php echo (isset($_GET['num'])) ? intval($_GET['num']) : ''; ?>" style="text-decoration:none;"><?php _e('Edit Title', $communities_text_domain); ?></a> |
						<a href="?page=communities&action=remove_topic&tid=<?php echo (isset($_GET['tid'])) ? intval($_GET['tid']) : ''; ?>&cid=<?php echo (isset($_GET['cid'])) ? intval($_GET['cid']) : ''; ?>&start=<?php echo (isset($_GET['start'])) ? intval($_GET['start']) : ''; ?>&num=<?php echo (isset($_GET['num'])) ? intval($_GET['num']) : ''; ?>" style="text-decoration:none;"><?php _e('Remove', $communities_text_domain); ?></a>
                        </li>
    	                <?php
					}
					?>
                </ul>
				<?php
				if( isset( $_GET[ 'start' ] ) == false ) {
					$start = 0;
				} else {
					$start = intval( $_GET[ 'start' ] );
				}
				if( isset( $_GET[ 'num' ] ) == false ) {
					$num = 15;
				} else {
					$num = intval( $_GET[ 'num' ] );
				}

				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_topic_ID = %d ORDER BY post_ID ASC LIMIT %d,%d", $_GET['tid'], $start, $num);
				$posts = $wpdb->get_results( $query, ARRAY_A );
				if( count( $posts ) < $num ) {
					$next = false;
				} else {
					$next = true;
				}
				if (count($posts) > 0){
					$post_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_posts WHERE post_topic_ID = %d", $_GET['tid']));
					if ($post_count > 15){
						?>
						<br />
						<table><td>
						<fieldset>
						<?php

						//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

						if( $start == 0 ) {
							echo __('Previous Page', $communities_text_domain);
						} elseif( $start <= 15 ) {
							echo '<a href="?page=communities&action=topic&tid=' . intval($_GET['tid']) . '&cid=' . intval($_GET['cid']) . '&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
						} else {
							echo '<a href="?page=communities&action=topic&tid=' . intval($_GET['tid']) . '&cid=' . intval($_GET['cid']) . '&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
						}
						if ( $next ) {
							echo '&nbsp;||&nbsp;<a href="?page=communities&action=topic&tid=' . intval($_GET['tid']) . '&cid=' . intval($_GET['cid']) . '&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page', $communities_text_domain) . '</a>';
						} else {
							echo '&nbsp;||&nbsp;' . __('Next Page', $communities_text_domain);
						}
						?>
						</fieldset>
						</td></table>
						<?php
					}
					echo "
					<br />
					<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
					<thead><tr>
					<th scope='col' width='100px'><center>" . __('Author', $communities_text_domain) . "</center></th>
					<th scope='col'>" . __('Post', $communities_text_domain) . "</th>
					</tr></thead>
					<tbody id='the-list'>
					";
					//=========================================================//
						$class = '';
						if (!isset($_GET['start'])) $_GET['start'] = '';
						else $_GET['start'] = intval($_GET['start']);
						
						if (!isset($_GET['num'])) $_GET['num'] = '';
						else $_GET['num'] = intval($_GET['num']);
						
						foreach ($posts as $post){
						//=========================================================//)
						$user_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $post['post_author']));
						$user_primary_blog = get_active_blog_for_user( $post['post_author'] );
						echo "<tr class='" . $class . "'>";
						echo "<td valign='top' style='border-right:#cccccc solid 1px;' ><center><strong>" . $user_details->display_name;
						echo "<br />";

						if (is_plugin_active('avatar/avatar.php')) {
							echo "<img src='http://" . $current_site->domain . $current_site->path . "avatar/user-" . $post['post_author'] . "-48.png' />";
						} else {
							echo get_avatar('dummy@dummy.com', 48);
						}
						if (is_plugin_active('messaging/messaging.php')) {
							if ( $post['post_author'] != $user_ID ) {
								echo "<br />";
								echo "<a href='admin.php?page=messaging_new&message_to=" . $user_details->user_login . "' style='text-decoration:none;'>" . __("Send Message", $communities_text_domain) . "</a>";
							}
						}
						echo "<br />";
						echo "<a href='http://" . $user_primary_blog->domain . $user_primary_blog->path . "' style='text-decoration:none;'>" . __("View Blog", $communities_text_domain) . "</a>";
						echo "</strong></center></td>";
						echo "<td valign='top'>";
						echo "<p>" . stripslashes( wp_kses($post['post_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS) ) . "</p>";
						echo "<br />";
						echo "<div style='border-top:#cccccc solid 1px;' >";
						echo __("Posted", $communities_text_domain) . ": " . date_i18n($date_format . ' ' . $time_format, $post['post_stamp']);
						$time_difference = time() - $post['post_stamp'];
						if ( $member_moderator == '1' || is_super_admin() ) {
							echo " | <a href='?page=communities&action=edit_post&pid=" . $post['post_ID'] . "&tid=" . $topic_details->topic_ID . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "' style='text-decoration:none;'>" . __("Edit", $communities_text_domain) . "</a>";
							if ( $topic_details->topic_posts > 1 ) {
								echo " | <a href='?page=communities&action=remove_post&pid=" . $post['post_ID'] . "&tid=" . $topic_details->topic_ID . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "' style='text-decoration:none;'>" . __("Remove", $communities_text_domain) . "</a>";
							}
						} else if ( $post['post_author'] && $time_difference < 900 ) {
							echo " | <a href='?page=communities&action=edit_post&pid=" . $post['post_ID'] . "&tid=" . $topic_details->topic_ID . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "' style='text-decoration:none;'>" . __("Edit", $communities_text_domain) . "</a>";
						}
						echo "</div>";
						echo "</td>";
						echo "</tr>";
						$class = ('alternate' == $class) ? '' : 'alternate';
						//=========================================================//
						}
					//=========================================================//
					?>
					</tbody></table>
					<?php
				}
				if ( $topic_details->topic_closed != '1' ) {
					?>
					<br />
					<h2><?php _e('New Post', $communities_text_domain) ?></h2>
					<form name="new_post" method="POST" action="?page=communities&action=new_post&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
						<table class="form-table">
						<tr valign="top">
						<th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
						<td><textarea name="post_content" id="post_content" style="width: 95%" rows="10"><?php echo (isset($_POST['post_content'])) ? stripslashes(wp_kses($_POST['post_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)) : ''; ?></textarea>
						<br />
						<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
						</tr>
						</table>
					<p class="submit">
					<input type="submit" name="Submit" value="<?php _e('Post', $communities_text_domain) ?>" />
					</p>
					</form>
					<?php
				}
			}

		break;
		//---------------------------------------------------//
		case "new_post":
			if ( isset( $_POST['Cancel'] ) ) {
					if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
						</script>
						";
					} else {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "';
						</script>
						";
					}
			} else {
				$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
				if ( $topic_details->topic_closed != '1' ) {
					$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
					if ( $member_count > 0 || is_super_admin() ) {
						if ( empty($_POST['post_content']) ) {
							$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
							?>
							<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=topic&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field($topic_details->topic_title)); ?></a></h2>
							<p><?php _e('Please provide some content.', $communities_text_domain); ?></p>
							<form name="new_post" method="POST" action="?page=communities&action=new_post&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
								<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
								<td><textarea name="post_content" id="post_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses($_POST['post_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)); ?></textarea>
								<br />
								<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
								</tr>
								</table>
							<p class="submit">
							<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
							<input type="submit" name="Submit" value="<?php _e('Post', $communities_text_domain) ?>" />
							</p>
							</form>
							<?php
						} else {
							communities_add_post(intval($_GET['cid']), intval($_GET['tid']), $user_ID, stripslashes(wp_kses($_POST['post_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)));
							if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
								echo "
								<script type='text/javascript'>
								window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Post added.', $communities_text_domain)) . "';
								</script>
								";
							} else {
								echo "
								<script type='text/javascript'>
								window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Post added.', $communities_text_domain)) . "';
								</script>
								";
							}
						}
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "edit_post":
			$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
			$post_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = %d", $_GET['pid']));
			if ( $topic_details->topic_closed != '1' ) {
				$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				$time_difference = time() - $post_details->post_stamp;
				if ( $member_moderator == '1' || is_super_admin() || ( $post_details->post_author == $user_ID && $time_difference < 900 ) ) {
					$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
					?>
					<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=topic&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field($topic_details->topic_title)); ?></a> &raquo; <?php _e('Edit Post', $communities_text_domain); ?></h2>
					<form name="edit_post" method="POST" action="?page=communities&action=edit_post_process&pid=<?php echo intval($_GET['pid']); ?>&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
						<table class="form-table">
						<tr valign="top">
						<th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
						<td><textarea name="post_content" id="post_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses( $post_details->post_content, $COMMUNITIES_ALLOWED_CONTENT_TAGS )); ?></textarea>
						<br />
						<?php _e('Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
						</tr>
						</table>
					<p class="submit">
					<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
					<input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
					</p>
					</form>
					<?php
				}
			}
		break;
		//---------------------------------------------------//
		case "edit_post_process":
			if ( isset( $_POST['Cancel'] ) ) {
					if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
						</script>
						";
					} else {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "';
						</script>
						";
					}
			} else {
				$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
				$post_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = %d", $_GET['pid']));
				if ( $topic_details->topic_closed != '1' ) {
					$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
					$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
					$time_difference = time() - $post_details->post_stamp;
					if ( $member_moderator == '1' || is_super_admin() || ( $post_details->post_author == $user_ID && $time_difference < 900 ) ) {
						if ( empty( $_POST['post_content'] ) ) {
							$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
							?>
							<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=topic&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field($topic_details->topic_title)); ?></a> &raquo; <?php _e('Edit Post', $communities_text_domain); ?></h2>
                            <p><?php _e('Please provide some content', $communities_text_domain); ?></p>
							<form name="edit_post" method="POST" action="?page=communities&action=edit_post_process&pid=<?php echo intval($_GET['pid']); ?>&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
								<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
								<td><textarea name="post_content" id="post_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses($_POST['post_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)); ?></textarea>
								<br />
								<?php _e('Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
								</tr>
								</table>
							<p class="submit">
							<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
							<input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
							</p>
							</form>
							<?php
						} else {
							communities_update_post_content(intval($_GET['pid']), stripslashes(wp_kses($_POST['post_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)));
							if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
								echo "
								<script type='text/javascript'>
								window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.', $communities_text_domain)) . "';
								</script>
								";
							} else {
								echo "
								<script type='text/javascript'>
								window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.', $communities_text_domain)) . "';
								</script>
								";
							}
						}
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_post":
			$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
			$post_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = %d", $_GET['pid']));
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_moderator == '1' || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=topic&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field($topic_details->topic_title)); ?></a> &raquo; <?php _e('Remove Post', $communities_text_domain); ?></h2>

				<form name="remove_post" method="POST" action="?page=communities&action=remove_post_process&pid=<?php echo intval($_GET['pid']); ?>&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Are you sure?', $communities_text_domain) ?></th>
                    <td><select name="remove_post">
                        <option value="no" selected="selected" ><?php _e('No', $communities_text_domain); ?></option>
                        <option value="yes" ><?php _e('Yes', $communities_text_domain); ?></option>
                    </select>
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                <input type="submit" name="Submit" value="<?php _e('Continue', $communities_text_domain) ?>" />
                </p>
                </form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "remove_post_process":
			if ( isset( $_POST['Cancel'] ) ) {
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "';
					</script>
					";
				}
			} else {
				$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
				$post_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = %d", $_GET['pid']));
				$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				if ( $member_moderator == '1' || is_super_admin() ) {
					if ( $_POST['remove_post'] == 'yes' ) {
						communities_delete_post(intval($_GET['tid']), intval($_GET['pid']));
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Post removed.', $communities_text_domain)) . "';
						</script>
						";
					} else {
						if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
							echo "
							<script type='text/javascript'>
							window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
							</script>
							";
						} else {
							echo "
							<script type='text/javascript'>
							window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "';
							</script>
							";
						}
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "close_topic":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if (  $member_moderator == '1' || is_super_admin() ) {
				communities_close_topic(intval($_GET['tid']));
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Topic closed.', $communities_text_domain)) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Topic closed.', $communities_text_domain)) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "open_topic":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if (  $member_moderator == '1' || is_super_admin() ) {
				communities_open_topic(intval($_GET['tid']));
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Topic opened.', $communities_text_domain)) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Topic opened.', $communities_text_domain)) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "stick_topic":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if (  $member_moderator == '1' || is_super_admin() ) {
				communities_stick_topic(intval($_GET['tid']));
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Topic made sticky.', $communities_text_domain)) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Topic made sticky.', $communities_text_domain)) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "unstick_topic":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if (  $member_moderator == '1' || is_super_admin() ) {
				communities_unstick_topic(intval($_GET['tid']));
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Sticky removed.', $communities_text_domain)) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Sticky removed.', $communities_text_domain)) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_topic":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if (  $member_moderator == '1' || is_super_admin() ) {
				$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=topic&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field($topic_details->topic_title)); ?></a> &raquo; <?php _e('Remove Topic', $communities_text_domain); ?></h2>

				<form name="remove_topic" method="POST" action="?page=communities&action=remove_topic_process&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Are you sure?', $communities_text_domain) ?></th>
                    <td><select name="remove_topic">
                        <option value="no" selected="selected" ><?php _e('No', $communities_text_domain); ?></option>
                        <option value="yes" ><?php _e('Yes', $communities_text_domain); ?></option>
                    </select>
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                <input type="submit" name="Submit" value="<?php _e('Continue', $communities_text_domain) ?>" />
                </p>
                </form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "remove_topic_process":
			if ( isset( $_POST['Cancel'] ) ) {
					if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
						</script>
						";
					} else {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "';
						</script>
						";
					}
			} else {
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				if (  $member_moderator == '1' || is_super_admin() ) {
					if ( $_POST['remove_topic'] == 'yes' ) {
						communities_delete_topic(intval($_GET['tid']));
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=message_board&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Topic Removed.', $communities_text_domain)) . "';
						</script>
						";
					} else {
						if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
							echo "
							<script type='text/javascript'>
							window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
							</script>
							";
						} else {
							echo "
							<script type='text/javascript'>
							window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "';
							</script>
							";
						}
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "edit_topic":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if (  $member_moderator == '1' || is_super_admin() ) {
				$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=topic&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field($topic_details->topic_title)); ?></a> &raquo; <?php _e('Edit Topic', $communities_text_domain); ?></h2>
                <form name="edit_topic" method="POST" action="?page=communities&action=edit_topic_process&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
                    <td><input type="text" name="topic_title" id="topic_title" style="width: 95%" value="<?php echo stripslashes( sanitize_text_field($topic_details->topic_title) ); ?>" />
                    <br />
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                <input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "edit_topic_process":
			if ( isset( $_POST['Cancel'] ) ) {
					if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
						</script>
						";
					} else {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "';
						</script>
						";
					}
			} else {
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				if (  $member_moderator == '1' || is_super_admin() ) {
					if ( empty( $_POST['topic_title'] ) ) {
						$topic_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = %d", $_GET['tid']));
						$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
						?>
						<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Message Board', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=topic&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field($topic_details->topic_title)); ?></a> &raquo; <?php _e('Edit Topic', $communities_text_domain); ?></h2>
                        <p><?php _e('Please provide a title.', $communities_text_domain); ?></p>
						<form name="edit_topic" method="POST" action="?page=communities&action=edit_topic_process&tid=<?php echo intval($_GET['tid']); ?>&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
							<table class="form-table">
							<tr valign="top">
							<th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
							<td><input type="text" name="topic_title" id="topic_title" style="width: 95%" value="<?php echo stripslashes( sanitize_text_field($topic_details->topic_title) ); ?>" />
							<br />
							</td>
							</tr>
							</table>
						<p class="submit">
						<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
						<input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
						</p>
						</form>
						<?php
					} else {
						communities_update_topic_title(intval($_GET['tid']), stripslashes(sanitize_text_field($_POST['topic_title'])));
						if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
							echo "
							<script type='text/javascript'>
							window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Changes Saved.', $communities_text_domain)) . "';
							</script>
							";
						} else {
							echo "
							<script type='text/javascript'>
							window.location='?page=communities&action=topic&tid=" . intval($_GET['tid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Changes Saved.', $communities_text_domain)) . "';
							</script>
							";
						}
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "wiki":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=wiki&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Wiki', $communities_text_domain) ?></a></h2>
                <?php
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				if (  $member_moderator == '1' || is_super_admin() ) {
					?>
					<h3><?php _e('Manage', $communities_text_domain) ?></h3>
					<ul>
					<li><strong><?php _e('Actions', $communities_text_domain); ?>:</strong>
					<a href="?page=communities&action=new_page&ppid=0&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('New Page', $communities_text_domain); ?></a>
					</li>
					</ul>
					<?php
				}
				?>
				<h3><?php _e('Pages', $communities_text_domain) ?></h3>
				<?php
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], '0');
				$pages[0] = $wpdb->get_results( $query, ARRAY_A );
				if ( count( $pages[0] ) > 0 ) {
					echo "<ul>";
					foreach ( $pages[0] as $page ) {
						echo "<li><strong><a href='?page=communities&action=page&pid=" . $page['page_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($page['page_title']) ) . "</a></strong></li>";
						$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], $page['page_ID']);
						$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
						if ( count( $pages[$page['page_ID']] ) > 0 ) {
							echo "<ul>";
							foreach ( $pages[$page['page_ID']] as $page ) {
								echo "<li><strong><a href='?page=communities&action=page&pid=" . $page['page_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($page['page_title']) ) . "</a></strong></li>";
								$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], $page['page_ID']);
								$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
								if ( count( $pages[$page['page_ID']] ) > 0 ) {
									echo "<ul>";
									foreach ( $pages[$page['page_ID']] as $page ) {
										echo "<li><strong><a href='?page=communities&action=page&pid=" . $page['page_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($page['page_title']) ) . "</a></strong></li>";
										$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], $page['page_ID']);
										$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
										if ( count( $pages[$page['page_ID']] ) > 0 ) {
											echo "<ul>";
											foreach ( $pages[$page['page_ID']] as $page ) {
												echo "<li><strong><a href='?page=communities&action=page&pid=" . $page['page_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($page['page_title']) ) . "</a></strong></li>";
												$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], $page['page_ID']);
												$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
												if ( count( $pages[$page['page_ID']] ) > 0 ) {
													echo "<ul>";
													foreach ( $pages[$page['page_ID']] as $page ) {
														echo "<li><strong><a href='?page=communities&action=page&pid=" . $page['page_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($page['page_title']) ) . "</a></strong></li>";
														$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], $page['page_ID']);
														$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
														if ( count( $pages[$page['page_ID']] ) > 0 ) {
															echo "<ul>";
															foreach ( $pages[$page['page_ID']] as $page ) {
																echo "<li><strong><a href='?page=communities&action=page&pid=" . $page['page_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($page['page_title']) ) . "</a></strong></li>";
																$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], $page['page_ID']);
																$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
																if ( count( $pages[$page['page_ID']] ) > 0 ) {
																	echo "<ul>";
																	foreach ( $pages[$page['page_ID']] as $page ) {
																		echo "<li><strong><a href='?page=communities&action=page&pid=" . $page['page_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($page['page_title']) ) . "</a></strong></li>";
																		$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], $page['page_ID']);
																		$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
																		if ( count( $pages[$page['page_ID']] ) > 0 ) {
																			echo "<ul>";
																			foreach ( $pages[$page['page_ID']] as $page ) {
																				echo "<li><strong><a href='?page=communities&action=page&pid=" . $page['page_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'>" . stripslashes( sanitize_text_field($page['page_title']) ) . "</a></strong></li>";
																				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d AND page_parent_page_ID = %d", $_GET['cid'], $page['page_ID']);
																				$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
																			}
																			echo "</ul>";
																		}
																	}
																	echo "</ul>";
																}
															}
															echo "</ul>";
														}
													}
													echo "</ul>";
												}
											}
											echo "</ul>";
										}
									}
									echo "</ul>";
								}
							}
							echo "</ul>";
						}
					}
					echo "</ul>";
				} else {
					?>
					<p><?php _e('There currently aren\'t any pages.', $communities_text_domain); ?></p>
                    <?php
				}
			}
		break;
		//---------------------------------------------------//
		case "page":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				$page_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = %d", $_GET['pid']));
				if ( $page_details->page_parent_page_ID == '0' ) {
					$depth = 1;
					$page_title = '<a href="?page=communities&action=page&pid=' . $page_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
				} else {
					$page_2_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = %d", $page_details->page_parent_page_ID));
					if ( $page_2_details->page_parent_page_ID == '0' ) {
						$depth = 2;
						$page_title = '<a href="?page=communities&action=page&pid=' . $page_2_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
					} else {
						$page_3_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = %d", $page_2_details->page_parent_page_ID));
						if ( $page_3_details->page_parent_page_ID == '0' ) {
							$depth = 3;
							$page_title = '<a href="?page=communities&action=page&pid=' . $page_3_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_3_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_2_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
						} else {
							$page_4_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = %d", $page_3_details->page_parent_page_ID));
							if ( $page_4_details->page_parent_page_ID == '0' ) {
								$depth = 4;
								$page_title = '<a href="?page=communities&action=page&pid=' . $page_4_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_4_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_3_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_3_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_2_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
							} else {
								$page_5_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = %d", $page_4_details->page_parent_page_ID));
								if ( $page_5_details->page_parent_page_ID == '0' ) {
									$depth = 5;
									$page_title = '<a href="?page=communities&action=page&pid=' . $page_5_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_5_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_4_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_4_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_3_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_3_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_2_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
								} else {
									$page_6_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = %d", $page_5_details->page_parent_page_ID));
									$depth = 6;
									$page_title = '<a href="?page=communities&action=page&pid=' . $page_6_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_6_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_5_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_5_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_4_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_4_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_3_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_3_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_2_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="?page=communities&action=page&pid=' . $page_details->page_ID . '&cid=' . intval($_GET['cid']) . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
								}
							}
						}
					}
				}
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=wiki&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Wiki', $communities_text_domain) ?></a> &raquo; <?php echo $page_title; ?></h2>
                <?php
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				if (  $member_moderator == '1' || is_super_admin() ) {
					?>
					<h3><?php _e('Manage', $communities_text_domain) ?></h3>
					<ul>
					<li><strong><?php _e('Actions', $communities_text_domain); ?>:</strong>
                    <?php
					if ( $depth < 6 ) {
						?>
						<a href="?page=communities&action=new_page&ppid=<?php echo intval($_GET['pid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('New Page', $communities_text_domain); ?></a> |
						<?php
                    }
                    ?>
					<a href="?page=communities&action=edit_page&pid=<?php echo intval($_GET['pid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Edit Page', $communities_text_domain); ?></a> |
					<a href="?page=communities&action=remove_page&pid=<?php echo intval($_GET['pid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Remove Page', $communities_text_domain); ?></a>
					</li>
					</ul>
                    <h3><?php _e('Page', $communities_text_domain) ?></h3>
					<?php
				}
				?>
                <p><?php echo stripslashes(wp_kses($page_details->page_content, $COMMUNITIES_ALLOWED_CONTENT_TAGS)); ?></p>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "new_page":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if (  $member_moderator == '1' || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=wiki&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Wiki', $communities_text_domain) ?></a> &raquo; <?php _e('New Page', $communities_text_domain); ?></h2>
                <form name="new_page" method="POST" action="?page=communities&action=new_page_process&ppid=<?php echo intval($_GET['ppid']); ?>&cid=<?php echo intval($_GET['cid']); ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
                    <td><input type="text" name="page_title" id="page_title" style="width: 95%" value="<?php echo (isset($_POST['page_title'])) ? stripslashes(sanitize_text_field($_POST['page_title'])) : ''; ?>" />
                    <br />
                    <?php _e('Required', $communities_text_domain) ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
                    <td><textarea name="page_content" id="page_content" style="width: 95%" rows="10"><?php echo (isset($_POST['page_content'])) ? stripslashes(wp_kses($_POST['page_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)) : ''; ?></textarea>
                    <br />
                    <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                <input type="submit" name="Submit" value="<?php _e('Publish', $communities_text_domain) ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "new_page_process":
			if ( isset( $_POST['Cancel'] ) ) {
				if ((!isset($_GET['ppid'])) || ($_GET['ppid'] == '0')) {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=wiki&cid=" . intval($_GET['cid']) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=page&pid=" . intval($_GET['ppid']) . "&cid=" . intval($_GET['cid']) . "';
					</script>
					";
				}
			} else {
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", intval($_GET['cid']), $user_ID));
				if (  $member_moderator == '1' || is_super_admin() ) {
					if ( empty( $_POST['page_title'] ) || empty( $_POST['page_content'] ) ) {
						$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
						?>
						<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=wiki&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Wiki', $communities_text_domain) ?></a> &raquo; <?php _e('New Page', $communities_text_domain); ?></h2>
                        <p><?php _e('Please fill in all fields.', $communities_text_domain); ?></p>
						<form name="new_page" method="POST" action="?page=communities&action=new_page_process&ppid=<?php echo intval($_GET['ppid']); ?>&cid=<?php echo intval($_GET['cid']); ?>">
							<table class="form-table">
							<tr valign="top">
							<th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
							<td><input type="text" name="page_title" id="page_title" style="width: 95%" value="<?php echo stripslashes(sanitize_text_field($_POST['page_title'])); ?>" />
							<br />
							<?php _e('Required', $communities_text_domain) ?></td>
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
							<td><textarea name="page_content" id="page_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses($_POST['page_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)); ?></textarea>
							<br />
							<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
							</tr>
							</table>
						<p class="submit">
						<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
						<input type="submit" name="Submit" value="<?php _e('Publish', $communities_text_domain) ?>" />
						</p>
						</form>
						<?php
					} else {
						$page_ID = communities_add_page(intval($_GET['cid']), intval($_GET['ppid']), stripslashes(sanitize_text_field($_POST['page_title'])), stripslashes(wp_kses($_POST['page_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)));
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=page&pid=" . $page_ID . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Page published.', $communities_text_domain)) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "edit_page":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			$page_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = %d", $_GET['pid']));
			if (  $member_moderator == '1' || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=wiki&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Wiki', $communities_text_domain) ?></a> &raquo; <?php _e('Edit Page', $communities_text_domain); ?></h2>
                <form name="new_page" method="POST" action="?page=communities&action=edit_page_process&pid=<?php echo intval($_GET['pid']); ?>&cid=<?php echo intval($_GET['cid']); ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
                    <td><input type="text" name="page_title" id="page_title" style="width: 95%" value="<?php echo stripslashes(sanitize_text_field( $page_details->page_title )); ?>" />
                    <br />
                    <?php _e('Required', $communities_text_domain) ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
                    <td><textarea name="page_content" id="page_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses( $page_details->page_content, $COMMUNITIES_ALLOWED_CONTENT_TAGS )); ?></textarea>
                    <br />
                    <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                <input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "edit_page_process":
			if ( isset( $_POST['Cancel'] ) ) {
				
				if ((!isset($_GET['ppid'])) || (intval($_GET['ppid']) == 0)) {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=wiki&cid=" . intval($_GET['cid']) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=communities&action=page&pid=" . intval($_GET['pid']) . "&cid=" . intval($_GET['cid']) . "';
					</script>
					";
					
				}
			} else {
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				if (  $member_moderator == '1' || is_super_admin() ) {
					if ( empty( $_POST['page_title'] ) || empty( $_POST['page_content'] ) ) {
						$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
						?>
						<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=wiki&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Wiki', $communities_text_domain) ?></a> &raquo; <?php _e('Edit Page', $communities_text_domain); ?></h2>
                        <p><?php _e('Please fill in all fields.', $communities_text_domain); ?></p>
						<form name="new_page" method="POST" action="?page=communities&action=edit_page_process&pid=<?php echo intval($_GET['ppid']); ?>&cid=<?php echo intval($_GET['cid']); ?>">
							<table class="form-table">
							<tr valign="top">
							<th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
							<td><input type="text" name="page_title" id="page_title" style="width: 95%" value="<?php echo stripslashes(sanitize_text_field($_POST['page_title'])); ?>" />
							<br />
							<?php _e('Required', $communities_text_domain) ?></td>
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
							<td><textarea name="page_content" id="page_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses($_POST['page_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)); ?></textarea>
							<br />
							<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
							</tr>
							</table>
						<p class="submit">
						<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
						<input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
						</p>
						</form>
						<?php
					} else {
						communities_update_page(intval($_GET['pid']), stripslashes(sanitize_text_field($_POST['page_title'])), stripslashes(wp_kses($_POST['page_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)));
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=page&pid=" . intval($_GET['pid']) . "&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.', $communities_text_domain)) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_page":
			$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if (  $member_moderator == '1' || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=wiki&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Wiki', $communities_text_domain) ?></a> &raquo; <?php _e('Remove Page', $communities_text_domain); ?></h2>
                <form name="leave_community" method="POST" action="?page=communities&action=remove_page_process&pid=<?php echo intval($_GET['pid']); ?>&cid=<?php echo intval($_GET['cid']); ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Are you sure?', $communities_text_domain) ?></th>
                    <td><select name="remove_page">
                        <option value="no" selected="selected" ><?php _e('No', $communities_text_domain); ?></option>
                        <option value="yes" ><?php _e('Yes', $communities_text_domain); ?></option>
                    </select>
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                <input type="submit" name="Submit" value="<?php _e('Continue', $communities_text_domain) ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "remove_page_process":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "
				<script type='text/javascript'>
				window.location='?page=communities&action=page&pid=" . intval($_GET['pid']) . "&cid=" . intval($_GET['cid']) . "';
				</script>
				";
			} else {
				$member_moderator = $wpdb->get_var($wpdb->prepare("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
				if (  $member_moderator == '1' || is_super_admin() ) {
					if ( $_POST['remove_page'] == 'yes' ) {
						communities_delete_page(intval($_GET['pid']));
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=wiki&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Page removed.', $communities_text_domain)) . "';
						</script>
						";
					} else {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&action=page&pid=" . intval($_GET['pid']) . "&cid=" . intval($_GET['cid']) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "dashboard":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Dashboard', $communities_text_domain) ?></a></h2>
                <div id="dashboard-widgets-wrap">
					<div id='dashboard-widgets' class='metabox-holder'>

						<!-- <div id='side-info-column' class='inner-sidebar'> -->
                        <div id='normal-sortables' class='meta-box-sortables'>
							<div class='postbox-container' style='width:49%;'>
                            <div id='side-sortables' class='meta-box-sortables'>
                            <div id="dashboard_quick_press" class="postbox " >
                               <h3 class='hndle'><span><?php _e('Recent Wiki Pages', $communities_text_domain); ?></span> (<small><a href="?page=communities&action=wiki&cid=<?php echo intval($_GET['cid']); ?>"><?php _e('See All', $communities_text_domain); ?></a></small>)</h3>
                                <div class="inside">
								<?php
                                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = %d ORDER BY page_ID DESC LIMIT 9", $_GET['cid']);
                                $pages = $wpdb->get_results( $query, ARRAY_A );
                                if ( count( $pages ) > 0 ) {
                                    echo "<ul>";
                                    foreach ( $pages as $page ) {
                                        ?>
                                        <li><strong><a href="?page=communities&action=page&pid=<?php echo $page['page_ID']; ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($page['page_title']) ); ?></a></strong></li>
                                        <?php
                                    }
                                    echo "</ul>";
                                } else {
                                    ?>
                                    <p><center><?php _e('No pages to display.', $communities_text_domain); ?></center></p>
                                    <?php
                                }
                                ?>
                                </div>
                            </div>

                            <div id="dashboard_quick_press" class="postbox " >
                               <h3 class='hndle'><span><?php _e('Recent News', $communities_text_domain); ?></span> (<small><a style="text-decoration:none;" href="?page=communities&action=news&cid=<?php echo intval($_GET['cid']); ?>"><?php _e('See All', $communities_text_domain); ?></a></small>)</h3>
                                <div class="inside">
								<?php
                                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = %d ORDER BY news_item_ID DESC LIMIT 9", $_GET['cid']);
                                $news_items = $wpdb->get_results( $query, ARRAY_A );
                                if ( count( $news_items ) > 0 ) {
                                    echo "<ul>";
                                    foreach ( $news_items as $news_item ) {
                                        ?>
                                        <li><strong><a href="?page=communities&action=news_item&niid=<?php echo $news_item['news_item_ID']; ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes(sanitize_text_field( $news_item['news_item_title'] )); ?></a></strong></li>
                                        <?php
                                    }
                                    echo "</ul>";
                                } else {
                                    ?>
                                    <p><center><?php _e('No news to display.', $communities_text_domain); ?></center></p>
                                    <?php
                                }
                                ?>
                                </div>
                            </div>
                            </div>
                            </div>
                        </div>



                        <div id='post-body' class="has-sidebar">
                        <div id='dashboard-widgets-main-content' class='has-sidebar-content'>

                            <!-- <div id='normal-sortables' class='meta-box-sortables'> -->
                            <div class='postbox-container' style='width:49%;'>
                            <div id='side-sortables' class='meta-box-sortables'>
                            <div id="dashboard_right_now" class="postbox " >
                               <h3 class='hndle'><span><?php _e('Recent Message Board Topics', $communities_text_domain); ?></span> (<small><a style="text-decoration:none;" href="?page=communities&action=message_board&cid=<?php echo intval($_GET['cid']); ?>"><?php _e('See All', $communities_text_domain); ?></a></small>)</h3>
                                <div class="inside">
								<?php
                                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_community_ID = %d AND topic_closed = %d ORDER BY topic_ID DESC LIMIT 9", $_GET['cid'], '0');
                                $topics = $wpdb->get_results( $query, ARRAY_A );
                                if ( count( $topics ) > 0 ) {
                                    echo "<ul>";
                                    foreach ( $topics as $topic ) {
                                        ?>
                                        <li><strong><a href="?page=communities&action=topic&tid=<?php echo $topic['topic_ID']; ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($topic['topic_title']) ); ?></a></strong></li>
                                        <?php
                                    }
                                    echo "</ul>";
                                } else {
                                    ?>
                                    <p><center><?php _e('No topics to display.', $communities_text_domain); ?></center></p>
                                    <?php
                                }
                                ?>

                                </div>
                            </div>

                            <div id="dashboard_right_now" class="postbox " >
                               <h3 class='hndle'><span><?php _e('Recent Members', $communities_text_domain); ?></span> (<small><a style="text-decoration:none;" href="?page=communities&action=member_list&cid=<?php echo intval($_GET['cid']); ?>"><?php _e('See All', $communities_text_domain); ?></a></small>)</h3>
                                <div class="inside">
								<?php
                                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID != %d ORDER BY member_ID DESC LIMIT 9", $_GET['cid'], $user_ID);
                                $members = $wpdb->get_results( $query, ARRAY_A );
                                if ( count( $members ) > 0 ) {
                                    echo "<ul>";
                                    foreach ( $members as $member ) {
                                        $member_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $member['member_user_ID']));
                                        $member_primary_blog = get_active_blog_for_user( $member['member_user_ID'] );
                                        ?>
                                        <li><strong><?php echo $member_details->display_name; ?></strong> (
										<?php if (is_plugin_active('messaging/messaging.php')) {
											?><a style="text-decoration:none;" href="admin.php?page=messaging_new&message_to=<?php echo $member_details->user_login; ?>" style="text-decoration:none;"><?php _e('Send Message', $communities_text_domain); ?></a> | <?php } ?><a href="http://<?php echo $member_primary_blog->domain . $member_primary_blog->path; ?>" style="text-decoration:none;"><?php _e('View Blog', $communities_text_domain); ?></a>)</li>
                                        <?php
                                    }
                                    echo "</ul>";
                                } else {
                                    ?>
                                    <p><center><?php _e('No recent members.', $communities_text_domain); ?></center></p>
                                    <?php
                                }
                                ?>
                                </div>
                            </div>
                            </div>
                            </div>
                        </div>
                        </div>
					</div>
                <div class="clear"></div>
                </div>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "news":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=news&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('News', $communities_text_domain) ?></a></h2>
				<?php
                if( isset( $_GET[ 'start' ] ) == false ) {
                    $start = 0;
                } else {
                    $start = intval( $_GET[ 'start' ] );
                }
                if( isset( $_GET[ 'num' ] ) == false ) {
                    $num = 30;
                } else {
                    $num = intval( $_GET[ 'num' ] );
                }
                $query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = %d", $_GET['cid']);
                $query .= " ORDER BY news_item_stamp DESC";
                $query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
                $news_items = $wpdb->get_results( $query, ARRAY_A );
                if( count( $news_items ) < $num ) {
                    $next = false;
                } else {
                    $next = true;
                }
                if (count( $news_items ) > 0){
                    $news_item_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = %d", $_GET['cid']));
                    if ($news_item_count > 30){
                        ?>
                        <table><td>
                        <fieldset>
                        <?php

                        //$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

                        if( $start == 0 ) {
                            echo __('Previous Page', $communities_text_domain);
                        } elseif( $start <= 30 ) {
                            echo '<a href="?page=manage-communities&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
                        } else {
                            echo '<a href="?page=manage-communities&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
                        }
                        if ( $next ) {
                            echo '&nbsp;||&nbsp;<a href="page=manage-communities&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page', $communities_text_domain) . '</a>';
                        } else {
                            echo '&nbsp;||&nbsp;' . __('Next Page', $communities_text_domain);
                        }
                        ?>
                        </fieldset>
                        </td></table>
                        <?php
                    }
                    echo "
                    <br />
                    <table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
                    <thead><tr>
                    <th scope='col'>" . __('Title', $communities_text_domain) . "</th>
                    <th scope='col'>" . __('Date/Time', $communities_text_domain) . "</th>
                    </tr></thead>
                    <tbody id='the-list'>
                    ";
                    //=========================================================//
                        $class = '';
                        $date_format = get_option('date_format');
                        $time_format = get_option('time_format');
                        foreach ($news_items as $news_item){
                        //=========================================================//
                        echo "<tr class='" . $class . "'>";
                        echo "<td valign='top'><a href='?page=communities&action=news_item&niid=" . $news_item['news_item_ID'] . "&cid=" . intval($_GET['cid']) . "' style='text-decoration:none;'><strong>" . stripslashes( sanitize_text_field($news_item['news_item_title']) ) . "</strong></a></td>";
                        echo "<td valign='top'>" . date_i18n( $date_format . ' ' . $time_format, $news_item['news_item_stamp']) . "</td>";
                        echo "</tr>";
                        $class = ('alternate' == $class) ? '' : 'alternate';
                        //=========================================================//
                        }
                    //=========================================================//
                    ?>
                    </tbody></table>
                    <?php
                } else {
                    ?>
                    <p><?php _e('There currently aren\'t any news items. Please check back later.', $communities_text_domain) ?></p>
                    <?php
                }
			}
		break;
		//---------------------------------------------------//
		case "news_item":
			$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $_GET['cid'], $user_ID));
			if ( $member_count > 0 || is_super_admin() ) {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				$news_item_title = $wpdb->get_var($wpdb->prepare("SELECT news_item_title FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = %d", $_GET['niid']));
				$news_item_content = $wpdb->get_var($wpdb->prepare("SELECT news_item_content FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = %d", $_GET['niid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=communities&action=news&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('News', $communities_text_domain) ?></a> &raquo; <a href="?page=communities&action=news_item&niid=<?php echo intval($_GET['niid']); ?>&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($news_item_title) ); ?></a></h2>
                <br />
                <p><?php echo $news_item_content; ?></p>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "send_message":

		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function communities_add_output() {
	global $wpdb, $user_ID, $communities_text_domain;

	?><div class="wrap"><?php

	if (!isset($_POST['community_name']))
		$_POST['community_name'] 			= '';
	else
		$_POST['community_name']	= stripslashes(sanitize_text_field($_POST['community_name']));

	if (!isset($_POST['community_description']))
		$_POST['community_description'] 			= '';
	else
		$_POST['community_description']	= stripslashes(sanitize_text_field($_POST['community_description']));

	if (!isset($_POST['community_private']))
		$_POST['community_private'] 			= '';
	else
		$_POST['community_private']	= intval($_POST['community_private']);

	if (!isset($_GET[ 'action' ])) $_GET[ 'action' ] = '';
	else $_GET[ 'action' ]	= sanitize_text_field($_GET[ 'action' ]);
	
	switch($_GET['action']) {
/*
		case 'edit_community':
			?>
			<h2><?php _e('Edit Community', $communities_text_domain) ?></h2>
			<p><?php _e('Please fill in all fields.', $communities_text_domain) ?></p>
			<?php
			break;
*/		
		case 'create_community':
		default:
			?>
			<h2><?php _e('Create Community', $communities_text_domain) ?></h2>
			<p><?php _e('Please fill in all fields.', $communities_text_domain) ?></p>
			<?php
			
			//$owner_community_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '%d'", $user_ID));
			/*if (( isset($owner_community_count) ) && ( $owner_community_count > 44 )) {
				?><p><?php _e('Sorry, you can only create a maximum of 45 communities.', $communities_text_domain) ?></p><?php
			} else {
			*/
				//echo "_POST<pre>"; print_r($_POST); echo "</pre>";
				if ( (!empty( $_POST['community_name'] )) && (!empty( $_POST['community_description'] )) ) {
					$community_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_name = %s",  $_POST['community_name'] ));
					if ($community_count > 0) {
						?><div id="message" class="error fade"><p><?php _e('Sorry, a community with that name already exists.', $communities_text_domain) ?></p></div><?php
					} else {
						communities_create_community($user_ID, stripslashes(sanitize_text_field($_POST['community_name'])), stripslashes(sanitize_text_field($_POST['community_description'])), intval($_POST['community_private']));
						?><div id="message" class="updated fade"><p><?php _e('Community created.', $communities_text_domain) ?></p></div><?php
						$_POST['community_name'] 			= '';
						$_POST['community_description'] 	= '';
						$_POST['community_private'] 		= '';
					}
				} else {
					?><div id="message" class="error fade"><p><?php _e('Community Name and Description are required.', $communities_text_domain) ?></p></div><?php
				}
			//} 

			break;
	}
	?>
		<form name="create_community" method="POST" action="?page=add-communities&amp;action=create_community">
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Name', $communities_text_domain) ?></th>
				<td><input type="text" name="community_name" id="community_name" style="width: 95%" value="<?php echo stripslashes(sanitize_text_field($_POST['community_name'])); ?>" />
				<br />
				<?php _e('Required', $communities_text_domain) ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Description', $communities_text_domain) ?></th>
				<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" 
					value="<?php echo stripslashes(sanitize_text_field($_POST['community_description'])); ?>" />
				<br />
				<?php _e('Required', $communities_text_domain) ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Private', $communities_text_domain) ?></th>
				<td><select name="community_private">
					<option value="0" <?php if ($_POST['community_private'] == '0' || $_POST['community_private'] == '') echo 'community_private"'; ?>><?php _e('No', $communities_text_domain); ?></option>
					<option value="1" <?php if ($_POST['community_private'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $communities_text_domain); ?></option>
				</select>
				<?php _e('Users have to enter a code to join private communities', $communities_text_domain) ?></td>
			</tr>
			</table>
			<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Create', $communities_text_domain) ?>" />
			</p>
		</form>
	</div>
	<?php
}

function communities_manage_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site, $communities_text_domain;
	
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field( $_GET['updatedmsg'] ) ) ?></p></div><?php
	}
	
	if (!isset($_GET['start'])) $_GET['start'] = 0;
	else $_GET['start'] = intval($_GET['start']);
	
	if (!isset($_GET['num'])) $_GET['num'] = 10;
	else $_GET['num'] = intval($_GET['num']);
	
	if (!isset($_GET['order'])) $_GET['order'] = "ASC";
	else $_GET['order'] = sanitize_text_field($_GET['order']);
	if (($_GET['order'] !== "ASC") && ($_GET['order'] !== "DESC")) $_GET['order'] = "ASC";
	
	if (!isset($_GET['orderby'])) $_GET['orderby'] = "community_name";
	else $_GET['orderby'] = sanitize_text_field($_GET['orderby']);
	
	echo '<div class="wrap">';
	if (!isset($_GET[ 'action' ])) $_GET[ 'action' ] = '';
	else $_GET[ 'action' ] = sanitize_text_field($_GET[ 'action' ]);
	
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		case '':
		default:
			if ( is_super_admin() ) {
				$community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities");
			} else {
				$community_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = %d", $user_ID));
			}
			//echo "community_count=[". $community_count ."]<br />";
			?>
			<h2><?php _e('Communities', $communities_text_domain) ?></h2>
			<?php
			if( isset( $_GET[ 'start' ] ) == false ) {
				$start = 0;
			} else {
				$start = intval( $_GET[ 'start' ] );
			}
			if( isset( $_GET[ 'num' ] ) == false ) {
				$num = 30;
			} else {
				$num = intval( $_GET[ 'num' ] );
			}
			if ( is_super_admin() ) {
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities ORDER BY %s %s LIMIT %d, %d", $_GET['orderby'], $_GET['order'], $_get['start'], $_GET['num']);
			} else {
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = %d ORDER BY %s %s LIMIT %d, %d", $user_ID, $_GET['orderby'], $_GET['order'], $_get['start'], $_GET['num']);
			}
			//echo "query<pre>"; print_r($query); echo "</pre>";
			$communities = $wpdb->get_results( $query, ARRAY_A );
			if( count( $communities ) < $num ) {
				$next = false;
			} else {
				$next = true;
			}
			if (count($communities) > 0) {
				//$community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "'");
				//echo "community_count=[". $community_count ."] num=[". $num ."] start=[". $start ."]<br />";
				//if ($community_count > ($_GET['num']+$_GET['start'])){
					?>
					<table><td>
					<fieldset>
					<?php

					$order_sort = "order=" . sanitize_text_field($_GET[ 'order' ]) . "&orderby=" . sanitize_text_field($_GET[ 'orderby' ]);

					if( $start == 0 ) {
						echo __('Previous Page', $communities_text_domain);
					} else if( $start > 0 ) {
						$start_prev = intval($start) - intval($num);
						echo '<a href="?page=manage-communities&start='. $start_prev .'&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
					} else {
						echo '<a href="?page=manage-communities&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
					}
					if ( $next ) {
						echo '&nbsp;|&nbsp;<a href="?page=manage-communities&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page', $communities_text_domain) . '</a>';
					} else {
						echo '&nbsp;|&nbsp;' . __('Next Page', $communities_text_domain);
					}
					?>
					</fieldset>
					</td></table>
					<?php
				//}
				echo "
				<br />
				<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
				<thead><tr>
				<th scope='col'>" . __('Name', $communities_text_domain) . "</th>
				<th scope='col'>" . __('Description', $communities_text_domain) . "</th>
				<th scope='col'>" . __('Private', $communities_text_domain) . "*</th>
				<th scope='col'>" . __('Actions', $communities_text_domain) . "</th>
				<th scope='col'></th>
				<th scope='col'></th>
				<th scope='col'></th>
				<th scope='col'></th>
				<th scope='col'></th>
				<th scope='col'></th>
				</tr></thead>
				<tbody id='the-list'>
				";
				//=========================================================//
					
					$class = '';
					foreach ($communities as $community){
					//=========================================================//
					echo "<tr class='" . $class . "'>";
					echo "<td valign='top'><a href='?page=communities&action=dashboard&cid=" . $community['community_ID'] . "' style='text-decoration:none;'><strong>" . stripslashes( sanitize_text_field($community['community_name']) ) . "</strong></a></td>";
					echo "<td valign='top'>" . stripslashes( sanitize_text_field($community['community_description']) ) . "</td>";
					if ( $community['community_private'] == '1' ) {
						$community_private = __('Yes', $communities_text_domain) . ' (' . __('Code', $communities_text_domain) . ': ' . substr(md5($community['community_ID'] . '1234'),0,5) . ')';
					} else {
						$community_private = __('No', $communities_text_domain);
					}
					echo "<td valign='top'>" . $community_private . "</td>";
					$community_members_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d", $community['community_ID']));
					$community_members_count = $community_members_count - 1;
					if ( $community_members_count < 0 ) {
						$community_members_count = 0;
					}
					if ( $community_members_count > 0 ) {
						echo "<td valign='top'><a href='?page=manage-communities&action=member_list&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Members', $communities_text_domain) . " (" . $community_members_count . ")</a></td>";
					} else {
						echo "<td valign='top'>" . __('Members', $communities_text_domain) . " (" . $community_members_count . ")</td>";
					}
					echo "<td valign='top'><a href='?page=communities&action=message_board&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Message Board', $communities_text_domain) . "</a></td>";
					echo "<td valign='top'><a href='?page=communities&action=wiki&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Wiki', $communities_text_domain) . "</a></td>";
					echo "<td valign='top'><a href='?page=communities&action=news&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('News', $communities_text_domain) . "</a></td>";
					echo "<td valign='top'><a href='?page=manage-communities&action=manage_news&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Manage News', $communities_text_domain) . "</a></td>";
					echo "<td valign='top'><a href='?page=manage-communities&action=edit_community&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Edit', $communities_text_domain) . "</a></td>";
					echo "<td valign='top'><a href='?page=manage-communities&action=remove_community&cid=" . $community['community_ID'] . "' rel='permalink' class='delete'>" . __('Remove', $communities_text_domain) . "</a></td>";
					echo "</tr>";
					$class = ('alternate' == $class) ? '' : 'alternate';
					//=========================================================//
					}
				//=========================================================//
				?>
				</tbody></table>
                <p>*<?php _e('Users must enter the code to join private communities.', $communities_text_domain); ?></p>
				<?php
			}

			$owner_community_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = %d", $user_ID));
			?>
            <br />
<?php /* ?>
			<h2><?php _e('ZZZ Create Community', $communities_text_domain) ?></h2>
			<?php
			if ( $owner_community_count > 44 ) {
				?>
				<p><?php _e('Sorry, you can only create a maximum of 45 communities.', $communities_text_domain) ?></p>
				<?php
			} else {
				?>
				<p><?php _e('You can create up to 45 communities of your own using the form below.', $communities_text_domain) ?></p>
				<form name="create_community" method="POST" action="?page=communities&action=create_community">
					<table class="form-table">
					<tr valign="top">
					<th scope="row"><?php _e('Name', $communities_text_domain) ?></th>
					<td><input type="text" name="community_name" id="community_name" style="width: 95%" value="<?php echo (isset($_POST['community_name'])) ? $_POST['community_name'] : ''; ?>" />
					<br />
					<?php _e('Required', $communities_text_domain) ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Description', $communities_text_domain) ?></th>
					<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo (isset($_POST['community_description'])) ? $_POST['community_description'] : ''; ?>" />
					<br />
					<?php _e('Required', $communities_text_domain) ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Private', $communities_text_domain) ?></th>
					<td><select name="community_private">
					<?php if (!isset($_POST['community_private'])) $_POST['community_private'] = ''; ?> 
						<option value="0" <?php if ($_POST['community_private'] == '0' || $_POST['community_private'] == '') echo 'selected="selected"'; ?>><?php _e('No', $communities_text_domain); ?></option>
						<option value="1" <?php if ($_POST['community_private'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $communities_text_domain); ?></option>
					</select>
					<?php _e('Users have to enter a code to join private communities', $communities_text_domain) ?></td>
					</tr>
					</table>
				<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Create', $communities_text_domain) ?>" />
				</p>
				</form>
				<?php
			}
*/ 
		break;
		//---------------------------------------------------//
		case "edit_community":
			$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			$community_description = $wpdb->get_var($wpdb->prepare("SELECT community_description FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			$community_private = $wpdb->get_var($wpdb->prepare("SELECT community_private FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			?>
			<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Edit Community', $communities_text_domain) ?></h2>
			<form name="edit_community" method="POST" action="?page=manage-communities&action=edit_community_process">
	            <input type="hidden" name="cid" value="<?php echo intval($_GET['cid']); ?>" />
				<table class="form-table">
				<tr valign="top">
				<th scope="row"><?php _e('Description', $communities_text_domain) ?></th>
				<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo stripslashes( sanitize_text_field($community_description) ); ?>" />
				<br />
				<?php _e('Required', $communities_text_domain) ?></td>
				</tr>
				<tr valign="top">
				<th scope="row"><?php _e('Private', $communities_text_domain) ?></th>
				<td><select name="community_private">
					<option value="0" <?php if ($community_private == '0' || $community_private == '') echo 'selected="selected"'; ?>><?php _e('No', $communities_text_domain); ?></option>
					<option value="1" <?php if ($community_private == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $communities_text_domain); ?></option>
				</select>
				<?php _e('Users have to enter a code to join private communities', $communities_text_domain) ?></td>
				</tr>
				</table>
			<p class="submit">
			<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
			<input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
			</p>
			</form>
			<?php
		break;
		//---------------------------------------------------//
		case "edit_community_process":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "
				<script type='text/javascript'>
				window.location='?page=manage-communities';
				</script>
				";
			} else {
				//echo "_REQUEST<pre>"; print_r($_REQUEST); echo "</pre>";
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_REQUEST['cid']));
				//die();
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Edit Community', $communities_text_domain) ?></h2>
				<?php
				if ( empty( $_POST['community_description'] ) ) {
					?>
					<p><?php _e('Please fill in all fields.', $communities_text_domain) ?></p>
					<form name="edit_community" method="POST" action="?page=manage-communities&action=edit_community_process">
                    	<input type="hidden" name="cid" value="<?php echo intval($_POST['cid']); ?>" />
						<table class="form-table">
						<tr valign="top">
						<th scope="row"><?php _e('Description', $communities_text_domain) ?></th>
						<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo stripslashes(sanitize_text_field($_POST['community_description'])); ?>" />
						<br />
						<?php _e('Required', $communities_text_domain) ?></td>
						</tr>
						<tr valign="top">
						<th scope="row"><?php _e('Private', $communities_text_domain) ?></th>
						<td><select name="community_private">
							<option value="0" <?php if ($_POST['community_private'] == '0' || $_POST['community_private'] == '') echo 'community_private"'; ?>><?php _e('No', $communities_text_domain); ?></option>
							<option value="1" <?php if ($_POST['community_private'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $communities_text_domain); ?></option>
						</select>
						<?php _e('Users have to enter a code to join private communities', $communities_text_domain) ?></td>
						</tr>
						</table>
					<p class="submit">
					<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
					<input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
					</p>
					</form>
					<?php
				} else {
					communities_update_community($user_ID, intval($_POST['cid']), stripslashes(sanitize_text_field($_POST['community_description'])), intval($_POST['community_private']));
					echo "
					<script type='text/javascript'>
					window.location='?page=manage-communities&updated=true&updatedmsg=" . urlencode(__('Changes saved.', $communities_text_domain)) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_community":
			$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			?>
			<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Remove', $communities_text_domain) ?></h2>
            <form name="edit_community" method="POST" action="?page=manage-communities&action=remove_community_process">
                <input type="hidden" name="cid" value="<?php echo intval($_GET['cid']); ?>" />
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Are you sure?', $communities_text_domain) ?></th>
                <td><select name="remove_community">
                    <option value="no" selected="selected" ><?php _e('No', $communities_text_domain); ?></option>
                    <option value="yes" ><?php _e('Yes', $communities_text_domain); ?></option>
                </select>
                </td>
                </tr>
                </table>
            <p class="submit">
            <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
            <input type="submit" name="Submit" value="<?php _e('Continue', $communities_text_domain) ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
		case "remove_community_process":
			if ( isset( $_POST['Cancel'] ) || $_POST['remove_community'] == 'no' ) {
				echo "
				<script type='text/javascript'>
				window.location='?page=manage-communities';
				</script>
				";
			} else {
				$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_POST['cid']));
				if ( $community_owner_user_ID == $user_ID || is_super_admin() ) {
					communities_remove_community(intval($_POST['cid']));
					$owner_community_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = %d", $user_ID));
					if ( $owner_community_count > 0 || is_super_admin() ) {
						echo "
						<script type='text/javascript'>
						window.location='?page=manage-communities&updated=true&updatedmsg=" . urlencode(__('Community removed.', $communities_text_domain)) . "';
						</script>
						";
					} else {
						echo "
						<script type='text/javascript'>
						window.location='?page=communities&updated=true&updatedmsg=" . urlencode(__('Community removed.', $communities_text_domain)) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------// 
		case "member_list":
			$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			?>
			<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <a href="?page=manage-communities&action=member_list&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php _e('Members', $communities_text_domain) ?></a></h2>
            <?php
			if( isset( $_GET[ 'start' ] ) == false ) {
				$start = 0;
			} else {
				$start = intval( $_GET[ 'start' ] );
			}
			if( isset( $_GET[ 'num' ] ) == false ) {
				$num = 30;
			} else {
				$num = intval( $_GET[ 'num' ] );
			}
			$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_members WHERE member_user_ID != '%d' AND community_ID = %d", $user_ID, $_GET['cid'] );
			$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
			$members = $wpdb->get_results( $query, ARRAY_A );
			if( count( $members ) < $num ) {
				$next = false;
			} else {
				$next = true;
			}
			if (count($members) > 0){
				$members_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE member_user_ID != %d AND community_ID = %d", $user_ID, $_GET['cid']));
				if ($members_count > 30){
					?>
					<br />
					<table><td>
					<fieldset>
					<?php

					//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

					if( $start == 0 ) {
						echo __('Previous Page', $communities_text_domain);
					} elseif( $start <= 30 ) {
						echo '<a href="?page=manage-communities&action=member_list&cid=' . intval($_GET['cid']) . '&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
					} else {
						echo '<a href="?page=manage-communities&action=member_list&cid=' . intval($_GET['cid']) . '&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
					}
					if ( $next ) {
						echo '&nbsp;||&nbsp;<a href="?page=manage-communities&action=member_list&cid=' . intval($_GET['cid']) . '&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page', $communities_text_domain) . '</a>';
					} else {
						echo '&nbsp;||&nbsp;' . __('Next Page', $communities_text_domain);
					}
					?>
					</fieldset>
					</td></table>
					<?php
				}
				?>
				<br />
				<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
				<thead><tr>
				<th scope='col'><?php _e('Name', $communities_text_domain); ?></th>
				<th scope='col'><?php _e('Avatar', $communities_text_domain); ?></th>
				<th scope='col'><?php _e('Type', $communities_text_domain); ?></th>
				<th scope='col'><?php _e('Actions', $communities_text_domain); ?></th>

				<?php if (is_plugin_active('messaging/messaging.php')) { ?>
					<th scope='col'></th>
				<?php } ?>
				<th scope='col'></th>
				</tr></thead>
				<tbody id='the-list'>
				<?php
				//=========================================================//
					$class = '';
					foreach ($members as $member){
					//=========================================================//
					echo "<tr class='" . $class . "'>";
					$member_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $member['member_user_ID']));
					echo "<td valign='top'><strong>" . $member_details->display_name . "</strong></td>";
					echo "<td valign='top'>";
					
					if (is_plugin_active('avatars/avatars.php')) {					
						echo "<img src='http://" . $current_site->domain . $current_site->path . "avatar/user-" . $member['member_user_ID'] . "-32.png' />";
					} else {
						echo get_avatar('dummy@dummy.com', 32);
					}	
					
					echo "</td>";
						$member_type = __('Member', $communities_text_domain);
					if ( $member['member_moderator'] == '1' ) {
						$member_type = __('Moderator', $communities_text_domain);
					}
					echo "<td valign='top'>" . $member_type . "</td>";
					$member_primary_blog = get_active_blog_for_user( $member['member_user_ID'] );
					echo "<td valign='top'><a href='http://" . $member_primary_blog->domain . $member_primary_blog->path . "' rel='permalink' class='edit'>" . __('Visit Blog', $communities_text_domain) . "</a></td>";

					if (is_plugin_active('messaging/messaging.php')) {
						echo "<td valign='top'><a href='admin.php?page=messaging_new&message_to=" . $member_details->user_login . "' rel='permalink' class='edit'>" . __('Send Message', $communities_text_domain) . "</a></td>";
					}

					if ( $member['member_moderator'] == '1' ) {
						echo "<td valign='top'><a href='?page=manage-communities&action=remove_moderator&uid=" . $member['member_user_ID'] . "&cid=" . intval($_GET['cid']) . "&num=" . intval($_GET['num']) . "&start=" . intval($_GET['start']) . "' rel='permalink' class='delete'>" . __('Remove Moderator Privelege', $communities_text_domain) . "</a></td>";
					} else {
						echo "<td valign='top'><a href='?page=manage-communities&action=add_moderator&uid=" . $member['member_user_ID'] . "&cid=" . intval($_GET['cid']) . "&num=" . intval($_GET['num']) . "&start=" . intval($_GET['start']) . "' rel='permalink' class='edit'>" . __('Add Moderator Privelege', $communities_text_domain) . "</a></td>";
					}
					echo "</tr>";
					$class = ('alternate' == $class) ? '' : 'alternate';
					//=========================================================//
					}
				//=========================================================//
				?>
				</tbody></table>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "add_moderator":
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			communities_add_moderator_privilege(intval($_GET['uid']), intval($_GET['cid']));
			if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
				echo "
				<script type='text/javascript'>
				window.location='?page=manage-communities&action=member_list&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Moderator privelege added.', $communities_text_domain)) . "';
				</script>
				";
			} else {
				echo "
				<script type='text/javascript'>
				window.location='?page=manage-communities&action=member_list&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Moderator privelege added.', $communities_text_domain)) . "';
				</script>
				";
			}
		break;
		//---------------------------------------------------//
		case "remove_moderator":
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			communities_remove_moderator_privilege(intval($_GET['uid']), intval($_GET['cid']));
			if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
				echo "
				<script type='text/javascript'>
				window.location='?page=manage-communities&action=member_list&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Moderator privelege removed.', $communities_text_domain)) . "';
				</script>
				";
			} else {
				echo "
				<script type='text/javascript'>
				window.location='?page=manage-communities&action=member_list&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Moderator privelege removed.', $communities_text_domain)) . "';
				</script>
				";
			}
		break;
		//---------------------------------------------------//
		case "manage_news":
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			?>
			<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Manage News', $communities_text_domain) ?></h2>
			<?php
			if( isset( $_GET[ 'start' ] ) == false ) {
				$start = 0;
			} else {
				$start = intval( $_GET[ 'start' ] );
			}
			if( isset( $_GET[ 'num' ] ) == false ) {
				$num = 30;
			} else {
				$num = intval( $_GET[ 'num' ] );
			}
				$query = $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = %d", $_GET['cid']);
			$query .= " ORDER BY news_item_stamp DESC";
			$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
			$news_items = $wpdb->get_results( $query, ARRAY_A );
			if( count( $news_items ) < $num ) {
				$next = false;
			} else {
				$next = true;
			}
			if (count( $news_items ) > 0){
				$news_item_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = %d", $_GET['cid']));
				if ($news_item_count > 30){
					?>
					<table><td>
					<fieldset>
					<?php

					//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

					if( $start == 0 ) {
						echo __('Previous Page', $communities_text_domain);
					} elseif( $start <= 30 ) {
						echo '<a href="?page=manage-communities&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
					} else {
						echo '<a href="?page=manage-communities&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page', $communities_text_domain) . '</a>';
					}
					if ( $next ) {
						echo '&nbsp;||&nbsp;<a href="?page=manage-communities&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page', $communities_text_domain) . '</a>';
					} else {
						echo '&nbsp;||&nbsp;' . __('Next Page', $communities_text_domain);
					}
					?>
					</fieldset>
					</td></table>
					<?php
				}
				echo "
				<br />
				<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
				<thead><tr>
				<th scope='col'>" . __('Title', $communities_text_domain) . "</th>
				<th scope='col'>" . __('Date/Time', $communities_text_domain) . "</th>
				<th scope='col'>" . __('Actions', $communities_text_domain) . "</th>
				<th scope='col'></th>
				</tr></thead>
				<tbody id='the-list'>
				";
				//=========================================================//
					$class = '';
					$date_format = get_option('date_format');
					$time_format = get_option('time_format');
					
					if (!isset($_GET['start'])) $_GET['start'] = 0;
					else $_GET['start'] - intval($_GET['start']);
					
					if (!isset($_GET['num'])) $_GET['num'] = 0;
					else $_GET['num'] = intval($_GET['num']);
					
					foreach ($news_items as $news_item){
					//=========================================================//
					echo "<tr class='" . $class . "'>";
					echo "<td valign='top'><strong>" . stripslashes( sanitize_text_field($news_item['news_item_title']) ) . "</strong></td>";
					echo "<td valign='top'>" . date_i18n( $date_format . ' ' . $time_format, $news_item['news_item_stamp']) . "</td>";
					echo "<td valign='top'><a href='?page=manage-communities&action=edit_news_item&niid=" . $news_item['news_item_ID'] . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "' rel='permalink' class='edit'>" . __('Edit', $communities_text_domain) . "</a></td>";
					echo "<td valign='top'><a href='?page=manage-communities&action=remove_news_item&niid=" . $news_item['news_item_ID'] . "&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "' rel='permalink' class='delete'>" . __('Remove', $communities_text_domain) . "</a></td>";
					echo "</tr>";
					$class = ('alternate' == $class) ? '' : 'alternate';
					//=========================================================//
					}
				//=========================================================//
				?>
				</tbody></table>
				<?php
			} else {
				?>
	            <p><?php _e('There currently aren\'t any news items for this community. Use the form below to add news!', $communities_text_domain) ?></p>
                <?php
			}

			?>
            <br />
			<h2><?php _e('New News Item', $communities_text_domain) ?></h2>
            <form name="new_news_item" method="POST" action="?page=manage-communities&action=new_news_item&cid=<?php echo intval($_GET['cid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
                <td><input type="text" name="news_item_title" id="news_item_title" style="width: 95%" value="<?php echo (isset($_POST['news_item_title'])) ? stripslashes(sanitize_text_field($_POST['news_item_title'])) : ''; ?>" />
                <br />
                <?php _e('Required', $communities_text_domain) ?></td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
                <td><textarea name="news_item_content" id="news_item_content" style="width: 95%" rows="10"><?php echo (isset($_POST['news_item_content'])) ? stripslashes(wp_kses($_POST['news_item_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)) : ''; ?></textarea>
                <br />
                <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
                </tr>
                </table>
            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Publish', $communities_text_domain) ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
		case "new_news_item":
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			if ( isset( $_POST['Cancel'] ) ) {
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "';
					</script>
					";
				}
			} else {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('New News Item', $communities_text_domain) ?></h2>
				<?php
				if ( empty( $_POST['news_item_title'] ) || empty( $_POST['news_item_content'] ) ) {
					?>
					<p><?php _e('Please fill in all fields.', $communities_text_domain); ?></p>
                    <form name="new_news_item" method="POST" action="?page=manage-communities&action=new_news_item&cid=<?php echo intval($_GET['cid']); ?>start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                        <table class="form-table">
                        <tr valign="top">
                        <th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
                        <td><input type="text" name="news_item_title" id="news_item_title" style="width: 95%" value="<?php echo stripslashes(sanitize_text_field($_POST['news_item_title'])); ?>" />
                        <br />
                        <?php _e('Required', $communities_text_domain) ?></td>
                        </tr>
                        <tr valign="top">
                        <th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
                        <td><textarea name="news_item_content" id="news_item_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses($_POST['news_item_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)); ?></textarea>
                        <br />
                        <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
                        </tr>
                        </table>
                    <p class="submit">
                    <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                    <input type="submit" name="Submit" value="<?php _e('Publish', $communities_text_domain) ?>" />
                    </p>
                    </form>
					<?php

				} else {
					$news_item_ID = communities_add_news_item(intval($_GET['cid']), stripslashes(sanitize_text_field($_POST['news_item_title'])), stripslashes(wp_kses($_POST['news_item_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)));
					echo "
					<script type='text/javascript'>
					window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('News item published.', $communities_text_domain)) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "edit_news_item":
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			$news_item_title = $wpdb->get_var($wpdb->prepare("SELECT news_item_title FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = %d", $_GET['niid']));
			$news_item_content = $wpdb->get_var($wpdb->prepare("SELECT news_item_content FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = %d", $_GET['niid']));
			?>
			<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Edit News Item', $communities_text_domain) ?></h2>
			<form name="edit_news_item" method="POST" action="?page=manage-communities&action=edit_news_item_process&cid=<?php echo intval($_GET['cid']); ?>&niid=<?php echo intval($_GET['niid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
				<table class="form-table">
				<tr valign="top">
				<th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
				<td><input type="text" name="news_item_title" id="news_item_title" style="width: 95%" value="<?php echo stripslashes( sanitize_text_field($news_item_title) ); ?>" />
				<br />
				<?php _e('Required', $communities_text_domain) ?></td>
				</tr>
				<tr valign="top">
				<th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
				<td><textarea name="news_item_content" id="news_item_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses( $news_item_content, $COMMUNITIES_ALLOWED_CONTENT_TAGS )); ?></textarea>
				<br />
				<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
				</tr>
				</table>
			<p class="submit">
			<input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
			<input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
			</p>
			</form>
			<?php
		break;
		//---------------------------------------------------//
		case "edit_news_item_process":
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			if ( isset( $_POST['Cancel'] ) ) {
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "';
					</script>
					";
				}
			} else {
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Edit News Item', $communities_text_domain) ?></h2>
				<?php
				if ( empty( $_POST['news_item_title'] ) || empty( $_POST['news_item_content'] ) ) {
					?>
					<p><?php _e('Please fill in all fields.', $communities_text_domain); ?></p>
                    <form name="edit_news_item" method="POST" action="?page=manage-communities&action=edit_news_item_process&cid=<?php echo intval($_GET['cid']); ?>&niid=<?php echo intval($_GET['niid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                        <table class="form-table">
                        <tr valign="top">
                        <th scope="row"><?php _e('Title', $communities_text_domain) ?></th>
                        <td><input type="text" name="news_item_title" id="news_item_title" style="width: 95%" value="<?php echo stripslashes(sanitize_text_field($_POST['news_item_title'])); ?>" />
                        <br />
                        <?php _e('Required', $communities_text_domain) ?></td>
                        </tr>
                        <tr valign="top">
                        <th scope="row"><?php _e('Content', $communities_text_domain) ?></th>
                        <td><textarea name="news_item_content" id="news_item_content" style="width: 95%" rows="10"><?php echo stripslashes(wp_kses($_POST['news_item_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)); ?></textarea>
                        <br />
                        <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>', $communities_text_domain) ?></td>
                        </tr>
                        </table>
                    <p class="submit">
                    <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                    <input type="submit" name="Submit" value="<?php _e('Save Changes', $communities_text_domain) ?>" />
                    </p>
                    </form>
					<?php

				} else {
					communities_update_news_item(intval($_GET['niid']), stripslashes(sanitize_text_field($_POST['news_item_title'])), stripslashes(wp_kses($_POST['news_item_content'], $COMMUNITIES_ALLOWED_CONTENT_TAGS)));
					if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
						echo "
						<script type='text/javascript'>
						window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.', $communities_text_domain)) . "';
						</script>
						";
					} else {
						echo "
						<script type='text/javascript'>
						window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.', $communities_text_domain)) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_news_item":
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			?>
			<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Remove News Item', $communities_text_domain) ?></h2>
            <form name="remove_news_item" method="POST" action="?page=manage-communities&action=remove_news_item_process&cid=<?php echo intval($_GET['cid']); ?>&niid=<?php echo intval($_GET['niid']); ?>&start=<?php echo intval($_GET['start']); ?>&num=<?php echo intval($_GET['num']); ?>">
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Are you sure?', $communities_text_domain) ?></th>
                <td><select name="remove_news_item">
                    <option value="no" selected="selected" ><?php _e('No', $communities_text_domain); ?></option>
                    <option value="yes" ><?php _e('Yes', $communities_text_domain); ?></option>
                </select>
                </td>
                </tr>
                </table>
            <p class="submit">
            <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
            <input type="submit" name="Submit" value="<?php _e('Continue', $communities_text_domain) ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
		case "remove_news_item_process":
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID != $user_ID && !is_super_admin() ) {
				die('Nice try');
			}
			if ( isset( $_POST['Cancel'] ) || $_POST['remove_news_item'] == 'no' ) {
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<script type='text/javascript'>
					window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "&start=" . intval($_GET['start']) . "&num=" . intval($_GET['num']) . "';
					</script>
					";
				} else {
					echo "
					<script type='text/javascript'>
					window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "';
					</script>
					";
				}
			} else {
				communities_delete_news_item(intval($_GET['niid']));
				echo "
				<script type='text/javascript'>
				window.location='?page=manage-communities&action=manage_news&cid=" . intval($_GET['cid']) . "&updated=true&updatedmsg=" . urlencode(__('News item removed.', $communities_text_domain)) . "';
				</script>
				";
			}
		break;
		//---------------------------------------------------//
		case "send_message":

		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function communities_find_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site, $communities_text_domain;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php echo stripslashes(sanitize_text_field( $_GET['updatedmsg'] )) ?></p></div><?php
	}
	echo '<div class="wrap">';
	if (!isset($_GET[ 'action' ])) $_GET[ 'action' ] = '';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			if (isset($_GET['search_terms'])) {
				$search_terms = stripslashes(sanitize_text_field(urldecode($_GET['search_terms'])));				
			}
			else if (isset($_POST['search_terms'])) {
				$search_terms = stripslashes(sanitize_text_field($_POST['search_terms']));
			} else {
				$search_terms = '';
			}

			?>
            <form id="posts-filter" action="?page=find-communities" method="post">
            <h2><?php _e('Find Communities', $communities_text_domain) ?>&nbsp;&nbsp;<em style="font-size:14px;"><?php _e("Searches community names and descriptions", $communities_text_domain) ?></em></h2>
            <p id="post-search">
                <input id="post-search-input" name="search_terms" value="<?php echo $search_terms; ?>" type="text">
                <input value="<?php _e('Search', $communities_text_domain) ?>" class="button" type="submit">
            </p>
            </form>
            <?php
			if ($search_terms != ''){
				$search_results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . 
					"communities WHERE (community_name LIKE %s OR community_description LIKE %s) ORDER BY community_name ASC LIMIT 50", "%%".$search_terms."%%", "%%".$search_terms."%%"), ARRAY_A );

				if (count($search_results) > 0){
					?>
					<br />
					<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
					<thead><tr>
					<th scope='col'><?php _e('Name', $communities_text_domain); ?></th>
					<th scope='col'><?php _e('Description', $communities_text_domain); ?></th>
					<th scope='col'><?php _e('Public', $communities_text_domain); ?></th>
					<th scope='col'><?php _e('Owner', $communities_text_domain); ?></th>
					<th scope='col'><?php _e('Actions', $communities_text_domain); ?></th>

					<?php if (is_plugin_active('messaging/messaging.php')) { ?>
						<th scope='col'></th>
					<?php } ?>
					</tr></thead>
					<tbody id='the-list'>
					<?php
					//=========================================================//
						$class = '';
						foreach ($search_results as $search_result){
						//=========================================================//
						echo "<tr class='" . $class . "'>";
						echo "<td valign='top'><strong>" . stripslashes( sanitize_text_field($search_result['community_name']) ) . "</strong></td>";
						echo "<td valign='top'>" . stripslashes( sanitize_text_field($search_result['community_description']) ) . "</td>";
						if ( $search_result['community_private'] == '1' ) {
							$community_public = __('No', $communities_text_domain);
						} else {
							$community_public = __('Yes', $communities_text_domain);
						}
						echo "<td valign='top'>" . $community_public . "</td>";
						$owner_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = %d", $search_result['community_owner_user_ID']));
						echo "<td valign='top'>" . $owner_details->display_name . "</td>";
						if ( $search_result['community_owner_user_ID'] != $user_ID ) {
							$member_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = %d AND member_user_ID = %d", $search_result['community_ID'], $user_ID));
							if ( $member_count > 0 ) {
								echo "<td valign='top'><a href='?page=communities&action=leave_community&return=find_communities&cid=" . $search_result['community_ID'] . "&search_terms=" . urlencode( $search_terms ) . "' rel='permalink' class='delete'>" . __('Leave', $communities_text_domain) . "</a></td>";
							} else {
								echo "<td valign='top'><a href='?page=find-communities&action=join_community&cid=" . $search_result['community_ID'] . "&search_terms=" . urlencode( $search_terms ) . "' rel='permalink' class='edit'>" . __('Join', $communities_text_domain) . "</a></td>";
							}

							if (is_plugin_active('messaging/messaging.php')) {
								echo "<td valign='top'><a href='admin.php?page=messaging_new&message_to=" . $owner_details->user_login . "' rel='permalink' class='edit'>" . __('Send Message to Owner', $communities_text_domain) . "</a></td>";
							}
						} else {
							echo "<td valign='top'>" . __('Join', $communities_text_domain) . "</a></td>";
							if (is_plugin_active('messaging/messaging.php')) {
								echo "<td valign='top'>" . __('Send Message to Owner', $communities_text_domain) . "</a></td>";
							}
						}
						echo "</tr>";
						$class = ('alternate' == $class) ? '' : 'alternate';
						//=========================================================//
						}
					//=========================================================//
					?>
					</tbody></table>
					<?php
				} else {
					?>
					<p><?php _e('Nothing found', $communities_text_domain) ?></p>
					<?php
				}
			}
		break;
		//---------------------------------------------------//
		case "join_community":
			if (isset($_GET['search_terms'])) {
				$search_terms = stripslashes(sanitize_text_field(urldecode($_GET['search_terms'])));				
			}
			else if (isset($_POST['search_terms'])) {
				$search_terms = stripslashes(sanitize_text_field($_POST['search_terms']));
			} else {
				$search_terms = '';
			}
		
			$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			$community_private = $wpdb->get_var($wpdb->prepare("SELECT community_private FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_GET['cid']));
			if ( $community_owner_user_ID == $user_ID ) {
				die('Nice try');
			}

			if ( $community_private != '1' ) {
				communities_join_community($user_ID, intval($_GET['cid']));
				echo "
				<script type='text/javascript'>
				window.location='?page=find-communities&xxx=123&search_terms=" . urlencode($search_terms) . "&updated=true&updatedmsg=" . urlencode(__('Successfully joined.', $communities_text_domain)) . "';
				</script>
				";
			} else {
				?>
				<h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Join', $communities_text_domain) ?></h2>
                <p><?php _e('This is a private community. Please supply the code below to join.', $communities_text_domain) ?></p>
                <form name="edit_community" method="POST" action="?page=find-communities&action=join_community_process">
                    <input type="hidden" name="cid" value="<?php echo intval($_GET['cid']); ?>" />
                    <input type="hidden" name="search_terms" value="<?php echo stripslashes(sanitize_text_field($_GET['search_terms'])); ?>" />
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Code', $communities_text_domain) ?></th>
                    <td><input type="text" name="code" id="code" style="width: 95%" maxlength="250" value="<?php echo stripslashes(sanitize_text_field($_POST['code'])); ?>" />
                    <br />
					</td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                <input type="submit" name="Submit" value="<?php _e('Join', $communities_text_domain) ?>" />
                </p>
                </form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "join_community_process":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "
				<script type='text/javascript'>
				window.location='?page=find-communities&search_terms=" . $_POST['search_terms'] . "';
				</script>
				";
			} else {
				if (isset($_GET['search_terms'])) {
					$search_terms = stripslashes(sanitize_text_field(urldecode($_GET['search_terms'])));				
				}
				else if (isset($_POST['search_terms'])) {
					$search_terms = stripslashes(sanitize_text_field($_POST['search_terms']));
				} else {
					$search_terms = '';
				}
				
				$community_name = $wpdb->get_var($wpdb->prepare("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_POST['cid']));
				$community_owner_user_ID = $wpdb->get_var($wpdb->prepare("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = %d", $_POST['cid']));
				if ( $community_owner_user_ID == $user_ID ) {
					die('Nice try');
				}
				if ( stripslashes(sanitize_text_field($_POST['code'])) != substr(md5(intval($_POST['cid']) . '1234'),0,5) ) {
				?>
                    <h2><a href="?page=communities&action=dashboard&cid=<?php echo intval($_GET['cid']); ?>" style="text-decoration:none;"><?php echo stripslashes( sanitize_text_field($community_name) ); ?></a> &raquo; <?php _e('Join', $communities_text_domain) ?></h2>
                    <p><?php _e('Sorry, the code you provided is invalid.', $communities_text_domain) ?></p>
                    <form name="edit_community" method="POST" action="?page=find-communities&action=join_community_process">
                        <input type="hidden" name="cid" value="<?php echo intval($_POST['cid']); ?>" />
                        <input type="hidden" name="search_terms" value="<?php echo $search_terms; ?>" />
                        <table class="form-table">
                        <tr valign="top">
                        <th scope="row"><?php _e('Code', $communities_text_domain) ?></th>
                        <td><input type="text" name="code" id="code" style="width: 95%" maxlength="250" value="<?php echo stripslashes(sanitize_text_field($_POST['code'])); ?>" />
                        <br />
                        </td>
                        </tr>
                        </table>
                    <p class="submit">
                    <input type="submit" name="Cancel" value="<?php _e('Cancel', $communities_text_domain) ?>" />
                    <input type="submit" name="Submit" value="<?php _e('Join', $communities_text_domain) ?>" />
                    </p>
                    </form>
				<?php
				} else {
					communities_join_community($user_ID, intval($_POST['cid']));
					echo "
					<script type='text/javascript'>
					window.location='?page=find-communities&search_terms=" . urlencode($search_terms) . "&updated=true&updatedmsg=" . urlencode(__('Successfully joined.', $communities_text_domain)) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

function communities_plugin_install() {
	global $wpdb;

	/**
	 * WordPress database upgrade/creation functions
	 */
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Get the correct character collate
	if ( ! empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty($wpdb->collate) )
		$charset_collate .= " COLLATE $wpdb->collate";


	$table_name = $wpdb->base_prefix . "communities";
	if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'") != $table_name) {
		$communities_create_table = 
			"CREATE TABLE IF NOT EXISTS `" . $table_name ."` (
				`community_ID` bigint(20) unsigned NOT NULL auto_increment,
				`community_owner_user_ID` int(11) NOT NULL default '0',
				`community_name` VARCHAR(255),
				`community_description` VARCHAR(255),
				`community_private` tinyint(1) NOT NULL default '0',
				PRIMARY KEY  (`community_ID`)
			) ENGINE=MyISAM;";
		dbDelta($communities_create_table);
	}

	$table_name = $wpdb->base_prefix . "communities_members";
	if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'") != $table_name) {
		$communities_create_table = 
			"CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
  				`member_ID` bigint(20) unsigned NOT NULL auto_increment,
  				`community_ID` int(11) NOT NULL default '0',
  				`member_moderator` tinyint(1) NOT NULL default '0',
  				`member_notifications` VARCHAR(255) NOT NULL default 'digest',
  				`member_user_ID` int(11) NOT NULL default '0',
  				PRIMARY KEY  (`member_ID`)
			) ENGINE=MyISAM;";
		dbDelta($communities_create_table);
	}

	$table_name = $wpdb->base_prefix . "communities_topics";
	if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'") != $table_name) {
		$communities_create_table = 
			"CREATE TABLE `" . $table_name . "` (
				`topic_ID` bigint(20) unsigned NOT NULL auto_increment,
				`topic_community_ID` bigint(20) NOT NULL,
				`topic_title` TEXT NOT NULL,
				`topic_author` bigint(20) NOT NULL,
				`topic_last_author` bigint(20) NOT NULL,
				`topic_stamp` bigint(30) NOT NULL,
				`topic_last_updated_stamp` bigint(30) NOT NULL,
				`topic_closed` tinyint(1) NOT NULL default '0',
				`topic_sticky` tinyint(1) NOT NULL default '0',
				`topic_posts` bigint(20) NOT NULL default '0',
				PRIMARY KEY  (`topic_ID`)
			) ENGINE=MyISAM;";
		dbDelta($communities_create_table);
	}

	$table_name = $wpdb->base_prefix . "communities_posts";
	if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'") != $table_name) {
		$communities_create_table = 
			"CREATE TABLE `" . $table_name . "` (
	  			`post_ID` bigint(20) unsigned NOT NULL auto_increment,
	  			`post_community_ID` bigint(20) NOT NULL,
	  			`post_topic_ID` bigint(20) NOT NULL,
	  			`post_author` bigint(20) NOT NULL,
	  			`post_content` TEXT,
	  			`post_stamp` bigint(30) NOT NULL,
	  			PRIMARY KEY  (`post_ID`)
			) ENGINE=MyISAM;";
		dbDelta($communities_create_table);
	}

	$table_name = $wpdb->base_prefix . "communities_pages";
	if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'") != $table_name) {
		$communities_create_table = 
			"CREATE TABLE `" . $table_name . "` (
	  			`page_ID` bigint(20) unsigned NOT NULL auto_increment,
	  			`page_community_ID` bigint(20) NOT NULL,
	  			`page_parent_page_ID` bigint(20) NOT NULL default '0',
	  			`page_title` TEXT NOT NULL,
	  			`page_content` TEXT,
	  			`page_stamp` bigint(30) NOT NULL,
	  			PRIMARY KEY  (`page_ID`)
			) ENGINE=MyISAM;";
		dbDelta($communities_create_table);
	}

	$table_name = $wpdb->base_prefix . "communities_news_items";
	if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'") != $table_name) {
		$communities_create_table = 
			"CREATE TABLE `" . $table_name . "` (
	  			`news_item_ID` bigint(20) unsigned NOT NULL auto_increment,
	  			`news_item_community_ID` bigint(20) NOT NULL,
	  			`news_item_title` TEXT NOT NULL,
	  			`news_item_content` TEXT,
	  			`news_item_stamp` bigint(30) NOT NULL,
	  			PRIMARY KEY  (`news_item_ID`)
			) ENGINE=MyISAM;";
		dbDelta($communities_create_table);
	}

	$table_name = $wpdb->base_prefix . "communities_notifications";
	if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'") != $table_name) {
		$communities_create_table =
			"CREATE TABLE `" . $table_name . "` (
	  			`notification_ID` bigint(20) unsigned NOT NULL auto_increment,
	  			`notification_community_ID` bigint(20) NOT NULL,
	  			`notification_user_ID` bigint(20) NOT NULL,
	  			`notification_item_title` TEXT NOT NULL,
	  			`notification_item_url` TEXT,
	  			`notification_item_type` VARCHAR(255) NOT NULL,
	  			`notification_stamp` bigint(30) NOT NULL,
	  			PRIMARY KEY  (`notification_ID`)
			) ENGINE=MyISAM;";
		dbDelta($communities_create_table);
	}
	update_site_option( "communities_installed", "yes" );
}
