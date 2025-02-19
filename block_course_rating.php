<?php

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

    public function get_content()
    {
        global $OUTPUT, $DB;

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;

        $my_rating = $DB->get_record('course_rating', ['userid' => $this->get_user_id(), 'courseid' => $this->get_course_id()]);

        $record = $DB->get_record("user", ["id" => $this->get_user_id()]);
        $record->pic =  $OUTPUT->user_picture($record, ['size' => 100, 'link' => true]);

        //echo '<pre>';
        //die(var_dump($my_rating));

        /************************************************************ */
        // My Rating
        if ($my_rating) {
            $my_review = html_writer::start_div('row pl-3');
            $my_review .= html_writer::start_div('col-md-2');
            $my_review .= $record->pic;
            $my_review .= html_writer::end_div();

            $my_review .= html_writer::start_div('col-md-9');
            $my_review .= html_writer::tag('strong', $record->firstname . ' ' . $record->lastname, ['class' => 'myreview_title']);
            $my_review .= html_writer::tag('span',  date('F d, Y', strtotime($my_rating->createat)), ['class' => 'float-right myreview_date']);
            $my_review .= '<br>';

            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $my_rating->rating) {
                    $my_review .=  $OUTPUT->pix_icon('star', $i, 'block_course_rating', ['class' => 'star-img-small']);
                } else {
                    $my_review .= $OUTPUT->pix_icon('star-o', $i, 'block_course_rating', ['class' => 'star-img-small']);
                }
            }

            $my_review .= '<br>';
            $my_review .= html_writer::tag('p', $my_rating->message, ['class' => 'myreview_text']);
            $my_review .= html_writer::end_div();

            $my_review .= html_writer::end_div();
        }

        /************************************************************ */

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

        /******************************************************************************************* */
        //Send Form 
        if (isset($_POST['rating']) && $_POST['rating'] > 0) {

            $recordtoinsert = new stdClass();
            $recordtoinsert->rating = $_POST['rating'];
            $recordtoinsert->message = $_POST['review_message'];
            $recordtoinsert->courseid = $this->get_course_id();
            $recordtoinsert->userid = $this->get_user_id();
            if (isset($_POST['review_message']) && $this->get_course_id() != 0 && $this->get_user_id() != 0) {
                $DB->insert_record('course_rating', $recordtoinsert);
                \core\notification::success(get_string('comment_save', 'block_course_rating'));
                $url = new moodle_url('/course/view.php', array('id' => $this->get_course_id()));
                redirect($url);
            } else {
                \core\notification::error(get_string('comment_error', 'block_course_rating'));
            }
        }

        $content = '';

        /**************************************************** */
        //All reviews ratings
        $title_review = '';
        $title_review .= html_writer::start_div('row pl-3');
        $title_review .= html_writer::start_div('col-md-2 text-right');
        $title_review .= html_writer::tag('h1', number_format($sum_rating / $total_ratings, 1, '.', ''), ['class' => 'review_title']);
        $title_review .= html_writer::end_div();

        $title_review .= html_writer::start_div('col-md-9');
        $sum_rating = round($sum_rating / $total_ratings);
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $sum_rating) {
                $title_review .= $OUTPUT->pix_icon('star', $i, 'block_course_rating', ['class' => 'star-img']);
            } else {
                $title_review .= $OUTPUT->pix_icon('star-o', $i, 'block_course_rating', ['class' => 'star-img']);
            }
        }

        $title_review .= html_writer::tag('p', 'baseado em ' . $total_ratings . ($total_ratings > 1 ? ' avaliações.' : ' avaliação.'), ['class' => 'pt-1 text_review']);
        $title_review .= html_writer::end_div();
        $title_review .= html_writer::end_div();

        $content .= $title_review;

        /**************************************************************************** */
        $sub_review = '';
        $sub_review .= html_writer::start_div('row pl-3 mb-3');

        $sub_review .= html_writer::start_div('col-md-3 pl-5');
        $percents = [0, 10, 15, 55, 10, 100];
        for ($x = 5; $x >= 1; $x--) {
            for ($y = 0; $y < $x; $y++) {
                $sub_review .=  $OUTPUT->pix_icon('star', $y + 1, 'block_course_rating', ['class' => 'star-img-small']);
            }
            for ($y = $x; $y < 5; $y++) {
                $sub_review .=  $OUTPUT->pix_icon('star-o', $y + 1, 'block_course_rating', ['class' => 'star-img-small']);
            }
            $sub_review .= html_writer::span((($rating_stars_percents[$x] / $total_ratings) * 100) . ' %', 'text_review');
            $sub_review .=  '<br />';
        }
        $sub_review .= html_writer::end_div();

        $sub_review .= html_writer::start_div('col-md-9');
        for ($x = 5; $x >= 1; $x--) {
            $sub_review .= html_writer::div('', 'bar_reviews', ['style' => 'width: calc(' . (($rating_stars_percents[$x] / $total_ratings) * 100) . ' * 5px )']);
        }
        $sub_review .= html_writer::end_div();

        $sub_review .= html_writer::end_div();

        $content .= $sub_review;

        /**************************************************** */
        //Message if review is not available
        $config = $this->get_config();
        if (array_key_exists('exibition', $config)) {
            if ($config['exibition'] == 'finished') {
                //TODO Check if course is finished
                $content .= html_writer::div(
                    html_writer::label(get_string('finish_before', 'block_course_rating'), ''),
                    'col-md-12 text_review text-center'
                );

                $this->content->text = $content;
                return $this->content;
            }
        } else { // if not has config (default: after finish)
            $content .= html_writer::div(
                html_writer::label(get_string('finish_before', 'block_course_rating'), ''),
                'col-md-12 text_review text-center'
            );

            $this->content->text = $content;
            return $this->content;
        }
        /**************************************************** */

        if ($my_rating) {
            $content .= $my_review;

            $this->content->text = $content;
            return $this->content;
        }

        /**************************************************** */


        // Begin form
        $content .= html_writer::start_tag('form', ['method' => 'post']);

        /**************************************************** */
        //  Stars to classification

        $stars = html_writer::start_div('row pl-3');
        for ($s = 1; $s <= 5; $s++) {
            $stars .= html_writer::start_span('block-rating-star', ['data-block_rating' => $s]);
            $stars .= $OUTPUT->pix_icon('star-o', $s, 'block_course_rating', ['class' => 'star-img']);
            $stars .= $OUTPUT->pix_icon('star', $s, 'block_course_rating', ['class' => 'star-img d-none']);
            $stars .= html_writer::end_span();
        }
        $stars .= html_writer::end_div();

        /**************************************************** */
        // Text area to comment
        $box = html_writer::tag('textarea', '', ['rows' => '5', 'class' => 'form-control comment-ta', 'name' => 'review_message']);

        /**************************************************** */
        // Stars Label
        $content .= html_writer::start_div('row');
        $content .= html_writer::div(
            html_writer::label(get_string('review', 'block_course_rating'), 'review'),
            'col-md-2 text-right col-form-label'
        );

        // Stars add to content
        $content .= html_writer::div($stars, 'col-md-9 col-form-label');
        $content .= html_writer::end_div();

        /**************************************************** */
        $content .= html_writer::start_div('row');
        $content .= html_writer::div(
            html_writer::label(get_string('comment', 'block_course_rating'), 'review'),
            'col-md-2 text-right col-form-label'
        );

        // Text area add to content
        $content .= html_writer::div($box, 'col-md-9 col-form-label');
        $content .= html_writer::end_div();

        //hidden fields
        $content .= html_writer::tag('input', '', [
            'type' => 'hidden',
            'id' => 'rating',
            'name' => 'rating',
            'value' => 0
        ]);

        /**************************************************** */
        //Confirm button
        $button_confirm = html_writer::tag(
            'button',
            get_string('sendbutton', 'block_course_rating'),
            ['class' => 'btn-comment mt-4 mr-3', 'type' => 'submit']
        );
        $content .= html_writer::div($button_confirm, 'row justify-content-end pr-6');
        /**************************************************** */
        // End form
        $content .= html_writer::end_tag('form');
        /**************************************************** */

        $this->content->text .= $content;

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
