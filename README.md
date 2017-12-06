VZ Picky
========

VZ Picky will display a dropdown `<select>` list of all the unique values for a particular custom field. Perfect for advanced search forms! You can also use it as a tag pair to loop through the unique values and generate your own markup.

Usage
-----

### Single tag

    <select>
        {exp:vz_picky field="field_name"}
    </select>

Outputs a list of `<option>` elements containing each unique value in the custom field.

### Tag pair

    {exp:vz_picky field="field_name"}
        {value}<br/>
    {/exp:vz_picky}

Loops through each unique value in the field.

Required parameter
------------------

`field` - The short name of the custom field to pull values from.

Optional parameters
-------------------

`separator` - You can further break apart each value using this parameter. If a field contains a comma-separated list of tags, for instance, this will list each tag as its own option

`sort="alpha"` - Sort the values alphabetically. If this is not set, they will be displayed in the order they appear in the database.

`status` - Only use values from entries with a particular status (or statuses). Uses the [same syntax as the channel:entries tag](https://docs.expressionengine.com/v2/add-ons/channel/channel_entries.html#status). Defaults to `open`.

`site_id` - On a multi-site installation, the id of the site to use. Defaults to 1.

### The following parameters only apply to the single-tag usage:

`value` - Value to pre-select.

`placeholder` - Text to display when nothing is selected.

`hide_placeholder="yes"` - Do not display any placeholder element. Mostly useful for multiple-selects.

Installation
============

Place the vz_picky folder into /system/expressionengine/third_party/. That's it, you're done!

Support
=======

This is provided for free with no guarantee or promise of support. However, if you run into any trouble or have a suggestion, please post an issue (or a pull-request!) at https://github.com/elivz/vz_picky.ee_addon. I'll do my best to help as time allows.