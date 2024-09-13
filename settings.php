<?php

/**
 *
 * @package   mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) { // needs this condition for know if user has admin rights
    require_once(dirname(__FILE__).'/lib.php');
    
    if (empty(get_config('mod_edusign', 'webhook_token'))) {
        set_config('webhook_token', bin2hex(random_bytes(20)), 'mod_edusign');
    }
    
    
    $tabmenu = edusign_print_settings_tabs();

    $settings->add(new admin_setting_heading('edusign_header', '', $tabmenu));

    // $plugininfos = core_plugin_manager::instance()->get_plugins_of_type('mod');

    // On récupère la valeur avec : get_config('mod_edusign', 'apikey_text')

    $settings->add(new admin_setting_heading('mod_edusign/settings_section_config', get_string('settings_section_config', 'mod_edusign'), null));
    
    $settings->add(new admin_setting_configtext(
        'mod_edusign/apiurl',
        get_string('apiurl_text', 'mod_edusign'),
        get_string('apiurl_text_help', 'mod_edusign'),
        'https://ext.edusign.fr',
        PARAM_URL
    ));
    
    
    $settings->add(new admin_setting_configpasswordunmask_with_advanced(
        'mod_edusign/apikey',
        get_string('apikey_text', 'mod_edusign'),
        get_string('apikey_text_help', 'mod_edusign'),
        null,
        PARAM_TEXT
    ));
    
    $ADMIN->add('modplugins', $settings);
}
