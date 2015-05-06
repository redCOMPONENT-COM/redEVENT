<?php
/**
 * @package		redcomponent.redeventsync
 * @subpackage	com_redeventsync
 * @copyright	Copyright (C) 2013 redCOMPONENT.com
 * @license		GNU General Public License version 2 or later
 */
defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class Com_RedeventsyncInstallerScript
{
	/**
	 * Status of the installation
	 *
	 * @var  stdClass
	 */
	public $status = null;

	public $installer = null;

	/**
	 * Get the common JInstaller instance used to install all the extensions
	 *
	 * @return JInstaller The JInstaller object
	 */
	public function getInstaller()	{
		if (is_null($this->installer))
		{
			$this->installer = new JInstaller;
		}

		return $this->installer;
	}

	/**
	 * Shit happens. Patched function to bypass bug in package uninstaller
	 *
	 * @param   JInstaller  $parent  Parent object
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getManifest($parent)
	{
		$element = strtolower(str_replace('InstallerScript', '', __CLASS__));
		$elementParts = explode('_', $element);

		if (count($elementParts) == 2)
		{
			$extType = $elementParts[0];

			if ($extType == 'pkg')
			{
				$rootPath = $parent->getParent()->getPath('extension_root');
				$manifestPath = dirname($rootPath);
				$manifestFile = $manifestPath . '/' . $element . '.xml';

				if (file_exists($manifestFile))
				{
					return JFactory::getXML($manifestFile);
				}
			}
		}

		return $parent->get('manifest');
	}

	/**
	 * Joomla! pre-flight event
	 *
	 * @param string $type Installation type (install, update, discover_install)
	 * @param JInstaller $parent Parent object
	 */
	public function preflight($type, $parent)
	{
		// Only allow to install on Joomla! 2.5.0 or later with PHP 5.3.0 or later
		if(defined('PHP_VERSION')) {
			$version = PHP_VERSION;
		} elseif(function_exists('phpversion')) {
			$version = phpversion();
		} else {
			$version = '5.0.0'; // all bets are off!
		}
		if(!version_compare(JVERSION, '2.5.0', 'ge')) {
			echo "<p>You need Joomla! 2.5 or later to install this component</p>";
			return false;
		}
		if(!version_compare($version, '5.3.0', 'ge')) {
			echo "<p>You need PHP 5.3 or later to install this component</p>";
			return false;
		}
		return true;
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		$this->installModsPlugs($parent);

		// Install extensions
		$this->installLibraries($parent);

		if (count($this->installed_plugs)) {
			echo '<div>
                          <table class="adminlist" cellspacing="1">
                            <thead>
                                <tr>
                                    <th>'.JText::_('Plugin').'</th>
                                    <th>'.JText::_('Group').'</th>
                                    <th>'.JText::_('Status').'</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                            </tfoot>
                            <tbody>';
			foreach ($this->installed_plugs as $plugin) :
				$pstatus    = ($plugin['upgrade']) ? JHtml::_('image','admin/tick.png', '', NULL, true) : JHtml::_('image','admin/publish_x.png', '', NULL, true);
				echo '<tr>
                                            <td>'.$plugin['plugin'].'</td>
                                            <td>'.$plugin['group'].'</td>
                                            <td style="text-align: center;">'.$pstatus.'</td>
                                          </tr>';
			endforeach;
			echo '   </tbody>
                         </table>
                         </div>';
		}

		if (count($this->installed_mods)) {
			echo '<div>
                          <table class="adminlist" cellspacing="1">
                            <thead>
                                <tr>
                                    <th>'.JText::_('Module').'</th>
                                    <th>'.JText::_('Status').'</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                            </tfoot>
                            <tbody>';
			foreach ($this->installed_mods as $module) :
				$mstatus    = ($module['upgrade']) ? JHtml::_('image','admin/tick.png', '', NULL, true) : JHtml::_('image','admin/publish_x.png', '', NULL, true);
				echo '<tr>
                                            <td>'.$module['module'].'</td>
                                            <td style="text-align: center;">'.$mstatus.'</td>
                                          </tr>';
			endforeach;
			echo '   </tbody>
            	</table>
			</div>';
		}
	}

	protected function installModsPlugs($parent)
	{
		$manifest       = $parent->get("manifest");
		$parent         = $parent->getParent();
		$source         = $parent->getPath("source");
		$db = JFactory::getDbo();

		//**********************************************************************
		// DO THIS IF WE DECIDE TO AUTOINSTALL PLUGINS/MODULES
		//**********************************************************************
		// install plugins and modules
		$installer = new JInstaller();

		// Install plugins
		foreach($manifest->plugins->plugin as $plugin) {
			$attributes                 = $plugin->attributes();
			$plg                        = $source . '/' . $attributes['folder'].'/'.$attributes['plugin'];
			// 			echo '<pre>';print_r($plg); echo '</pre>';exit;
			$new                        = ($attributes['new']) ? '&nbsp;(<span class="green">New in v.'.$attributes['new'].'!</span>)' : '';
			if ($installer->install($plg)) {
				// autopublish the plugin
				$query = ' UPDATE #__extensions SET enabled = 1 WHERE folder = '. $db->Quote($attributes['group']) . ' AND element = '.$db->Quote($attributes['plugin']);
				$db->setQuery($query);
				$db->query();
				$this->installed_plugs[]    = array('plugin' => $attributes['plugin'].$new, 'group'=> $attributes['group'], 'upgrade' => true);
			} else {
				$this->installed_plugs[]    = array('plugin' => $attributes['plugin'], 'group'=> $attributes['group'], 'upgrade' => false);
				$this->iperror[] = JText::_('Error installing plugin').': '.$attributes['plugin'];
			}
		}
		return true;

		// Install modules
		foreach($manifest->modules->module as $module) {
			$attributes             = $module->attributes();
			$mod                    = $source . '/' . $attributes['folder'].'/'.$attributes['module'];
			$new                    = ($attributes['new']) ? '&nbsp;(<span class="green">New in v.'.$attributes['new'].'!</span>)' : '';
			if($installer->install($mod)){
				$this->installed_mods[] = array('module' => $attributes['module'].$new, 'upgrade' => true);
			}else{
				$this->installed_mods[] = array('module' => $attributes['module'], 'upgrade' => false);
				$this->iperror[] = JText::_('Error installing module').': '.$attributes['module'];
			}
		}
	}

	/**
	 * Install the package libraries
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 */
	private function installLibraries($parent)
	{
		// Required objects
		$installer = $this->getInstaller();
		$manifest  = $parent->get('manifest');
		$src       = $parent->getParent()->getPath('source');

		if ($nodes = $manifest->libraries->library)
		{
			foreach ($nodes as $node)
			{
				$extName = $node->attributes()->name;
				$extPath = $src . '/libraries/' . $extName;
				$result  = 0;

				// Standard install
				if (is_dir($extPath))
				{
					$result = $installer->install($extPath);
				}
				elseif ($extId = $this->searchExtension($extName, 'library', '-1'))
				// Discover install
				{
					$result = $installer->discover_install($extId);
				}
			}
		}
	}

	/**
	 * Search a extension in the database
	 *
	 * @param   string  $element  Extension technical name/alias
	 * @param   string  $type     Type of extension (component, file, language, library, module, plugin)
	 * @param   string  $state    State of the searched extension
	 * @param   string  $folder   Folder name used mainly in plugins
	 *
	 * @return  integer           Extension identifier
	 */
	protected function searchExtension($element, $type, $state = null, $folder = null)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('extension_id')
			->from($db->quoteName("#__extensions"))
			->where("type = " . $db->quote($type))
			->where("element = " . $db->quote($element));

		if (!is_null($state))
		{
			$query->where("state = " . (int) $state);
		}

		if (!is_null($folder))
		{
			$query->where("folder = " . $db->quote($folder));
		}

		$db->setQuery($query);

		return $db->loadResult();
	}
}
