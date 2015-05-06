<?php
/**
 * @version		1.6 October 6, 2011
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

defined('_JEXEC') or die ('Restricted access');

class JElementRokMiniEventsSources extends JElement
{
    static $ROKMINIEVENTS_ROOT;
    static $SOURCE_DIR;

    protected $element_dirs  = array();

    public function __construct($parent = null)
    {
        if (!defined('ROKMINIEVENTS')) define('ROKMINIEVENTS','ROKMINIEVENTS');

        // Set base dirs
        self::$ROKMINIEVENTS_ROOT = JPATH_ROOT.'/modules/mod_rokminievents';
        self::$SOURCE_DIR = self::$ROKMINIEVENTS_ROOT.'/lib/RokMiniEvents/Source';

        //load up the RTCommon
        require_once(self::$ROKMINIEVENTS_ROOT. '/lib/include.php');

        parent::__construct($parent);
    }

	function fetchElement($name, $value, &$node, $control_name) {

        $buffer = '';
        //Find Sources
        $sources = RokMiniEvents_SourceLoader::getAvailableSources(self::$SOURCE_DIR);
        foreach($sources as $source_name => $source){
            if (file_exists($source->paramspath) && is_readable($source->paramspath))
            {
                $this->element_dirs[] = dirname($source->paramspath)."/".$source->name;
                $language =JFactory::getLanguage();
                $language->load('com_'.$source->name, JPATH_ADMINISTRATOR);
                $language->load($source->name ,dirname($source->paramspath), $language->getTag(), true);
                $buffer .= $this->renderParamFile($source->paramspath,$name, $value, $node, $control_name);
            }
        }
        return $buffer;
	}

    function renderParamFile($file, $name, $value, &$node, $control_name)
    {
        $subparams = new JParameter($this->_parent->_raw, $file);
        foreach($this->element_dirs as $element_dir) $subparams->addElementPath($element_dir);
        $renders = $subparams->renderToArray();
        $html =array();

       $html[] = '</td></tr>';

        $i = 1;
        foreach ($renders as $param)
		{
            $html[] = '<tr>';
            if ($param[0]) {
                $html[] = '<td width="40%" class="paramlist_key"><span class="editlinktip">'.$param[0].'</span></td>';
                $html[] = '<td class="paramlist_value">'.$param[1].'</td>';
            } else {
                $html[] = '<td class="paramlist_value" colspan="2">'.$param[1].'</td>';
            }
            $html[] = '</tr>';
		}

        $html[] =  '<td class="paramlist_value" colspan="2">';

        return implode("\n", $html);
    }

}
