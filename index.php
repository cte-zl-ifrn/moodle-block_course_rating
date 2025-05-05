<?php


require('../../../config.php');

$courseid = required_param('id', PARAM_INT);

require_course_login($courseid);