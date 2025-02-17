<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('block_slack_heading', 
                get_string('settings_heading', 'block_course_rating'),
                get_string('settings_content', 'block_course_rating')));
}