<?php

/**
 * Block history class for the block_pluginname plugin.
 *
 * @package     block_course_rating
 * @category    block
 * @copyright   2025, Daniel Morais <danielbergmorais@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

global $DB, $USER;

$history = $DB->get_records_sql(
    'SELECT crh.rating, crh.message, CONCAT(us.firstname, \' \', us.lastname) as user_name , cs.fullname as course_name
    FROM {course_rating_history} crh
    LEFT JOIN {course_rating} cr ON cr.id = crh.originid
    LEFT JOIN  {user} us ON us.id = cr.userid 
    LEFT JOIN  {course} cs ON cs.id = cr.courseid
    WHERE cr.userid = :userid
     ',
    ['userid' => $USER->id]
);

echo $OUTPUT->header();

$templatecontent = (object)[
    'registros' => array_values($history)
];

echo $OUTPUT->render_from_template('block_course_rating/history', $templatecontent);

echo $OUTPUT->footer();
