<?php


class ECF_FontData {

    function add_font_family($family_name) {

        if (is_array($this->font_families[$family_name])) {
            return false;
        }

        if ($family_name != '' && preg_match('/^[a-z0-9 .\-]+$/i', $family_name)) {
            $this->font_families[$family_name] = array();
            return true;
        } else {
            return false;
        }
    }

    function delete_font_family($family_name) {
        unset($this->font_families[$family_name]);
    }


    function add_font_face($family_name, $font_face_array) {

        if (!is_array($this->font_families[$family_name])) {
            return false;
        }

        if (!$font_face_array['font-weight'] || !$font_face_array['font-style'] || $_POST['font_file_url']=='' ) {
            return false;
        }

        $this->font_families[$family_name][] = $font_face_array;

        return true;
    }            

    function get_css() {

        ob_start();

        foreach ($this->font_families as $family_name => $family) {

            foreach ($family as $font_face) {

                echo "@font-face {\n";
                echo "\t"."font-family: '".$family_name."';\n";

                foreach ($font_face as $property => $value) {
                    echo "\t".$property.": ".$value.";\n";
                }

                echo "}\n\n";

            }
        }

        $css = ob_get_clean();

        return $css;

    }

}

