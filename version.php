<?php
/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var stdClass $plugin
 */

defined('MOODLE_INTERNAL') || die();
$composer = json_decode(file_get_contents(__DIR__ . '/composer.json'));

$plugin->version   = 2024111217;      // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2023042408;      // Requires this Moodle version.
$plugin->component = 'mod_edusign';   // Full name of the plugin (used for diagnostics).
$plugin->maturity = MATURITY_BETA;
$plugin->release = $composer->version;
