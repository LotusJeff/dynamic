<?php
/**
 * Dynamic Opengraph and Twitter Meta Tags extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Jeff Cocking
 * @license   GNU General Public License, version 2 (GPL-2.0)
 */

namespace lotusjeff\dynamic\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/**
	* @var \phpbb\config\config
	*/
	protected $config;
	/**
	* @var \phpbb\template\template
	*/
	protected $template;
	/**
	* @var \phpbb\user
	*/
	protected $user;
	/**
	* @var \phpbb\db
	*/
	protected $db;

	/**
	 * Constructor
	 *
	 * @param  \phpbb\config\config     $config   Config object
	 * @param  \phpbb\template\template $template Template object
	 * @param  \phpbb\user              $user     User object
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
		'core.page_header_after'            => 'lotusjeff_dynamic_master_tpl_data',
		'core.acp_board_config_edit_add'    => 'lotusjeff_dynamic_add_acp_config',
		'core.viewtopic_modify_post_data'    => 'lotusjeff_dynamic_viewtopic_header_data',
		'core.viewforum_get_topic_data'        => 'lotusjeff_dynamic_viewforum_header_data',
		'core.index_modify_page_title'        => 'lotusjeff_dynamic_index_header_data',
		'core.user_setup'                     => 'lotusjeff_dynamic_load_language',
		);
	}

	/**
	 * Set Dynamic template data
	 *
	 * @return null
	 * @access public
	 */
	public function lotusjeff_dynamic_master_tpl_data()
	{
		/**
		* Assigns a default image and site description for pages without.
		*/
		if ((empty($this->config['lotusjeff_dynamic_image'])) && ($this->config['lotusjeff_dynamic_random_image'])  )
		{
			$this->config['lotusjeff_dynamic_image'] = $this->lotusjeff_dynamic_get_random_image();
		}
		if (empty($this->config['lotusjeff_dynamic_description']))
		{
			$this->config['lotusjeff_dynamic_description'] = $this->config['site_desc'];
		}
		/**
		* Assigns base variables to template.
		* S_DYNAMIC_FACEBOOK AND S_DYNAMIC_TWITTER on/off switches.
		* DYNAMIC_TWITTER_SITE is defined in the ACP
		*/
		$this->template->assign_vars(
			array(
			'S_LOTUSJEFF_DYNAMIC_FACEBOOK'        => (int) $this->config['lotusjeff_dynamic_facebook'],
			'S_LOTUSJEFF_DYNAMIC_TWITTER'        => (int) $this->config['lotusjeff_dynamic_twitter'],
			'LOTUSJEFF_DYNAMIC_TWITTER_SITE'    => $this->config['lotusjeff_dynamic_twitter_site'],
			'LOTUSJEFF_DYNAMIC_IMAGE'            => $this->config['lotusjeff_dynamic_image'],
			'LOTUSJEFF_DYNAMIC_DESCRIPTION'        => $this->config['lotusjeff_dynamic_description'],
			'LOTUSJEFF_DYNAMIC_URL'                => $this->config['lotusjeff_dynamic_board_url'],
			)
		);
	}

	/**
	 * Add Dynamic settings to the ACP
	 *
	 * @return null
	 * @access public
	 */
	public function lotusjeff_dynamic_add_acp_config($event)
	{
		if ($event['mode'] == 'features' && isset($event['display_vars']['vars']['legend3']))
		{
			$this->user->add_lang_ext('lotusjeff/dynamic', 'dynamic');
			$display_vars = $event['display_vars'];

			$my_config_vars = array(
			'legend_lotusjeff_dynamic'            => 'LOTUSJEFF_DYNAMIC_SETTINGS',
			'lotusjeff_dynamic_facebook'        => array('lang' => 'LOTUSJEFF_DYNAMIC_FACEBOOK', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
			'lotusjeff_dynamic_twitter'            => array('lang' => 'LOTUSJEFF_DYNAMIC_TWITTER', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
			'lotusjeff_dynamic_first_image'        => array('lang' => 'LOTUSJEFF_DYNAMIC_FIRST_IMAGE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
			'lotusjeff_dynamic_random_image'    => array('lang' => 'LOTUSJEFF_DYNAMIC_RANDOM_IMAGE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
			'lotusjeff_dynamic_twitter_site'    => array('lang' => 'LOTUSJEFF_DYNAMIC_TWITTER_SITE','validate' => 'string', 'type' => 'text:10:16', 'method' => '$this->lotusjeff_dynamic_twitter_site', 'explain' => true),
			);

			$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $my_config_vars, array('before' => 'legend3'));

			$event['display_vars'] = $display_vars;
		}
	}

	/**
	 * Loads language file for og:locale value deteremine in language files.
	 *
	 * @return null
	 * @access public
	 */
	public function lotusjeff_dynamic_load_language($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'lotusjeff/dynamic',
			'lang_set' => 'dynamic',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Determine image and description data for Viewtopic pages.
	 *
	 * @return null
	 * @access public
	 */
	public function lotusjeff_dynamic_viewtopic_header_data($event)
	{
		global $db;
		$attachments = $event['attachments'];
		$topic_data = $event['topic_data'];
		$rowset = $event['rowset'];
		/**
		* Determine image to use.
		*  We first process attachments.  If we do not find any attachments, we then will process for external images embedded in the posts.
		*  If we do not find any images or attachments, we will review the entire topic to see if there are any images on different pages.
		*  If we do not find any images, we will then assign a random image to the topic.
		*/
		$dynamic_image = null;
		$base_url = generate_board_url()."/download/file.php?id=";
		$append_url = "&t=";
		if (!empty($attachments))
		{
			if ($this->config['lotusjeff_dynamic_first_image'] == 1)
			{
				$set_postition = min($attachments);
				$attach_id = $set_postition['0']['attach_id'];
				$thumbnail = $set_postition['0']['thumbnail'];
			}
			else
			{
				$set_postition = max($attachments);
				$set_last_image = max($set_postition);
				$attach_id = $set_last_image['attach_id'];
				$thumbnail = $set_last_image['thumbnail'];
			}
			$dynamic_image = $base_url.$attach_id.$append_url.$thumbnail;
		}
		else
		{
			/*
            * Future section to add parsing of image external bbcode
            *
            foreach ($rowset as &$row) {
             $post_content = $row['post_text'];
            }
            //*/
		}

		if (($topic_data['topic_attachment'] == 1) && (empty($dynamic_image)))
		{
			/*
            * Image exists in topic, but not on this paganation. Get image data.
            * Currently just get first image. Could expand to get first and last image.
            */
			$sql_array = array(
			 'topic_id'    => $topic_data['topic_id'],
			);
			$sql = 'SELECT attach_id, thumbnail 
			        FROM ' . ATTACHMENTS_TABLE . ' 
			        WHERE ' . $db->sql_build_array('SELECT', $sql_array);
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$dynamic_image = $base_url.$row['attach_id'].$append_url.$row['thumbnail'];
		}
		$this->config['lotusjeff_dynamic_image'] = $dynamic_image;
		/*
        * Obtain description data.
        * First determine if we are dealing with page 1 of a topic. If not, we pull
        * the first post_text from the database.
        * If this is page one, we use the post_text from the existing array.
        */
		if ($event['start'] > 0 )
		{
			$sql_array = array(
			 'post_id'    => $topic_data['topic_first_post_id'],
			);
			$sql = 'SELECT post_text 
			        FROM ' . POSTS_TABLE . ' 
			        WHERE ' . $db->sql_build_array('SELECT', $sql_array);
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$post_content = $row['post_text'];
		}
		else
		{
			$post_content = $rowset[$topic_data['topic_first_post_id']]['post_text'];
		}
		$post_content = $this->lotusjeff_dynamic_strip_code($post_content);
		$this->config['lotusjeff_dynamic_description'] = $post_content;
	}

	/**
	 * Determine image and description data from Viewforum pages.
	 *
	 * @return null
	 * @access public
	 */
	public function lotusjeff_dynamic_viewforum_header_data($event)
	{
		$forum_data = $event['forum_data'];
		if (!empty($forum_data['forum_desc']))
		{
			$forum_desc = $this->lotusjeff_dynamic_strip_code($forum_data['forum_desc']);
			$this->template->assign_var('lotusjeff_dynamic_description', $forum_desc);
		}
		if ($this->config['lotusjeff_dynamic_random_image'])
		{
			$dynamic_image = $this->lotusjeff_dynamic_get_random_image();
			$this->template->assign_var('lotusjeff_dynamic_image', $dynamic_image);
		}
	}

	/**
	 * The index page does not have the U_CANNONICAL template tag. This
	 * requires adding the board url for the url meta tags.
	 *
	 * @return null
	 * @access public
	 */
	public function lotusjeff_dynamic_index_header_data()
	{
		$board_url = generate_board_url()."/";
		$this->config['lotusjeff_dynamic_board_url'] = $board_url;
	}

	/**
	 * Pulls a random image for forum and index pages.  The routing looks
	 * for images that are not in PMs, are thumbnails and are jpg images.
	 *
	 * @return image attach id
	 * @access private
	 */
	private function lotusjeff_dynamic_get_random_image()
	{
		global $db;
		$sql_array = array(
		 'in_message'    =>    '0',
		 'thumbnail'        =>    '1',
		 'extension'        =>    'jpg',
		);
		$sql = 'SELECT attach_id 
		        FROM ' . ATTACHMENTS_TABLE . ' 
		        WHERE ' . $db->sql_build_array('SELECT', $sql_array) .'
		        ORDER BY RAND() LIMIT 0,1';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$base_url = generate_board_url()."/download/file.php?id=";
		$append_url = "&t=1";
		$dynamic_image = $base_url.$row['attach_id'].$append_url;
		return $dynamic_image;
	}

	/**
	 * ACP validation of the twitter_site for proper format
	 *
	 * @return null
	 * @access private
	 */
	private function lotusjeff_dynamic_twitter_site($selected_value, $value)
	{
		return;
	}

	/**
	 * Clean up post_text and forum_desc for non text data
	 *
	 * @return $text
	 * @access private
	 */
	private function lotusjeff_dynamic_strip_code($text)
	{
		$text = censor_text($text);
		strip_bbcode($text);
		$text = str_replace(array("&quot;", "/", "\n", "\t", "\r"), ' ', $text);
		$text = preg_replace(array("|http(.*)jpg|isU", "@(http(s)?://)?(([a-z0-9.-]+)?[a-z0-9-]+(!?\.[a-z]{2,4}))@"), ' ', $text);
		$text = preg_replace("/[^A-ZА-ЯЁ.,-–?]+/ui", " ", $text);
		if (strlen($text) > 180)
		{
			$text_ar = explode("\n", wordwrap($text, 180));
			$text = $text_ar[0] . '...';
		}
		return $text;
	}

}
