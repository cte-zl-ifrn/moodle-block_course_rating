<?php
require_once("{$CFG->libdir}/completionlib.php");
require_once("{$CFG->libdir}/accesslib.php");

class block_course_rating extends block_base
{
    function init()
    {
        global $CFG;
        $this->title = get_string('block_course_rating', 'block_course_rating');
    }

    function has_config()
    {
        return true;
        $this->blockconfig = (object)(array)unserialize(base64_decode($instance->configdata ?? ''));
    }

    private function get_course_id()
    {
        global $PAGE;
        if ($PAGE->course) {
            return $PAGE->course->id;
        }
        return 0;
    }

    private function get_user_id()
    {
        global $USER;
        if ($USER) {
            return $USER->id;
        }
        return 0;
    }

    private function get_config()
    {
        global $PAGE, $DB;
        if ($PAGE->context) {
            $configdata = $DB->get_field('block_instances', 'configdata', ['parentcontextid' => $PAGE->context->id, 'blockname' => 'course_rating']);
            $config = (array)unserialize(base64_decode($configdata));
            return $config;
        }
    }

    private function get_is_complete_course()
    {
        global $DB;
        $course_object = $DB->get_record('course', array('id' => $this->get_course_id()));

        $cinfo = new completion_info($course_object);
        $iscomplete = $cinfo->is_course_complete($this->get_user_id());
        return $iscomplete;
    }

    private function is_after_finished()
    {
        $config = $this->get_config();

        if (array_key_exists('exibition', $config)) {
            if (
                $config['exibition'] == 'finished' &&
                !$this->get_is_complete_course()
            )
                return true;
        } else {
            if (!$this->get_is_complete_course())
                return true;
        }

        return false;
    }

    public function get_content()
    {
        global $OUTPUT, $DB;

        if ($this->content !== null) {
            return $this->content;
        }
        $fmt = new IntlDateFormatter(
            'pt_BR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'America/Sao_Paulo',
            IntlDateFormatter::GREGORIAN
        );
        $fmt->setPattern('MMMM dd, yyyy');

        $this->content = new stdClass;

        //recover if exist user rating
        $my_rating = $DB->get_record('course_rating', ['userid' => $this->get_user_id(), 'courseid' => $this->get_course_id()]);

        /********************************************************* */
        // Receive post
        if (isset($_POST['rating']) && is_numeric($_POST['rating']) && $_POST['rating'] > 0 && $_POST['rating'] < 6) {

            $recordtoinsert = new stdClass();
            $recordtoinsert->rating = $_POST['rating'];
            $recordtoinsert->message = $_POST['review_message'];
            $recordtoinsert->courseid = $this->get_course_id();
            $recordtoinsert->userid = $this->get_user_id();
            if (isset($_POST['review_message']) && $this->get_course_id() != 0 && $this->get_user_id() != 0) {
                if ($my_rating) {
                    $historytoinsert = new stdClass();
                    $historytoinsert->rating = $my_rating->rating;
                    $historytoinsert->message = $my_rating->message;
                    $historytoinsert->originid = $my_rating->id;
                    $DB->insert_record('course_rating_history', $historytoinsert);
                    //updating actual rating
                    $recordtoinsert->updatedat = date("Y-m-d H:i:s");
                    $recordtoinsert->id = $my_rating->id;
                    $DB->update_record('course_rating', $recordtoinsert);
                } else {
                    $DB->insert_record('course_rating', $recordtoinsert);
                }
                \core\notification::success(get_string('comment_save', 'block_course_rating'));
                $url = new moodle_url('/course/view.php', array('id' => $this->get_course_id()));
                redirect($url);
            } else {
                \core\notification::error(get_string('comment_error', 'block_course_rating'));
            }
        }
        /********************************************************* */

        //user logged
        $user = $DB->get_record("user", ["id" => $this->get_user_id()]);

        //user review stars
        $review_stars = '';
        if ($my_rating) {
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $my_rating->rating) {
                    $review_stars .=  $OUTPUT->pix_icon('star', $i, 'block_course_rating', ['class' => 'star-img-small']);
                } else {
                    $review_stars .= $OUTPUT->pix_icon('star-o', $i, 'block_course_rating', ['class' => 'star-img-small']);
                }
            }
        }

        /********************************************************* */
        // Get all ratings
        $ratings = $DB->get_records_sql(
            'SELECT rating, count(id) FROM {course_rating} WHERE courseid = :course
            GROUP BY rating, courseid ORDER BY rating DESC',
            ['course' => $this->get_course_id()]
        );

        $rating_stars_percents = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        ];
        $total_ratings = 0;
        $sum_rating = 0;

        foreach ($ratings as $rating) {
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
        /********************************************************* */
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
            $stars_bars .= html_writer::div('', 'bar_reviews', ['style' => 'width: calc(' . ($percent_bar * 100) . ' * 1% )']);
        }
        /********************************************************* */
        // Stars 
        $stars = '';
        for ($s = 1; $s <= 5; $s++) {
            $stars .= html_writer::start_span('block-rating-star', ['data-block_rating' => $s]);
            $stars .= $OUTPUT->pix_icon('star-o', $s, 'block_course_rating', ['class' => 'star-img']);
            $stars .= $OUTPUT->pix_icon('star', $s, 'block_course_rating', ['class' => 'star-img d-none']);
            $stars .= html_writer::end_span();
        }

        $template_context = (object)[
            'rating_total' => number_format($sum_rating, 1, '.', ''),
            'rating_stars' => $rating_stars,
            'rating_text_votes' => 'baseado em ' . $total_ratings . ($total_ratings > 1 ? ' avaliações.' : ' avaliação.'),

            'stars_percents' => $stars_percents,
            'stars_bars' => $stars_bars,
            'review_message_label' => get_string('comment', 'block_course_rating'),
            'stars_label' => get_string('review', 'block_course_rating'),
            'stars_vote' => $stars,
            'submit_text' => get_string('sendbutton', 'block_course_rating'),
            'cancel_text' => get_string('cancelbutton', 'block_course_rating'),

            'user_img' => $OUTPUT->user_picture($user, ['size' => 100, 'link' => true]),
            'user_name' => $user->firstname . ' ' . $user->lastname,
            'review_stars' => $review_stars,
            'review_date' => ($my_rating) ? ucfirst($fmt->format(strtotime($my_rating->createdat))) : '',
            'review_text' => ($my_rating) ? $my_rating->message : '',
            'message_finish_course_before' => get_string('finish_before', 'block_course_rating'),

            'is_after_finish' => $this->is_after_finished(),
            'rating' => $my_rating,
            'rating_note' => $my_rating->rating,
            'rating_edited' => $my_rating->createdat != $my_rating->updatedat,
            'complete_course' => $this->get_is_complete_course()

        ];
        $this->content->text = $OUTPUT->render_from_template('block_course_rating/rating', $template_context);
        return $this->content;
    }


    // Create multiple instances on a page.
    public function instance_allow_multiple()
    {
        return false;
    }

    function instance_allow_config()
    {

        return false;
    }

    public function applicable_formats()
    {
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => true,
            'mod' => false,
            'my' => true,
        ];
    }
}
