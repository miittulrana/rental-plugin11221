<?php
/**
 * ItemModel Option Manager (with setup for single item)

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\ItemModel;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class ItemModelOptionManager
{
    private $conf 	    = null;
    private $lang 		= null;
    private $settings   = array();
    private $debugMode 	= 0;
    private $itemModelId 	= 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramItemModelId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        $this->itemModelId = StaticValidator::getValidPositiveInteger($paramItemModelId, 0);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getFirstIds()
    {
        $ret = 0;
        $validItemModelId = StaticValidator::getValidPositiveInteger($this->itemModelId, 0);
        $optionIds = $this->conf->getInternalWPDB()->get_col("
            SELECT option_id
            FROM {$this->conf->getPrefix()}options
            WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
            ORDER BY option_name ASC
            LIMIT 1
        ");
        if(sizeof($optionIds) > 0)
        {
            $ret = $optionIds[0];
        }

        return $ret;
    }

    public function getAllIds()
    {
        $validItemModelId = StaticValidator::getValidPositiveInteger($this->itemModelId, 0);
        $optionIds = $this->conf->getInternalWPDB()->get_col("
            SELECT option_id
            FROM {$this->conf->getPrefix()}options
            WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
            ORDER BY option_name ASC
        ");

        return $optionIds;
    }

    private function getOptions()
    {
        $retOptions = array();

        $optionIds = $this->getAllIds();
        foreach($optionIds AS $optionId)
        {
            $objOption = new ItemModelOption($this->conf, $this->lang, $this->settings, $optionId);
            $retOptions[] = $objOption->getDetails();
        }

        return $retOptions;
    }

    /**
     * @return int
     */
    public function getTotalOptions()
    {
        $validItemModelId = StaticValidator::getValidPositiveInteger($this->itemModelId, 0);
        $totalOptions = $this->conf->getInternalWPDB()->get_var("
			SELECT COUNT(option_id) AS total_options
			FROM {$this->conf->getPrefix()}options
			WHERE item_id='{$validItemModelId}' AND blog_id='{$this->conf->getBlogId()}'
		");

        return !is_null($totalOptions) ? intval($totalOptions) : 0;
    }

    public function getTranslatedDropdown($paramSelectedOptionId = 0)
    {
        return $this->getDropdown($paramSelectedOptionId, true);
    }

    public function getDropdown($paramSelectedOptionId = 0, $paramTranslated = false)
    {
        $options = $this->getOptions();

        $ret = '';
        $ret .= '<select name="item_model_options['.$this->itemModelId.']">';
        foreach($options AS $option)
        {
            $printOptionName = $paramTranslated ? $option['print_translated_option_name'] : $option['print_option_name'];
            $selected = $option['option_id'] == $paramSelectedOptionId ? ' selected="selected"': '';
            $ret .= '<option value="'.$option['option_id'].'"'.$selected.'>'.$printOptionName.'</option>';
        }
        $ret .= '</select>';

        return $ret;
    }
}