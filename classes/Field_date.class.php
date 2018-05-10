<?php
/**
*   Class to handle individual form fields.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2018 Lee Garner <lee@leegarner.com>
*   @package    forms
*   @version    0.3.1
*   @since      0.3.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
namespace Forms;

/**
*   Class for form fields
*/
class Field_date extends Field
{

    /**
    *   Get the field value when submitted by a form.
    *   Need to assemble the date from the three form fields.
    *
    *   @param  array   $A      Array of all form fields
    *   @return string          Data value
    */
    public function valueFromForm($A)
    {
        $dt = array('0000', '00', '00');
        if (isset($A[$this->name . '_month'])) {
            $dt[1] = (int)$A[$this->name . '_month'];
        }
        if (isset($A[$this->name . '_day'])) {
            $dt[2] = (int)$A[$this->name . '_day'];
        }
        if (isset($A[$this->name . '_year'])) {
            $dt[0] = (int)$A[$this->name . '_year'];
        }
        if (isset($this->options['century']) && $this->options['century'] == 1 && $dt[0] < 100) {
            $dt[0] += 2000;
        }
        $tmpval = sprintf('%04d-%02d-%02d', $dt[0], $dt[1], $dt[2]);

        if (isset($this->options['showtime']) &&
                    $this->options['showtime'] == 1) {
            $hour = isset($A[$this->name . '_hour']) ?
                        (int)$A[$this->name . '_hour'] : 0;
            $minute = isset($A[$this->name . '_minute']) ?
                        (int)$A[$this->name . '_minute'] : 0;
            $tmpval .= sprintf(' %02d:%02d', $hour, $minute);
        }
        return $tmpval;
    }


    public function setValue($val)
    {
        $this->value = trim($val);
        $this->value_text = $this->DateDisplay();
        return $this->value;
    }


