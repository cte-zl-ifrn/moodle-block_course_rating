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

global $DB;

$course = (int) $_GET['course'];
$offset = (int) $_GET['offset'];

if (!(is_int($course) && is_int($offset) && $course > 0 && $offset >= 0))
    return null;

$ratings = $DB->get_records('course_rating', ['courseid' => $course], 'createdat desc', ' *', $offset, 5);

$fmt = new IntlDateFormatter(
    'pt_BR',
    IntlDateFormatter::FULL,
    IntlDateFormatter::FULL,
    'America/Sao_Paulo',
    IntlDateFormatter::GREGORIAN
);
$fmt->setPattern('MMMM dd, yyyy');

$rating_list = [];
foreach ($ratings as $rating) {
    $record = $DB->get_record("user", ["id" => $rating->userid]);
    $record->pic =  $OUTPUT->user_picture($record, ['size' => 100, 'link' => false]);
    $review_stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating->rating) {
            $review_stars .=  $OUTPUT->pix_icon('star', $i, 'block_course_rating', ['class' => 'star-img-small']);
        } else {
            $review_stars .= $OUTPUT->pix_icon('star-o', $i, 'block_course_rating', ['class' => 'star-img-small']);
        }
    }

    $rating_list[] = [
        'user_name' => $record->firstname . ' ' . $record->lastname,
        'user_img' => $record->pic,
        'rating' => $review_stars,
        'rating_message' => $rating->message,
        'rating_date' => ucfirst($fmt->format(strtotime($rating->createdat))),
        'rating_edited' => $rating->createdat != $rating->updatedat,

    ];
}

$templatecontent = (object)[
    'ratings' => array_values($rating_list)
];

echo $OUTPUT->render_from_template('block_course_rating/rating_user', $templatecontent);
