
<?php
// Check that the class exists before trying to use it
if (class_exists('ECF_FontData')) {
  add_filter('add_font_family', array('ECF_FontData', 'add_font_family_and_fallbacks'), 10,1)
}

function add_font_family_and_fallbacks($family_name) {
     
        if (is_array($this->font_families[$family_name])) {
            return false;
        }
        $edited_regexp = '/^[a-z0-9 ,.\-]+$/i'; // added a comma to allow for fallback fonts 
        if ($family_name != '' && preg_match($edited_regexp, $family_name)) {
            $this->font_families[$family_name] = array();
            return true;
        } else {
            return false;
        }
    }




?>
