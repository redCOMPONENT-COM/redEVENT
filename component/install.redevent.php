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
    private $installed_mods             = array();
    private $installed_plugs            = array();
    
	protected $_is15update = false;
	
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
		// in the case of an update from 1.5 version (2.0 stable), we might have just the tables without the extension being registered
		// in that case, we will need to run the mysql updates sql 2.5.b.2  , which was the same db as 2.0 stable		
		$row = JTable::getInstance('extension');
		$eid = $row->find(array('element' => 'com_redevent', 'type' => 'component'));
		if ($eid) { // version 2.5 already installed, or migrated from jupgrade
			$row->load($eid);
			if ($row->manifest_cache) { // it's really an update...
				return true;
			}
			// no manifest means it was migrated from 2.0 from jupgrade, be we still need to update the table from 2.0 structure
		}
		else {		
			// not installed, do we have a redevent table ?
			$tables = JFactory::getDbo()->getTableList();
			if (!in_array(JFactory::getDbo()->getPrefix().'redevent_settings', $tables)) {				
				// it s a clean install;
				return true;	
			}
		}
		// still here... means this is an update from 2.0
		$this->_is15update = true;
		return true;
	}
	
	public function install()
	{
		if ($this->_is15update) 
		{
			$this->updateFrom20();
		}
	}
	
	public function update()
	{
		if ($this->_is15update) {
			$this->updateFrom20();
		}
	}
	
	/**
	* method to run after an install/update/uninstall method
	*
	* @return void
	*/
	public function postflight($type, $parent)
	{
		$this->installModsPlugs($parent);
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
	 * method to update the database structure from a 2.0 version
	 * 
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
		$basepath = JPATH_ADMINISTRATOR.'/components/com_redevent/sql/updates/'.$dbDriver;
		$files = str_replace('.sql', '', JFolder::files($basepath, '\.sql$'));
		usort($files, 'version_compare');
		
		if (!count($files))
		{
			return false;
		}

		// equivalent version
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
				echo Jtext::sprintf('COM_REDEVENT_UPDATED_DB_TO', $file).'<br/>';
			}
		}
	}
}
