<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
    'pi_version'     => '1.2.0',
    'pi_author'      => 'Eli Van Zoeren',
    'pi_author_url'  => 'https://github.com/elivz/vz_picky.ee_addon',
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
        // Store the template parameters
        $field = ee()->TMPL->fetch_param('field');
        if ( ! $field ) return;

        $placeholder = ee()->TMPL->fetch_param('placeholder', '');
        $hide_placeholder = ee()->TMPL->fetch_param('hide_placeholder');
        $site_id = ee()->TMPL->fetch_param('site_id', '1');
        $selected = ee()->TMPL->fetch_param('value');
        $sort = ee()->TMPL->fetch_param('sort');
        $sep = ee()->TMPL->fetch_param('separator');

        // Get the field ID
        ee()->db->select('field_id');
        $query = ee()->db->get_where(
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
            $this->return_data = "Field was not found";
            return;
        }

        // Get all the values from the database
        $query = ee()->db->select($field_column)
                         ->distinct()
                         ->get('exp_channel_data');

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

        if (ee()->TMPL->tagdata)
        {
            if (count($values) > 0)
            {
                // Put the array in a format parse_variables can use
                $data = array();
                foreach ($values as $value)
                {
                    $data[] = array('value' => $value);
                }

                // Parse the template tags
                $this->return_data = ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $data);
            }
            else
            {
                // No records were found
                $this->return_data = ee()->TMPL->no_results();
            }
        }
        else
        {
            // Construct the markup string
            $output = '';
            if ($hide_placeholder != 'yes') {
                $output = '<option value="">'.$placeholder.'</option>';
            }

            foreach ($values as $value)
            {
                $output .= ($value == $selected) ? '<option selected="selected">' : '<option>';
                $output .= $value.'</option>';
            }

            $this->return_data = $output;
        }
    }

    // --------------------------------------------------------------------

    public static function usage()
    {
        ob_start();
    ?>

VZ Picky will display a dropdown <select> list of all the unique values for a particular custom field. Perfect for advanced search forms! You can also use it as a tag pair to loop through the unique values and generate your own markup.

USAGE

Single tag

<select>{exp:vz_picky field="field_name"}</select>

Outputs a list of <option> elements containing each unique value in the custom field.

Tag pair

{exp:vz_picky field="field_name"}
  {value}<br/>
{/exp:vz_picky}

Loops through each unique value in the field.

REQUIRED PARAMETER

field - The short name of the custom field to pull values from.

OPTIONAL PARAMETERS

separator - You can further break apart each value using this parameter. If a field contains a comma-separated list of tags, for instance, this will list each tag as its own option

sort="alpha" - Sort the values alphabetically. If this is not set, they will be displayed in the order they appear in the database.

site_id - On a multi-site installation, the id of the site to use. Defaults to 1.

The following parameters only apply to the single-tag usage:

value - Value to pre-select.

placeholder - Text to display when nothing is selected.

hide_placeholder="yes" - Do not display any placeholder element. Mostly useful for multiple-selects.

    <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

}
/* End of file pi.vz_picky.php */
/* Location: ./system/expressionengine/third_party/memberlist/pi.vz_picky.php */