<?php

/**
 * Block edit form class for the block_pluginname plugin.
 *
 * @package     block_course_rating
 * @category    block
 * @copyright   2025, Daniel Morais <danielbergmorais@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_course_rating_edit_form extends block_edit_form
{
    protected function specific_definition($mform)
    {
        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block_course_rating'));

        //Options
        $options = [
            'finished' => get_string('finished', 'block_course_rating'),
            'in_progress' => get_string('in_progress', 'block_course_rating'),
        ];

        $mform->addElement(
            'select',
            'config_exibition',
            get_string('exibition', 'block_course_rating'),
            $options
        );
        $mform->setDefault('exibition', 'finished');
        $mform->setType('exibition', PARAM_TEXT);
    }
}
