<?php
/**
 * @package    Redevent.github
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file should be placed in a direct subdirectory of site root
 */

if (!defined('_JEXEC'))
{
	// Initialize Joomla framework
	define('_JEXEC', 1);
}

@ini_set('zend.ze1_compatibility_mode', '0');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', dirname(__DIR__));
}

if (!defined('_JDEFINES'))
{
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.php';

/**
 * Put an application online
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class RedInstall extends JApplicationCli
{
	private $extension = 'redeventsync';

	private $manifest;

	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		$db = JFactory::getDbo();

		$manifest = $this->getManifest();
		$newVersion = (string) $manifest->version;

		$manifestCache = $db->setQuery(
			$db->getQuery(true)
				->select('manifest_cache')
				->from('#__extensions')
				->where('element = "com_' . $this->extension . '"')
		)
			->loadResult();
		$manifestCache = json_decode($manifestCache);


		$oldVersion = (string) $manifestCache->version;

		$this->dbUpdate();

		if (version_compare($newVersion, $oldVersion) > 0)
		{
			$manifestCache->version = $newVersion;
			$newManifestCache = json_encode($manifestCache);
			$db->setQuery(
				$db->getQuery(true)
					->update('#__extensions')
					->set('manifest_cache = ' . $db->q($newManifestCache))
					->where('element = "com_' . $this->extension . '"')
			)
				->execute();
			$this->out('Extension Version Updated');
		}
	}

	/**
	 * Perform db update
	 *
	 * @return bool
	 */
	private function dbUpdate()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$db = JFactory::getDbo();

		$oldVersion = $this->getCurrentDbSchemaVersion();

		$path = $this->getPath();
		$files = JFolder::files($path . '/sql/updates/mysql', '.sql');
		usort($files, 'version_compare');

		foreach ($files as $queryFile)
		{
			if (version_compare(JFile::stripExt($queryFile), $oldVersion) > 0)
			{
				$queryString = file_get_contents($path . '/sql/updates/mysql/' . $queryFile);
				$queries = JInstallerHelper::splitSql($queryString);

				// Process each query in the $queries array (split out of sql file).
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);

						if (!$db->execute())
						{
							JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), JLog::WARNING, 'jerror');

							return false;
						}
					}
				}
			}
		}
	}

	/**
	 * Get current schema version
	 *
	 * @return mixed
	 */
	private function getCurrentDbSchemaVersion()
	{
		$row = JTable::getInstance('extension');
		$eid = $row->find(array('element' => strtolower('com_' . $this->extension), 'type' => 'component'));

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('version_id')
			->from('#__schemas')
			->where('extension_id = ' . $eid);
		$db->setQuery($query);
		$version = $db->loadResult();

		return $version;
	}

	/**
	 * Return manifest SimpleXMLElement
	 *
	 * @return SimpleXMLElement manifest
	 */
	private function getManifest()
	{
		if (!$this->manifest)
		{
			$path = $this->getPath();

			$manifestFile = $path . '/' . $this->extension . '.xml';
			$this->manifest = new SimpleXMLElement(file_get_contents($manifestFile));
		}

		return $this->manifest;
	}

	/**
	 * Path to admin
	 *
	 * @return string
	 */
	private function getPath()
	{
		return JPATH_ADMINISTRATOR . '/components/com_' . $this->extension;
	}
}

JApplicationCli::getInstance('RedInstall')->execute();
