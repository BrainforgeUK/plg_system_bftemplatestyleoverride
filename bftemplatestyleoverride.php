<?php
/**
 * @package   HikaShop for Joomla!
 * @author    https://www.brainforge.co.uk
 * @copyright (C) 2020 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

class plgSystemBftemplatestyleoverride extends CMSPlugin
{
	const TEMPLATESTYLEOVERIDESTATE = "Bftemplatestyleoverride.template";

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	public function onAfterRoute() {
		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}

		// Get the id of the active menu item
		$menu = $app->getMenu();
		$item = $menu->getActive();
		$template = null;

		if (!$item) {
			$item = $menu->getItem($app->input->getInt('Itemid', null));
		}
		$isHome = ($item && $item->home);

		$tid = $app->input->getUint($this->params->get('paramname', 'bftemplatestyleid'), null);

		if ($tid) {
			// Load style
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('id, home, template, s.params')
				->from('#__template_styles as s')
				->where('s.client_id = 0')
				->where('e.enabled = 1')
				->where('s.id = ' . $tid)
				->join('LEFT', '#__extensions as e ' .
					'ON e.element=s.template ' .
					'AND e.type=' . $db->quote('template') . ' ' .
					'AND e.client_id=s.client_id');

			$db->setQuery($query);
			$template = $db->loadObject();
			if (!empty($template)) {
				$template->params = new Registry($template->params);
				// TODO See https://issues.joomla.org/tracker/joomla-cms/30314
				// Workaround added for tpl_bfprotostar
				$template->params->set('styleid', $tid);
			}

			$app->setUserState(self::TEMPLATESTYLEOVERIDESTATE, $template);
		}
		else if (!$isHome) {
			$template = $app->getUserState(self::TEMPLATESTYLEOVERIDESTATE, $template);
		}
		else {
			$app->setUserState(self::TEMPLATESTYLEOVERIDESTATE, null);
		}

		if (!empty($template)) {
			$app->setTemplate($template->template, $template->params);
		}
	}
}
