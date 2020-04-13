<?php
/**
 * Class to handle multi-line textarea form fields.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2018 Lee Garner <lee@leegarner.com>
 * @package     forms
 * @version     0.3.1
 * @since       0.3.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Forms\Fields;


/**
 * Textarea field type.
 */
class TextareaField extends \Forms\Field
{

    /**
     * Create a single form field for data entry.
     *
     * @param   integer $res_id Results ID, zero for new form
     * @param   string  $mode   Mode, e.g. "preview"
     * @return  string      HTML for this field, including prompt
     */
    public function displayField($res_id = 0, $mode = NULL)
    {
        global $_CONF, $LANG_FORMS, $_CONF_FRM;

        if (!$this->canViewField()) return NULL;
        $this->value = $this->renderValue($res_id, $mode);
        $elem_id = $this->_elemID();
        $js = $this->renderJS($mode);
        $access = $this->renderAccess();
        $cols = $this->getOption('cols', 80);
        $rows = $this->getOpton['rows', 5);
        $fld = "<textarea $access name=\"{$this->getName()}\"
                    id=\"$elem_id\"
                    cols=\"$cols\" rows=\"$rows\" $js
                    >{$this->value}</textarea>" . LB;
        return $fld;
    }


    /**
     * Get the formatted field to display in the results.
     *
     * @param   array   $fields     Array of all field objects (not used)
     * @return  string              Formatted field for display
     */
    public function displayValue($fields)
    {
        if (!$this->canViewResults()) return NULL;
        return nl2br($this->value);
    }


    /**
     * Get the field options when the definition form is submitted.
     *
     * @param   array   $A  Array of all form fields
     * @return  array       Array of options for this field type
     */
    protected function optsFromForm($A)
    {
        global $_CONF_FRM;

        // Call the parent function to get default options
        $options = parent::optsFromForm($A);
        // Add in options specific to this field type
        $options['cols'] = $A['cols'] > 0 ? (int)$A['cols'] : $_CONF_FRM['def_textarea_cols'];
        $options['rows'] = $A['rows'] > 0 ? (int)$A['rows'] : $_CONF_FRM['def_textarea_rows'];
        return $options;
    }
 
}

?>