    /**
    *   Create a single form field for data entry.
    *
    *   @param  integer $res_id Results ID, zero for new form
    *   @param  string  $mode   Mode, e.g. "preview"
    *   @return string      HTML for this field, including prompt
    */
    public function displayField($res_id = 0, $mode = NULL)
    {
        global $_CONF, $LANG_FORMS, $_CONF_FRM;

        if (!$this->canViewField()) return NULL;

        $elem_id = $this->_elemID();
        $access = $this->renderAccess();

        //  Create the field HTML based on the type of field.
        $fld = '';
        $dt = array();
        // Check for POSTed values first, coming from a previous form
        // If one is set, all should be set, and empty values are ok
        if (isset($_POST[$this->name . '_month'])) {
            $dt[1] = $_POST[$this->name . '_month'];
        }
        if (isset($_POST[$this->name . '_day'])) {
            $dt[2] = $_POST[$this->name . '_day'];
        }
        if (isset($_POST[$this->name . '_year'])) {
            $dt[0] = $_POST[$this->name . '_year'];
        }

        // Nothing from POST, check for an existing value.  If none,
        // use the default.
        $value = $this->value;
        if (empty($dt)) {
            if (empty($value) && isset($this->options['default']) && !empty($this->options['default'])) {
                $this->value = $this->options['default'];
            } else {
                $dt = new \Date('now', $_CONF['timezone']);
                $this->value = $dt->format('Y-m-d', true);
            }
            $datestr = explode(' ', $this->value);  // separate date & time
            $dt = explode('-', $datestr[0]);        // get date components
        }

        $m_fld = $LANG_FORMS['month'] .
                ": <select $access id=\"{$this->name}_month\" name=\"{$this->name}_month\">\n";
        $m_fld .= "<option value=\"0\">--{$LANG_FORMS['select']}--</option>\n";
        $m_fld .= COM_getMonthFormOptions($dt[1]) . "</select>\n";

        $d_fld = $LANG_FORMS['day'] .
                ": <select $access id=\"{$this->name}_day\" name=\"{$this->name}_day\">\n";
        $d_fld .= "<option value=\"0\">--{$LANG_FORMS['select']}--</option>\n";
        $d_fld .= COM_getDayFormOptions($dt[2]) . "</select>\n";

        $y_fld = $LANG_FORMS['year'] .
                ': <input ' . $access . ' type="text" id="' . $this->name.'_year" name="'.$this->name.'_year"
                size="5" value="' . $dt[0] . "\"/>\n";

        switch ($this->options['input_format']) {
        case 1:
            $fld .= $m_fld . ' ' . $d_fld . ' ' . $y_fld;
            break;
        case 2:
            $fld .= $d_fld . ' ' . $m_fld . ' ' . $y_fld;
            break;
        }

        if ($this->options['showtime'] == 1) {
            $fld .= ' ' . $this->TimeField($datestr[1]);
            $timeformat = $this->options['timeformat'];
        } else {
            $timeformat = 0;
        }
        $fld .= '<i id="' . $this->name .
                    '_trigger" class="' . $_CONF_FRM['_iconset'] . '-calendar tooltip" ' .
                    'title="' . $LANG_FORMS['datepicker'] . '"></i>';
        $fld .= LB . "<script type=\"text/javascript\">
Calendar.setup({
    inputField  :    \"{$this->name}dummy\",
    ifFormat    :    \"%Y-%m-%d\",
    showsTime   :    false,
    timeFormat  :    \"{$timeformat}\",
    button      :   \"{$this->name}_trigger\",
    onUpdate    :   {$this->name}_onUpdate
});
function {$this->name}_onUpdate(cal)
{
    var d = cal.date;

    if (cal.dateClicked && d) {
        FRM_updateDate(d, \"{$this->name}\", \"{$timeformat}\");
    }
    return true;
}
</script>" . LB;
        return $fld;
    }


    /**
    *   Get the formatted field prompt and data to display with the results.
    *
    *   @param  object  $Result     Optional result to allow access to other fields
    *   @return array   Array of data, prompt
    */
    public function XXrenderData($Result=NULL)
    {
        return array(
            'data' => $this->value_text,
            'prompt' => $this->prompt == '' ? $this->name : $this->prompt,
        );
     }


    /**
    *   Rudimentary date display function to mimic strftime()
    *   Timestamps don't handle dates far in the past or future.  This function
    *   does a str_replace using a subset of PHP's date variables.  Only the
    *   numeric variables with leading zeroes are used.
    *
    *   @return string  Date formatted for display
    */
    public function displayValue($fields)
    {
        if (!$this->canViewResults()) return NULL;
        $dt_tm = explode(' ', $this->value);
        if (strpos($dt_tm[0], '-')) {
            list($year, $month, $day) = explode('-', $dt_tm[0]);
        } else {
            $year = '0000';
            $month = '01';
            $day = '01';
        }
        if (isset($dt_tm[1]) && strpos($dt_tm[1], ':')) {
            list($hour, $minute, $second) = explode(':', $dt_tm[1]);
        } else {
            $hour = '00';
            $minute = '00';
            $second = '00';
        }

        switch ($this->options['input_format']) {
        case 2:
            $retval = sprintf('%02d/%02d/%04d', $day, $month, $year);
            break;
        case 1:
        default:
            $retval = sprintf('%02d/%02d/%04d', $month, $day, $year);
            break;
        }
        if ($this->options['showtime'] == 1) {
            if ($this->options['timeformat'] == '12') {
                list($hour, $ampm) = $this->hour24to12($hour);
                $retval .= sprintf(' %02d:%02d %s', $hour, $minute, $ampm);
            } else {
                $retval .= sprintf(' %02d:%02d', $hour, $minute);
            }
        }
        return $retval;
    }


    /**
    *   Get the defined date formats into an array.
    *   Static for now, maybe allow more user-defined options in the future.
    *
    *   return  array   Array of date formats
    */
    public function DateFormats()
    {
        global $LANG_FORMS;
        $_formats = array(
            1 => $LANG_FORMS['month'].' '.$LANG_FORMS['day'].' '.$LANG_FORMS['year'],
            2 => $LANG_FORMS['day'].' '.$LANG_FORMS['month'].' '.$LANG_FORMS['year'],
        );
        return $_formats;
    }


    /**
    *   Provide a dropdown selection of date formats
    *
    *   @param  integer $cur    Option to be selected by default
    *   @return string          HTML for selection, without select tags
    */
    public function DateFormatSelect($cur=0)
    {
        $retval = '';
        $_formats = self::DateFormats();
        foreach ($_formats as $key => $string) {
            $sel = $cur == $key ? 'selected="selected"' : '';
            $retval .= "<option value=\"$key\" $sel>$string</option>\n";
        }
        return $retval;
    }


    /**
    *   Validate the submitted field value(s)
    *
    *   @param  array   $vals  All form values
    *   @return string      Empty string for success, or error message
    */
    public function Validate(&$vals)
    {
        global $LANG_FORMS;

        $msg = '';
        if (!$this->enabled) return $msg;   // not enabled
        if (($this->access & FRM_FIELD_REQUIRED) != FRM_FIELD_REQUIRED)
            return $msg;        // not required

        if (empty($vals[$this->name . '_month']) ||
                empty($vals[$this->name . '_day']) ||
                empty($vals[$this->name . '_year'])) {
                $msg = $this->prompt . ' ' . $LANG_FORMS['is_required'];
       }
       return $msg;
    }


    /**
    *   Create the time field.
    *   This is in a separate function so it can be used by both date
    *   and time fields
    *
    *   @uses   hour24to12()
    *   @param  string  $timestr    Optional HH:MM string.  Seconds ignored.
    *   @return string  HTML for time selection field
    */
    public function XXTimeField($timestr = '')
    {
        $ampm_fld = '';
        $hour = '';
        $minute = '';

        // Check for POSTed values first, coming from a previous form
        // If one is set, all should be set, and empty values are ok
        if (isset($_POST[$this->name . '_hour']) &&
            isset($_POST[$this->name . '_minute'])) {
            $hour = (int)$_POST[$this->name . '_hour'];
            $minute = (int)$_POST[$this->name . '_minute'];
        }
        if (empty($hour) || empty($minute)) {
            if (!empty($timestr)) {
                // Default to the specified time string
                list($hour, $minute)  = explode(':', $timestr);
            } elseif (!empty($this->options['default'])) {
                if (strtolower($this->options['default']) == '$now') {
                    // Handle the special "now" default"
                    list($hour, $minute) = explode(':', date('H:i:s'));
                } else {
                    // Expecting a 24-hour time as "HH:MM"
                    list($hour, $minute) = explode(':', $this->options['default']);
                }
            }
        }

        // Nothing selected by default, or invalid values
        if (empty($hour) || empty($minute) ||
            !is_numeric($hour) || !is_numeric($minute) ||
            $hour < 0 || $hour > 23 ||
            $minute < 0 || $minute > 59) {
            list($hour, $minute) = array(0, 0);
        }

        if ($this->options['timeformat'] == '12') {
            list($hour, $ampm_sel) = $this->hour24to12($hour);
            $ampm_fld = '&nbsp;&nbsp;' .
                COM_getAmPmFormSelection($this->name . '_ampm', $ampm_sel);
        }

        $h_fld = '<select name="' . $this->name . '_hour">' . LB .
                COM_getHourFormOptions($hour, $this->options['timeformat']) .
                '</select>' . LB;
        $m_fld = '<select name="' . $this->name . '_minute">' . LB .
                COM_getMinuteFormOptions($minute) .
                '</select>' . LB;
        return $h_fld . ' ' . $m_fld . $ampm_fld;
    }


    /**
    *   Convert an hour from 24-hour to 12-hour format for display.
    *
    *   @param  integer $hour   Hour to convert
    *   @return array       array(new_hour, ampm_indicator)
    */
    public function XXhour24to12($hour)
    {
        if ($hour >= 12) {
            $ampm = 'pm';
            if ($hour > 12) $hour -= 12;
        } else {
            $ampm = 'am';
            if ($hour == 0) $hour = 12;
        }
        return array($hour, $ampm);
    }


    /**
    *   Get the field options when the definition form is submitted.
    *
    *   @param  array   $A  Array of all form fields
    *   @return array       Array of options for this field type
    */
    public function optsFromForm($A)
    {
        global $_CONF_FRM;

        // Call the parent function to get default options
        $options = parent::optsFromForm($A);
        // Add in options specific to this field type
        $options['showtime'] = isset($A['showtime']) && $A['showtime'] == 1 ? 1 : 0;
        $options['timeformat'] = $A['timeformat'] == '24' ? '24' : '12';
        $options['format'] = isset($A['format']) ? $A['format'] :
                        $_CONF_FRM['def_date_format'];
        $options['input_format'] = (int)$A['input_format'];
        $options['century'] = isset($A['century']) && $A['century'] == 1 ? 1 : 0;
        return $options;
    }

}

?>
