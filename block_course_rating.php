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
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;


        $title_review = '';

        /**************************************************** */
        //All reviews ratings
        $title_review .= html_writer::start_div('row pl-3');
        $title_review .= html_writer::start_div('col-md-2 text-right');
        $title_review .= html_writer::tag('h1', '4.0', ['class' => 'review_title']);
        $title_review .= html_writer::end_div();

        $title_review .= html_writer::start_div('col-md-9');
        $title_review .= $OUTPUT->pix_icon('star', 1, 'block_course_rating', ['class' => 'star-img']);
        $title_review .= $OUTPUT->pix_icon('star', 2, 'block_course_rating', ['class' => 'star-img']);
        $title_review .= $OUTPUT->pix_icon('star', 3, 'block_course_rating', ['class' => 'star-img']);
        $title_review .= $OUTPUT->pix_icon('star', 4, 'block_course_rating', ['class' => 'star-img']);
        $title_review .= $OUTPUT->pix_icon('star-o', 5, 'block_course_rating', ['class' => 'star-img']);

        $title_review .= html_writer::tag('p', 'baseado em 3200 avaliações.', ['class' => 'pt-1 text_review']);
        $title_review .= html_writer::end_div();
        $title_review .= html_writer::end_div();

        $this->content->text .= $title_review;

        $sub_review = '';
        $sub_review .= html_writer::start_div('row pl-3 mb-3');

        $sub_review .= html_writer::start_div('col-md-3 pl-5');
        $percents = [0, 1, 2, 2, 20, 70];
        for ($x = 5; $x >= 1; $x--) {
            for ($y = 0; $y < $x; $y++) {
                $sub_review .=  $OUTPUT->pix_icon('star', 1, 'block_course_rating', ['class' => 'star-img-small']);
            }
            for ($y = $x; $y < 5; $y++) {
                $sub_review .=  $OUTPUT->pix_icon('star-o', 1, 'block_course_rating', ['class' => 'star-img-small']);
            }
            $sub_review .= html_writer::span($percents[$x] . ' %', 'text_review');
            $sub_review .=  '<br />';
        }
        $sub_review .= html_writer::end_div();

        $sub_review .= html_writer::start_div('col-md-9');
        for ($x = 5; $x >= 1; $x--) {
            $sub_review .= html_writer::div('', 'bar_reviews', ['style' => 'width:' . $percents[$x] . '%']);
        }
        $sub_review .= html_writer::end_div();

        $sub_review .= html_writer::end_div();

        $this->content->text .= $sub_review;
        /**************************************************** */
        //Message if review is not available
        $config = $this->get_config();
        if (array_key_exists('exibition', $config)) {
            if ($config['exibition'] == 'finished') {
                //TODO Check if course is finished
                $this->content->text .= html_writer::div(
                    html_writer::label(get_string('finish_before', 'block_course_rating'), ''),
                    'col-md-12 text_review text-center'
                );
                return $this->content;
            }
        } else { // if not has config (default: after finish)
            $this->content->text .= html_writer::div(
                html_writer::label(get_string('finish_before', 'block_course_rating'), ''),
                'col-md-12 text_review text-center'
            );
            return $this->content;
        }
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
        $box = html_writer::tag('textarea', '', ['rows' => '5', 'class' => 'form-control comment-ta']);

        /**************************************************** */
        // Stars Label
        $content = '';
        $content .= html_writer::div(
            html_writer::label(get_string('review', 'block_course_rating'), 'review'),
            'col-md-2 text-right col-form-label'
        );
        // Stars add to content
        $content .= html_writer::div($stars, 'col-md-9 col-form-label');


        $content .= html_writer::div(
            html_writer::label(get_string('comment', 'block_course_rating'), 'review'),
            'col-md-2 text-right col-form-label'
        );

        // Text area add to content
        $content .= html_writer::div($box, 'col-md-9 col-form-label');
        $content = html_writer::div($content, 'row');

        //hidden fields
        $content .= html_writer::tag('input', '', [
            'type' => 'hidden',
            'id' => 'rating',
            'name' => 'rating',
            'value' => 0
        ]);
        $content .= html_writer::tag('input', '', [
            'type' => 'hidden',
            'id' => 'courseid',
            'name' => 'courseid',
            'value' => $this->get_course_id()
        ]);

        $this->content->text .= $content;

        /**************************************************** */
        //Confirm button
        $button_confirm = html_writer::tag(
            'button',
            get_string('sendbutton', 'block_course_rating'),
            ['class' => ' btn-comment mt-4 mr-3', 'type' => 'submit']
        );
        $this->content->text .=  html_writer::div($button_confirm, 'row justify-content-end pr-6');

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
