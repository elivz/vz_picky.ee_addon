<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ Picky Plugin
 *
 * @package     ExpressionEngine
 * @subpackage  Addons
 * @category    Plugin
 * @author      Eli Van Zoeren
 * @link        http://elivz.com
 */


$plugin_info = array(
    'pi_name'        => 'VZ Picky',
    'pi_version'     => '1.3.0',
    'pi_author'      => 'Eli Van Zoeren',
    'pi_author_url'  => 'https://github.com/elivz/vz_picky.ee_addon',
    'pi_description' => 'Generates an option tag for each unique entry in a particular custom field.',
    'pi_usage'       => Vz_picky::usage()
);


class Vz_picky
{
    /**
     * Template output
     * @var string
     */
    public $return_data;

    /**
     * Main template tag
     *
     * @access public
     * @return void
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
        $status = ee()->TMPL->fetch_param('status');
        $sort = ee()->TMPL->fetch_param('sort');
        $sep = ee()->TMPL->fetch_param('separator');

        // Get the field ID
        ee()->db->select('field_id');
        $query = ee()->db->get_where(
            'exp_channel_fields',
            array(
                'field_name' => $field,
                'site_id' => $site_id,
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
            $this->return_data = '';
            return;
        }

        // Handle status parameter
        if ($status)
        {
            $status = str_replace('Open',   'open', $status);
            $status = str_replace('Closed', 'closed', $status);

            $status = ee()->functions->sql_andor_string($status, 'exp_channel_titles.status');
            $status = str_replace('AND', '', $status);

            if (stristr($status, "'closed'") === FALSE)
            {
                $status .= "exp_channel_titles.status != 'closed'";
            }
        }
        else
        {
            $status = "exp_channel_titles.status = 'open'";
        }

        // Get all the values from the database
        ee()->db->select($field_column);
        ee()->db->distinct();
        ee()->db->from('exp_channel_titles');
        ee()->db->join('exp_channel_data', 'exp_channel_data.entry_id = exp_channel_titles.entry_id');
        ee()->db->where($status, NULL, FALSE);

        $result = ee()->db->get();

        // Collect the values
        $values = array();
        foreach ($result->result_array() as $row)
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
                $output .= strip_tags($value);
                $output .= '</option>';
            }
            print($output); die();

            $this->return_data = $output;
        }
    }


    /**
     * Usage
     *
     * This function describes how the plugin is used.
     *
     * @access  public
     * @return  string
     */
    public static function usage()
    {
      ob_start();  ?>

VZ Picky will display a dropdown <select> list of all the unique values for a particular custom field. Perfect for advanced search forms! You can also use it as a tag pair to loop through the unique values and generate your own markup.

Single tag
---------------------------

<select>{exp:vz_picky field="field_name"}</select>

Outputs a list of <option> elements containing each unique value in the custom field.

Tag pair
---------------------------

{exp:vz_picky field="field_name"}
    {value}<br/>
{/exp:vz_picky}

Loops through each unique value in the field.

Required Parameter
---------------------------

field - The short name of the custom field to pull values from.

Optional Parameters
---------------------------

separator - You can further break apart each value using this parameter. If a field contains a comma-separated list of tags, for instance, this will list each tag as its own option

sort="alpha" - Sort the values alphabetically. If this is not set, they will be displayed in the order they appear in the database.

status - Only use values from entries with a particular status (or statuses). Uses the <a href="https://docs.expressionengine.com/v2/add-ons/channel/channel_entries.html#status">same syntax as the channel:entries tag</a>. Defaults to 'open'.

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