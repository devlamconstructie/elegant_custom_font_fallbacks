
<?php

/*
Plugin Name: Elegant Custom Fonts
Description: Plugin appears in Settings -> Fonts. For each @font-face, specify .woff URL, weight, style, and a font family name. Appropriate @font-face rules will be saved to a CSS file and enqueued with wp_enqueue_stylesheet, so that you can use the font with the font-family CSS property.
Version: 1.0
Author: Louis Reingold
Author URI: http://louisreingold.com/
License: MIT
*/

define("ECF_OPTION_NAME", "elegant_custom_fonts_fontdata"); /* everything is stored in a single option in the wp_options table */

include "ECF_FontData.php";


class ECF_Plugin {
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));

        /* -1337 priority because @font-face is supposed to be first (or close to it) and because i'm way l33ter than you */
        add_action('wp_enqueue_scripts', array($this, 'enqueue_stylesheet'), -1337); 

        add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'plugin_action_links'));

    }

    function plugin_action_links($links) {
        array_push($links, '<a href="options-general.php?page=Elegant_Custom_Fonts"><b>Manage Fonts</b></a>');
        return $links;
    }

    function admin_menu() {
        add_options_page('Elegant Custom Fonts', 'Fonts', 'administrator', "Elegant_Custom_Fonts", array($this, 'admin_page'));
    }

    function admin_page() {

        $FontData = unserialize(get_option(ECF_OPTION_NAME));

        if (!is_a($FontData, "ECF_FontData")) {
            $FontData = new ECF_FontData();
        }

        if (isset($_POST['action'])) {

            if ($_POST['action'] == 'add_font_family') {

                if ( $FontData->add_font_family( sanitize_text_field($_POST['font_family_name']) ) ) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p>Font family added.</p>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p>Unable to add font family. Duplicate font family names are not allowed, and font family names can only contain letters, numbers, spaces and dashes.</p>
                    </div>
                    <?php
                }

            }


            if ($_POST['action'] == 'delete_font_family') {
                $FontData->delete_font_family($_POST['font_family_name']);

                ?>
                <div class="notice notice-success is-dismissible">
                    <p>Font family and all associated @font-face rules deleted.</p>
                </div>
                <?php

            }


            if ($_POST['action'] == 'add_font_face') {

                $font_face_array = array(
                    'font-weight' => sanitize_text_field($_POST['font_weight']),
                    'font-style' => sanitize_text_field($_POST['font_style']),
                    'src' => "url(".sanitize_text_field($_POST['font_file_url']).")"
                );                                

                if ( $FontData->add_font_face(sanitize_text_field($_POST['font_family_name']), $font_face_array) ) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p>@font-face rule added.</p>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p>Unable to add @font-face rule.</p>
                    </div>
                    <?php
                }

            }

            update_option(ECF_OPTION_NAME, serialize($FontData));

            if ($this->write_css_to_file()) {
                $upload_dir = wp_upload_dir();
                $filepath = $upload_dir['basedir'].'/elegant-custom-fonts'."/ecf.css";

                ?>
                <div class="notice notice-success is-dismissible">
                    <p>@font-face rules written to <?php echo $filepath; ?> </p>
                </div>
                <?php
            } else {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>Unable to write @font-face rules to <?php echo $filepath; ?></p>
                </div>
                <?php
            }

        }



        ?>

        <style>
        /*
        inline styles cause that's how i roll sometimes.
        don't hate. 
        */

        .ecf-admin-fontcard-toprow {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ecf-admin-add-font-face-form, .ecf-font-face-rules, .ecf-admin-add-font-family-form {
            display: none;
        }

        .ecf-font-face-rules pre {
             white-space: pre-wrap;
             word-wrap: break-word;
             font-size: 0.8em;
        }

        </style>

        <script>
        /*
        inline scripts too cause that's how i roll sometimes.
        don't hate. 
        */

        jQuery(document).ready(function() {

            jQuery('.ecf-add-font-face-toggle-button').click(function(e) {

                e.preventDefault();

                jQuery(this).next().toggle();
                jQuery(this).hide();

            });

            jQuery('.ecf-view-font-face-rules-toggle-button').click(function(e) {

                e.preventDefault();

                jQuery(this).next().toggle();

            });

            jQuery('.ecf-add-font-family-button').click(function(e) {

                e.preventDefault();

                jQuery('.ecf-admin-font-family-card').hide();

                jQuery('.ecf-admin-add-font-family-form').toggle();

            });


        });

        </script>



        <div class="wrap">

            <h1 class="wp-heading-inline">Elegant Custom Fonts</h1>
            <a href="#" class="page-title-action ecf-add-font-family-button">Add Font Family</a>
            <hr class="wp-header-end">

            <div class='ecf-admin-add-font-family-form'>
                <form method='post'>

                    <input type='hidden' name='action' value='add_font_family' />

                    <table class="form-table">
                    <tbody>

                    <tr>
                    <th scope="row"><label for="font_family_name">Font Family Name</label></th>
                    <td>
                        <input type='text' name='font_family_name' class='regular-text' />
                    </td>
                    </tr>

                    </table>

                    <input type='submit' value='Add Font Family' class='button button-primary button-large' />

                </form>
            </div>

            <?php

            if (count($FontData->font_families) < 1) {
                ?>
                <div class='card'>
                    <p>
                        Dear User,
                    </p>

                    <p>
                        Before you <a href='#' class='ecf-add-font-family-button'>add your first font family</a>, do you know what makes a website look elegant?
                    </p>

                    <p>
                        A major factor is the quality of its typography.
                    </p>

                    <p>
                        To learn how to make your typography great, I recommend reading Matthew Butterick's article <a href='https://practicaltypography.com/typography-in-ten-minutes.html' target='_blank'>Typography in Ten Minutes</a> or his excellent book <a href='https://practicaltypography.com/' target='_blank'>Practical Typography</a>.
                    </p>

                    <p>
                        Best Regards,<br />
                        Louis Reingold<br />
                        <i>Soflyy Founder &amp; CEO</i><br />
                    </p>

                </div>

                <?php
            }

            ?>


            <?php

            foreach ($FontData->font_families as $family_name => $family) {

                echo "<div class='card ecf-admin-font-family-card'>";

                    echo "
                    <div class='ecf-admin-fontcard-toprow'>
                        <h3>".$family_name."</h3>";
                    
                        ?>

                        <form method='post'>
                            <input type='hidden' name='font_family_name' value='<?php echo $family_name; ?>' />
                            <input type='hidden' name='action' value='delete_font_family' />
                            <input type='submit' value='delete' class='button' />
                        </form>

                    </div>

                    <a href='#' class='button button-primary ecf-add-font-face-toggle-button'>add @font-face</a>

                    <form method='post' class='ecf-admin-add-font-face-form'>

                        <input type='hidden' name='font_family_name' value='<?php echo $family_name; ?>' />

                        <table class="form-table">
                        <tbody>

                        <tr>
                        <th scope="row"><label for="font_weight">font-weight</label></th>
                        <td>
                        <select name='font_weight' class='postform'>
                            <option>100</option>
                            <option>200</option>
                            <option>300</option>
                            <option selected>400</option>
                            <option>500</option>
                            <option>600</option>
                            <option>700</option>
                            <option>800</option>
                            <option>900</option>
                        </select>
                        </td>
                        </tr>

                        <tr>
                        <th scope="row"><label for="font_style">font-style</label></th>
                        <td>
                        <select name='font_style'>
                            <option>normal</option>
                            <option>italic</option>
                        </select>
                        </td>
                        </tr>

                        <tr>
                        <th scope="row"><label for="font_file_url">.woff URL<br />
                        </label></th>
                        <td>
                        <input type='text' name='font_file_url' class='regular-text' /><br />
                        <p class='description'>Use a protocol agnostic URL, e.g. <code>//example.com/example.woff</code></p>
                        </td>
                        </tr>

                        </tbody></table>

                        <input type='hidden' name='action' value='add_font_face' />

                        <input type='submit' value='Add Font Face' class='button button-primary button-large' />

                    </form>                   

                    <br /><br />
                    
                    <?php

                    if (count($family) > 0) {
                        ?>
                        <a href='#' class='ecf-view-font-face-rules-toggle-button'>View @font-face Rules</a>
                        <?php
                    }

                    ?>

                    <div class='ecf-font-face-rules'>
                        <?php

                        foreach ($family as $font_face) {

                            echo "<pre>@font-face {\n";
                            echo "\t"."font-family: '".$family_name."';\n";

                            foreach ($font_face as $property => $value) {
                                echo "\t".$property.": ".$value.";\n";
                            }

                            echo "}\n</pre>";

                        }

                        ?>
                    </div>

                    <?php

                echo "</div>";

            }

            ?>

        </div>

        <?php
    }


    function write_css_to_file() {

        $FontData = unserialize(get_option(ECF_OPTION_NAME));

        $css = $FontData->get_css();

        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['basedir'].'/elegant-custom-fonts';

        if (!is_dir($filepath)) {
            mkdir($filepath);
        }

        if (file_put_contents($filepath."/ecf.css", $css) === FALSE) {
            return false;
        } else {
            return true;
        }

    }


    function enqueue_stylesheet() {

        $upload_dir = wp_upload_dir();

        $url = $upload_dir['baseurl'].'/elegant-custom-fonts/ecf.css';

        /* according to https://developer.wordpress.org/reference/functions/wp_upload_dir/#comment-2576 
        wp_upload_dir doesn't work with https... lmfao.
        so i will strip out http (AND https since this might get fixed) 
        and just use the relative URL with no protofol prefix, i.e. //example.com/wp-content/uploads/
        which is the right way to do things anyway
        */

        $url = str_replace("http://", "//", $url);
        $url = str_replace("https://", "//", $url);

        wp_enqueue_style('elegant-custom-fonts', $url);

    }

    // plugin & theme developers... just call this function and then add the returned fonts to your "fonts" dropdown.
    function get_font_families() {
        $FontData = unserialize(get_option(ECF_OPTION_NAME));

        foreach ($FontData->font_families as $family_name => $family) {

            $font_family_list[] = $family_name;

        }

        return $font_family_list;
    }



}


$ECF_Plugin = new ECF_Plugin();

?>
