<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Attendance module renderering methods
 *
 * @package    mod_edusign
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_edusign\output;

use plugin_renderer_base;
use html_table;
use html_table_row;
use html_table_cell;
use html_writer;
use single_select;
use stdClass;
use pix_icon;
use moodle_url;
use context_module;
use tabobject;
use js_writer;
use renderable;

class accordion implements renderable {
    public $title = "My testing title";
    public $content = "My testing content";
    
    public function __construct($content) {
        $this->content = $content['content'];        
    }
}

class renderer extends plugin_renderer_base {

    protected function render_accordion(accordion $submission) {
        $out  = $this->output->heading(format_string($submission->title), 2);
        $out .= $this->output->container($submission->content, 'content');
        return $this->output->container($out, 'submission');
    }
}