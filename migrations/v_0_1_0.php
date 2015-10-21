<?php
/**
 *
 * Dynamic Opengraph and Twitter Metatags extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Jeff Cocking
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace lotusjeff\dynamic\migrations;

class v_0_1_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return;
	}
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\gold');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('lotusjeff_dynamic_facebook', 1)),
			array('config.add', array('lotusjeff_dynamic_twitter', 1)),
			array('config.add', array('lotusjeff_dynamic_first_image', 1)),
			array('config.add', array('lotusjeff_dynamic_twitter_site', '')),
			);
	}
}
