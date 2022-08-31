<?php
/**
 * @package   Template style override plugin by Brainforge
 * @author    https://www.brainforge.co.uk
 * @copyright (C) 2020-2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Templates\Administrator\Model\StyleModel;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

class plgSystemBftemplatestyleoverride extends CMSPlugin
{
	const TEMPLATESTYLEOVERIDESTATE = "Bftemplatestyleoverride.template";

	protected $app;
	protected $params;

	/*
	 */
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		// TODO Needed?
		//$this->app = Factory::getApplication();
	}

	/*
	 */
	public function onAfterRoute() {
		if (!$this->app->isClient('site')) {
			return;
		}

		// Get the id of the active menu item
		$menu = $this->app->getMenu();
		$item = $menu->getActive();
		$template = null;

		if (!$item) {
			$item = $menu->getItem($this->app->input->getInt('Itemid', null));
		}
		$isHome = ($item && $item->home);

		$tid = $this->app->input->getUint($this->params->get('paramname', 'bftemplatestyleid'), null);

		if ($tid) {
			// Load style
			$db = Factory::getDbo();
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

			$this->app->setUserState(self::TEMPLATESTYLEOVERIDESTATE, $template);
		}
		else if (!$isHome) {
			$template = $this->app->getUserState(self::TEMPLATESTYLEOVERIDESTATE, $template);
		}
		else {
			$this->app->setUserState(self::TEMPLATESTYLEOVERIDESTATE, null);
		}

		if (!empty($template)) {
			$this->app->setTemplate($template->template, $template->params);
		}
	}

	/**
	 */
	public function onBeforeRender()
	{
		$template = $this->app->getTemplate(true);
		$styleModel = new StyleModel();
		$style = $styleModel->getItem($template->id);
		$style->alias = OutputFilter::stringURLSafe($style->title);

		$base = Uri::base() .  'media/templates/' . $this->template;
		$doc = Factory::getDocument();
		$doc->addStyleSheet($base . '/css/' . $style->alias . '.css');
		$doc->addScript($base . '/js/' . $style->alias . '.js', 'text/javascript');
	}
}
