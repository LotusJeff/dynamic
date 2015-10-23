<?php
/**
 *
 * Dyanmic Opengraph and Twitter Meta tags extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Jeff Cocking
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'LOTUSJEFF_DYNAMIC_SETTINGS'				=> 'Dynamic OpenGraph (Facebook) and Twitter Meta Tags',
	'LOTUSJEFF_DYNAMIC_FACEBOOK'				=> 'Enable OpenGraph Meta Tags (Facebook)',
	'LOTUSJEFF_DYNAMIC_TWITTER'					=> 'Enable Twiiter Cards',
	'LOTUSJEFF_DYNAMIC_FIRST_IMAGE'				=> 'First attachement(image)',
	'LOTUSJEFF_DYNAMIC_FIRST_IMAGE_EXPLAIN'		=> 'Selecting YES will use the first attachment(image). NO will use the last attachment (image) in the topic.',
	'LOTUSJEFF_DYNAMIC_RANDOM_IMAGE'			=> 'Enable Random attachement(image)',
	'LOTUSJEFF_DYNAMIC_RANDOM_IMAGE_EXPLAIN'	=> 'Selecting YES will use a random (image) from your fourm. It will not use images within PMs.',
	'LOTUSJEFF_DYNAMIC_TWITTER_SITE'			=> 'Twitter @username',
	'LOTUSJEFF_DYNAMIC_TWITTER_SITE_EXPLAIN'	=> 'Enter the @username associated with this website. Must include the @',
	'LOTUSJEFF_DYANMIC_OPENGRAPH_LOCALE'		=> 'en-US',
));
