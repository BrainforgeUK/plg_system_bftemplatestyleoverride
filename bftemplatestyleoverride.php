<?php
/**
 * @package   Template style override plugin by Brainforge
 * @author    https://www.brainforge.co.uk
 * @copyright (C) 2020-2023 Jonathan Brain. All rights reserved.
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
		switch($app->getName())
		{
			case 'administrator':
				$client_id = 1;
				break;
			case 'site':
				$client_id = 0;
				break;
			default:
				return;
		}

		$tid = 0;
		$talias = $app->input->getCmd($this->params->get('paramname', 'bftemplatestyleid'), null);
		if (!empty($talias))
		{
			$stylealiases = $this->params->get('stylealiases');
			if (!empty($stylealiases))
			{
				foreach($stylealiases as $stylealias)
				{
					if ($stylealias->paramvalue == $talias)
					{
						$tid = $stylealias->styleid;
						break;
					}
				}
			}
		}

		if ($tid) {
			// Load style
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('s.id, s.home, s.template, s.params AS styleParams, e.manifest_cache, e.params')
				->from('#__template_styles as s')
				->where('s.client_id = ' . $client_id)
				->where('e.enabled = 1')
				->where('s.id = ' . $tid)
				->join('LEFT', '#__extensions as e ' .
					'ON e.element=s.template ' .
					'AND e.type=' . $db->quote('template') . ' ' .
					'AND e.client_id=s.client_id');

			$db->setQuery($query);
			$selectedTemplate = $db->loadObject();

			$app->setUserState(self::TEMPLATESTYLEOVERIDESTATE, $selectedTemplate);
		}
		else {
			$selectedTemplate = $app->getUserState(self::TEMPLATESTYLEOVERIDESTATE, null);
		}

		if (!empty($selectedTemplate)) {
			if (!is_object($selectedTemplate->manifest_cache))
			{
				$selectedTemplate->manifest_cache = json_decode($selectedTemplate->manifest_cache);
			}

			$template = new \stdClass();
			$template->template = $selectedTemplate->template;
			$template->inheritable = $selectedTemplate->manifest_cache->inheritable;
			$template->parent = $selectedTemplate->manifest_cache->parent;
			if (empty($selectedTemplate->styleParams))
			{
				$template->params = json_decode($selectedTemplate->params);
			}
			else
			{
				$template->params = json_decode($selectedTemplate->styleParams);
			}

			$app->setTemplate($template);
		}

		$app->setUserState(self::TEMPLATESTYLEOVERIDESTATE, $selectedTemplate);
	}
}
