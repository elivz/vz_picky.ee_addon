<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ Picky Plugin
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2011 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 *
 */

$plugin_info = array(
    'pi_name'        => 'VZ Picky',
    'pi_version'     => '1.1.1',
    'pi_author'      => 'Eli Van Zoeren',
    'pi_author_url'  => 'http://github.com/elivz/vz_picky.ee2_addon',
    'pi_description' => 'Generates an option tag for each unique entry in a particular custom field.',
    'pi_usage'       => Vz_picky::usage()
);

class Vz_picky {

    public $return_data = "";

    /**
     * Constructor
    */
    public function __construct()
    {
        $this->EE =& get_instance();

        // Store the template parameters
        $placeholder = $this->EE->TMPL->fetch_param('placeholder', '');
        $hide_placeholder = $this->EE->TMPL->fetch_param('hide_placeholder');
        $site_id = $this->EE->TMPL->fetch_param('site_id', '1');
        $selected = $this->EE->TMPL->fetch_param('value');
        $sort = $this->EE->TMPL->fetch_param('sort');
        $sep = $this->EE->TMPL->fetch_param('separator');
        $field = $this->EE->TMPL->fetch_param('field');
        if (!$field) return;

        $output = '';
        if ($hide_placeholder != 'yes') {
            $output = '<option value="">'.$placeholder.'</option>';
        }

        // Get the field ID
        $this->EE->db->select('field_id');
        $query = $this->EE->db->get_where(
            'exp_channel_fields',
            array(
                'field_name' => $field,
                'site_id' => $site_id
            ),
            1
        );
        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            $field_column = 'field_id_'.$row->field_id;
        }
        else
        {
            die("Field was not found");
        }

        // Get all the values from the database
        $this->EE->db->select($field_column);
        $this->EE->db->distinct();
        $query = $this->EE->db->get('exp_channel_data');

        // Collect the values
        $values = array();
        foreach ($query->result_array() as $row)
        {
            if ($row[$field_column] != '')
            {
                if ($sep)
                {
                    $values = array_merge($values, array_map('trim', explode($sep, $row[$field_column])));
                }
                else
                {
                    $values[] = $row[$field_column];
                }
            }
        }

        // Remove duplicates
        $values = array_unique($values);

        // Sort them
        if ($sort == 'alpha')
        {
            natcasesort($values);
        }

        // Construct the markup string
        foreach ($values as $value)
        {
            $output .= ($value == $selected) ? '<option selected="selected">' : '<option>';
            $output .= $value.'</option>';
        }
        $this->return_data = $output;
    }

    // --------------------------------------------------------------------

    public static function usage()
    {
        ob_start();
    ?>

        VZ Picky will display a dropdown <select> list of all the unique values for a particular custom field. Perfect for advanced search forms!

        Usage:

        {exp:vz_picky field="field_name"}

        field="" - The short name of the custom field to pull values from.

        Optional parameters:

        value="" - Value to pre-select.

        separator="" - You can further break apart each value using this parameter. If a field contains a comma-separated list of tags, for instance, this will list each tag as its own <option>.

        placeholder="" - Text to display when nothing is selected.

        sort="alpha" - Sort the values alphabetically. If this is not set, they will be displayed in the order they appear in the database.

        site_id="" - On a multi-site installation, the id of the site to use. Defaults to 1.

    <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

}
/* End of file pi.vz_picky.php */
/* Location: ./system/expressionengine/third_party/memberlist/pi.vz_picky.php */