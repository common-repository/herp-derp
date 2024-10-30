<?php
/*
Plugin Name: Herp Derp
Plugin URI: https://www.jwz.org/herpderp/
Version: 1.7
Description: Adds a checkbox to replace the text of your comments with "Herp Derp".
Author: Jamie Zawinski
Author URI: https://www.jwz.org/
*/

/* Copyright © 2012-2023 Jamie Zawinski <jwz@jwz.org>

   Permission to use, copy, modify, distribute, and sell this software and its
   documentation for any purpose is hereby granted without fee, provided that
   the above copyright notice appear in all copies and that both that
   copyright notice and this permission notice appear in supporting
   documentation.  No representations are made about the suitability of this
   software for any purpose.  It is provided "as is" without express or 
   implied warranty.
 
   Inspired by "Herp Derp Youtube Comments" by Tanner Stokes:
   http://www.tannr.com/herp-derp-youtube-comments/

   This version by jwz, created: 13-Dec-2012.
 */


$herpderp_plugin_title     = 'Herp Derp';
$herpderp_plugin_name      = 'herpderp';
$herpderp_prefs_toggle_key = 'herp';
$herpderp_prefs_toggle_id  = "$herpderp_plugin_name-$herpderp_prefs_toggle_key";

add_action ('wp_enqueue_scripts', 'herpderp_init');
function herpderp_init() {  

  if (!is_singular() || is_feed() || !get_comments_number()) return;

  // Pass the default value of the prefs checkbox down into JS.
  global $herpderp_plugin_name;
  global $herpderp_prefs_toggle_key;
  $options = get_option ($herpderp_plugin_name) ?? null;
  $def = $options ? $options[$herpderp_prefs_toggle_key] : false;

  wp_register_script ('herpderp',
                      plugins_url ('herpderp.js' , __FILE__ ),
                      array(), null, true);
  wp_localize_script ('herpderp', 'Derpfault', array('herp' => $def));
  wp_enqueue_script ('herpderp');
}


// Include default styling.  Maybe this should be in the preferences page.
//
add_action ('wp_head', 'herpderp_head');
function herpderp_head() {
  if (!is_singular() || is_feed() || !get_comments_number()) return;
?>
<STYLE TYPE="text/css">
 .herpderp { float: right; text-transform: uppercase;
             font-size: 7pt; font-weight: bold; }
</STYLE>
<?php
}

// Identify comment text so we can find it later.
// Different themes emit comments differently.
add_filter ('comment_text', 'herpderp_comment_text', 40);  // after wpautop
function herpderp_comment_text($text) {
  if (!is_singular() || is_feed() || !get_comments_number())
    return $text;
  return "<span class='herpc'>$text</span>";
}


/*************************************************************************
 Admin pages
 *************************************************************************/

// Create the preferences fields and hook in to the database.
// Add the preferences field on the "Settings / Discussion" page,
// before the "Avatars" section.
//
add_action ('admin_init', 'herpderp_admin_init');
function herpderp_admin_init() {
  global $herpderp_plugin_name;
  global $herpderp_prefs_toggle_id;

  register_setting ('discussion', $herpderp_plugin_name, 'array');
  add_settings_field ($herpderp_prefs_toggle_id,
                      'Herp Derp',
                      'herpderp_setting_string',
                      'discussion',
                      'default');
}

// Generates the checkbox for our preference.
//
function herpderp_setting_string() {
  global $herpderp_plugin_name;
  global $herpderp_prefs_toggle_key;
  global $herpderp_prefs_toggle_id;

  $options = get_option ($herpderp_plugin_name);
  $def_toggle = $options ? $options[$herpderp_prefs_toggle_key] : false;

  echo "<input id='$herpderp_prefs_toggle_id'
             name='" . $herpderp_plugin_name . "[" .
                       $herpderp_prefs_toggle_key . "]'
             type='checkbox' value='herp' " . ($def_toggle ? ' checked' : '') .
       ' /> Herp Derp on by default';
}
