<?php
/**
 * @version 2.5
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 - 2010 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class com_redeventInstallerScript
{
	protected $_is15update = false;

	private $installed_mods             = array();

	private $installed_plugs            = array();

	/**
	 * @var array Obsolete files and folders to remove
	 */
	private $removeFiles = array(
		'files'    => array(
			'administrator/components/com_redevent/views/customfield/tmpl/form.php',
		),
		'folders'  => array(
			'administrator/components/com_redevent/customfield',
		)
	);

	public $installer = null;

	/**
	 * Get the common JInstaller instance used to install all the extensions
	 *
	 * @return JInstaller The JInstaller object
	 */
	public function getInstaller()
	{
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

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
		// In the case of an update from 1.5 version (2.0 stable), we might have just the tables without the extension being registered
		// In that case, we will need to run the mysql updates sql 2.5.b.2  , which was the same db as 2.0 stable
		$row = JTable::getInstance('extension');
		$eid = $row->find(array('element' => 'com_redevent', 'type' => 'component'));

		if ($eid)
		{
			// Version 2.5 already installed, or migrated from jupgrade
			$row->load($eid);

			if ($row->manifest_cache)
			{
				// It's really an update...
				return true;
			}

			// No manifest means it was migrated from 2.0 from jupgrade, be we still need to update the table from 2.0 structure
		}
		else
		{
			// Not installed, do we have a redevent table ?
			$tables = JFactory::getDbo()->getTableList();

			if (!in_array(JFactory::getDbo()->getPrefix() . 'redevent_settings', $tables))
			{
				// It s a clean install;
				return true;
			}
		}

		// Still here... means this is an update from 2.0
		$this->_is15update = true;

		return true;
	}

	/**
	 * Performs install
	 *
	 * @return void
	 */
	public function install()
	{
		if ($this->_is15update)
		{
			$this->updateFrom20();
		}
	}

	/**
	 * method to update the database structure from a 2.0 version
	 *
	 * @return void
	 */
	public function updateFrom20()
	{
		$db = &JFactory::getDbo();
		$dbDriver = strtolower($db->name);

		if ($dbDriver == 'mysqli')
		{
			$dbDriver = 'mysql';
		}
		elseif ($dbDriver == 'sqlsrv')
		{
			$dbDriver = 'sqlazure';
		}

		$basepath = JPATH_ADMINISTRATOR . '/components/com_redevent/sql/updates/' . $dbDriver;
		$files = str_replace('.sql', '', JFolder::files($basepath, '\.sql$'));
		usort($files, 'version_compare');

		if (!count($files))
		{
			return false;
		}

		// Equivalent version
		$version = '2.5.b.3.0';

		// We have a version!
		foreach ($files as $file)
		{
			if (version_compare($file, $version) > 0)
			{
				$buffer = file_get_contents($basepath . '/' . $file . '.sql');

				// Graceful exit and rollback if read not successful
				if ($buffer === false)
				{
					JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'));

					return false;
				}

				// Create an array of queries from the sql file
				$queries = JInstallerHelper::splitSql($buffer);

				if (count($queries) == 0)
				{
					// No queries to process
					continue;
				}

				// Process each query in the $queries array (split out of sql file).
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);

						if (!$db->query())
						{
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

							return false;
						}
					}
				}

				echo Jtext::sprintf('COM_REDEVENT_UPDATED_DB_TO', $file) . '<br/>';
			}
		}
	}

	/**
	 * performs update
	 *
	 * @return void
	 */
	public function update()
	{
		if ($this->_is15update) {
			$this->updateFrom20();
		}
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @param   string  $type    type
	 * @param   object  $parent  parent
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		// Check for FOF
		//$fofInstallationStatus = $this->_installFOF($parent);

		// Remove obsolete files and folders
		$this->_removeObsoleteFilesAndFolders($this->removeFiles);

		$this->installLibraries($parent);

		$this->installModsPlugs($parent);

		if (count($this->installed_plugs))
		{
			echo '<div>
					<table class="adminlist" cellspacing="1">
						<thead>
							<tr>
								<th>' . JText::_('Plugin') . '</th>
								<th>' . JText::_('Group') . '</th>
								<th>' . JText::_('Status') . '</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
						</tfoot>
						<tbody>';

			foreach ($this->installed_plugs as $plugin) :
				$pstatus = ($plugin['upgrade']) ? JHtml::_('image', 'admin/tick.png', '', null, true) : JHtml::_('image', 'admin/publish_x.png', '', null, true);
				echo '<tr>
					<td>' . $plugin['plugin'] . '</td>
					<td>' . $plugin['group'] . '</td>
					<td style="text-align: center;">' . $pstatus . '</td>
				</tr>';
			endforeach;
			echo '</tbody>
				</table>
			</div>';
		}

		if (count($this->installed_mods))
		{
			echo '<div>
				<table class="adminlist" cellspacing="1">
					<thead>
						<tr>
							<th>' . JText::_('Module') . '</th>
							<th>' . JText::_('Status') . '</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
					</tfoot>
					<tbody>';

			foreach ($this->installed_mods as $module) :
			$mstatus = ($module['upgrade']) ? JHtml::_('image', 'admin/tick.png', '', null, true) : JHtml::_('image', 'admin/publish_x.png', '', null, true);
			echo '<tr>
				<td>' . $module['module'] . '</td>
				<td style="text-align: center;">' . $mstatus . '</td>
			</tr>';
			endforeach;
			echo '</tbody>
				</table>
			</div>';
		}
	}

	/**
	 * Check if FoF is already installed and install if not
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  array            Array with performed actions summary
	 */
	private function _installFOF($parent)
	{
		$src = $parent->getParent()->getPath('source');

		// Load dependencies
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.utilities.date');
		$source = $src . '/fof';

		if (!defined('JPATH_LIBRARIES'))
		{
			$target = JPATH_ROOT . '/libraries/fof';
		}
		else
		{
			$target = JPATH_LIBRARIES . '/fof';
		}

		$haveToInstallFOF = false;

		if (!is_dir($target))
		{
			$haveToInstallFOF = true;
		}
		else
		{
			$fofVersion = array();

			if (file_exists($target . '/version.txt'))
			{
				$rawData = JFile::read($target . '/version.txt');
				$info    = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'   => trim($info[0]),
					'date'      => new JDate(trim($info[1]))
				);
			}
			else
			{
				$fofVersion['installed'] = array(
					'version'   => '0.0',
					'date'      => new JDate('2011-01-01')
				);
			}

			$rawData = JFile::read($source . '/version.txt');
			$info    = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'   => trim($info[0]),
				'date'      => new JDate(trim($info[1]))
			);

			$haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
		}

		$installedFOF = false;

		if ($haveToInstallFOF)
		{
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedFOF = $installer->install($source);
		}
		else
		{
			$versionSource = 'installed';
		}

		if (!isset($fofVersion))
		{
			$fofVersion = array();

			if (file_exists($target . '/version.txt'))
			{
				$rawData = JFile::read($target . '/version.txt');
				$info    = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'   => trim($info[0]),
					'date'      => new JDate(trim($info[1]))
				);
			}
			else
			{
				$fofVersion['installed'] = array(
					'version'   => '0.0',
					'date'      => new JDate('2011-01-01')
				);
			}

			$rawData = JFile::read($source . '/version.txt');
			$info    = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'   => trim($info[0]),
				'date'      => new JDate(trim($info[1]))
			);
			$versionSource = 'installed';
		}

		if (!($fofVersion[$versionSource]['date'] instanceof JDate))
		{
			$fofVersion[$versionSource]['date'] = new JDate;
		}

		return array(
			'required'  => $haveToInstallFOF,
			'installed' => $installedFOF,
			'version'   => $fofVersion[$versionSource]['version'],
			'date'      => $fofVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $files  files and folders to be removed
	 */
	private function _removeObsoleteFilesAndFolders($files)
	{
		// Remove files
		JLoader::import('joomla.filesystem.file');

		if (!empty($files['files']))
		{
			foreach($files['files'] as $file)
			{
				$f = JPATH_ROOT . '/' . $file;

				if(!JFile::exists($f))
				{
					continue;
				}

				JFile::delete($f);
			}
		}

		if (!empty($files['folders']))
		{
			foreach($files['folders'] as $folder)
			{
				$f = JPATH_ROOT . '/' . $folder;

				if(!JFolder::exists($f))
				{
					continue;
				}

				JFolder::delete($f);
			}
		}
	}

	/**
	 * install modules
	 *
	 * @param   object  $parent  parent
	 *
	 * @return bool
	 */
	protected function installModsPlugs($parent)
	{
		$manifest       = $parent->get("manifest");
		$parent         = $parent->getParent();
		$source         = $parent->getPath("source");
		$db = JFactory::getDbo();

		/**********************************************************************
		 * DO THIS IF WE DECIDE TO AUTOINSTALL PLUGINS/MODULES
		 **********************************************************************/

		// Install plugins and modules
		$installer = new JInstaller();

		// Install plugins
		foreach ($manifest->plugins->plugin as $plugin)
		{
			$attributes                 = $plugin->attributes();
			$plg                        = $source . '/' . $attributes['folder'] . '/' . $attributes['plugin'];
			$new                        = ($attributes['new']) ? '&nbsp;(<span class="green">New in v.' . $attributes['new'] . '!</span>)' : '';

			if ($installer->install($plg))
			{
				// Autopublish the plugin
				$query = ' UPDATE #__extensions SET enabled = 1 WHERE folder = ' . $db->Quote($attributes['group']) . ' AND element = ' . $db->Quote($attributes['plugin']);
				$db->setQuery($query);
				$db->query();
				$this->installed_plugs[]    = array('plugin' => $attributes['plugin'] . $new, 'group'=> $attributes['group'], 'upgrade' => true);
			}
			else
			{
				$this->installed_plugs[]    = array('plugin' => $attributes['plugin'], 'group' => $attributes['group'], 'upgrade' => false);
				$this->iperror[] = JText::_('Error installing plugin') . ': ' . $attributes['plugin'];
			}
		}

		return true;

		// Install modules
		foreach($manifest->modules->module as $module)
		{
			$attributes             = $module->attributes();
			$mod                    = $source . '/' . $attributes['folder'] . '/' . $attributes['module'];
			$new                    = ($attributes['new']) ? '&nbsp;(<span class="green">New in v.' . $attributes['new'] . '!</span>)' : '';

			if ($installer->install($mod))
			{
				$this->installed_mods[] = array('module' => $attributes['module'] . $new, 'upgrade' => true);
			}
			else
			{
				$this->installed_mods[] = array('module' => $attributes['module'], 'upgrade' => false);
				$this->iperror[] = JText::_('Error installing module') . ': ' . $attributes['module'];
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
	 * method to uninstall the component
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function uninstall($parent)
	{
		// Uninstall extensions
		$this->uninstallLibraries($parent);
	}

	/**
	 * Uninstall the package libraries
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 */
	protected function uninstallLibraries($parent)
	{
		// Required objects
		$installer = $this->getInstaller();
		$manifest  = $this->getManifest($parent);

		if ($nodes = $manifest->libraries->library)
		{
			foreach ($nodes as $node)
			{
				$extName = $node->attributes()->name;
				$result  = 0;

				if ($extId = $this->searchExtension($extName, 'library', 0))
				{
					$result = $installer->uninstall('library', $extId);
				}
			}
		}
	}
}
