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
    'SELECT crh.id, crh.rating, crh.message, crh.createdat, CONCAT(us.firstname, \' \', us.lastname) as user_name , cs.fullname as course_name
    FROM {course_rating_history} crh
    LEFT JOIN {course_rating} cr ON cr.id = crh.originid
    LEFT JOIN  {user} us ON us.id = cr.userid 
    LEFT JOIN  {course} cs ON cs.id = cr.courseid
    WHERE cr.userid = :userid
    order by crh.createdat
    LIMIT 10
    ',
    ['userid' => $USER->id]
);
$fmt = new IntlDateFormatter(
    'pt_BR',
    IntlDateFormatter::FULL,
    IntlDateFormatter::FULL,
    'America/Sao_Paulo',
    IntlDateFormatter::GREGORIAN
);
$fmt->setPattern('dd/MM/yyyy H:mm:ss');

echo $OUTPUT->header();

$registros = [];
foreach ($history as $h) {
    $h->date_create = $fmt->format(strtotime($h->createdat));
    $registros[] = $h;
}

$templatecontent = (object)[
    'registros' => $registros
];

echo $OUTPUT->render_from_template('block_course_rating/history', $templatecontent);

echo $OUTPUT->footer();
