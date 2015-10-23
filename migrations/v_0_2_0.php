<?php
/**
 * Dynamic Opengraph and Twitter Metatags extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Jeff Cocking
 * @license   GNU General Public License, version 2 (GPL-2.0)
 */

namespace lotusjeff\dynamic\migrations;

class v_0_2_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['lotusjeff_dynamic_versions']) && version_compare($this->config['lotusjeff_dynamic_versions'], '0.2.0', '>=');
	}

	static public function depends_on()
	{
		return array('\lotusjeff\dynamic\migrations\v_0_1_0');
	}

	public function update_data()
	{
		return array(
		array('config.add', array('lotusjeff_dynamic_random_image', 1)),
		array('config.add', array('lotusjeff_dynamic_versions', '0.2.0')),
		);
	}
}
