<?php
/*
Plugin Name: Communities
Plugin URI: http://premium.wpmudev.org/project/communities
Description: Create internal communities with their own discussion boards, wikis, news dashboards, user lists and messaging facilities
Author: Andrew Billits, Andrey Shipilov (Incsub)
Version: 1.1.4
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

$communities_current_version = '1.1.4';
//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//
$communities_notifications_default = 'digest'; // 'digest', 'instant', OR 'none'

$communities_notifications_digest_subject = __("COMMUNITY_NAME newsletter");
$communities_notifications_digest_content = __("Hi COMMUNITY_NAME subscriber,

The following new items have been posted to COMMUNITY_NAME over the
last 24 hours, click on the link next to each item to access it on the
site.

TOPICS

PAGES

NEWS

Cheers, The SITE_NAME Team


To receive instant notifications or to turn off notifications please
visit: NOTIFCATIONS_URL");

$communities_notifications_instant_news_subject = __("New news at COMMUNITY_NAME");
$communities_notifications_instant_news_content = __("Hi COMMUNITY_NAME subscriber,

There's a new news item called 'NEWS_ITEM_TITLE' at COMMUNITY_NAME.

Click on the following link to read it: NEWS_ITEM_URL

Cheers, The SITE_NAME Team


To receive daily digest notifications or to turn off notifications
please visit: NOTIFCATIONS_URL");

$communities_notifications_instant_page_subject = __("New wiki page at COMMUNITY_NAME");
$communities_notifications_instant_page_content = __("Hi COMMUNITY_NAME subscriber,

There's a new wiki page called 'PAGE_TITLE' at COMMUNITY_NAME.

Click on the following link to read it: PAGE_URL

Cheers, The SITE_NAME Team


To receive daily digest notifications or to turn off notifications
please visit: NOTIFCATIONS_URL");

$communities_notifications_instant_topic_subject = __("New topic at COMMUNITY_NAME");
$communities_notifications_instant_topic_content = __("Hi COMMUNITY_NAME subscriber,

There's a new topic called 'TOPIC_TITLE' at COMMUNITY_NAME.

Click on the following link to read it: TOPIC_URL

Cheers, The SITE_NAME Team


To receive daily digest notifications or to turn off notifications
please visit: NOTIFCATIONS_URL");

//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//
//check for activating
if ($_GET['key'] == '' || $_GET['key'] === ''){
	add_action('admin_head', 'communities_make_current');
}
if ( $_GET['action'] == 'dashboard' ) {
	add_action('admin_head','communities_dashboard_css');
}
if ( $_GET['action'] == 'digest_notifications' ) {
	communities_digest_notifications();
}
add_action('admin_menu', 'communities_plug_pages');
add_action('wpabar_menuitems', 'communities_admin_bar');
add_action('communities_digest_notifications_cron', 'communities_digest_notifications');
//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//
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
	communities_global_install();
	//--------------------------------------------------//
	if (get_option( "communities_version" ) == '') {
		add_option( 'communities_version', '0.0.0' );
	}

	if (get_option( "communities_version" ) == $communities_current_version) {
		// do nothing
	} else {
		//up to current version
		update_option( "communities_version", $communities_current_version );
		communities_blog_install();
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

function communities_plug_pages() {
	global $wpdb, $user_ID;
	$owner_community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "'");

	add_menu_page(__('communities'), __('Communities'), 0, 'communities.php');
	if ( $owner_community_count > 0 ) {
		add_submenu_page('communities.php', __('Communities'), __('Manage Communities'), 0, 'manage-communities', 'communities_manage_output' );
	}
	add_submenu_page('communities.php', __('Communities'), __('Find Communities'), 0, 'find-communities', 'communities_find_output' );
}

function communities_admin_bar( $menu ) {
	unset( $menu['communities.php'] );
	return $menu;
}


function communities_create_community($user_ID, $name, $description, $private = '0') {
	global $wpdb;
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities (community_owner_user_ID,community_name,community_description,community_private) VALUES ( '" . $user_ID . "','" . addslashes( $name ) . "','" . addslashes( $description ) . "','" . $private . "' )" );
	$community_ID = $wpdb->get_var("SELECT community_ID FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "' AND community_name = '" . addslashes( $name ) . "'");
	communities_join_community($user_ID, $community_ID, '1');
}

function communities_update_community($user_ID, $community_ID, $description, $private = '0') {
	global $wpdb;
	if ( is_site_admin() ) {
		$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities SET community_description = '" . $description . "' WHERE community_ID = '" . $community_ID . "'");
		$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities SET community_private = '" . $private . "' WHERE community_ID = '" . $community_ID . "'");
	} else {
		$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities SET community_description = '" . $description . "' WHERE community_ID = '" . $community_ID . "' AND community_owner_user_ID = '" . $user_ID . "'");
		$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities SET community_private = '" . $private . "' WHERE community_ID = '" . $community_ID . "' AND community_owner_user_ID = '" . $user_ID . "'");
	}
}

function communities_remove_community($community_ID) {
	global $wpdb;
	do_action('remove_community', $community_ID);

	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $community_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_community_ID = '" . $community_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_posts WHERE post_community_ID = '" . $community_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $community_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = '" . $community_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_community_ID = '" . $community_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $community_ID . "'" );
}

function communities_join_community($user_ID, $community_ID, $moderator = '0') {
	global $wpdb, $communities_notifications_default;
	if ( empty( $communities_notifications_default ) ) {
		$communities_notifications_default = 'digest';
	}
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_members (community_ID,member_moderator,member_notifications,member_user_ID) VALUES ( '" . $community_ID . "', '" . $moderator . "', '" . $communities_notifications_default . "', '" . $user_ID . "' )" );
}

function communities_leave_community($user_ID, $community_ID) {
	global $wpdb;
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $community_ID . "' AND member_user_ID = '" . $user_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_community_ID = '" . $community_ID . "' AND notification_user_ID = '" . $user_ID . "'" );
}

function communities_add_moderator_privilege($user_ID, $community_ID) {
	global $wpdb;
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_members SET member_moderator = '1' WHERE community_ID = '" . $community_ID . "' AND member_user_ID = '" . $user_ID . "'");
}

function communities_remove_moderator_privilege($user_ID, $community_ID) {
	global $wpdb;
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_members SET member_moderator = '0' WHERE community_ID = '" . $community_ID . "' AND member_user_ID = '" . $user_ID . "'");
}

function communities_update_notifications($user_ID, $community_ID, $notifications) {
	global $wpdb;

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_members SET member_notifications = '" . $notifications . "' WHERE member_user_ID = '" . $user_ID . "' AND community_ID = '" . $community_ID . "'" );
}

function communities_count_posts($topic_ID) {
	global $wpdb;
	$post_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_posts WHERE post_topic_ID = '" . $topic_ID . "'");
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_posts = '" . $post_count . "' WHERE topic_ID = '" . $topic_ID . "'" );
}

function communities_add_topic($community_ID, $user_ID, $title, $content, $sticky = '0') {
	global $wpdb;

	$content = strip_tags($content, '<a><p><ul><li><br><strong><img>');
	$title = strip_tags($title);

	$time = time();
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_topics (topic_community_ID, topic_title, topic_author, topic_last_author, topic_stamp, topic_last_updated_stamp, topic_sticky) VALUES ( '" . $community_ID . "', '" . addslashes( $title ) . "', '" . $user_ID . "', '" . $user_ID . "', '" . $time . "', '" . $time . "', '" . $sticky . "')" );
	$topic_ID = $wpdb->get_var("SELECT topic_ID FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_stamp = '" . $time . "' AND topic_title = '" . addslashes( $title ) . "' AND topic_author = '" . $user_ID . "'");
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_posts (post_community_ID, post_topic_ID, post_author, post_content, post_stamp) VALUES ( '" . $community_ID . "', '" . $topic_ID . "', '" . $user_ID . "', '" . addslashes( $content ) . "', '" . $time . "')" );

	communities_count_posts($topic_ID);

	communities_topic_notification($community_ID, $topic_ID, $title);

	return 	$topic_ID;
}

function communities_add_post($community_ID, $topic_ID, $user_ID, $content) {
	global $wpdb;

	$content = strip_tags($content, '<a><p><ul><li><br><strong><img>');

	$time = time();
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_posts (post_community_ID, post_topic_ID, post_author, post_content, post_stamp) VALUES ( '" . $community_ID . "', '" . $topic_ID . "', '" . $user_ID . "', '" . addslashes( $content ) . "', '" . $time . "')" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_last_author = '" . $user_ID . "' WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_last_updated_stamp = '" . $time . "' WHERE topic_ID = '" . $topic_ID . "'" );

	communities_count_posts($topic_ID);

}

function communities_update_post_content($post_ID, $content) {
	global $wpdb;

	$content = strip_tags($content, '<a><p><ul><li><br><strong><img>');

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_posts SET post_content = '" . addslashes( $content ) . "' WHERE post_ID = '" . $post_ID . "'" );
}

function communities_update_topic_title($topic_ID, $title) {
	global $wpdb;

	$title = strip_tags($title, '<a><p><ul><li><br><strong><img>');

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_title = '" . addslashes( $title ) . "' WHERE topic_ID = '" . $topic_ID . "'" );
}

function communities_close_topic($topic_ID) {
	global $wpdb;

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_closed = '1' WHERE topic_ID = '" . $topic_ID . "'" );
}

function communities_open_topic($topic_ID) {
	global $wpdb;

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_closed = '0' WHERE topic_ID = '" . $topic_ID . "'" );
}

function communities_stick_topic($topic_ID) {
	global $wpdb;

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_sticky = '1' WHERE topic_ID = '" . $topic_ID . "'" );
}

function communities_unstick_topic($topic_ID) {
	global $wpdb;

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_topics SET topic_sticky = '0' WHERE topic_ID = '" . $topic_ID . "'" );
}

function communities_delete_topic($topic_ID) {
	global $wpdb;

	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $topic_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_posts WHERE post_topic_ID = '" . $topic_ID . "'" );
}

function communities_delete_post($topic_ID, $post_ID) {
	global $wpdb;

	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = '" . $post_ID . "'" );

	communities_count_posts($topic_ID);

}

function communities_add_page($community_ID, $parent_page_ID, $title, $content) {
	global $wpdb;

	$content = strip_tags($content, '<a><p><ul><li><br><strong><img>');
	$title = strip_tags($title);

	$time = time();
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_pages (page_community_ID, page_parent_page_ID, page_title, page_content, page_stamp) VALUES ( '" . $community_ID . "', '" . $parent_page_ID . "', '" . addslashes( $title ) . "', '" . addslashes( $content ) . "', '" . $time . "')" );
	$page_ID = $wpdb->get_var("SELECT page_ID FROM " . $wpdb->base_prefix . "communities_pages WHERE page_stamp = '" . $time . "' AND page_title = '" . addslashes( $title ) . "'");

	communities_page_notification($community_ID, $page_ID, $title);

	return 	$page_ID;
}

function communities_update_page($page_ID, $title, $content) {
	global $wpdb;

	$title = strip_tags($title, '<a><p><ul><li><br><strong><img>');
	$content = strip_tags($content, '<a><p><ul><li><br><strong><img>');

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_pages SET page_title = '" . addslashes( $title ) . "' WHERE page_ID = '" . $page_ID . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_pages SET page_content = '" . addslashes( $content ) . "' WHERE page_ID = '" . $page_ID . "'" );
}

function communities_delete_page($page_ID) {
	global $wpdb;

	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = '" . $page_ID . "'" );
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_pages WHERE page_parent_page_ID = '" . $page_ID . "'" );
}

function communities_add_news_item($community_ID, $title, $content) {
	global $wpdb;

	$content = strip_tags($content, '<a><p><ul><li><br><strong><img>');
	$title = strip_tags($title);

	$time = time();
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_news_items (news_item_community_ID, news_item_title, news_item_content, news_item_stamp) VALUES ( '" . $community_ID . "', '" . addslashes( $title ) . "', '" . addslashes( $content ) . "', '" . $time . "')" );
	$news_item_ID = $wpdb->get_var("SELECT news_item_ID FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_stamp = '" . $time . "' AND news_item_title = '" . addslashes( $title ) . "'");

	communities_news_notification($community_ID, $news_item_ID, $title);

	return 	$news_item_ID;
}

function communities_update_news_item($news_item_ID, $title, $content) {
	global $wpdb;

	$title = strip_tags($title, '<a><p><ul><li><br><strong><img>');
	$content = strip_tags($content, '<a><p><ul><li><br><strong><img>');

	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_news_items SET news_item_title = '" . addslashes( $title ) . "' WHERE news_item_ID = '" . $news_item_ID . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "communities_news_items SET news_item_content = '" . addslashes( $content ) . "' WHERE news_item_ID = '" . $news_item_ID . "'" );
}

function communities_delete_news_item($news_item_ID) {
	global $wpdb;

	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = '" . $news_item_ID . "'" );
}

function communities_topic_notification($community_ID, $topic_ID, $title) {
	global $wpdb, $communities_notifications_instant_topic_subject, $communities_notifications_instant_topic_content, $current_site;

	$email_subject = $communities_notifications_instant_topic_subject;
	$email_content = $communities_notifications_instant_topic_content;

	$item_url = 'wp-admin/communities.php?action=topic&tid=' . $topic_ID . '&cid=' . $community_ID;

	// digest

	$query = "SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = 'digest' AND community_ID = '" . $community_ID . "'";
	$digest_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $digest_members ) > 0){
		$time = time();
		foreach ( $digest_members as $digest_member ) {
			$member_primary_blog = get_active_blog_for_user( $digest_member['member_user_ID'] );
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_notifications (notification_community_ID, notification_user_ID, notification_stamp, notification_item_title, notification_item_url, notification_item_type) VALUES ( '" . $community_ID . "', '" . $digest_member['member_user_ID'] . "', '" . $time . "', '" . addslashes( $title ) . "', '" . $notification_item_url . "', 'topic')" );
		}
	}

	// instant

	$query = "SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = 'instant' AND community_ID = '" . $community_ID . "'";
	$instant_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $instant_members ) > 0){
		$blog_charset = get_option('blog_charset');
		$community_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $community_ID . "'");
		$email_subject = str_replace('COMMUNITY_NAME', stripslashes( $community_details->community_name ), $email_subject);
		$email_content = str_replace('COMMUNITY_NAME', stripslashes( $community_details->community_name ), $email_content);
		$email_content = str_replace('SITE_NAME', $current_site->site_name, $email_content);
		$email_content = str_replace('TOPIC_TITLE', $title, $email_content);
		foreach ( $instant_members as $instant_member ) {
			$loop_email_subject = $email_subject;
			$loop_email_content = $email_content;

			$member_primary_blog = get_active_blog_for_user( $instant_member['member_user_ID'] );
			$member_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $instant_member['member_user_ID'] . "'");
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$notifications_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . 'wp-admin/communities.php?action=notifications&cid=' . $community_ID;

			$loop_email_content = str_replace('TOPIC_URL', $notification_item_url, $loop_email_content);
			$loop_email_content = str_replace('NOTIFCATIONS_URL', $notifications_url, $loop_email_content);

			$from_email = 'noreply@' . $current_site->domain;
			$message_headers = "MIME-Version: 1.0\n" . "From: " . $current_site->site_name .  " <{$from_email}>\n" . "Content-Type: text/plain; charset=\"" . $blog_charset . "\"\n";
			wp_mail($member_details->user_email, $loop_email_subject, $loop_email_content, $message_headers);
		}
	}
}

function communities_page_notification($community_ID, $page_ID, $title) {
	global $wpdb, $communities_notifications_instant_page_subject, $communities_notifications_instant_page_content, $current_site, $current_site;

	$email_subject = $communities_notifications_instant_page_subject;
	$email_content = $communities_notifications_instant_page_content;

	$item_url = 'wp-admin/communities.php?action=page&pid=' . $page_ID . '&cid=' . $community_ID;

	// digest

	$query = "SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = 'digest' AND community_ID = '" . $community_ID . "'";
	$digest_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $digest_members ) > 0){
		$time = time();
		foreach ( $digest_members as $digest_member ) {
			$member_primary_blog = get_active_blog_for_user( $digest_member['member_user_ID'] );
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_notifications (notification_community_ID, notification_user_ID, notification_stamp, notification_item_title, notification_item_url, notification_item_type) VALUES ( '" . $community_ID . "', '" . $digest_member['member_user_ID'] . "', '" . $time . "', '" . addslashes( $title ) . "', '" . $notification_item_url . "', 'page')" );
		}
	}

	// instant

	$query = "SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = 'instant' AND community_ID = '" . $community_ID . "'";
	$instant_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $instant_members ) > 0){
		$blog_charset = get_option('blog_charset');
		$community_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $community_ID . "'");
		$email_subject = str_replace('COMMUNITY_NAME', stripslashes( $community_details->community_name ), $email_subject);
		$email_content = str_replace('COMMUNITY_NAME', stripslashes( $community_details->community_name ), $email_content);
		$email_content = str_replace('SITE_NAME', $current_site->site_name, $email_content);
		$email_content = str_replace('PAGE_TITLE', $title, $email_content);
		foreach ( $instant_members as $instant_member ) {
			$loop_email_subject = $email_subject;
			$loop_email_content = $email_content;

			$member_primary_blog = get_active_blog_for_user( $instant_member['member_user_ID'] );
			$member_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $instant_member['member_user_ID'] . "'");
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$notifications_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . 'wp-admin/communities.php?action=notifications&cid=' . $community_ID;

			$loop_email_content = str_replace('PAGE_URL', $notification_item_url, $loop_email_content);
			$loop_email_content = str_replace('NOTIFCATIONS_URL', $notifications_url, $loop_email_content);

			$from_email = 'noreply@' . $current_site->domain;
			$message_headers = "MIME-Version: 1.0\n" . "From: " . $current_site->site_name .  " <{$from_email}>\n" . "Content-Type: text/plain; charset=\"" . $blog_charset . "\"\n";
			wp_mail($member_details->user_email, $loop_email_subject, $loop_email_content, $message_headers);
		}
	}
}

function communities_news_notification($community_ID, $news_item_ID, $title) {
	global $wpdb, $communities_notifications_instant_news_subject, $communities_notifications_instant_news_content, $current_site;

	$email_subject = $communities_notifications_instant_news_subject;
	$email_content = $communities_notifications_instant_news_content;

	$item_url = 'wp-admin/communities.php?action=news_item&niid=' . $news_item_ID . '&cid=' . $community_ID;

	// digest

	$query = "SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = 'digest' AND community_ID = '" . $community_ID . "'";
	$digest_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $digest_members ) > 0){
		$time = time();
		foreach ( $digest_members as $digest_member ) {
			$member_primary_blog = get_active_blog_for_user( $digest_member['member_user_ID'] );
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "communities_notifications (notification_community_ID, notification_user_ID, notification_stamp, notification_item_title, notification_item_url, notification_item_type) VALUES ( '" . $community_ID . "', '" . $digest_member['member_user_ID'] . "', '" . $time . "', '" . addslashes( $title ) . "', '" . $notification_item_url . "', 'news')" );
		}
	}

	// instant

	$query = "SELECT member_user_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = 'instant' AND community_ID = '" . $community_ID . "'";
	$instant_members = $wpdb->get_results( $query, ARRAY_A );
	if (count( $instant_members ) > 0){
		$blog_charset = get_option('blog_charset');
		$community_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $community_ID . "'");
		$email_subject = str_replace('COMMUNITY_NAME', stripslashes( $community_details->community_name ), $email_subject);
		$email_content = str_replace('COMMUNITY_NAME', stripslashes( $community_details->community_name ), $email_content);
		$email_content = str_replace('SITE_NAME', $current_site->site_name, $email_content);
		$email_content = str_replace('NEWS_ITEM_TITLE', $title, $email_content);
		foreach ( $instant_members as $instant_member ) {
			$loop_email_subject = $email_subject;
			$loop_email_content = $email_content;

			$member_primary_blog = get_active_blog_for_user( $instant_member['member_user_ID'] );
			$member_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $instant_member['member_user_ID'] . "'");
			$notification_item_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . $item_url;
			$notifications_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . 'wp-admin/communities.php?action=notifications&cid=' . $community_ID;

			$loop_email_content = str_replace('NEWS_ITEM_URL', $notification_item_url, $loop_email_content);
			$loop_email_content = str_replace('NOTIFCATIONS_URL', $notifications_url, $loop_email_content);

			$from_email = 'noreply@' . $current_site->domain;
			$message_headers = "MIME-Version: 1.0\n" . "From: " . $current_site->site_name .  " <{$from_email}>\n" . "Content-Type: text/plain; charset=\"" . $blog_charset . "\"\n";
			wp_mail($member_details->user_email, $loop_email_subject, $loop_email_content, $message_headers);
		}
	}
}

function communities_digest_notifications() {
	global $wpdb, $communities_notifications_digest_subject, $communities_notifications_digest_content, $current_site;

	$email_subject = $communities_notifications_digest_subject;
	$email_content = $communities_notifications_digest_content;

	$query = "SELECT member_user_ID, community_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_notifications = 'digest'";
	$digest_members = $wpdb->get_results( $query, ARRAY_A );

	if (count( $digest_members ) > 0){
		$blog_charset = get_option('blog_charset');
		foreach ( $digest_members as $digest_member ) {
			$notification_item_count = $wpdb->get_row("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_community_ID = '" . $digest_member['community_ID'] . "' AND notification_user_ID = '" . $digest_member['member_user_ID'] . "'");
			if ( $notification_item_count > 0 ) {
				unset( $topics );
				unset( $pages );
				unset( $news_items );
				unset( $notification_topics );
				unset( $notification_pages );
				unset( $notification_news_items );
				$loop_email_subject = $email_subject;
				$loop_email_content = $email_content;
				$community_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $digest_member['community_ID'] . "'");
				$member_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $digest_member['member_user_ID'] . "'");
				$member_primary_blog = get_active_blog_for_user( $digest_member['member_user_ID'] );
				$notifications_url = 'http://' . $member_primary_blog->domain . $member_primary_blog->path . 'wp-admin/communities.php?action=notifications&cid=' . $digest_member['community_ID'];
				$loop_email_subject = str_replace('COMMUNITY_NAME', stripslashes( $community_details->community_name ), $loop_email_subject);
				$loop_email_content = str_replace('COMMUNITY_NAME', stripslashes( $community_details->community_name ), $loop_email_content);
				$loop_email_content = str_replace('SITE_NAME', $current_site->site_name, $loop_email_content);
				$loop_email_content = str_replace('NOTIFCATIONS_URL', $notifications_url, $loop_email_content);

				// topics

				$query = "SELECT notification_item_title, notification_item_url FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = 'topic' AND notification_community_ID = '" . $digest_member['community_ID'] . "' AND notification_user_ID = '" . $digest_member['member_user_ID'] . "'";
				$topics = $wpdb->get_results( $query, ARRAY_A );
				if ( count( $topics ) > 0 ) {
					$notification_topics = __('New Topics') . ":\n\n";
					foreach ( $topics as $topic ) {
						$notification_topics = $notification_topics . $topic['notification_item_title'] . "\n";
						$notification_topics = $notification_topics . $topic['notification_item_url'] . "\n\n";
					}
					$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = 'topic' AND notification_community_ID = '" . $digest_member['community_ID'] . "' AND notification_user_ID = '" . $digest_member['member_user_ID'] . "'" );
				} else {
					$loop_email_content = str_replace('TOPICS', '', $loop_email_content);
				}

				// pages

				$query = "SELECT notification_item_title, notification_item_url FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = 'page' AND notification_community_ID = '" . $digest_member['community_ID'] . "' AND notification_user_ID = '" . $digest_member['member_user_ID'] . "'";
				$pages = $wpdb->get_results( $query, ARRAY_A );
				if ( count( $pages ) > 0 ) {
					$notification_pages = __('New Pages') . ":\n\n";
					foreach ( $pages as $page ) {
						$notification_pages = $notification_pages . $page['notification_item_title'] . "\n";
						$notification_pages = $notification_pages . $page['notification_item_url'] . "\n\n";
					}

					$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = 'page' AND notification_community_ID = '" . $digest_member['community_ID'] . "' AND notification_user_ID = '" . $digest_member['member_user_ID'] . "'" );
				} else {
					$loop_email_content = str_replace('PAGES', '', $loop_email_content);
				}

				// news items

				$query = "SELECT notification_item_title, notification_item_url FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = 'news' AND notification_community_ID = '" . $digest_member['community_ID'] . "' AND notification_user_ID = '" . $digest_member['member_user_ID'] . "'";
				$news_items = $wpdb->get_results( $query, ARRAY_A );
				if ( count( $news_items ) > 0 ) {
					$notification_news_items = __('New News') . ":\n\n";
					foreach ( $news_items as $news_item ) {
						$notification_news_items = $notification_news_items . $news_item['notification_item_title'] . "\n";
						$notification_news_items = $notification_news_items . $news_item['notification_item_url'] . "\n\n";
					}
					$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "communities_notifications WHERE notification_item_type = 'news' AND notification_community_ID = '" . $digest_member['community_ID'] . "' AND notification_user_ID = '" . $digest_member['member_user_ID'] . "'" );
				} else {
					$loop_email_content = str_replace('NEWS', '', $loop_email_content);
				}
				$loop_email_content = str_replace('PAGES', $notification_pages, $loop_email_content);
				$loop_email_content = str_replace('TOPICS', $notification_topics, $loop_email_content);
				$loop_email_content = str_replace('NEWS', $notification_news_items, $loop_email_content);
				if ( count( $news_items ) > 0 || count( $topics ) > 0 || count( $pages ) > 0 ) {
					$from_email = 'noreply@' . $current_site->domain;
					$message_headers = "MIME-Version: 1.0\n" . "From: " . $current_site->site_name .  " <{$from_email}>\n" . "Content-Type: text/plain; charset=\"" . $blog_charset . "\"\n";
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
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			?>
			<h2><?php _e('Communities') ?></h2>
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

			$query = "SELECT community_ID FROM " . $wpdb->base_prefix . "communities_members WHERE member_user_ID = '" . $user_ID . "'";
			$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
			$communities = $wpdb->get_results( $query, ARRAY_A );
			if( count( $communities ) < $num ) {
				$next = false;
			} else {
				$next = true;
			}
			if (count($communities) > 0){
				$community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE member_user_ID = '" . $user_ID . "'");
				if ($community_count > 30){
					?>
                    <br />
                    <table><td>
					<fieldset>
					<?php

					//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

					if( $start == 0 ) {
						echo __('Previous Page');
					} elseif( $start <= 30 ) {
						echo '<a href="communities.php?start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page') . '</a>';
					} else {
						echo '<a href="communities.php?start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page') . '</a>';
					}
					if ( $next ) {
						echo '&nbsp;||&nbsp;<a href="communities.php?start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page') . '</a>';
					} else {
						echo '&nbsp;||&nbsp;' . __('Next Page');
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
				<th scope='col'>" . __('Name') . "</th>
				<th scope='col'>" . __('Owner') . "</th>
				<th scope='col'>" . __('Actions') . "</th>
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
					$class = ('alternate' == $class) ? '' : 'alternate';
					foreach ($communities as $community){
					$community_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $community['community_ID'] . "'");
					//=========================================================//
					echo "<tr class='" . $class . "'>";
					echo "<td valign='top'><a href='communities.php?action=dashboard&cid=" . $community['community_ID'] . "' style='text-decoration:none;'><strong>" . stripslashes( $community_details->community_name ) . "</strong></a></td>";
					$owner_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $community_details->community_owner_user_ID . "'");
					echo "<td valign='top'>" . $owner_details->display_name . "</td>";
					echo "<td valign='top'><a href='communities.php?action=message_board&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Message Board') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?action=wiki&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Wiki') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?action=news&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('News') . "</a></td>";
					$community_member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $community['community_ID'] . "'");
					echo "<td valign='top'><a href='communities.php?action=member_list&cid=" . $community['community_ID'] . "' rel='permalink' class='delete'>" . __('Members') . " (" . $community_member_count . ")</a></td>";
					echo "<td valign='top'><a href='admin.php?page=messaging_new&message_to=" . $owner_details->user_login . "' rel='permalink' class='edit'>" . __('Send Message to Owner') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?action=notifications&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Notifications') . "</a></td>";
					if ( $community_details->community_owner_user_ID == $user_ID ) {
						echo "<td valign='top'>" . __('Leave') . "</td>";
					} else {
						echo "<td valign='top'><a href='communities.php?action=leave_community&cid=" . $community['community_ID'] . "' rel='permalink' class='delete'>" . __('Leave') . "</a></td>";
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
	            <p><?php _e('You currently are not a member of a community. Please visit the "Find Communities" tab to search for communities to join. Alternatively you can create your own community using the form below!') ?></p>
                <?php
			}

			$owner_community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "'");
			?>
            <br />
			<h2><?php _e('Create Community') ?></h2>
			<?php
			if ( $owner_community_count > 44 ) {
				?>
				<p><?php _e('Sorry, you can only create a maximum of 45 communities.') ?></p>
				<?php
			} else {
				?>
				<p><?php _e('You can create up to 45 communities of your own using the form below.') ?></p>
				<form name="create_community" method="POST" action="communities.php?action=create_community">
					<table class="form-table">
					<tr valign="top">
					<th scope="row"><?php _e('Name') ?></th>
					<td><input type="text" name="community_name" id="community_name" style="width: 95%" value="<?php echo $_POST['community_name']; ?>" />
					<br />
					<?php _e('Required') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Description') ?></th>
					<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo $_POST['community_description']; ?>" />
					<br />
					<?php _e('Required') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Private') ?></th>
					<td><select name="community_private">
						<option value="0" <?php if ($_POST['community_private'] == '0' || $_POST['community_private'] == '') echo 'selected="selected"'; ?>><?php _e('No'); ?></option>
						<option value="1" <?php if ($_POST['community_private'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes'); ?></option>
					</select>
					<?php _e('Users have to enter a code to join private communities') ?></td>
					</tr>
					</table>
				<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Create') ?>" />
				</p>
				</form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "create_community":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php';
				</script>
				";
			} else {
				?>
				<h2><?php _e('Create Community') ?></h2>
				<?php
				if ( $owner_community_count > 44 ) {
					?>
					<p><?php _e('Sorry, you can only create a maximum of 45 communities.') ?></p>
					<?php
				} else {
					if ( !empty( $_POST['community_name'] ) || !empty( $_POST['community_description'] ) ) {
						$community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_name = '" . addslashes( $_POST['community_name'] ) . "'");
					}
					if ( empty( $_POST['community_name'] ) || empty( $_POST['community_description'] ) ) {
						?>
						<p><?php _e('Please fill in all fields.') ?></p>
						<form name="create_community" method="POST" action="communities.php?action=create_community">
							<table class="form-table">
							<tr valign="top">
							<th scope="row"><?php _e('Name') ?></th>
							<td><input type="text" name="community_name" id="community_name" style="width: 95%" value="<?php echo $_POST['community_name']; ?>" />
							<br />
							<?php _e('Required') ?></td>
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('Description') ?></th>
							<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo $_POST['community_description']; ?>" />
							<br />
							<?php _e('Required') ?></td>
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('Private') ?></th>
							<td><select name="community_private">
								<option value="0" <?php if ($_POST['community_private'] == '0' || $_POST['community_private'] == '') echo 'community_private"'; ?>><?php _e('No'); ?></option>
								<option value="1" <?php if ($_POST['community_private'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes'); ?></option>
							</select>
							<?php _e('Users have to enter a code to join private communities') ?></td>
							</tr>
							</table>
						<p class="submit">
						<input type="submit" name="Submit" value="<?php _e('Create') ?>" />
						</p>
						</form>
						<?php
					} else if ( $community_count > 0 ) {
						?>
						<p><?php _e('Sorry, a community with that name already exists.') ?></p>
						<form name="create_community" method="POST" action="communities.php?action=create_community">
							<table class="form-table">
							<tr valign="top">
							<th scope="row"><?php _e('Name') ?></th>
							<td><input type="text" name="community_name" id="community_name" style="width: 95%" value="" />
							<br />
							<?php _e('Required') ?></td>
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('Description') ?></th>
							<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo $_POST['community_description']; ?>" />
							<br />
							<?php _e('Required') ?></td>
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('Private') ?></th>
							<td><select name="community_private">
								<option value="0" <?php if ($_POST['community_private'] == '0' || $_POST['community_private'] == '') echo 'selected="selected"'; ?>><?php _e('No'); ?></option>
								<option value="1" <?php if ($_POST['community_private'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes'); ?></option>
							</select>
							<?php _e('Users have to enter a code to join private communities') ?></td>
							</tr>
							</table>
						<p class="submit">
                        <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
						<input type="submit" name="Submit" value="<?php _e('Create') ?>" />
						</p>
						</form>
						<?php
					} else {
						communities_create_community($user_ID, $_POST['community_name'], $_POST['community_description'], $_POST['community_private']);
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?page=manage-communities&updated=true&updatedmsg=" . urlencode(__('Community created.')) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "notifications":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				$member_notifications = $wpdb->get_var("SELECT member_notifications FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=notifications&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Notifications') ?></a></h2>
                <form name="notifications" method="POST" action="communities.php?action=notifications_process&cid=<?php echo $_GET['cid']; ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Notifications') ?></th>
                    <td><select name="notifications">
                        <option value="instant" <?php if ( $member_notifications == 'instant' ) { echo 'selected="selected"'; } ?> ><?php _e('Instant'); ?></option>
                        <option value="digest" <?php if ( $member_notifications == 'digest' ) { echo 'selected="selected"'; } ?> ><?php _e('Daily Digest'); ?></option>
                        <option value="none" <?php if ( $member_notifications == 'none' ) { echo 'selected="selected"'; } ?> ><?php _e('None'); ?></option>
                    </select>
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
                </p>
                </form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "notifications_process":
			communities_update_notifications($user_ID, $_GET['cid'], $_POST['notifications']);
			echo "
			<SCRIPT LANGUAGE='JavaScript'>
			window.location='communities.php?action=notifications&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
			</script>
			";
		break;
		//---------------------------------------------------//
		case "member_list":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=member_list&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Members') ?></a></h2>
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
				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "'";
				$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
				$members = $wpdb->get_results( $query, ARRAY_A );
				if( count( $members ) < $num ) {
					$next = false;
				} else {
					$next = true;
				}
				if (count($members) > 0){
					$members_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "'");
					if ($members_count > 30){
						?>
						<br />
						<table><td>
						<fieldset>
						<?php

						//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

						if( $start == 0 ) {
							echo __('Previous Page');
						} elseif( $start <= 30 ) {
							echo '<a href="communities.php?action=member_list&cid=' . $_GET['cid'] . '&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page') . '</a>';
						} else {
							echo '<a href="communities.php?action=member_list&cid=' . $_GET['cid'] . '&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page') . '</a>';
						}
						if ( $next ) {
							echo '&nbsp;||&nbsp;<a href="communities.php?action=member_list&cid=' . $_GET['cid'] . '&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page') . '</a>';
						} else {
							echo '&nbsp;||&nbsp;' . __('Next Page');
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
					<th scope='col'>" . __('Name') . "</th>
					<th scope='col'>" . __('Avatar') . "</th>
					<th scope='col'>" . __('Type') . "</th>
					<th scope='col'>" . __('Actions') . "</th>
					<th scope='col'></th>
					</tr></thead>
					<tbody id='the-list'>
					";
					//=========================================================//
						$class = ('alternate' == $class) ? '' : 'alternate';
						foreach ($members as $member){
						//=========================================================//
						echo "<tr class='" . $class . "'>";
						$member_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $member['member_user_ID'] . "'");
						echo "<td valign='top'><strong>" . $member_details->display_name . "</strong></td>";
						echo "<td valign='top'><img src='http://" . $current_site->domain . $current_site->path . "avatar/user-" . $member['member_user_ID'] . "-32.png' /></td>";
						$member_type = __('Member');
						if ( $member['member_moderator'] == '1' ) {
							$member_type = __('Moderator');
						}
						if ( $community_owner_user_ID == $member['member_user_ID'] ) {
							$member_type = __('Owner');
						}
						echo "<td valign='top'>" . $member_type . "</td>";
						$member_primary_blog = get_active_blog_for_user( $member['member_user_ID'] );
						echo "<td valign='top'><a href='http://" . $member_primary_blog->domain . $member_primary_blog->path . "' rel='permalink' class='edit'>" . __('Visit Blog') . "</a></td>";
						if ( $member['member_user_ID'] == $user_ID ) {
							echo "<td valign='top'>" . __('Send Message') . "</td>";
						} else {
							echo "<td valign='top'><a href='admin.php?page=messaging_new&message_to=" . $member_details->user_login . "' rel='permalink' class='edit'>" . __('Send Message') . "</a></td>";
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
			$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			?>
			<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Leave') ?></h2>
            <form name="leave_community" method="POST" action="communities.php?action=leave_community_process">
                <input type="hidden" name="cid" value="<?php echo $_GET['cid']; ?>" />
                <input type="hidden" name="search_terms" value="<?php echo $_GET['search_terms']; ?>" />
                <input type="hidden" name="return" value="<?php echo $_GET['return']; ?>" />
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Are you sure?') ?></th>
                <td><select name="leave_community">
                    <option value="no" selected="selected" ><?php _e('No'); ?></option>
                    <option value="yes" ><?php _e('Yes'); ?></option>
                </select>
                </td>
                </tr>
                </table>
            <p class="submit">
            <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
            <input type="submit" name="Submit" value="<?php _e('Continue') ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
		case "leave_community_process":
			if ( isset( $_POST['Cancel'] ) || $_POST['leave_community'] == 'no' ) {
				if ( $_POST['return'] == 'find_communities' ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=find-communities&search_terms=" . $_POST['search_terms'] . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php';
					</script>
					";
				}
			} else {
				$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_POST['cid'] . "'");
				if ( $community_owner_user_ID == $user_ID ) {
					die();
				}
				communities_leave_community($user_ID, $_POST['cid']);
				if ( $_POST['return'] == 'find_communities' ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=find-communities&search_terms=" . $_POST['search_terms'] . "&updated=true&updatedmsg=" . urlencode(__('Successfully left.')) . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?updated=true&updatedmsg=" . urlencode(__('Successfully left.')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "message_board":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a></h2>
				<h3><?php _e('Topics') ?></h3>
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

				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_community_ID = '" . $_GET['cid'] . "'";
				$query .= " ORDER BY topic_sticky DESC, topic_last_updated_stamp DESC";
				$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
				$topics = $wpdb->get_results( $query, ARRAY_A );
				if( count( $topics ) < $num ) {
					$next = false;
				} else {
					$next = true;
				}
				if (count($topics) > 0){
					$topic_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_community_ID = '" . $_GET['cid'] . "'");
					if ($topic_count > 30){
						?>
						<br />
						<table><td>
						<fieldset>
						<?php

						//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

						if( $start == 0 ) {
							echo __('Previous Page');
						} elseif( $start <= 30 ) {
							echo '<a href="communities.php?action=message_board&cid=' . $_GET['cid'] . '&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page') . '</a>';
						} else {
							echo '<a href="communities.php?action=message_board&cid=' . $_GET['cid'] . '&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page') . '</a>';
						}
						if ( $next ) {
							echo '&nbsp;||&nbsp;<a href="communities.php?action=message_board&cid=' . $_GET['cid'] . '&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page') . '</a>';
						} else {
							echo '&nbsp;||&nbsp;' . __('Next Page');
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
					<th scope='col'>" . __('Topic') . "</th>
					<th scope='col'>" . __('Posts') . "</th>
					<th scope='col'>" . __('Last Poster') . "</th>
					</tr></thead>
					<tbody id='the-list'>
					";
					//=========================================================//
						$class = ('alternate' == $class) ? '' : 'alternate';
						foreach ($topics as $topic){
						if ( $topic['topic_sticky'] == '1' ) {
							$style = 'style="background-color:#D5EBEC;"';
						} else {
							$style = '';
						}
						//=========================================================//)
						echo "<tr class='" . $class . "' " . $style . " >";
						if ( $topic['topic_closed'] == '1' ) {
							$topic_closed = ' (' . __('Closed') . ')';
						} else {
							$topic_closed = '';
						}
						if ( $topic['topic_sticky'] == '1' ) {
							$topic_sticky = ' (' . __('Sticky') . ')';
						} else {
							$topic_sticky = '';
						}
						echo "<td valign='top'><strong><a href='communities.php?action=topic&tid=" . $topic['topic_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $topic['topic_title'] ) . "</a>" . $topic_closed . $topic_sticky . "</strong></td>";
						echo "<td valign='top'>" . $topic['topic_posts'] . "</td>";
						$user_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $topic['topic_last_author'] . "'");
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
					<p><?php _e('There currently aren\'t any topics on this message board. Use the form below to create the first topic!') ?></p>
					<?php
				}
				?>
				<br />
				<h2><?php _e('New Topic') ?></h2>
                <form name="new_topic" method="POST" action="communities.php?action=new_topic&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Title') ?></th>
                    <td><input type="text" name="topic_title" id="topic_title" style="width: 95%" value="<?php echo $_POST['topic_title']; ?>" />
                    <br />
                    <?php _e('Required') ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Content') ?></th>
                    <td><textarea name="topic_content" id="topic_content" style="width: 95%" rows="10"><?php echo $_POST['topic_content']; ?></textarea>
                    <br />
                    <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
                    </tr>
                    <?php
                    $member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
					if (  $member_moderator == '1' || is_site_admin() ) {
					?>
                        <tr valign="top">
                        <th scope="row"><?php _e('Sticky') ?></th>
                        <td><select name="topic_sticky">
                            <option value="0" <?php if ($_POST['topic_sticky'] == '0' || $_POST['topic_sticky'] == '') echo 'selected="selected"'; ?>><?php _e('No'); ?></option>
                            <option value="1" <?php if ($_POST['topic_sticky'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes'); ?></option>
                        </select>
						</td>
                        </tr>
                    <?php
					}
					?>
                    </table>
                <p class="submit">
                <input type="submit" name="Submit" value="<?php _e('Post') ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "new_topic":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				if ( isset( $_POST['Cancel'] ) ) {
					if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=message_board&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
						</script>
						";
					} else {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=message_board&cid=" . $_GET['cid'] . "';
						</script>
						";
					}
				} else {
					$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
					?>
					<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <?php _e('New Topic') ?></h2>
					<?php
					if ( empty( $_POST['topic_title'] ) || empty( $_POST['topic_content'] ) ) {
						?>
                        <p><?php _e('Please fill in all fields.'); ?></p>
                        <form name="new_topic" method="POST" action="communities.php?action=new_topic&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                            <table class="form-table">
                            <tr valign="top">
                            <th scope="row"><?php _e('Title') ?></th>
                            <td><input type="text" name="topic_title" id="topic_title" style="width: 95%" value="<?php echo $_POST['topic_title']; ?>" />
                            <br />
                            <?php _e('Required') ?></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row"><?php _e('Content') ?></th>
                            <td><textarea name="topic_content" id="topic_content" style="width: 95%" rows="10"><?php echo $_POST['topic_content']; ?></textarea>
                            <br />
                            <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
                            </tr>
                            <?php
                            $member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
                            if (  $member_moderator == '1' || is_site_admin() ) {
                            ?>
                                <tr valign="top">
                                <th scope="row"><?php _e('Sticky') ?></th>
                                <td><select name="topic_sticky">
                                    <option value="0" <?php if ($_POST['topic_sticky'] == '0' || $_POST['topic_sticky'] == '') echo 'selected="selected"'; ?>><?php _e('No'); ?></option>
                                    <option value="1" <?php if ($_POST['topic_sticky'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes'); ?></option>
                                </select>
                                </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </table>
                        <p class="submit">
                        <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                        <input type="submit" name="Submit" value="<?php _e('Post') ?>" />
                        </p>
                        </form>
                        <?php
					} else {
						$topic_ID = communities_add_topic($_GET['cid'], $user_ID, $_POST['topic_title'], $_POST['topic_content'], $_POST['topic_sticky']);
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $topic_ID . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Topic added.')) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "topic":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
				$date_format = get_option('date_format');
				$time_format = get_option('time_format');
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <a href="communities.php?action=topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes($topic_details->topic_title); ?></a><?php if ( $topic_details->topic_closed == '1' ) { echo ' (' . __('Closed') . ')'; }; ?></h2>
                <ul>
	                <li><strong><?php _e('Started'); ?>:</strong> <?php echo date($date_format . ' ' . $time_format,$topic_details->topic_stamp); ?></li>
	                <li><strong><?php _e('Last Updated'); ?>:</strong> <?php echo date($date_format . ' ' . $time_format,$topic_details->topic_last_updated_stamp); ?></li>
                    <?php
                    $last_poster_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $topic_details->topic_last_author . "'");
                    $last_poster_primary_blog = get_active_blog_for_user( $topic_details->topic_last_author );
					?>
	                <li><strong><?php _e('Last Poster'); ?>:</strong> <?php echo $last_poster_details->display_name; ?> (<a href="admin.php?page=messaging_new&message_to=<?php echo $last_poster_details->user_login; ?>" style="text-decoration:none;"><?php _e('Send Message'); ?></a> | <a href="http://<?php echo  $last_poster_primary_blog->domain .  $last_poster_primary_blog->path; ?>" style="text-decoration:none;"><?php _e('View Blog'); ?></a>)</li>
                    <?php
					if ( is_site_admin() || $member_moderator == '1' ) {
						?>
		                <li><strong><?php _e('Actions'); ?>:</strong>
                        <?php
						if ( $topic_details->topic_closed == '1' ) {
							?>
							<a href="communities.php?action=open_topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>" style="text-decoration:none;"><?php _e('Open'); ?></a> |
							<?php
						} else {
							?>
							<a href="communities.php?action=close_topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>" style="text-decoration:none;"><?php _e('Close'); ?></a> |
							<?php
						}
						if ( $topic_details->topic_sticky == '1' ) {
							?>
							<a href="communities.php?action=unstick_topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>" style="text-decoration:none;"><?php _e('Unstick'); ?></a> |
							<?php
						} else {
							?>
							<a href="communities.php?action=stick_topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>" style="text-decoration:none;"><?php _e('Make Sticky'); ?></a> |
							<?php
						}
						?>
						<a href="communities.php?action=edit_topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>" style="text-decoration:none;"><?php _e('Edit Title'); ?></a> |
						<a href="communities.php?action=remove_topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>" style="text-decoration:none;"><?php _e('Remove'); ?></a>
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

				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_topic_ID = '" . $_GET['tid'] . "'";
				$query .= " ORDER BY post_ID ASC";
				$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
				$posts = $wpdb->get_results( $query, ARRAY_A );
				if( count( $posts ) < $num ) {
					$next = false;
				} else {
					$next = true;
				}
				if (count($posts) > 0){
					$post_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_posts WHERE post_topic_ID = '" . $_GET['tid'] . "'");
					if ($post_count > 15){
						?>
						<br />
						<table><td>
						<fieldset>
						<?php

						//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

						if( $start == 0 ) {
							echo __('Previous Page');
						} elseif( $start <= 15 ) {
							echo '<a href="communities.php?action=topic&tid=' . $_GET['tid'] . '&cid=' . $_GET['cid'] . '&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page') . '</a>';
						} else {
							echo '<a href="communities.php?action=topic&tid=' . $_GET['tid'] . '&cid=' . $_GET['cid'] . '&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page') . '</a>';
						}
						if ( $next ) {
							echo '&nbsp;||&nbsp;<a href="communities.php?action=topic&tid=' . $_GET['tid'] . '&cid=' . $_GET['cid'] . '&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page') . '</a>';
						} else {
							echo '&nbsp;||&nbsp;' . __('Next Page');
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
					<th scope='col' width='100px'><center>" . __('Author') . "</center></th>
					<th scope='col'>" . __('Post') . "</th>
					</tr></thead>
					<tbody id='the-list'>
					";
					//=========================================================//
						$class = ('alternate' == $class) ? '' : 'alternate';
						foreach ($posts as $post){
						//=========================================================//)
						$user_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $post['post_author'] . "'");
						$user_primary_blog = get_active_blog_for_user( $post['post_author'] );
						echo "<tr class='" . $class . "'>";
						echo "<td valign='top' style='border-right:#cccccc solid 1px;' ><center><strong>" . $user_details->display_name;
						echo "<br />";
						echo "<img src='http://" . $current_site->domain . $current_site->path . "avatar/user-" . $post['post_author'] . "-48.png' />";
						if ( $post['post_author'] != $user_ID ) {
							echo "<br />";
							echo "<a href='admin.php?page=messaging_new&message_to=" . $user_details->user_login . "' style='text-decoration:none;'>" . __("Send Message") . "</a>";
						}
						echo "<br />";
						echo "<a href='http://" . $user_primary_blog->domain . $user_primary_blog->path . "' style='text-decoration:none;'>" . __("View Blog") . "</a>";
						echo "</strong></center></td>";
						echo "<td valign='top'>";
						echo "<p>" . stripslashes( $post['post_content'] ) . "</p>";
						echo "<br />";
						echo "<div style='border-top:#cccccc solid 1px;' >";
						echo __("Posted") . ": " . date($date_format . ' ' . $time_format, $post['post_stamp']);
						$time_difference = time() - $post['post_stamp'];
						if ( $member_moderator == '1' || is_site_admin() ) {
							echo " | <a href='communities.php?action=edit_post&pid=" . $post['post_ID'] . "&tid=" . $topic_details->topic_ID . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "' style='text-decoration:none;'>" . __("Edit") . "</a>";
							if ( $topic_details->topic_posts > 1 ) {
								echo " | <a href='communities.php?action=remove_post&pid=" . $post['post_ID'] . "&tid=" . $topic_details->topic_ID . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "' style='text-decoration:none;'>" . __("Remove") . "</a>";
							}
						} else if ( $post['post_author'] && $time_difference < 900 ) {
							echo " | <a href='communities.php?action=edit_post&pid=" . $post['post_ID'] . "&tid=" . $topic_details->topic_ID . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "' style='text-decoration:none;'>" . __("Edit") . "</a>";
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
					<h2><?php _e('New Post') ?></h2>
					<form name="new_post" method="POST" action="communities.php?action=new_post&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
						<table class="form-table">
						<tr valign="top">
						<th scope="row"><?php _e('Content') ?></th>
						<td><textarea name="post_content" id="post_content" style="width: 95%" rows="10"><?php echo $_POST['post_content']; ?></textarea>
						<br />
						<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
						</tr>
						</table>
					<p class="submit">
					<input type="submit" name="Submit" value="<?php _e('Post') ?>" />
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
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
						</script>
						";
					} else {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "';
						</script>
						";
					}
			} else {
				$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
				if ( $topic_details->topic_closed != '1' ) {
					$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
					if ( $member_count > 0 || is_site_admin() ) {
						if ( empty($_POST['post_content']) ) {
							$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
							?>
							<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <a href="communities.php?action=topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes($topic_details->topic_title); ?></a></h2>
							<p><?php _e('Please provide some content.'); ?></p>
							<form name="new_post" method="POST" action="communities.php?action=new_post&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
								<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php _e('Content') ?></th>
								<td><textarea name="post_content" id="post_content" style="width: 95%" rows="10"><?php echo $_POST['post_content']; ?></textarea>
								<br />
								<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
								</tr>
								</table>
							<p class="submit">
							<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
							<input type="submit" name="Submit" value="<?php _e('Post') ?>" />
							</p>
							</form>
							<?php
						} else {
							communities_add_post($_GET['cid'], $_GET['tid'], $user_ID, $_POST['post_content']);
							if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
								echo "
								<SCRIPT LANGUAGE='JavaScript'>
								window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Post added.')) . "';
								</script>
								";
							} else {
								echo "
								<SCRIPT LANGUAGE='JavaScript'>
								window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Post added.')) . "';
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
			$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
			$post_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = '" . $_GET['pid'] . "' ");
			if ( $topic_details->topic_closed != '1' ) {
				$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				$time_difference = time() - $post_details->post_stamp;
				if ( $member_moderator == '1' || is_site_admin() || ( $post_details->post_author == $user_ID && $time_difference < 900 ) ) {
					$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
					?>
					<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <a href="communities.php?action=topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes($topic_details->topic_title); ?></a> &raquo; <?php _e('Edit Post'); ?></h2>
					<form name="edit_post" method="POST" action="communities.php?action=edit_post_process&pid=<?php echo $_GET['pid']; ?>&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
						<table class="form-table">
						<tr valign="top">
						<th scope="row"><?php _e('Content') ?></th>
						<td><textarea name="post_content" id="post_content" style="width: 95%" rows="10"><?php echo stripslashes( $post_details->post_content ); ?></textarea>
						<br />
						<?php _e('Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
						</tr>
						</table>
					<p class="submit">
					<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
					<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
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
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
						</script>
						";
					} else {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "';
						</script>
						";
					}
			} else {
				$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
				$post_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = '" . $_GET['pid'] . "' ");
				if ( $topic_details->topic_closed != '1' ) {
					$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
					$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
					$time_difference = time() - $post_details->post_stamp;
					if ( $member_moderator == '1' || is_site_admin() || ( $post_details->post_author == $user_ID && $time_difference < 900 ) ) {
						if ( empty( $_POST['post_content'] ) ) {
							$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
							?>
							<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <a href="communities.php?action=topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes($topic_details->topic_title); ?></a> &raquo; <?php _e('Edit Post'); ?></h2>
                            <p><?php _e('Please provide some content'); ?></p>
							<form name="edit_post" method="POST" action="communities.php?action=edit_post_process&pid=<?php echo $_GET['pid']; ?>&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
								<table class="form-table">
								<tr valign="top">
								<th scope="row"><?php _e('Content') ?></th>
								<td><textarea name="post_content" id="post_content" style="width: 95%" rows="10"><?php echo $_POST['post_content']; ?></textarea>
								<br />
								<?php _e('Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
								</tr>
								</table>
							<p class="submit">
							<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
							<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
							</p>
							</form>
							<?php
						} else {
							communities_update_post_content($_GET['pid'], $_POST['post_content']);
							if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
								echo "
								<SCRIPT LANGUAGE='JavaScript'>
								window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
								</script>
								";
							} else {
								echo "
								<SCRIPT LANGUAGE='JavaScript'>
								window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
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
			$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
			$post_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = '" . $_GET['pid'] . "' ");
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_moderator == '1' || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <a href="communities.php?action=topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes($topic_details->topic_title); ?></a> &raquo; <?php _e('Remove Post'); ?></h2>

				<form name="remove_post" method="POST" action="communities.php?action=remove_post_process&pid=<?php echo $_GET['pid']; ?>&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Are you sure?') ?></th>
                    <td><select name="remove_post">
                        <option value="no" selected="selected" ><?php _e('No'); ?></option>
                        <option value="yes" ><?php _e('Yes'); ?></option>
                    </select>
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                <input type="submit" name="Submit" value="<?php _e('Continue') ?>" />
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
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "';
					</script>
					";
				}
			} else {
				$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
				$post_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_posts WHERE post_ID = '" . $_GET['pid'] . "' ");
				$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				if ( $member_moderator == '1' || is_site_admin() ) {
					if ( $_POST['remove_post'] == 'yes' ) {
						communities_delete_post($_GET['tid'], $_GET['pid']);
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Post removed.')) . "';
						</script>
						";
					} else {
						if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
							echo "
							<SCRIPT LANGUAGE='JavaScript'>
							window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
							</script>
							";
						} else {
							echo "
							<SCRIPT LANGUAGE='JavaScript'>
							window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "';
							</script>
							";
						}
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "close_topic":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if (  $member_moderator == '1' || is_site_admin() ) {
				communities_close_topic($_GET['tid']);
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Topic closed.')) . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Topic closed.')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "open_topic":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if (  $member_moderator == '1' || is_site_admin() ) {
				communities_open_topic($_GET['tid']);
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Topic opened.')) . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Topic opened.')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "stick_topic":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if (  $member_moderator == '1' || is_site_admin() ) {
				communities_stick_topic($_GET['tid']);
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Topic made sticky.')) . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Topic made sticky.')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "unstick_topic":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if (  $member_moderator == '1' || is_site_admin() ) {
				communities_unstick_topic($_GET['tid']);
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Sticky removed.')) . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Sticky removed.')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_topic":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if (  $member_moderator == '1' || is_site_admin() ) {
				$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <a href="communities.php?action=topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes($topic_details->topic_title); ?></a> &raquo; <?php _e('Remove Topic'); ?></h2>

				<form name="remove_topic" method="POST" action="communities.php?action=remove_topic_process&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Are you sure?') ?></th>
                    <td><select name="remove_topic">
                        <option value="no" selected="selected" ><?php _e('No'); ?></option>
                        <option value="yes" ><?php _e('Yes'); ?></option>
                    </select>
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                <input type="submit" name="Submit" value="<?php _e('Continue') ?>" />
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
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
						</script>
						";
					} else {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "';
						</script>
						";
					}
			} else {
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				if (  $member_moderator == '1' || is_site_admin() ) {
					if ( $_POST['remove_topic'] == 'yes' ) {
						communities_delete_topic($_GET['tid']);
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=message_board&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Topic Removed.')) . "';
						</script>
						";
					} else {
						if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
							echo "
							<SCRIPT LANGUAGE='JavaScript'>
							window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
							</script>
							";
						} else {
							echo "
							<SCRIPT LANGUAGE='JavaScript'>
							window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "';
							</script>
							";
						}
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "edit_topic":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if (  $member_moderator == '1' || is_site_admin() ) {
				$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <a href="communities.php?action=topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes($topic_details->topic_title); ?></a> &raquo; <?php _e('Edit Topic'); ?></h2>
                <form name="edit_topic" method="POST" action="communities.php?action=edit_topic_process&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Title') ?></th>
                    <td><input type="text" name="topic_title" id="topic_title" style="width: 95%" value="<?php echo stripslashes( $topic_details->topic_title ); ?>" />
                    <br />
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
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
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
						</script>
						";
					} else {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "';
						</script>
						";
					}
			} else {
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				if (  $member_moderator == '1' || is_site_admin() ) {
					if ( empty( $_POST['topic_title'] ) ) {
						$topic_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_ID = '" . $_GET['tid'] . "' ");
						$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
						?>
						<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Message Board') ?></a> &raquo; <a href="communities.php?action=topic&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes($topic_details->topic_title); ?></a> &raquo; <?php _e('Edit Topic'); ?></h2>
                        <p><?php _e('Please provide a title.'); ?></p>
						<form name="edit_topic" method="POST" action="communities.php?action=edit_topic_process&tid=<?php echo $_GET['tid']; ?>&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
							<table class="form-table">
							<tr valign="top">
							<th scope="row"><?php _e('Title') ?></th>
							<td><input type="text" name="topic_title" id="topic_title" style="width: 95%" value="<?php echo stripslashes( $topic_details->topic_title ); ?>" />
							<br />
							</td>
							</tr>
							</table>
						<p class="submit">
						<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
						<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
						</p>
						</form>
						<?php
					} else {
						communities_update_topic_title($_GET['tid'], $_POST['topic_title']);
						if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
							echo "
							<SCRIPT LANGUAGE='JavaScript'>
							window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Changes Saved.')) . "';
							</script>
							";
						} else {
							echo "
							<SCRIPT LANGUAGE='JavaScript'>
							window.location='communities.php?action=topic&tid=" . $_GET['tid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Changes Saved.')) . "';
							</script>
							";
						}
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "wiki":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=wiki&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Wiki') ?></a></h2>
                <?php
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				if (  $member_moderator == '1' || is_site_admin() ) {
					?>
					<h3><?php _e('Manage') ?></h3>
					<ul>
					<li><strong><?php _e('Actions'); ?>:</strong>
					<a href="communities.php?action=new_page&ppid=0&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('New Page'); ?></a>
					</li>
					</ul>
					<?php
				}
				?>
				<h3><?php _e('Pages') ?></h3>
				<?php
				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '0'";
				$pages[0] = $wpdb->get_results( $query, ARRAY_A );
				if ( count( $pages[0] ) > 0 ) {
					echo "<ul>";
					foreach ( $pages[0] as $page ) {
						echo "<li><strong><a href='communities.php?action=page&pid=" . $page['page_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $page['page_title'] ) . "</a></strong></li>";
						$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '" . $page['page_ID'] . "'";
						$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
						if ( count( $pages[$page['page_ID']] ) > 0 ) {
							echo "<ul>";
							foreach ( $pages[$page['page_ID']] as $page ) {
								echo "<li><strong><a href='communities.php?action=page&pid=" . $page['page_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $page['page_title'] ) . "</a></strong></li>";
								$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '" . $page['page_ID'] . "'";
								$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
								if ( count( $pages[$page['page_ID']] ) > 0 ) {
									echo "<ul>";
									foreach ( $pages[$page['page_ID']] as $page ) {
										echo "<li><strong><a href='communities.php?action=page&pid=" . $page['page_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $page['page_title'] ) . "</a></strong></li>";
										$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '" . $page['page_ID'] . "'";
										$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
										if ( count( $pages[$page['page_ID']] ) > 0 ) {
											echo "<ul>";
											foreach ( $pages[$page['page_ID']] as $page ) {
												echo "<li><strong><a href='communities.php?action=page&pid=" . $page['page_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $page['page_title'] ) . "</a></strong></li>";
												$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '" . $page['page_ID'] . "'";
												$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
												if ( count( $pages[$page['page_ID']] ) > 0 ) {
													echo "<ul>";
													foreach ( $pages[$page['page_ID']] as $page ) {
														echo "<li><strong><a href='communities.php?action=page&pid=" . $page['page_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $page['page_title'] ) . "</a></strong></li>";
														$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '" . $page['page_ID'] . "'";
														$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
														if ( count( $pages[$page['page_ID']] ) > 0 ) {
															echo "<ul>";
															foreach ( $pages[$page['page_ID']] as $page ) {
																echo "<li><strong><a href='communities.php?action=page&pid=" . $page['page_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $page['page_title'] ) . "</a></strong></li>";
																$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '" . $page['page_ID'] . "'";
																$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
																if ( count( $pages[$page['page_ID']] ) > 0 ) {
																	echo "<ul>";
																	foreach ( $pages[$page['page_ID']] as $page ) {
																		echo "<li><strong><a href='communities.php?action=page&pid=" . $page['page_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $page['page_title'] ) . "</a></strong></li>";
																		$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '" . $page['page_ID'] . "'";
																		$pages[$page['page_ID']] = $wpdb->get_results( $query, ARRAY_A );
																		if ( count( $pages[$page['page_ID']] ) > 0 ) {
																			echo "<ul>";
																			foreach ( $pages[$page['page_ID']] as $page ) {
																				echo "<li><strong><a href='communities.php?action=page&pid=" . $page['page_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'>" . stripslashes( $page['page_title'] ) . "</a></strong></li>";
																				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' AND page_parent_page_ID = '" . $page['page_ID'] . "'";
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
					<p><?php _e('There currently aren\'t any pages.'); ?></p>
                    <?php
				}
			}
		break;
		//---------------------------------------------------//
		case "page":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				$page_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = '" . $_GET['pid'] . "' ");
				if ( $page_details->page_parent_page_ID == '0' ) {
					$depth = 1;
					$page_title = '<a href="communities.php?action=page&pid=' . $page_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
				} else {
					$page_2_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = '" . $page_details->page_parent_page_ID . "' ");
					if ( $page_2_details->page_parent_page_ID == '0' ) {
						$depth = 2;
						$page_title = '<a href="communities.php?action=page&pid=' . $page_2_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
					} else {
						$page_3_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = '" . $page_2_details->page_parent_page_ID . "' ");
						if ( $page_3_details->page_parent_page_ID == '0' ) {
							$depth = 3;
							$page_title = '<a href="communities.php?action=page&pid=' . $page_3_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_3_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_2_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
						} else {
							$page_4_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = '" . $page_3_details->page_parent_page_ID . "' ");
							if ( $page_4_details->page_parent_page_ID == '0' ) {
								$depth = 4;
								$page_title = '<a href="communities.php?action=page&pid=' . $page_4_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_4_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_3_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_3_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_2_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
							} else {
								$page_5_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = '" . $page_4_details->page_parent_page_ID . "' ");
								if ( $page_5_details->page_parent_page_ID == '0' ) {
									$depth = 5;
									$page_title = '<a href="communities.php?action=page&pid=' . $page_5_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_5_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_4_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_4_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_3_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_3_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_2_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
								} else {
									$page_6_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = '" . $page_5_details->page_parent_page_ID . "' ");
									$depth = 6;
									$page_title = '<a href="communities.php?action=page&pid=' . $page_6_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_6_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_5_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_5_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_4_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_4_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_3_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_3_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_2_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_2_details->page_title . '</a>' . ' &raquo; ' . '<a href="communities.php?action=page&pid=' . $page_details->page_ID . '&cid=' . $_GET['cid'] . '" style="text-decoration:none;">' . $page_details->page_title . '</a>';
								}
							}
						}
					}
				}
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=wiki&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Wiki') ?></a> &raquo; <?php echo $page_title; ?></h2>
                <?php
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				if (  $member_moderator == '1' || is_site_admin() ) {
					?>
					<h3><?php _e('Manage') ?></h3>
					<ul>
					<li><strong><?php _e('Actions'); ?>:</strong>
                    <?php
					if ( $depth < 6 ) {
						?>
						<a href="communities.php?action=new_page&ppid=<?php echo $_GET['pid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('New Page'); ?></a> |
						<?php
                    }
                    ?>
					<a href="communities.php?action=edit_page&pid=<?php echo $_GET['pid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Edit Page'); ?></a> |
					<a href="communities.php?action=remove_page&pid=<?php echo $_GET['pid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Remove Page'); ?></a>
					</li>
					</ul>
                    <h3><?php _e('Page') ?></h3>
					<?php
				}
				?>
                <p><?php echo $page_details->page_content; ?></p>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "new_page":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if (  $member_moderator == '1' || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=wiki&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Wiki') ?></a> &raquo; <?php _e('New Page'); ?></h2>
                <form name="new_page" method="POST" action="communities.php?action=new_page_process&ppid=<?php echo $_GET['ppid']; ?>&cid=<?php echo $_GET['cid']; ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Title') ?></th>
                    <td><input type="text" name="page_title" id="page_title" style="width: 95%" value="<?php echo $_POST['page_title']; ?>" />
                    <br />
                    <?php _e('Required') ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Content') ?></th>
                    <td><textarea name="page_content" id="page_content" style="width: 95%" rows="10"><?php echo $_POST['page_content']; ?></textarea>
                    <br />
                    <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                <input type="submit" name="Submit" value="<?php _e('Publish') ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "new_page_process":
			if ( isset( $_POST['Cancel'] ) ) {
				if ( $_GET['ppid'] == '0' ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=wiki&cid=" . $_GET['cid'] . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=page&pid=" . $_GET['ppid'] . "&cid=" . $_GET['cid'] . "';
					</script>
					";
				}
			} else {
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				if (  $member_moderator == '1' || is_site_admin() ) {
					if ( empty( $_POST['page_title'] ) || empty( $_POST['page_content'] ) ) {
						$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
						?>
						<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=wiki&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Wiki') ?></a> &raquo; <?php _e('New Page'); ?></h2>
                        <p><?php _e('Please fill in all fields.'); ?></p>
						<form name="new_page" method="POST" action="communities.php?action=new_page_process&ppid=<?php echo $_GET['ppid']; ?>&cid=<?php echo $_GET['cid']; ?>">
							<table class="form-table">
							<tr valign="top">
							<th scope="row"><?php _e('Title') ?></th>
							<td><input type="text" name="page_title" id="page_title" style="width: 95%" value="<?php echo $_POST['page_title']; ?>" />
							<br />
							<?php _e('Required') ?></td>
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('Content') ?></th>
							<td><textarea name="page_content" id="page_content" style="width: 95%" rows="10"><?php echo $_POST['page_content']; ?></textarea>
							<br />
							<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
							</tr>
							</table>
						<p class="submit">
						<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
						<input type="submit" name="Submit" value="<?php _e('Publish') ?>" />
						</p>
						</form>
						<?php
					} else {
						$page_ID = communities_add_page($_GET['cid'], $_GET['ppid'], $_POST['page_title'], $_POST['page_content']);
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=page&pid=" . $page_ID . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Page published.')) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "edit_page":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			$page_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_ID = '" . $_GET['pid'] . "' ");
			if (  $member_moderator == '1' || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=wiki&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Wiki') ?></a> &raquo; <?php _e('Edit Page'); ?></h2>
                <form name="new_page" method="POST" action="communities.php?action=edit_page_process&pid=<?php echo $_GET['pid']; ?>&cid=<?php echo $_GET['cid']; ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Title') ?></th>
                    <td><input type="text" name="page_title" id="page_title" style="width: 95%" value="<?php echo stripslashes( $page_details->page_title ); ?>" />
                    <br />
                    <?php _e('Required') ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Content') ?></th>
                    <td><textarea name="page_content" id="page_content" style="width: 95%" rows="10"><?php echo stripslashes( $page_details->page_content ); ?></textarea>
                    <br />
                    <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "edit_page_process":
			if ( isset( $_POST['Cancel'] ) ) {
				if ( $_GET['ppid'] == '0' ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=wiki&cid=" . $_GET['cid'] . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?action=page&pid=" . $_GET['pid'] . "&cid=" . $_GET['cid'] . "';
					</script>
					";
				}
			} else {
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				if (  $member_moderator == '1' || is_site_admin() ) {
					if ( empty( $_POST['page_title'] ) || empty( $_POST['page_content'] ) ) {
						$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
						?>
						<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=wiki&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Wiki') ?></a> &raquo; <?php _e('Edit Page'); ?></h2>
                        <p><?php _e('Please fill in all fields.'); ?></p>
						<form name="new_page" method="POST" action="communities.php?action=edit_page_process&pid=<?php echo $_GET['ppid']; ?>&cid=<?php echo $_GET['cid']; ?>">
							<table class="form-table">
							<tr valign="top">
							<th scope="row"><?php _e('Title') ?></th>
							<td><input type="text" name="page_title" id="page_title" style="width: 95%" value="<?php echo $_POST['page_title']; ?>" />
							<br />
							<?php _e('Required') ?></td>
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('Content') ?></th>
							<td><textarea name="page_content" id="page_content" style="width: 95%" rows="10"><?php echo $_POST['page_content']; ?></textarea>
							<br />
							<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
							</tr>
							</table>
						<p class="submit">
						<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
						<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
						</p>
						</form>
						<?php
					} else {
						communities_update_page($_GET['pid'], $_POST['page_title'], $_POST['page_content']);
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=page&pid=" . $_GET['pid'] . "&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_page":
			$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if (  $member_moderator == '1' || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=wiki&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Wiki') ?></a> &raquo; <?php _e('Remove Page'); ?></h2>
                <form name="leave_community" method="POST" action="communities.php?action=remove_page_process&pid=<?php echo $_GET['pid']; ?>&cid=<?php echo $_GET['cid']; ?>">
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Are you sure?') ?></th>
                    <td><select name="remove_page">
                        <option value="no" selected="selected" ><?php _e('No'); ?></option>
                        <option value="yes" ><?php _e('Yes'); ?></option>
                    </select>
                    </td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                <input type="submit" name="Submit" value="<?php _e('Continue') ?>" />
                </p>
                </form>
                <?php
			}
		break;
		//---------------------------------------------------//
		case "remove_page_process":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?action=page&pid=" . $_GET['pid'] . "&cid=" . $_GET['cid'] . "';
				</script>
				";
			} else {
				$member_moderator = $wpdb->get_var("SELECT member_moderator FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
				if (  $member_moderator == '1' || is_site_admin() ) {
					if ( $_POST['remove_page'] == 'yes' ) {
						communities_delete_page($_GET['pid']);
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=wiki&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Page removed.')) . "';
						</script>
						";
					} else {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?action=page&pid=" . $_GET['pid'] . "&cid=" . $_GET['cid'] . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "dashboard":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Dashboard') ?></a></h2>
                <div id="dashboard-widgets-wrap">
					<div id='dashboard-widgets' class='metabox-holder'>

						<!---<div id='side-info-column' class='inner-sidebar'>--->
                        <div id='normal-sortables' class='meta-box-sortables'>
							<div class='postbox-container' style='width:49%;'>
                            <div id='side-sortables' class='meta-box-sortables'>
                            <div id="dashboard_quick_press" class="postbox " >
                               <h3 class='hndle'><span><?php _e('Recent Wiki Pages'); ?></span> (<small><a href="communities.php?action=wiki&cid=<?php echo $_GET['cid']; ?>"><?php _e('See All'); ?></a></small>)</h3>
                                <div class="inside">
								<?php
                                $query = "SELECT * FROM " . $wpdb->base_prefix . "communities_pages WHERE page_community_ID = '" . $_GET['cid'] . "' ORDER BY page_ID DESC LIMIT 9";
                                $pages = $wpdb->get_results( $query, ARRAY_A );
                                if ( count( $pages ) > 0 ) {
                                    echo "<ul>";
                                    foreach ( $pages as $page ) {
                                        ?>
                                        <li><strong><a href="communities.php?action=page&pid=<?php echo $page['page_ID']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $page['page_title'] ); ?></a></strong></li>
                                        <?php
                                    }
                                    echo "</ul>";
                                } else {
                                    ?>
                                    <p><center><?php _e('No pages to display.'); ?></center></p>
                                    <?php
                                }
                                ?>
                                </div>
                            </div>

                            <div id="dashboard_quick_press" class="postbox " >
                               <h3 class='hndle'><span><?php _e('Recent News'); ?></span> (<small><a style="text-decoration:none;" href="communities.php?action=news&cid=<?php echo $_GET['cid']; ?>"><?php _e('See All'); ?></a></small>)</h3>
                                <div class="inside">
								<?php
                                $query = "SELECT * FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = '" . $_GET['cid'] . "' ORDER BY news_item_ID DESC LIMIT 9";
                                $news_items = $wpdb->get_results( $query, ARRAY_A );
                                if ( count( $news_items ) > 0 ) {
                                    echo "<ul>";
                                    foreach ( $news_items as $news_item ) {
                                        ?>
                                        <li><strong><a href="communities.php?action=news_item&niid=<?php echo $news_item['news_item_ID']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $news_item['news_item_title'] ); ?></a></strong></li>
                                        <?php
                                    }
                                    echo "</ul>";
                                } else {
                                    ?>
                                    <p><center><?php _e('No news to display.'); ?></center></p>
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

                            <!---<div id='normal-sortables' class='meta-box-sortables'>--->
                            <div class='postbox-container' style='width:49%;'>
                            <div id='side-sortables' class='meta-box-sortables'>
                            <div id="dashboard_right_now" class="postbox " >
                               <h3 class='hndle'><span><?php _e('Recent Message Board Topics'); ?></span> (<small><a style="text-decoration:none;" href="communities.php?action=message_board&cid=<?php echo $_GET['cid']; ?>"><?php _e('See All'); ?></a></small>)</h3>
                                <div class="inside">
								<?php
                                $query = "SELECT * FROM " . $wpdb->base_prefix . "communities_topics WHERE topic_community_ID = '" . $_GET['cid'] . "' AND topic_closed = '0' ORDER BY topic_ID DESC LIMIT 9";
                                $topics = $wpdb->get_results( $query, ARRAY_A );
                                if ( count( $topics ) > 0 ) {
                                    echo "<ul>";
                                    foreach ( $topics as $topic ) {
                                        ?>
                                        <li><strong><a href="communities.php?action=topic&tid=<?php echo $topic['topic_ID']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $topic['topic_title'] ); ?></a></strong></li>
                                        <?php
                                    }
                                    echo "</ul>";
                                } else {
                                    ?>
                                    <p><center><?php _e('No topics to display.'); ?></center></p>
                                    <?php
                                }
                                ?>

                                </div>
                            </div>

                            <div id="dashboard_right_now" class="postbox " >
                               <h3 class='hndle'><span><?php _e('Recent Members'); ?></span> (<small><a style="text-decoration:none;" href="communities.php?action=member_list&cid=<?php echo $_GET['cid']; ?>"><?php _e('See All'); ?></a></small>)</h3>
                                <div class="inside">
								<?php
                                $query = "SELECT * FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID != '" . $user_ID . "' ORDER BY member_ID DESC LIMIT 9";
                                $members = $wpdb->get_results( $query, ARRAY_A );
                                if ( count( $members ) > 0 ) {
                                    echo "<ul>";
                                    foreach ( $members as $member ) {
                                        $member_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $member['member_user_ID'] . "'");
                                        $member_primary_blog = get_active_blog_for_user( $member['member_user_ID'] );
                                        ?>
                                        <li><strong><?php echo $member_details->display_name; ?></strong> (<a style="text-decoration:none;" href="admin.php?page=messaging_new&message_to=<?php echo $member_details->user_login; ?>" style="text-decoration:none;"><?php _e('Send Message'); ?></a> | <a href="http://<?php echo $member_primary_blog->domain . $member_primary_blog->path; ?>" style="text-decoration:none;"><?php _e('View Blog'); ?></a>)</li>
                                        <?php
                                    }
                                    echo "</ul>";
                                } else {
                                    ?>
                                    <p><center><?php _e('No recent members.'); ?></center></p>
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
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=news&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('News') ?></a></h2>
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
                $query = "SELECT * FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = '" . $_GET['cid'] . "'";
                $query .= " ORDER BY news_item_stamp DESC";
                $query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
                $news_items = $wpdb->get_results( $query, ARRAY_A );
                if( count( $news_items ) < $num ) {
                    $next = false;
                } else {
                    $next = true;
                }
                if (count( $news_items ) > 0){
                    $news_item_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = '" . $_GET['cid'] . "'");
                    if ($news_item_count > 30){
                        ?>
                        <table><td>
                        <fieldset>
                        <?php

                        //$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

                        if( $start == 0 ) {
                            echo __('Previous Page');
                        } elseif( $start <= 30 ) {
                            echo '<a href="communities.php?page=manage-communities&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page') . '</a>';
                        } else {
                            echo '<a href="communities.php?page=manage-communities&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page') . '</a>';
                        }
                        if ( $next ) {
                            echo '&nbsp;||&nbsp;<a href="communities.php?page=manage-communities&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page') . '</a>';
                        } else {
                            echo '&nbsp;||&nbsp;' . __('Next Page');
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
                    <th scope='col'>" . __('Title') . "</th>
                    <th scope='col'>" . __('Date/Time') . "</th>
                    </tr></thead>
                    <tbody id='the-list'>
                    ";
                    //=========================================================//
                        $class = ('alternate' == $class) ? '' : 'alternate';
                        $date_format = get_option('date_format');
                        $time_format = get_option('time_format');
                        foreach ($news_items as $news_item){
                        //=========================================================//
                        echo "<tr class='" . $class . "'>";
                        echo "<td valign='top'><a href='communities.php?action=news_item&niid=" . $news_item['news_item_ID'] . "&cid=" . $_GET['cid'] . "' style='text-decoration:none;'><strong>" . stripslashes( $news_item['news_item_title'] ) . "</strong></a></td>";
                        echo "<td valign='top'>" . date( $date_format . ' ' . $time_format, $news_item['news_item_stamp']) . "</td>";
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
                    <p><?php _e('There currently aren\'t any news items. Please check back later.') ?></p>
                    <?php
                }
			}
		break;
		//---------------------------------------------------//
		case "news_item":
			$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $_GET['cid'] . "' AND member_user_ID = '" . $user_ID . "'");
			if ( $member_count > 0 || is_site_admin() ) {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				$news_item_title = $wpdb->get_var("SELECT news_item_title FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = '" . $_GET['niid'] . "'");
				$news_item_content = $wpdb->get_var("SELECT news_item_content FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = '" . $_GET['niid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?action=news&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('News') ?></a> &raquo; <a href="communities.php?action=news_item&niid=<?php echo $_GET['niid']; ?>&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $news_item_title ); ?></a></h2>
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

function communities_manage_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			?>
			<h2><?php _e('Communities') ?></h2>
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
			if ( is_site_admin() ) {
				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities";
			} else {
				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "'";
			}
			$query .= " ORDER BY community_name ASC";
			$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
			$communities = $wpdb->get_results( $query, ARRAY_A );
			if( count( $communities ) < $num ) {
				$next = false;
			} else {
				$next = true;
			}
			if (count($communities) > 0){
				$community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "'");
				if ($community_count > 30){
					?>
					<table><td>
					<fieldset>
					<?php

					//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

					if( $start == 0 ) {
						echo __('Previous Page');
					} elseif( $start <= 30 ) {
						echo '<a href="communities.php?page=manage-communities&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page') . '</a>';
					} else {
						echo '<a href="communities.php?page=manage-communities&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page') . '</a>';
					}
					if ( $next ) {
						echo '&nbsp;||&nbsp;<a href="communities.php?page=manage-communities&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page') . '</a>';
					} else {
						echo '&nbsp;||&nbsp;' . __('Next Page');
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
				<th scope='col'>" . __('Name') . "</th>
				<th scope='col'>" . __('Description') . "</th>
				<th scope='col'>" . __('Private') . "*</th>
				<th scope='col'>" . __('Actions') . "</th>
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
					$class = ('alternate' == $class) ? '' : 'alternate';
					foreach ($communities as $community){
					//=========================================================//
					echo "<tr class='" . $class . "'>";
					echo "<td valign='top'><a href='communities.php?action=dashboard&cid=" . $community['community_ID'] . "' style='text-decoration:none;'><strong>" . stripslashes( $community['community_name'] ) . "</strong></a></td>";
					echo "<td valign='top'>" . stripslashes( $community['community_description'] ) . "</td>";
					if ( $community['community_private'] == '1' ) {
						$community_private = __('Yes') . ' (' . __('Code') . ': ' . substr(md5($community['community_ID'] . '1234'),0,5) . ')';
					} else {
						$community_private = __('No');
					}
					echo "<td valign='top'>" . $community_private . "</td>";
					$community_members_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $community['community_ID'] . "'");
					$community_members_count = $community_members_count - 1;
					if ( $community_members_count < 0 ) {
						$community_members_count = 0;
					}
					if ( $community_members_count > 0 ) {
						echo "<td valign='top'><a href='communities.php?page=manage-communities&action=member_list&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Members') . " (" . $community_members_count . ")</a></td>";
					} else {
						echo "<td valign='top'>" . __('Members') . " (" . $community_members_count . ")</td>";
					}
					echo "<td valign='top'><a href='communities.php?action=message_board&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Message Board') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?action=wiki&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Wiki') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?action=news&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('News') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?page=manage-communities&action=manage_news&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Manage News') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?page=manage-communities&action=edit_community&cid=" . $community['community_ID'] . "' rel='permalink' class='edit'>" . __('Edit') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?page=manage-communities&action=remove_community&cid=" . $community['community_ID'] . "' rel='permalink' class='delete'>" . __('Remove') . "</a></td>";
					echo "</tr>";
					$class = ('alternate' == $class) ? '' : 'alternate';
					//=========================================================//
					}
				//=========================================================//
				?>
				</tbody></table>
                <p>*<?php _e('Users must enter the code to join private communities.'); ?></p>
				<?php
			}

			$owner_community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "'");
			?>
            <br />
			<h2><?php _e('Create Community') ?></h2>
			<?php
			if ( $owner_community_count > 44 ) {
				?>
				<p><?php _e('Sorry, you can only create a maximum of 45 communities.') ?></p>
				<?php
			} else {
				?>
				<p><?php _e('You can create up to 45 communities of your own using the form below.') ?></p>
				<form name="create_community" method="POST" action="communities.php?action=create_community">
					<table class="form-table">
					<tr valign="top">
					<th scope="row"><?php _e('Name') ?></th>
					<td><input type="text" name="community_name" id="community_name" style="width: 95%" value="<?php echo $_POST['community_name']; ?>" />
					<br />
					<?php _e('Required') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Description') ?></th>
					<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo $_POST['community_description']; ?>" />
					<br />
					<?php _e('Required') ?></td>
					</tr>
					<tr valign="top">
					<th scope="row"><?php _e('Private') ?></th>
					<td><select name="community_private">
						<option value="0" <?php if ($_POST['community_private'] == '0' || $_POST['community_private'] == '') echo 'selected="selected"'; ?>><?php _e('No'); ?></option>
						<option value="1" <?php if ($_POST['community_private'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes'); ?></option>
					</select>
					<?php _e('Users have to enter a code to join private communities') ?></td>
					</tr>
					</table>
				<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Create') ?>" />
				</p>
				</form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "edit_community":
			$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			$community_description = $wpdb->get_var("SELECT community_description FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			$community_private = $wpdb->get_var("SELECT community_private FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			?>
			<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Edit Community') ?></h2>
			<form name="edit_community" method="POST" action="communities.php?page=manage-communities&action=edit_community_process">
	            <input type="hidden" name="cid" value="<?php echo $_GET['cid']; ?>" />
				<table class="form-table">
				<tr valign="top">
				<th scope="row"><?php _e('Description') ?></th>
				<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo stripslashes( $community_description ); ?>" />
				<br />
				<?php _e('Required') ?></td>
				</tr>
				<tr valign="top">
				<th scope="row"><?php _e('Private') ?></th>
				<td><select name="community_private">
					<option value="0" <?php if ($community_private == '0' || $community_private == '') echo 'selected="selected"'; ?>><?php _e('No'); ?></option>
					<option value="1" <?php if ($community_private == '1') echo 'selected="selected"'; ?>><?php _e('Yes'); ?></option>
				</select>
				<?php _e('Users have to enter a code to join private communities') ?></td>
				</tr>
				</table>
			<p class="submit">
			<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
			<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			</p>
			</form>
			<?php
		break;
		//---------------------------------------------------//
		case "edit_community_process":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=manage-communities';
				</script>
				";
			} else {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Edit Community') ?></h2>
				<?php
				if ( empty( $_POST['community_description'] ) ) {
					?>
					<p><?php _e('Please fill in all fields.') ?></p>
					<form name="edit_community" method="POST" action="communities.php?page=manage-communities&action=edit_community_process">
                    	<input type="hidden" name="cid" value="<?php echo $_POST['cid']; ?>" />
						<table class="form-table">
						<tr valign="top">
						<th scope="row"><?php _e('Description') ?></th>
						<td><input type="text" name="community_description" id="community_description" style="width: 95%" maxlength="250" value="<?php echo $_POST['community_description']; ?>" />
						<br />
						<?php _e('Required') ?></td>
						</tr>
						<tr valign="top">
						<th scope="row"><?php _e('Private') ?></th>
						<td><select name="community_private">
							<option value="0" <?php if ($_POST['community_private'] == '0' || $_POST['community_private'] == '') echo 'community_private"'; ?>><?php _e('No'); ?></option>
							<option value="1" <?php if ($_POST['community_private'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes'); ?></option>
						</select>
						<?php _e('Users have to enter a code to join private communities') ?></td>
						</tr>
						</table>
					<p class="submit">
					<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
					<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
					</p>
					</form>
					<?php
				} else {
					communities_update_community($user_ID, $_POST['cid'], $_POST['community_description'], $_POST['community_private']);
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=manage-communities&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_community":
			$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			?>
			<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Remove') ?></h2>
            <form name="edit_community" method="POST" action="communities.php?page=manage-communities&action=remove_community_process">
                <input type="hidden" name="cid" value="<?php echo $_GET['cid']; ?>" />
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Are you sure?') ?></th>
                <td><select name="remove_community">
                    <option value="no" selected="selected" ><?php _e('No'); ?></option>
                    <option value="yes" ><?php _e('Yes'); ?></option>
                </select>
                </td>
                </tr>
                </table>
            <p class="submit">
            <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
            <input type="submit" name="Submit" value="<?php _e('Continue') ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
		case "remove_community_process":
			if ( isset( $_POST['Cancel'] ) || $_POST['remove_community'] == 'no' ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=manage-communities';
				</script>
				";
			} else {
				$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_POST['cid'] . "'");
				if ( $community_owner_user_ID == $user_ID || is_site_admin() ) {
					communities_remove_community($_POST['cid']);
					$owner_community_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities WHERE community_owner_user_ID = '" . $user_ID . "'");
					if ( $owner_community_count > 0 || is_site_admin() ) {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?page=manage-communities&updated=true&updatedmsg=" . urlencode(__('Community removed.')) . "';
						</script>
						";
					} else {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?updated=true&updatedmsg=" . urlencode(__('Community removed.')) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "member_list":
			$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			?>
			<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <a href="communities.php?page=manage-communities&action=member_list&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php _e('Members') ?></a></h2>
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
			$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_members WHERE member_user_ID != '" . $user_ID . "' AND community_ID = '" . $_GET['cid'] . "'";
			$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
			$members = $wpdb->get_results( $query, ARRAY_A );
			if( count( $members ) < $num ) {
				$next = false;
			} else {
				$next = true;
			}
			if (count($members) > 0){
				$members_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE member_user_ID != '" . $user_ID . "' AND community_ID = '" . $_GET['cid'] . "'");
				if ($members_count > 30){
					?>
					<br />
					<table><td>
					<fieldset>
					<?php

					//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

					if( $start == 0 ) {
						echo __('Previous Page');
					} elseif( $start <= 30 ) {
						echo '<a href="communities.php?page=manage-communities&action=member_list&cid=' . $_GET['cid'] . '&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page') . '</a>';
					} else {
						echo '<a href="communities.php?page=manage-communities&action=member_list&cid=' . $_GET['cid'] . '&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page') . '</a>';
					}
					if ( $next ) {
						echo '&nbsp;||&nbsp;<a href="communities.php?page=manage-communities&action=member_list&cid=' . $_GET['cid'] . '&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page') . '</a>';
					} else {
						echo '&nbsp;||&nbsp;' . __('Next Page');
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
				<th scope='col'>" . __('Name') . "</th>
				<th scope='col'>" . __('Avatar') . "</th>
				<th scope='col'>" . __('Type') . "</th>
				<th scope='col'>" . __('Actions') . "</th>
				<th scope='col'></th>
				<th scope='col'></th>
				</tr></thead>
				<tbody id='the-list'>
				";
				//=========================================================//
					$class = ('alternate' == $class) ? '' : 'alternate';
					foreach ($members as $member){
					//=========================================================//
					echo "<tr class='" . $class . "'>";
					$member_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $member['member_user_ID'] . "'");
					echo "<td valign='top'><strong>" . $member_details->display_name . "</strong></td>";
					echo "<td valign='top'><img src='http://" . $current_site->domain . $current_site->path . "avatar/user-" . $member['member_user_ID'] . "-32.png' /></td>";
						$member_type = __('Member');
					if ( $member['member_moderator'] == '1' ) {
						$member_type = __('Moderator');
					}
					echo "<td valign='top'>" . $member_type . "</td>";
					$member_primary_blog = get_active_blog_for_user( $member['member_user_ID'] );
					echo "<td valign='top'><a href='http://" . $member_primary_blog->domain . $member_primary_blog->path . "' rel='permalink' class='edit'>" . __('Visit Blog') . "</a></td>";
					echo "<td valign='top'><a href='admin.php?page=messaging_new&message_to=" . $member_details->user_login . "' rel='permalink' class='edit'>" . __('Send Message') . "</a></td>";
					if ( $member['member_moderator'] == '1' ) {
						echo "<td valign='top'><a href='communities.php?page=manage-communities&action=remove_moderator&uid=" . $member['member_user_ID'] . "&cid=" . $_GET['cid'] . "&num=" . $_GET['num'] . "&start=" . $_GET['start'] . "' rel='permalink' class='delete'>" . __('Remove Moderator Privelege') . "</a></td>";
					} else {
						echo "<td valign='top'><a href='communities.php?page=manage-communities&action=add_moderator&uid=" . $member['member_user_ID'] . "&cid=" . $_GET['cid'] . "&num=" . $_GET['num'] . "&start=" . $_GET['start'] . "' rel='permalink' class='edit'>" . __('Add Moderator Privelege') . "</a></td>";
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
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			communities_add_moderator_privilege($_GET['uid'], $_GET['cid']);
			if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=manage-communities&action=member_list&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Moderator privelege added.')) . "';
				</script>
				";
			} else {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=manage-communities&action=member_list&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Moderator privelege added.')) . "';
				</script>
				";
			}
		break;
		//---------------------------------------------------//
		case "remove_moderator":
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			communities_remove_moderator_privilege($_GET['uid'], $_GET['cid']);
			if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=manage-communities&action=member_list&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Moderator privelege removed.')) . "';
				</script>
				";
			} else {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=manage-communities&action=member_list&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Moderator privelege removed.')) . "';
				</script>
				";
			}
		break;
		//---------------------------------------------------//
		case "manage_news":
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			?>
			<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Manage News') ?></h2>
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
				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = '" . $_GET['cid'] . "'";
			$query .= " ORDER BY news_item_stamp DESC";
			$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
			$news_items = $wpdb->get_results( $query, ARRAY_A );
			if( count( $news_items ) < $num ) {
				$next = false;
			} else {
				$next = true;
			}
			if (count( $news_items ) > 0){
				$news_item_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_community_ID = '" . $_GET['cid'] . "'");
				if ($news_item_count > 30){
					?>
					<table><td>
					<fieldset>
					<?php

					//$order_sort = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ];

					if( $start == 0 ) {
						echo __('Previous Page');
					} elseif( $start <= 30 ) {
						echo '<a href="communities.php?page=manage-communities&start=0&' . $order_sort . ' " style="text-decoration:none;" >' . __('Previous Page') . '</a>';
					} else {
						echo '<a href="communities.php?page=manage-communities&start=' . ( $start - $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Previous Page') . '</a>';
					}
					if ( $next ) {
						echo '&nbsp;||&nbsp;<a href="communities.php?page=manage-communities&start=' . ( $start + $num ) . '&' . $order_sort . '" style="text-decoration:none;" >' . __('Next Page') . '</a>';
					} else {
						echo '&nbsp;||&nbsp;' . __('Next Page');
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
				<th scope='col'>" . __('Title') . "</th>
				<th scope='col'>" . __('Date/Time') . "</th>
				<th scope='col'>" . __('Actions') . "</th>
				<th scope='col'></th>
				</tr></thead>
				<tbody id='the-list'>
				";
				//=========================================================//
					$class = ('alternate' == $class) ? '' : 'alternate';
					$date_format = get_option('date_format');
					$time_format = get_option('time_format');
					foreach ($news_items as $news_item){
					//=========================================================//
					echo "<tr class='" . $class . "'>";
					echo "<td valign='top'><strong>" . stripslashes( $news_item['news_item_title'] ) . "</strong></td>";
					echo "<td valign='top'>" . date( $date_format . ' ' . $time_format, $news_item['news_item_stamp']) . "</td>";
					echo "<td valign='top'><a href='communities.php?page=manage-communities&action=edit_news_item&niid=" . $news_item['news_item_ID'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "' rel='permalink' class='edit'>" . __('Edit') . "</a></td>";
					echo "<td valign='top'><a href='communities.php?page=manage-communities&action=remove_news_item&niid=" . $news_item['news_item_ID'] . "&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "' rel='permalink' class='delete'>" . __('Remove') . "</a></td>";
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
	            <p><?php _e('There currently aren\'t any news items for this community. Use the form below to add news!') ?></p>
                <?php
			}

			?>
            <br />
			<h2><?php _e('New News Item') ?></h2>
            <form name="new_news_item" method="POST" action="communities.php?page=manage-communities&action=new_news_item&cid=<?php echo $_GET['cid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Title') ?></th>
                <td><input type="text" name="news_item_title" id="news_item_title" style="width: 95%" value="<?php echo $_POST['news_item_title']; ?>" />
                <br />
                <?php _e('Required') ?></td>
                </tr>
                <tr valign="top">
                <th scope="row"><?php _e('Content') ?></th>
                <td><textarea name="news_item_content" id="news_item_content" style="width: 95%" rows="10"><?php echo $_POST['news_item_content']; ?></textarea>
                <br />
                <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
                </tr>
                </table>
            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Publish') ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
		case "new_news_item":
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			if ( isset( $_POST['Cancel'] ) ) {
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "';
					</script>
					";
				}
			} else {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('New News Item') ?></h2>
				<?php
				if ( empty( $_POST['news_item_title'] ) || empty( $_POST['news_item_content'] ) ) {
					?>
					<p><?php _e('Please fill in all fields.'); ?></p>
                    <form name="new_news_item" method="POST" action="communities.php?page=manage-communities&action=new_news_item&cid=<?php echo $_GET['cid']; ?>start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                        <table class="form-table">
                        <tr valign="top">
                        <th scope="row"><?php _e('Title') ?></th>
                        <td><input type="text" name="news_item_title" id="news_item_title" style="width: 95%" value="<?php echo $_POST['news_item_title']; ?>" />
                        <br />
                        <?php _e('Required') ?></td>
                        </tr>
                        <tr valign="top">
                        <th scope="row"><?php _e('Content') ?></th>
                        <td><textarea name="news_item_content" id="news_item_content" style="width: 95%" rows="10"><?php echo $_POST['news_item_content']; ?></textarea>
                        <br />
                        <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
                        </tr>
                        </table>
                    <p class="submit">
                    <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                    <input type="submit" name="Submit" value="<?php _e('Publish') ?>" />
                    </p>
                    </form>
					<?php

				} else {
					$news_item_ID = communities_add_news_item($_GET['cid'], $_POST['news_item_title'], $_POST['news_item_content']);
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('News item published.')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
		case "edit_news_item":
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			$news_item_title = $wpdb->get_var("SELECT news_item_title FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = '" . $_GET['niid'] . "'");
			$news_item_content = $wpdb->get_var("SELECT news_item_content FROM " . $wpdb->base_prefix . "communities_news_items WHERE news_item_ID = '" . $_GET['niid'] . "'");
			?>
			<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Edit News Item') ?></h2>
			<form name="edit_news_item" method="POST" action="communities.php?page=manage-communities&action=edit_news_item_process&cid=<?php echo $_GET['cid']; ?>&niid=<?php echo $_GET['niid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
				<table class="form-table">
				<tr valign="top">
				<th scope="row"><?php _e('Title') ?></th>
				<td><input type="text" name="news_item_title" id="news_item_title" style="width: 95%" value="<?php echo stripslashes( $news_item_title ); ?>" />
				<br />
				<?php _e('Required') ?></td>
				</tr>
				<tr valign="top">
				<th scope="row"><?php _e('Content') ?></th>
				<td><textarea name="news_item_content" id="news_item_content" style="width: 95%" rows="10"><?php echo stripslashes( $news_item_content ); ?></textarea>
				<br />
				<?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
				</tr>
				</table>
			<p class="submit">
			<input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
			<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			</p>
			</form>
			<?php
		break;
		//---------------------------------------------------//
		case "edit_news_item_process":
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			if ( isset( $_POST['Cancel'] ) ) {
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "';
					</script>
					";
				}
			} else {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Edit News Item') ?></h2>
				<?php
				if ( empty( $_POST['news_item_title'] ) || empty( $_POST['news_item_content'] ) ) {
					?>
					<p><?php _e('Please fill in all fields.'); ?></p>
                    <form name="edit_news_item" method="POST" action="communities.php?page=manage-communities&action=edit_news_item_process&cid=<?php echo $_GET['cid']; ?>&niid=<?php echo $_GET['niid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                        <table class="form-table">
                        <tr valign="top">
                        <th scope="row"><?php _e('Title') ?></th>
                        <td><input type="text" name="news_item_title" id="news_item_title" style="width: 95%" value="<?php echo $_POST['news_item_title']; ?>" />
                        <br />
                        <?php _e('Required') ?></td>
                        </tr>
                        <tr valign="top">
                        <th scope="row"><?php _e('Content') ?></th>
                        <td><textarea name="news_item_content" id="news_item_content" style="width: 95%" rows="10"><?php echo $_POST['news_item_content']; ?></textarea>
                        <br />
                        <?php _e('Required - Some tags allowed: <code>a p ul li br strong img</code>') ?></td>
                        </tr>
                        </table>
                    <p class="submit">
                    <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                    <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
                    </p>
                    </form>
					<?php

				} else {
					communities_update_news_item($_GET['niid'], $_POST['news_item_title'], $_POST['news_item_content']);
					if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
						</script>
						";
					} else {
						echo "
						<SCRIPT LANGUAGE='JavaScript'>
						window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
						</script>
						";
					}
				}
			}
		break;
		//---------------------------------------------------//
		case "remove_news_item":
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			?>
			<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Remove News Item') ?></h2>
            <form name="remove_news_item" method="POST" action="communities.php?page=manage-communities&action=remove_news_item_process&cid=<?php echo $_GET['cid']; ?>&niid=<?php echo $_GET['niid']; ?>&start=<?php echo $_GET['start']; ?>&num=<?php echo $_GET['num']; ?>">
                <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Are you sure?') ?></th>
                <td><select name="remove_news_item">
                    <option value="no" selected="selected" ><?php _e('No'); ?></option>
                    <option value="yes" ><?php _e('Yes'); ?></option>
                </select>
                </td>
                </tr>
                </table>
            <p class="submit">
            <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
            <input type="submit" name="Submit" value="<?php _e('Continue') ?>" />
            </p>
            </form>
            <?php
		break;
		//---------------------------------------------------//
		case "remove_news_item_process":
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID != $user_ID && !is_site_admin() ) {
				die('Nice try');
			}
			if ( isset( $_POST['Cancel'] ) || $_POST['remove_news_item'] == 'no' ) {
				if ( !empty( $_GET['start'] ) || !empty( $_GET['num'] ) ) {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "&start=" . $_GET['start'] . "&num=" . $_GET['num'] . "';
					</script>
					";
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "';
					</script>
					";
				}
			} else {
				communities_delete_news_item($_GET['niid']);
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=manage-communities&action=manage_news&cid=" . $_GET['cid'] . "&updated=true&updatedmsg=" . urlencode(__('News item removed.')) . "';
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
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site;

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			$search_terms = $_POST['search_terms'];
			if ($search_terms == ''){
				$search_terms = rawurldecode($_GET['search_terms']);
			}
			?>
            <form id="posts-filter" action="communities.php?page=find-communities" method="post">
            <h2><?php _e('Find Communities') ?>&nbsp;&nbsp;<em style="font-size:14px;"><?php _e("Searches community names and descriptions") ?></em></h2>
            <p id="post-search">
                <input id="post-search-input" name="search_terms" value="<?php echo $search_terms; ?>" type="text">
                <input value="<?php _e('Search') ?>" class="button" type="submit">
            </p>
            </form>
            <?php
			if ($search_terms != ''){
				$query = "SELECT * FROM " . $wpdb->base_prefix . "communities
					WHERE (community_name LIKE '%" . $search_terms . "%'
					OR community_description LIKE '%" . $search_terms . "%')
					ORDER BY community_name ASC LIMIT 50";
				$search_results = $wpdb->get_results( $query, ARRAY_A );

				if (count($search_results) > 0){
					echo "
					<br />
					<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
					<thead><tr>
					<th scope='col'>" . __('Name') . "</th>
					<th scope='col'>" . __('Description') . "</th>
					<th scope='col'>" . __('Public') . "</th>
					<th scope='col'>" . __('Owner') . "</th>
					<th scope='col'>" . __('Actions') . "</th>
					<th scope='col'></th>
					</tr></thead>
					<tbody id='the-list'>
					";
					//=========================================================//
						$class = ('alternate' == $class) ? '' : 'alternate';
						foreach ($search_results as $search_result){
						//=========================================================//
						echo "<tr class='" . $class . "'>";
						echo "<td valign='top'><strong>" . stripslashes( $search_result['community_name'] ) . "</strong></td>";
						echo "<td valign='top'>" . stripslashes( $search_result['community_description'] ) . "</td>";
						if ( $search_result['community_private'] == '1' ) {
							$community_public = __('No');
						} else {
							$community_public = __('Yes');
						}
						echo "<td valign='top'>" . $community_public . "</td>";
						$owner_details = $wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $search_result['community_owner_user_ID'] . "'");
						echo "<td valign='top'>" . $owner_details->display_name . "</td>";
						if ( $search_result['community_owner_user_ID'] != $user_ID ) {
							$member_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "communities_members WHERE community_ID = '" . $search_result['community_ID'] . "' AND member_user_ID = '" . $user_ID . "'");
							if ( $member_count > 0 ) {
								echo "<td valign='top'><a href='communities.php?action=leave_community&return=find_communities&cid=" . $search_result['community_ID'] . "&search_terms=" . rawurlencode( $search_terms ) . "' rel='permalink' class='delete'>" . __('Leave') . "</a></td>";
							} else {
								echo "<td valign='top'><a href='communities.php?page=find-communities&action=join_community&cid=" . $search_result['community_ID'] . "&search_terms=" . rawurlencode( $search_terms ) . "' rel='permalink' class='edit'>" . __('Join') . "</a></td>";
							}
							echo "<td valign='top'><a href='admin.php?page=messaging_new&message_to=" . $owner_details->user_login . "' rel='permalink' class='edit'>" . __('Send Message to Owner') . "</a></td>";
						} else {
							echo "<td valign='top'>" . __('Join') . "</a></td>";
							echo "<td valign='top'>" . __('Send Message to Owner') . "</a></td>";
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
					<p><?php _e('Nothing found') ?></p>
					<?php
				}
			}
		break;
		//---------------------------------------------------//
		case "join_community":
			$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			$community_private = $wpdb->get_var("SELECT community_private FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_GET['cid'] . "'");
			if ( $community_owner_user_ID == $user_ID ) {
				die('Nice try');
			}

			if ( $community_private != '1' ) {
				communities_join_community($user_ID, $_GET['cid']);
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=find-communities&search_terms=" . $_GET['search_terms'] . "&updated=true&updatedmsg=" . urlencode(__('Successfully joined.')) . "';
				</script>
				";
			} else {
				?>
				<h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Join') ?></h2>
                <p><?php _e('This is a private community. Please supply the code below to join.') ?></p>
                <form name="edit_community" method="POST" action="communities.php?page=find-communities&action=join_community_process">
                    <input type="hidden" name="cid" value="<?php echo $_GET['cid']; ?>" />
                    <input type="hidden" name="search_terms" value="<?php echo $_GET['search_terms']; ?>" />
                    <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Code') ?></th>
                    <td><input type="text" name="code" id="code" style="width: 95%" maxlength="250" value="<?php echo $_POST['code']; ?>" />
                    <br />
					</td>
                    </tr>
                    </table>
                <p class="submit">
                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                <input type="submit" name="Submit" value="<?php _e('Join') ?>" />
                </p>
                </form>
				<?php
			}
		break;
		//---------------------------------------------------//
		case "join_community_process":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='communities.php?page=find-communities&search_terms=" . $_POST['search_terms'] . "';
				</script>
				";
			} else {
				$community_name = $wpdb->get_var("SELECT community_name FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_POST['cid'] . "'");
				$community_owner_user_ID = $wpdb->get_var("SELECT community_owner_user_ID FROM " . $wpdb->base_prefix . "communities WHERE community_ID = '" . $_POST['cid'] . "'");
				if ( $community_owner_user_ID == $user_ID ) {
					die('Nice try');
				}
				if ( $_POST['code'] != substr(md5($_POST['cid'] . '1234'),0,5) ) {
				?>
                    <h2><a href="communities.php?action=dashboard&cid=<?php echo $_GET['cid']; ?>" style="text-decoration:none;"><?php echo stripslashes( $community_name ); ?></a> &raquo; <?php _e('Join') ?></h2>
                    <p><?php _e('Sorry, the code you provided is invalid.') ?></p>
                    <form name="edit_community" method="POST" action="communities.php?page=find-communities&action=join_community_process">
                        <input type="hidden" name="cid" value="<?php echo $_POST['cid']; ?>" />
                        <input type="hidden" name="search_terms" value="<?php echo $_GET['search_terms']; ?>" />
                        <table class="form-table">
                        <tr valign="top">
                        <th scope="row"><?php _e('Code') ?></th>
                        <td><input type="text" name="code" id="code" style="width: 95%" maxlength="250" value="<?php echo $_POST['code']; ?>" />
                        <br />
                        </td>
                        </tr>
                        </table>
                    <p class="submit">
                    <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" />
                    <input type="submit" name="Submit" value="<?php _e('Join') ?>" />
                    </p>
                    </form>
				<?php
				} else {
					communities_join_community($user_ID, $_POST['cid']);
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='communities.php?page=find-communities&search_terms=" . $_POST['search_terms'] . "&updated=true&updatedmsg=" . urlencode(__('Successfully joined.')) . "';
					</script>
					";
				}
			}
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

//------------------------------------------------------------------------//
//---Support Functions----------------------------------------------------//
//------------------------------------------------------------------------//

?>