VZ Picky
========

VZ Picky will display a dropdown <select> list of all the unique values for a particular custom field. Perfect for advanced search forms!

Usage
-----

{exp:vz_picky field="field_name"}

field="" - The short name of the custom field to pull values from.

Optional parameters
-------------------

value="" - Value to pre-select.

separator="" - You can further break apart each value using this parameter. If a field contains a comma-separated list of tags, for instance, this will list each tag as its own <option>.

placeholder="" - Text to display when nothing is selected.

sort="alpha" - Sort the values alphabetically. If this is not set, they will be displayed in the order they appear in the database.

site_id="" - On a multi-site installation, the id of the site to use. Defaults to 1.