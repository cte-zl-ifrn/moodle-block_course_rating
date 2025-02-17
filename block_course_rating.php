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
    }

    public function get_content()
    {
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
        //$this->content->text   .= html_writer::div(get_string('content', 'block_course_rating'), 'status');

        /**************************************************** */

        $stars = html_writer::start_div('row', ['class' => 'pl-3']);

        for ($s = 0; $s < 5; $s++) {
            $stars .= html_writer::start_span('start_start', ['data-block_rating' => $s]);
            $stars .= html_writer::tag('i', '', ['class' => 'icon fa fa-star-o block-rating-start', 'id' => 'block-rating_start-' . $s, 'data-block_rating' => $s]);
            $stars .= html_writer::end_span();
        }

        $stars .= html_writer::end_div();

        /**************************************************** */

        $box = html_writer::tag('textarea', '', ['rows' => '5', 'class' => 'form-control']);

        /**************************************************** */
        $content = '';
        $content .= html_writer::div(
            html_writer::label(get_string('review', 'block_course_rating'), 'review'),
            'col-md-3 col-form-label'
        );

        $content .= html_writer::div($stars, 'col-md-9 col-form-label');


        $content .= html_writer::div(
            html_writer::label(get_string('comment', 'block_course_rating'), 'review'),
            'col-md-3 col-form-label'
        );

        $content .= html_writer::div($box, 'col-md-9 col-form-label');

        $content = html_writer::div($content, 'row');

        $this->content->text .= $content;

        /**************************************************** */

        $button_confirm = html_writer::tag(
            'button',
            get_string('sendbutton', 'block_course_rating'),
            ['class' => 'btn btn-primary mt-4 mr-3', 'type' => 'submit']
        );

        $this->content->text .=  html_writer::div($button_confirm, 'row justify-content-end');

        return $this->content;
    }

    // Create multiple instances on a page.
    public function instance_allow_multiple()
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
