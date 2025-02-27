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

$section = $_GET['section'];
$course = (int) $_GET['course'];
$offset = (int) $_GET['offset'];
$limit = 5;

switch ($section) {
    case 'RATINGS':
        // Get all ratings
        $ratings_all = $DB->get_records_sql(
            'SELECT rating, count(id) FROM {course_rating} WHERE courseid = :course
            GROUP BY rating, courseid ORDER BY rating DESC',
            ['course' => $course]
        );
        $total_ratings = 0;
        $sum_rating = 0;
        foreach ($ratings_all as $rating) {
            $rating_stars_percents[$rating->rating] = (int)$rating->count;
            $total_ratings += $rating->count;
            $sum_rating += $rating->rating * $rating->count;
        }

        $sum_rating = $total_ratings ? round($sum_rating / $total_ratings) : 0;
        $rating_stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $sum_rating) {
                $rating_stars .= $OUTPUT->pix_icon('star', $i, 'block_course_rating', ['class' => 'star-img']);
            } else {
                $rating_stars .= $OUTPUT->pix_icon('star-o', $i, 'block_course_rating', ['class' => 'star-img']);
            }
        }

        //Rating percents
        $stars_percents = '';
        $stars_bars = '';
        for ($x = 5; $x >= 1; $x--) {
            $stars_percents .= html_writer::start_span('d-flex justify-content-center justify-content-sm-start', []);
            for ($y = 0; $y < $x; $y++) {
                $stars_percents .=  $OUTPUT->pix_icon('star', $y + 1, 'block_course_rating', ['class' => 'star-img-small']);
            }
            for ($y = $x; $y < 5; $y++) {
                $stars_percents .=  $OUTPUT->pix_icon('star-o', $y + 1, 'block_course_rating', ['class' => 'star-img-small']);
            }
            $_calc_rating = $total_ratings ?  round(($rating_stars_percents[$x] / $total_ratings) * 100) : 0;
            $stars_percents .= html_writer::span($_calc_rating . ' %', 'text_review text_percent');
            $stars_percents .= html_writer::end_span();
            $percent_bar = $total_ratings ? ($rating_stars_percents[$x] / $total_ratings) : 0;
            $stars_bars .= html_writer::div('', 'bar_ratings', ['style' => 'width: calc(' . ($percent_bar * 100) . ' * 1% )', 'title' => $_calc_rating . ' %']);
        }

        $templatecontent = [
            'ratings' => $rating_list,
            'rating_total' => number_format($sum_rating, 1, '.', ''),
            'rating_stars' => $rating_stars,
            'rating_text_votes' => get_string('based_on', 'block_course_rating') . ' ' . $total_ratings . ' ' . ($total_ratings > 1 ? get_string('ratings', 'block_course_rating') : get_string('rating', 'block_course_rating')),
            'stars_bars' => $stars_bars,
            'stars_percents' => $stars_percents,
        ];
        echo $OUTPUT->render_from_template('block_course_rating/rating_bars', $templatecontent);
        break;
    case 'COMMENTS':

        if (!(is_int($course) && is_int($offset) && $course > 0 && $offset >= 0))
            return null;

        $ratings = $DB->get_records('course_rating', ['courseid' => $course], 'createdat desc', ' *', $offset, $limit);
        $ratings_count = $DB->get_record_sql('SELECT count(id) as total FROM {course_rating} WHERE courseid = :course', ['course' => $course]);


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

        echo json_encode([
            'ratings_remaining' => ($ratings_count->total - $offset - $limit),
            'button_show_more' => get_string('comments_button', 'block_course_rating') . ' (' . ($ratings_count->total - $offset - $limit) . ')',
            'content' => $OUTPUT->render_from_template('block_course_rating/rating_user', $templatecontent)
        ]);
        break;
    default:
        echo 'Nothing';
}
