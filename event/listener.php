<?php
/**
*
* Xiti marker.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace Aurelienazerty\xiti\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface {
	
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;
	
	/** @var string */
	protected $pageTitle;

	/**
	* Constructor
	*
	* @param \phpbb\config\config        $config             Config object
	* @param \phpbb\template\template    $template           Template object
	* @param \phpbb\user                 $user               User object
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user) {
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
	static public function getSubscribedEvents() {
		return array(
			'core.acp_board_config_edit_add'	=> 'add_xiti_configs',
			'core.page_header'					=> 'load_xiti_analytics',
			'core.page_footer'					=> 'display_xiti_analytics',
		);
	}

	/**
	* Load Google Analytics js code
	*
	* @return null
	* @access public
	*/
	public function load_xiti_analytics($events) {
		$this->pageTitle = $events['page_title'];
	}
	
	public function display_xiti_analytics() {
		$this->template->assign_var('XITI_ID', $this->config['xiti_id']);
		$this->template->assign_var('XITI_PAGE', $this->config['xiti_prefix'] . $this->_xtTraiter($this->pageTitle));
		$this->template->assign_var('XITI_LOGO', $this->config['xiti_logo']);
	}

	/**
	* Add config vars to ACP Board Settings
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_xiti_configs($event) {
		// Load language file
		$this->user->add_lang_ext('Aurelienazerty/xiti', 'xiti_acp');

		// Add a config to the settings mode, after board_timezone
		if ($event['mode'] == 'settings' && isset($event['display_vars']['vars']['board_timezone'])) {
			// Store display_vars event in a local variable
			$display_vars = $event['display_vars'];

			// Define the new config vars
			$config_vars = array(
				'legendXiti' => 'ACP_XITI_TITLE',
				'xiti_id' => array(
					'lang' => 'ACP_XITI_ID',
					'type' => 'text:20:10',
					'validate' 	=> 'string',
					'explain' => true,
				),
				'xiti_prefix' => array(
					'lang' => 'ACP_XITI_PREFIX',
					'type' => 'text:40:20',
					'explain' => true,
				),
				'xiti_logo' => array(
					'lang' 			=> 'ACP_XITI_LOGO',
					'type' 			=> 'text:4:4',
					'validate' 		=> 'string',
					'explain'		=> true,
				),
			);

			// Add the new config vars after board_timezone in the display_vars config array
			$insert_after = array('after' => 'board_timezone');
			$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $config_vars, $insert_after);
			// Update the display_vars event with the new array
			$event['display_vars'] = $display_vars;
		}
	}
	
	/**
	 * Make page id
	 */
	private function _xtTraiter($nompage) {
		$nompage = strtolower($nompage);
		$nompage = html_entity_decode($nompage, ENT_NOQUOTES, 'UTF-8');
		$search = array ("@[éèêëÊË]@i","@[àâäÂÄ]@i","@[îïÎÏ]@i","@[ûùüÛÜ]@i","@[ôöÔÖ]@i","@[ç]@i","@[ ]@i","@[^a-zA-Z0-9_]@");
		$replace = array ("e","a","i","u","o","c","-","-");
		$nompage = preg_replace($search, $replace, $nompage);
		return $nompage;
	} 
}
