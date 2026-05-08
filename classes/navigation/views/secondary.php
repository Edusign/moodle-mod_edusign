<?php
/**
 * Secondary navigation for the Edusign activity.
 *
 * @package    mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_edusign\navigation\views;

defined('MOODLE_INTERNAL') || die();

class secondary extends \core\navigation\views\secondary
{
    /**
     * Defines the module secondary navigation order.
     *
     * @return array
     */
    protected function get_default_module_mapping(): array
    {
        $nodes = parent::get_default_module_mapping();
        $nodes[self::TYPE_SETTING]['edusignreport'] = 1;
        $nodes[self::TYPE_SETTING]['modedit'] = 2;
        return $nodes;
    }
}
