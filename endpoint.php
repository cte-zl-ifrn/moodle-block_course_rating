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
                'user_name' =>  $rating->id . ' - ' . $record->firstname . ' ' . $record->lastname,
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
//echo $OUTPUT->render_from_template('block_course_rating/rating_user', $templatecontent);

/*


$coments = [
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi fermentum, arcu sit amet sodales scelerisque, felis leo vehicula dolor, sagittis faucibus risus dolor et lacus. Pellentesque in mauris a justo volutpat commodo ac a magna. Suspendisse accumsan et neque at vehicula. Donec tortor urna, eleifend et porta at, ultrices non dolor. Proin sit amet nulla sed velit luctus dapibus non id eros. Integer sodales dui eget efficitur aliquam. Praesent quis risus in nunc posuere varius a eu felis. Morbi nunc lectus, semper a feugiat id, sodales facilisis augue.',

    'Sed ornare porttitor odio, quis dapibus nisi ultrices et. In a lacus id diam posuere venenatis ac in felis. Duis ac tempor mi, pellentesque elementum turpis. Ut lobortis tristique ligula eget sollicitudin. Nulla gravida, augue nec lacinia euismod, ante odio malesuada mi, eu porta odio sapien sit amet dolor. Nam euismod massa vitae dolor mattis vestibulum viverra non est. Cras ex risus, dapibus vitae accumsan ut, dictum eu elit. Nulla facilisi. Nullam id feugiat urna.',

    'Aliquam luctus hendrerit tortor, a bibendum diam tristique id. Nunc in nibh nisi. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Phasellus quis ornare diam. Nam sagittis ipsum maximus tellus consequat, tempus interdum augue porta. Suspendisse eget neque augue. Pellentesque cursus in ante sed tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Etiam ut posuere risus, nec accumsan lacus. Duis congue ullamcorper orci nec malesuada. Nullam nec nisi sed massa placerat sollicitudin. Nunc fringilla rhoncus tortor, sit amet pharetra sapien pulvinar a. Proin vulputate mi sit amet lorem molestie, quis vestibulum arcu gravida. Integer laoreet purus nec mi auctor, non congue nisi vehicula. Integer sit amet neque et lorem vestibulum luctus vitae ut lectus. Vivamus id neque id orci rutrum aliquet.',

    'Nulla fringilla nisl nibh, sit amet blandit eros vehicula eu. Duis orci sem, sollicitudin quis laoreet ac, luctus sit amet lectus. Maecenas nec convallis augue. Vivamus tempus luctus lacus, vel gravida ex fringilla at. Duis congue sem vel ante laoreet suscipit. Aliquam porttitor aliquam dolor at posuere. Nullam tristique sapien a dui vestibulum faucibus. Maecenas nulla velit, auctor eget lectus a, varius porttitor libero. Ut sed lectus pulvinar, luctus sapien ac, mattis tortor.',

    'Praesent nec magna porta, pretium tortor non, viverra libero. Morbi quis mauris enim. Sed odio nisi, accumsan quis hendrerit a, pretium in nunc. Mauris blandit venenatis nunc in efficitur. Quisque vel lacus ullamcorper, sagittis ante sed, lacinia orci. In hac habitasse platea dictumst. Duis viverra at ex tincidunt mattis. Morbi vestibulum viverra eros sit amet iaculis.',

    'Sed pulvinar leo ac nunc accumsan mattis. Curabitur volutpat, arcu id sodales tempus, erat elit laoreet eros, id faucibus arcu leo non ante. In pharetra est tellus, in eleifend risus consequat ac. Suspendisse potenti. Donec id libero eros. Vivamus sit amet diam vel arcu mattis vestibulum. Praesent sodales elit bibendum aliquet varius. Aliquam mattis, leo at dapibus semper, justo eros elementum erat, quis rhoncus ex tortor ut mauris. Fusce ac arcu ut mauris vehicula condimentum dapibus eget lacus. Duis congue nunc quis urna dictum, in mattis nulla interdum. Ut erat augue, gravida eu porttitor vitae, aliquam eget orci. Nam et sem maximus, condimentum velit vel, venenatis lectus. Nullam quis risus blandit, luctus lacus iaculis, semper tortor. Nam aliquet ullamcorper tortor, in elementum ligula laoreet eu. Cras quis leo dictum, auctor augue at, porta orci.',

    'Ut in nisl nisi. Duis eget tincidunt orci. Fusce gravida pharetra sem, non vulputate enim pulvinar vitae. Nunc feugiat sollicitudin enim quis dictum. Nunc ac volutpat nunc, a sollicitudin justo. Aliquam a libero lorem. Phasellus ac dolor justo. Pellentesque congue enim eget tristique tempus. Nunc sit amet scelerisque ligula. Cras in lacinia erat. Quisque rhoncus ex vel tortor efficitur, pellentesque porta nulla sodales. Quisque et porta nibh. Donec tristique rutrum orci, in euismod mauris tempor sit amet. Donec velit lacus, hendrerit ut ex vitae, ultrices consectetur risus.',

    'Vestibulum id eleifend purus. Mauris a congue mauris, nec pharetra turpis. Nullam volutpat orci quis diam aliquam, in sodales felis faucibus. Quisque feugiat velit vel vestibulum feugiat. Maecenas convallis accumsan eros id aliquam. Sed ac consequat lorem, at pulvinar mi. Sed tincidunt, augue eu mollis congue, diam velit dignissim lorem, ac viverra leo massa quis enim. Mauris quis leo enim. Vestibulum mollis, magna sit amet vulputate consequat, massa lectus gravida eros, a condimentum erat lacus eu orci. Etiam sit amet eros faucibus, malesuada velit volutpat, faucibus purus. Curabitur dignissim, lectus non aliquet pellentesque, urna turpis hendrerit orci, faucibus viverra libero lectus quis arcu. Aliquam non risus eget justo rutrum sagittis.',

    'Duis nec feugiat turpis, quis pulvinar odio. Aenean nec dolor mi. Integer ut malesuada mauris. Proin luctus pharetra molestie. Pellentesque scelerisque elementum facilisis. Nam sit amet velit non eros dapibus blandit vel a odio. Praesent diam massa, elementum eu nunc nec, vehicula placerat ante. Aliquam sed orci vel elit rhoncus tincidunt. Pellentesque ullamcorper facilisis consequat. Nullam odio orci, tempor at ligula eget, porttitor auctor turpis. Phasellus efficitur mollis consequat. Nulla ultricies, tellus sit amet euismod tincidunt, felis erat ultricies dolor, vel ultrices diam orci quis nulla. Aenean congue, ligula ac tristique placerat, arcu mauris luctus erat, vitae vehicula tellus elit non enim. Interdum et malesuada fames ac ante ipsum primis in faucibus. Integer tincidunt turpis eu lacinia vehicula. Sed convallis, lectus sit amet malesuada varius, neque justo vehicula felis, non volutpat mi augue auctor turpis.',

    'Fusce lectus lectus, gravida non imperdiet eget, tincidunt rhoncus tortor. Suspendisse potenti. Vivamus et tincidunt arcu. Quisque placerat cursus lectus in condimentum. Cras dapibus, diam a hendrerit finibus, ante leo imperdiet turpis, et lobortis sapien lectus vel sapien. Maecenas eleifend elit at enim porta volutpat. Proin et vehicula orci. Suspendisse mattis ac justo vel efficitur. Aenean fermentum tristique nibh ac aliquam. Integer magna sem, luctus quis elementum vel, mattis vitae diam. Aenean elementum viverra pulvinar. Suspendisse ultricies purus ac ullamcorper rutrum.',

    'Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aliquam eget tellus ac risus dictum suscipit a nec ipsum. Nulla facilisi. Sed euismod quam et risus dignissim, at aliquet massa volutpat. Aliquam erat volutpat. Nam tempor maximus lacinia. Quisque ultricies scelerisque leo. Ut varius felis quis tellus consequat, at fringilla ex dapibus. Suspendisse potenti. Phasellus porta, massa non mattis varius, justo elit varius nibh, et interdum neque purus id enim. Aliquam tristique ipsum dolor, sed semper mi lacinia at. Aenean egestas libero sit amet magna cursus finibus. Nam ultrices mauris ac nisi aliquam placerat. Donec pellentesque, purus non ultricies accumsan, purus nulla lobortis justo, nec vehicula massa lectus non urna. Pellentesque id quam magna.',

    'Vestibulum sed tincidunt magna. Maecenas mattis placerat turpis sed laoreet. Morbi pretium fermentum orci, non bibendum turpis tincidunt vitae. Duis commodo diam est, vel gravida orci ullamcorper eget. Sed consequat cursus est, eget vehicula urna rhoncus vel. Aliquam condimentum, turpis eget rutrum vulputate, dui nisi mollis tellus, in lobortis sem mi ut magna. Aliquam magna magna, lobortis at condimentum sit amet, rhoncus in massa. Sed posuere nibh eget velit maximus blandit. Donec tortor quam, faucibus id libero vel, lobortis sollicitudin metus. Donec et nisl convallis, posuere mi ut, rhoncus mi.',

    'Mauris interdum lectus non nisi rutrum posuere. Aenean non eros massa. Proin auctor diam ut justo consequat fringilla. Fusce viverra sed leo sit amet dignissim. Nunc metus nulla, ornare ut accumsan sed, ultrices sed turpis. Proin eu viverra velit. Donec ut lectus sed lorem imperdiet faucibus eget eget urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris pellentesque aliquam tincidunt. Praesent vel commodo purus. Quisque imperdiet eros non risus congue, vel tincidunt dui dignissim. Vestibulum venenatis urna eu dolor faucibus, non rutrum risus accumsan. Vestibulum tristique lorem et vestibulum convallis. Ut at dignissim lacus. Fusce urna justo, aliquet eu enim eget, convallis varius quam.',

    'Duis vitae ipsum sit amet mauris blandit volutpat nec sed eros. Integer efficitur vulputate velit, sit amet scelerisque massa. Mauris euismod purus a elit ornare, ac maximus velit bibendum. Nulla facilisi. Maecenas rutrum eros sed enim viverra, vel viverra magna semper. Quisque euismod nisl et purus volutpat blandit hendrerit placerat ante. Duis in velit feugiat mi ultricies rutrum. Sed urna diam, fringilla a eleifend et, fringilla a ex. Nulla diam mauris, consectetur sit amet consequat eget, accumsan nec nulla. In egestas tellus sed velit tempor, eget cursus enim dictum. Nam et lacus imperdiet, blandit nulla sed, euismod mauris. Quisque aliquet dui sed turpis egestas dignissim. Pellentesque sed nibh eu diam cursus pulvinar. Cras ut tincidunt ipsum. Donec egestas neque id elit cursus posuere.',

    'Donec blandit vulputate dolor, in dictum diam convallis a. Fusce at congue felis. Quisque at diam vel sem maximus viverra vitae non mi. Nullam quis mi purus. Fusce et pharetra risus. Curabitur tristique rhoncus sagittis. Quisque a dictum velit.',

    'Donec eget nisi est. Morbi consequat dignissim sem sit amet lacinia. Donec efficitur, mi molestie interdum consequat, mauris nisi rutrum mauris, sit amet accumsan eros nisl sed turpis. Duis quis condimentum mauris. Integer ac pellentesque elit, ac aliquam neque. Morbi vestibulum dapibus nisl, eget pretium arcu. Phasellus id consectetur massa, quis facilisis nibh. Sed sit amet eros convallis, commodo lacus suscipit, elementum nisi. Integer pellentesque enim ut finibus tincidunt. Praesent maximus risus quis metus commodo pharetra sed eu augue. Maecenas maximus massa ante, id laoreet urna maximus in. Etiam egestas iaculis lobortis.',

    'Cras vel faucibus neque. Fusce a malesuada risus. Maecenas scelerisque est ultricies nisi dignissim convallis. Nam suscipit a urna nec placerat. Sed laoreet sodales erat tristique imperdiet. Praesent blandit vulputate justo id ultrices. Donec pulvinar imperdiet justo, vitae vulputate libero egestas in. Suspendisse malesuada semper urna, quis malesuada felis iaculis ut. Integer lobortis augue a elementum rhoncus. Donec bibendum ante in placerat venenatis. Pellentesque varius fringilla placerat. Proin gravida elementum nibh vel volutpat. Suspendisse sed varius tellus. Donec pretium commodo ipsum eu consectetur.',

    'Duis pharetra odio lorem, at luctus neque ultricies in. Cras ullamcorper felis nec fringilla tincidunt. Duis ultrices varius tortor et sodales. Pellentesque in convallis felis. Nullam massa nibh, hendrerit quis rutrum eu, ullamcorper sed erat. Vivamus eget sodales augue. Sed dictum ultrices diam, sed fringilla dui mattis at. Suspendisse ullamcorper lectus vel ante imperdiet, vulputate efficitur elit iaculis. Proin sodales, elit vel ultrices viverra, nisl odio porttitor massa, non suscipit erat nisi non mi. Vestibulum mollis felis id blandit luctus. Pellentesque pharetra tincidunt dolor quis dapibus. Nam nisl dolor, varius sit amet condimentum vitae, efficitur sit amet nisl. Sed magna orci, tincidunt ut risus sit amet, laoreet vehicula nulla. Sed rutrum ultricies lacus eget consectetur. Suspendisse potenti. Suspendisse mi tellus, faucibus vel viverra et, dictum in neque.',

    'Vivamus est turpis, faucibus sit amet ipsum euismod, condimentum tincidunt leo. Nunc diam dui, gravida a erat non, suscipit volutpat erat. Morbi quis elementum nisi. Mauris urna nibh, varius in felis vitae, venenatis finibus diam. Mauris id maximus nibh, sed finibus nulla. Etiam lobortis elit ac magna pulvinar, at efficitur odio porttitor. Nullam tristique sagittis enim, sit amet imperdiet risus eleifend tristique. Fusce fringilla purus eget laoreet luctus. Vivamus vel erat non justo malesuada pretium. Mauris rhoncus dui molestie tellus dictum condimentum. Nam quis cursus orci, et luctus leo. Suspendisse aliquet finibus consequat. Aliquam a rhoncus turpis. Cras ac urna in tellus laoreet hendrerit accumsan vitae turpis. Nullam quis lorem sed risus eleifend venenatis ut eget augue. Morbi lacus nibh, aliquam aliquam imperdiet non, mattis id turpis.',

    'Phasellus lobortis tortor in leo tincidunt, malesuada dapibus neque dignissim. Morbi et massa hendrerit, malesuada dui at, scelerisque urna. Aliquam eros tortor, pretium non volutpat sit amet, varius vel mauris. Suspendisse dapibus velit a commodo cursus. Etiam et consequat turpis, vitae consectetur est. Nullam varius est eu nisl scelerisque, vel feugiat urna egestas. Suspendisse in elementum eros.',

    'Quisque eget quam non lacus gravida efficitur eu sit amet elit. Quisque in nisl aliquet, egestas ipsum non, mattis ex. Donec pretium mi eu nibh cursus, vitae aliquet enim suscipit. Curabitur nibh odio, semper at diam id, euismod iaculis ante. Maecenas vel molestie metus, sit amet semper neque. Aliquam dictum neque sit amet nisl mattis viverra. Aenean volutpat condimentum neque, ut mattis mauris aliquam non. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',

    'Fusce at suscipit est, non congue odio. Vestibulum lectus nisl, pulvinar id hendrerit eget, elementum vel metus. Morbi eu ante lacinia, sodales orci in, volutpat orci. Sed id venenatis eros. Integer elementum fringilla ante id vehicula. Fusce ac dolor a massa laoreet vulputate. Suspendisse sapien urna, placerat vel rutrum vel, hendrerit id purus. Vivamus vel bibendum elit. Quisque egestas congue consequat. Mauris non sodales diam.',

    'Vestibulum id purus massa. Aenean hendrerit dapibus felis et scelerisque. Aenean quis posuere nulla. Proin mattis ultrices purus. Nulla imperdiet venenatis lacinia. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent ex risus, vulputate a ultricies in, vulputate ac purus. Aliquam vehicula quam id varius tincidunt. Vivamus nec feugiat ex, in tristique nisl.',

    'Nullam nec dolor non ante auctor pulvinar. Integer consequat, lacus eu lobortis venenatis, metus mi auctor mauris, eget tincidunt nulla ex vel lorem. Nunc mollis, magna ac mattis condimentum, mi orci ultricies enim, sed pharetra ante felis ac tortor. Fusce ac lacinia erat, sit amet maximus metus. Nullam hendrerit metus ac lacus dapibus, nec laoreet dolor interdum. Phasellus pretium dui non nulla facilisis accumsan. Ut eu justo dui. Sed nisl velit, laoreet consectetur risus vel, varius pharetra mauris. Phasellus sem quam, consequat eget condimentum nec, posuere eget nisl. Cras lobortis elit luctus, pharetra nibh in, gravida est. Nulla eleifend odio vitae aliquet tincidunt. Sed id faucibus sapien. Etiam ultricies in turpis et dapibus. Proin efficitur, tortor et pharetra sagittis, magna sem commodo ex, sed convallis neque lacus malesuada ligula.',

    'Mauris id accumsan metus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed risus velit, venenatis vitae arcu id, eleifend ultricies leo. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Pellentesque tincidunt felis eu sapien efficitur, non ultrices eros viverra. Ut laoreet neque at blandit euismod. Sed vehicula sem eu tortor imperdiet, vitae accumsan nisl dignissim. Vestibulum vel urna ac ligula volutpat malesuada sed in diam. Cras id ipsum quis metus hendrerit accumsan.',

    'Interdum et malesuada fames ac ante ipsum primis in faucibus. Cras in porta lacus, et facilisis tortor. Cras sollicitudin, justo vel sagittis sollicitudin, neque erat ullamcorper ex, in faucibus odio ante vitae dui. Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec at auctor enim. Curabitur orci ante, ultricies et auctor ac, condimentum non nisi. Nulla nec quam purus. Integer sit amet placerat mauris. Donec venenatis eu diam ac auctor. Donec et nunc eget erat vestibulum finibus quis a turpis. Morbi id lacus dignissim, pretium lorem quis, pulvinar nunc. Nulla ac porttitor lorem. Donec dapibus bibendum purus, id ullamcorper odio congue et. Quisque vel enim libero. Cras venenatis pellentesque nibh id hendrerit.',

    'Nunc pulvinar augue eros, non sagittis erat pellentesque et. Fusce sollicitudin enim at sollicitudin tincidunt. Etiam maximus erat vitae iaculis tincidunt. Suspendisse efficitur sapien quam, vitae iaculis massa euismod vitae. Pellentesque cursus orci ut ornare ornare. Duis vestibulum, orci ut efficitur sollicitudin, velit diam dignissim ante, id molestie arcu nisi nec tellus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin congue fermentum arcu at ornare. Nam congue lacus id scelerisque finibus. Praesent est tellus, luctus ut ullamcorper ac, consectetur in velit.',

    'In hac habitasse platea dictumst. Aenean faucibus nisl ut eros consequat molestie. Maecenas faucibus dui sapien, vel fringilla metus lobortis non. In hac habitasse platea dictumst. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Vestibulum scelerisque metus sem, vel ultrices tellus blandit id. Curabitur venenatis, nulla vel egestas tincidunt, diam ante dictum nibh, eu consectetur tortor nisl non turpis. Maecenas in porttitor ligula. Vestibulum convallis ante a feugiat pulvinar.',

    'Nulla faucibus nulla id felis porttitor, eget pellentesque libero aliquam. Nam eget elementum magna, sed pretium justo. Nunc molestie aliquam ante, ut vestibulum dolor congue quis. Quisque sit amet venenatis elit, eu vehicula mauris. Sed id quam at mi dictum mollis. Cras scelerisque, nulla sed viverra tempor, eros ex venenatis nisl, a ultricies nulla dolor ut urna. Quisque ornare sed diam in finibus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec malesuada sapien eget nisi egestas pharetra. Donec rhoncus porta rhoncus. Nunc quis vulputate lacus. Integer consectetur enim vel est semper facilisis. Nullam tristique vitae purus in fermentum.',

    'Nulla ligula metus, iaculis ac mattis sed, finibus eu leo. Sed placerat augue ac sem convallis ultrices. Phasellus non mauris id tortor congue ornare. Suspendisse fermentum, ipsum a accumsan volutpat, eros ipsum vestibulum diam, in tincidunt enim ipsum in velit. Integer fringilla dolor quis sagittis finibus. Nam congue eros id interdum convallis. Quisque quis commodo nisl. Suspendisse tincidunt ipsum sem, vel aliquet ante luctus ac. Aliquam congue vulputate ante eget commodo.',

    'In lorem quam, cursus et justo nec, vulputate porttitor sapien. Mauris suscipit, ex nec placerat gravida, massa risus sagittis magna, vitae faucibus tellus nibh vel leo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam accumsan ornare molestie. Phasellus blandit odio a massa sodales, sit amet molestie tellus fringilla. Donec at sem maximus, elementum risus eget, mollis massa. Ut aliquam diam non ipsum blandit, et luctus diam aliquam. Ut sagittis at ipsum non volutpat. Aenean consequat imperdiet magna non condimentum. Quisque elementum diam massa, non faucibus tortor cursus nec. Donec porta tincidunt mi, quis ultricies nibh mollis sit amet. Pellentesque suscipit varius tellus, pharetra aliquam tellus ultrices nec. Nam ornare sed nunc a sodales. Sed vel risus id nisl viverra tincidunt vel sed elit.',

    'Proin consectetur imperdiet ullamcorper. Sed tincidunt ultrices dignissim. Curabitur dapibus massa non lorem lacinia, quis iaculis enim feugiat. Cras consequat velit at felis varius consequat. Maecenas nec nisi tincidunt, dictum eros id, gravida risus. In at neque augue. Sed in gravida mauris, sed volutpat ipsum.',

    'Praesent ut tellus nisl. Cras laoreet ante nec libero sodales tempor. Maecenas in feugiat quam. Sed aliquet diam ut congue dignissim. Donec id dictum nisi. Suspendisse lacinia sem et porta gravida. Nam varius cursus arcu vel pellentesque. Cras accumsan convallis nisl at aliquam. Maecenas suscipit massa sit amet nunc consectetur ornare. Vivamus urna neque, pellentesque non sem vitae, sollicitudin dictum tellus. Vestibulum vel rhoncus libero, eget ullamcorper lorem. Donec mauris ipsum, fermentum quis iaculis sit amet, bibendum quis tortor.',

    'In ut dolor arcu. Nulla iaculis malesuada tortor semper auctor. Fusce id volutpat risus. Cras a imperdiet purus. Cras fermentum eros a sem sagittis, et aliquam tellus tempor. Ut quis leo dolor. Vivamus vitae ipsum laoreet, efficitur neque eget, ornare risus.',

    'Curabitur et arcu pretium, tempor nulla nec, vestibulum mi. Nullam sit amet lorem diam. Donec feugiat ullamcorper cursus. Vivamus sed libero euismod, finibus purus id, posuere lectus. Maecenas tincidunt in nisl id pharetra. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed id dignissim risus. Donec ac justo vulputate, posuere ipsum ac, mattis dolor. Fusce aliquet dictum eros, quis sollicitudin arcu pellentesque sed. Ut at lobortis nulla. Ut a dictum nunc. Cras finibus, ex a pharetra lacinia, magna arcu facilisis ante, sit amet tristique est magna nec est. Phasellus a tempus lectus.',

    'Quisque efficitur, purus at porttitor finibus, sapien quam mattis odio, eu fermentum sem est in nulla. Pellentesque ultricies orci at sapien tempor, sed fermentum felis accumsan. Suspendisse ullamcorper orci sed lectus venenatis lacinia. Donec dictum quis turpis ac blandit. Mauris quis feugiat dui, et egestas quam. Proin euismod congue semper. Vivamus scelerisque elit a erat sollicitudin, eget euismod quam venenatis. Etiam eu pretium dui, id consequat tellus. Praesent malesuada lectus eget commodo interdum. Integer turpis odio, faucibus a sodales eget, venenatis id purus. Cras id facilisis urna, vel pharetra erat. Integer dui lacus, viverra vitae mollis vitae, laoreet sit amet odio. Morbi dictum rutrum lorem, congue vehicula enim mattis mattis.',

    'Donec finibus, ipsum in suscipit pulvinar, lectus quam faucibus est, et congue lectus nibh eget arcu. Phasellus vulputate feugiat mauris, nec dignissim augue ornare vel. Donec est dui, pretium euismod finibus in, finibus nec sem. Mauris ultrices aliquet lorem. Phasellus vestibulum egestas sagittis. Ut dolor lectus, ullamcorper nec ultricies ut, scelerisque in massa. Fusce faucibus nec nulla ut facilisis. Praesent vel interdum nisl. Etiam ornare aliquet odio non pellentesque.',

    'Donec condimentum suscipit ex sit amet faucibus. Vestibulum mattis aliquet sagittis. Proin ut felis nec nisl vestibulum auctor sit amet aliquet massa. Nulla interdum augue arcu, quis ultrices metus suscipit sed. Nulla facilisi. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In augue elit, aliquet vitae fermentum a, placerat at sapien. Pellentesque non felis id massa mattis vehicula. Duis varius ante sit amet quam fringilla dignissim.',

    'Vestibulum ullamcorper, nulla quis pulvinar sagittis, risus eros sollicitudin arcu, eu fringilla dui libero eget ligula. Nunc sodales sed metus quis consectetur. Duis urna urna, ullamcorper eget maximus ac, porta id lacus. Maecenas viverra tortor nec sem consequat, vel scelerisque quam porttitor. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Etiam eu risus vel nulla aliquet porta sed eget leo. Quisque non convallis arcu, at tincidunt nulla.',

    'Ut quis dui ultricies, eleifend magna at, iaculis velit. Aliquam interdum cursus tortor vitae malesuada. Etiam pharetra imperdiet libero, sed facilisis nunc. Nunc scelerisque semper odio interdum aliquam. Fusce euismod quam erat, sit amet bibendum massa dictum vel. Proin quis vehicula diam, a vulputate diam. Curabitur non mauris ut enim vestibulum ultrices sed quis erat. Phasellus mattis auctor mattis. Nunc porta mauris in fringilla malesuada. Nullam in massa nec eros luctus sodales at efficitur lorem.',

    'Quisque sit amet vestibulum odio, sit amet euismod purus. Integer posuere orci at cursus sagittis. Morbi quis turpis viverra ipsum blandit molestie ut eget leo. Suspendisse sit amet nunc vestibulum, ornare lectus a, condimentum tortor. Nam ultrices, enim non posuere ultricies, tortor orci dignissim magna, at cursus tortor dui nec justo. Curabitur quis iaculis lacus, id placerat est. Sed sit amet aliquam diam, at tincidunt ligula. Suspendisse vulputate justo vel magna vehicula iaculis. aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Proin consectetur varius euismod.',

    'Maecenas vel mauris placerat, mattis nisl sit amet, gravida dolor. Integer lorem mauris, egestas a ligula eget, malesuada mollis est. Fusce ac ipsum sed tortor mattis ornare quis ut metus. Nunc iaculis nisl at enim dictum, nec elementum leo bibendum. Donec ultricies dapibus molestie. Vivamus blandit laoreet hendrerit. Pellentesque malesuada et urna non consectetur. Aliquam pretium feugiat justo, id dapibus augue aliquam vitae. Aliquam nisi justo, pulvinar cursus dui eu, vestibulum pretium purus.',

    'Curabitur id sagittis libero. Vivamus pulvinar convallis erat, lobortis aliquam arcu auctor fringilla. Aenean congue fermentum auctor. Fusce nisl nulla, condimentum non placerat tristique, viverra eu justo. Suspendisse ultricies hendrerit facilisis. Phasellus dui lorem, facilisis at consectetur quis, aliquam suscipit diam. Ut sagittis libero non augue condimentum gravida. Fusce pulvinar tincidunt nisi sed maximus. Aliquam eget augue gravida turpis efficitur posuere nec sed lacus. Nulla sit amet nunc non sapien convallis fermentum. Praesent rhoncus mi eu quam pulvinar tincidunt. In vel nulla iaculis quam commodo vestibulum vel sit amet nibh. Nunc sed tellus ipsum. Nulla ultricies elit sed pulvinar suscipit.',

    'Aenean semper nibh et massa iaculis condimentum. Sed vel quam pharetra, rutrum neque sed, congue orci. Nunc placerat vestibulum lorem et ornare. Morbi pharetra tortor in nunc dignissim, vitae rutrum tellus elementum. Integer varius pellentesque laoreet. Ut diam nisl, sollicitudin nec libero nec, semper auctor turpis. Etiam tempor lorem eu gravida dignissim. Phasellus a pulvinar nisl. Sed sagittis aliquam bibendum. Aliquam fringilla, turpis eget gravida lacinia, neque lacus convallis mi, vitae fermentum libero neque vel sapien. Duis non risus odio. Aliquam fermentum, nisi ac pharetra posuere, lectus tortor porttitor mauris, blandit dictum sem nulla et dui.',

    'Aenean nec nulla rutrum, malesuada augue fermentum, mattis nibh. Vivamus elementum molestie massa nec blandit. Fusce non aliquet magna. Curabitur ut accumsan massa. Vivamus convallis nisl libero, nec sagittis arcu auctor ut. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse molestie felis eu nulla rhoncus, a scelerisque mi tempor. Nam efficitur libero non pellentesque malesuada. Interdum et malesuada fames ac ante ipsum primis in faucibus.',

    'Proin eu blandit enim, sit amet tincidunt ligula. Aliquam faucibus eleifend laoreet. Sed pulvinar diam lacus, non scelerisque purus efficitur sit amet. In accumsan dui ut erat luctus molestie. Integer mattis, lectus nec egestas dignissim, ex felis bibendum turpis, a hendrerit purus urna nec urna. Aliquam sed egestas neque, ac tempor libero. Quisque rhoncus risus eget enim tempus lobortis. Maecenas erat ex, dictum in sagittis quis, lobortis eget purus. Pellentesque condimentum felis vitae velit eleifend, ac rhoncus elit feugiat.',

    'Nullam non elementum augue, sed aliquam velit. Nam at varius eros. Curabitur ac quam risus. Curabitur quam justo, luctus bibendum leo sit amet, pellentesque tempus ligula. Integer nec diam vel metus tempus bibendum ac tristique ex. Morbi lacinia ultrices magna ac pretium. Sed id nibh ornare, accumsan ligula nec, volutpat velit. Vestibulum id quam hendrerit, blandit nunc nec, viverra orci. Sed sagittis, erat in interdum convallis, dolor augue imperdiet nisl, rutrum congue ex elit non tellus. Vivamus nec nisi pharetra, finibus purus vitae, interdum lectus. Cras et est nec ligula vulputate luctus congue vel nisi. Fusce quis felis purus. Cras scelerisque arcu sit amet nulla dapibus euismod. Etiam congue risus vel vehicula pretium. Morbi dapibus mollis orci sit amet suscipit. Ut iaculis dolor turpis, at pharetra est laoreet pulvinar.',

    'Vivamus placerat metus quis congue dapibus. Cras quis tincidunt quam. Proin felis mauris, rhoncus a efficitur id, consectetur a nisl. Pellentesque consequat rutrum ligula non ultrices. In massa enim, ultrices pharetra blandit eget, pretium vel justo. Nunc vel mauris at erat lobortis placerat. In scelerisque diam dui, eu ultrices odio lacinia vitae.',

    'Sed fermentum tellus ut lectus condimentum accumsan. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis luctus, nunc non condimentum vehicula, magna ligula congue orci, sed sollicitudin urna quam et enim. Nulla facilisi. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce consequat metus lacinia vulputate posuere. Sed eu urna nibh. Aliquam iaculis tempor odio, eget volutpat massa blandit at. Proin sollicitudin congue commodo. Sed aliquet, tellus vitae laoreet tristique, nisl nisl ornare sapien, id semper est eros non sapien. Sed laoreet libero lectus. Suspendisse pharetra commodo sapien ac auctor. Nunc volutpat iaculis mi vel tempor. Vestibulum dapibus lectus at dictum commodo. Quisque in leo velit.',

    'In ut ipsum sem. Nulla ligula metus, tincidunt ac mi vel, dignissim lacinia ante. Praesent auctor lectus et lorem convallis, id dignissim tortor pellentesque. In cursus in neque quis pellentesque. Vivamus commodo mi id cursus iaculis. Nulla sit amet leo quis sapien lacinia dignissim. Quisque vehicula vehicula eros, ac sagittis ante sollicitudin at. ',

    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi fermentum, arcu sit amet sodales scelerisque, felis leo vehicula dolor, sagittis faucibus risus dolor et lacus. Pellentesque in mauris a justo volutpat commodo ac a magna. Suspendisse accumsan et neque at vehicula. Donec tortor urna, eleifend et porta at, ultrices non dolor. Proin sit amet nulla sed velit luctus dapibus non id eros. Integer sodales dui eget efficitur aliquam. Praesent quis risus in nunc posuere varius a eu felis. Morbi nunc lectus, semper a feugiat id, sodales facilisis augue.',

    'Sed ornare porttitor odio, quis dapibus nisi ultrices et. In a lacus id diam posuere venenatis ac in felis. Duis ac tempor mi, pellentesque elementum turpis. Ut lobortis tristique ligula eget sollicitudin. Nulla gravida, augue nec lacinia euismod, ante odio malesuada mi, eu porta odio sapien sit amet dolor. Nam euismod massa vitae dolor mattis vestibulum viverra non est. Cras ex risus, dapibus vitae accumsan ut, dictum eu elit. Nulla facilisi. Nullam id feugiat urna.',

    'Aliquam luctus hendrerit tortor, a bibendum diam tristique id. Nunc in nibh nisi. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Phasellus quis ornare diam. Nam sagittis ipsum maximus tellus consequat, tempus interdum augue porta. Suspendisse eget neque augue. Pellentesque cursus in ante sed tempus. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Etiam ut posuere risus, nec accumsan lacus. Duis congue ullamcorper orci nec malesuada. Nullam nec nisi sed massa placerat sollicitudin. Nunc fringilla rhoncus tortor, sit amet pharetra sapien pulvinar a. Proin vulputate mi sit amet lorem molestie, quis vestibulum arcu gravida. Integer laoreet purus nec mi auctor, non congue nisi vehicula. Integer sit amet neque et lorem vestibulum luctus vitae ut lectus. Vivamus id neque id orci rutrum aliquet.',

    'Nulla fringilla nisl nibh, sit amet blandit eros vehicula eu. Duis orci sem, sollicitudin quis laoreet ac, luctus sit amet lectus. Maecenas nec convallis augue. Vivamus tempus luctus lacus, vel gravida ex fringilla at. Duis congue sem vel ante laoreet suscipit. Aliquam porttitor aliquam dolor at posuere. Nullam tristique sapien a dui vestibulum faucibus. Maecenas nulla velit, auctor eget lectus a, varius porttitor libero. Ut sed lectus pulvinar, luctus sapien ac, mattis tortor.',

    'Praesent nec magna porta, pretium tortor non, viverra libero. Morbi quis mauris enim. Sed odio nisi, accumsan quis hendrerit a, pretium in nunc. Mauris blandit venenatis nunc in efficitur. Quisque vel lacus ullamcorper, sagittis ante sed, lacinia orci. In hac habitasse platea dictumst. Duis viverra at ex tincidunt mattis. Morbi vestibulum viverra eros sit amet iaculis.',

    'Sed pulvinar leo ac nunc accumsan mattis. Curabitur volutpat, arcu id sodales tempus, erat elit laoreet eros, id faucibus arcu leo non ante. In pharetra est tellus, in eleifend risus consequat ac. Suspendisse potenti. Donec id libero eros. Vivamus sit amet diam vel arcu mattis vestibulum. Praesent sodales elit bibendum aliquet varius. Aliquam mattis, leo at dapibus semper, justo eros elementum erat, quis rhoncus ex tortor ut mauris. Fusce ac arcu ut mauris vehicula condimentum dapibus eget lacus. Duis congue nunc quis urna dictum, in mattis nulla interdum. Ut erat augue, gravida eu porttitor vitae, aliquam eget orci. Nam et sem maximus, condimentum velit vel, venenatis lectus. Nullam quis risus blandit, luctus lacus iaculis, semper tortor. Nam aliquet ullamcorper tortor, in elementum ligula laoreet eu. Cras quis leo dictum, auctor augue at, porta orci.',

    'Ut in nisl nisi. Duis eget tincidunt orci. Fusce gravida pharetra sem, non vulputate enim pulvinar vitae. Nunc feugiat sollicitudin enim quis dictum. Nunc ac volutpat nunc, a sollicitudin justo. Aliquam a libero lorem. Phasellus ac dolor justo. Pellentesque congue enim eget tristique tempus. Nunc sit amet scelerisque ligula. Cras in lacinia erat. Quisque rhoncus ex vel tortor efficitur, pellentesque porta nulla sodales. Quisque et porta nibh. Donec tristique rutrum orci, in euismod mauris tempor sit amet. Donec velit lacus, hendrerit ut ex vitae, ultrices consectetur risus.',

    'Vestibulum id eleifend purus. Mauris a congue mauris, nec pharetra turpis. Nullam volutpat orci quis diam aliquam, in sodales felis faucibus. Quisque feugiat velit vel vestibulum feugiat. Maecenas convallis accumsan eros id aliquam. Sed ac consequat lorem, at pulvinar mi. Sed tincidunt, augue eu mollis congue, diam velit dignissim lorem, ac viverra leo massa quis enim. Mauris quis leo enim. Vestibulum mollis, magna sit amet vulputate consequat, massa lectus gravida eros, a condimentum erat lacus eu orci. Etiam sit amet eros faucibus, malesuada velit volutpat, faucibus purus. Curabitur dignissim, lectus non aliquet pellentesque, urna turpis hendrerit orci, faucibus viverra libero lectus quis arcu. Aliquam non risus eget justo rutrum sagittis.',

    'Duis nec feugiat turpis, quis pulvinar odio. Aenean nec dolor mi. Integer ut malesuada mauris. Proin luctus pharetra molestie. Pellentesque scelerisque elementum facilisis. Nam sit amet velit non eros dapibus blandit vel a odio. Praesent diam massa, elementum eu nunc nec, vehicula placerat ante. Aliquam sed orci vel elit rhoncus tincidunt. Pellentesque ullamcorper facilisis consequat. Nullam odio orci, tempor at ligula eget, porttitor auctor turpis. Phasellus efficitur mollis consequat. Nulla ultricies, tellus sit amet euismod tincidunt, felis erat ultricies dolor, vel ultrices diam orci quis nulla. Aenean congue, ligula ac tristique placerat, arcu mauris luctus erat, vitae vehicula tellus elit non enim. Interdum et malesuada fames ac ante ipsum primis in faucibus. Integer tincidunt turpis eu lacinia vehicula. Sed convallis, lectus sit amet malesuada varius, neque justo vehicula felis, non volutpat mi augue auctor turpis.',

    'Fusce lectus lectus, gravida non imperdiet eget, tincidunt rhoncus tortor. Suspendisse potenti. Vivamus et tincidunt arcu. Quisque placerat cursus lectus in condimentum. Cras dapibus, diam a hendrerit finibus, ante leo imperdiet turpis, et lobortis sapien lectus vel sapien. Maecenas eleifend elit at enim porta volutpat. Proin et vehicula orci. Suspendisse mattis ac justo vel efficitur. Aenean fermentum tristique nibh ac aliquam. Integer magna sem, luctus quis elementum vel, mattis vitae diam. Aenean elementum viverra pulvinar. Suspendisse ultricies purus ac ullamcorper rutrum.',

    'Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aliquam eget tellus ac risus dictum suscipit a nec ipsum. Nulla facilisi. Sed euismod quam et risus dignissim, at aliquet massa volutpat. Aliquam erat volutpat. Nam tempor maximus lacinia. Quisque ultricies scelerisque leo. Ut varius felis quis tellus consequat, at fringilla ex dapibus. Suspendisse potenti. Phasellus porta, massa non mattis varius, justo elit varius nibh, et interdum neque purus id enim. Aliquam tristique ipsum dolor, sed semper mi lacinia at. Aenean egestas libero sit amet magna cursus finibus. Nam ultrices mauris ac nisi aliquam placerat. Donec pellentesque, purus non ultricies accumsan, purus nulla lobortis justo, nec vehicula massa lectus non urna. Pellentesque id quam magna.',

    'Vestibulum sed tincidunt magna. Maecenas mattis placerat turpis sed laoreet. Morbi pretium fermentum orci, non bibendum turpis tincidunt vitae. Duis commodo diam est, vel gravida orci ullamcorper eget. Sed consequat cursus est, eget vehicula urna rhoncus vel. Aliquam condimentum, turpis eget rutrum vulputate, dui nisi mollis tellus, in lobortis sem mi ut magna. Aliquam magna magna, lobortis at condimentum sit amet, rhoncus in massa. Sed posuere nibh eget velit maximus blandit. Donec tortor quam, faucibus id libero vel, lobortis sollicitudin metus. Donec et nisl convallis, posuere mi ut, rhoncus mi.',

    'Mauris interdum lectus non nisi rutrum posuere. Aenean non eros massa. Proin auctor diam ut justo consequat fringilla. Fusce viverra sed leo sit amet dignissim. Nunc metus nulla, ornare ut accumsan sed, ultrices sed turpis. Proin eu viverra velit. Donec ut lectus sed lorem imperdiet faucibus eget eget urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris pellentesque aliquam tincidunt. Praesent vel commodo purus. Quisque imperdiet eros non risus congue, vel tincidunt dui dignissim. Vestibulum venenatis urna eu dolor faucibus, non rutrum risus accumsan. Vestibulum tristique lorem et vestibulum convallis. Ut at dignissim lacus. Fusce urna justo, aliquet eu enim eget, convallis varius quam.',

    'Duis vitae ipsum sit amet mauris blandit volutpat nec sed eros. Integer efficitur vulputate velit, sit amet scelerisque massa. Mauris euismod purus a elit ornare, ac maximus velit bibendum. Nulla facilisi. Maecenas rutrum eros sed enim viverra, vel viverra magna semper. Quisque euismod nisl et purus volutpat blandit hendrerit placerat ante. Duis in velit feugiat mi ultricies rutrum. Sed urna diam, fringilla a eleifend et, fringilla a ex. Nulla diam mauris, consectetur sit amet consequat eget, accumsan nec nulla. In egestas tellus sed velit tempor, eget cursus enim dictum. Nam et lacus imperdiet, blandit nulla sed, euismod mauris. Quisque aliquet dui sed turpis egestas dignissim. Pellentesque sed nibh eu diam cursus pulvinar. Cras ut tincidunt ipsum. Donec egestas neque id elit cursus posuere.',

    'Donec blandit vulputate dolor, in dictum diam convallis a. Fusce at congue felis. Quisque at diam vel sem maximus viverra vitae non mi. Nullam quis mi purus. Fusce et pharetra risus. Curabitur tristique rhoncus sagittis. Quisque a dictum velit.',

    'Donec eget nisi est. Morbi consequat dignissim sem sit amet lacinia. Donec efficitur, mi molestie interdum consequat, mauris nisi rutrum mauris, sit amet accumsan eros nisl sed turpis. Duis quis condimentum mauris. Integer ac pellentesque elit, ac aliquam neque. Morbi vestibulum dapibus nisl, eget pretium arcu. Phasellus id consectetur massa, quis facilisis nibh. Sed sit amet eros convallis, commodo lacus suscipit, elementum nisi. Integer pellentesque enim ut finibus tincidunt. Praesent maximus risus quis metus commodo pharetra sed eu augue. Maecenas maximus massa ante, id laoreet urna maximus in. Etiam egestas iaculis lobortis.',

    'Cras vel faucibus neque. Fusce a malesuada risus. Maecenas scelerisque est ultricies nisi dignissim convallis. Nam suscipit a urna nec placerat. Sed laoreet sodales erat tristique imperdiet. Praesent blandit vulputate justo id ultrices. Donec pulvinar imperdiet justo, vitae vulputate libero egestas in. Suspendisse malesuada semper urna, quis malesuada felis iaculis ut. Integer lobortis augue a elementum rhoncus. Donec bibendum ante in placerat venenatis. Pellentesque varius fringilla placerat. Proin gravida elementum nibh vel volutpat. Suspendisse sed varius tellus. Donec pretium commodo ipsum eu consectetur.',

    'Duis pharetra odio lorem, at luctus neque ultricies in. Cras ullamcorper felis nec fringilla tincidunt. Duis ultrices varius tortor et sodales. Pellentesque in convallis felis. Nullam massa nibh, hendrerit quis rutrum eu, ullamcorper sed erat. Vivamus eget sodales augue. Sed dictum ultrices diam, sed fringilla dui mattis at. Suspendisse ullamcorper lectus vel ante imperdiet, vulputate efficitur elit iaculis. Proin sodales, elit vel ultrices viverra, nisl odio porttitor massa, non suscipit erat nisi non mi. Vestibulum mollis felis id blandit luctus. Pellentesque pharetra tincidunt dolor quis dapibus. Nam nisl dolor, varius sit amet condimentum vitae, efficitur sit amet nisl. Sed magna orci, tincidunt ut risus sit amet, laoreet vehicula nulla. Sed rutrum ultricies lacus eget consectetur. Suspendisse potenti. Suspendisse mi tellus, faucibus vel viverra et, dictum in neque.',

    'Vivamus est turpis, faucibus sit amet ipsum euismod, condimentum tincidunt leo. Nunc diam dui, gravida a erat non, suscipit volutpat erat. Morbi quis elementum nisi. Mauris urna nibh, varius in felis vitae, venenatis finibus diam. Mauris id maximus nibh, sed finibus nulla. Etiam lobortis elit ac magna pulvinar, at efficitur odio porttitor. Nullam tristique sagittis enim, sit amet imperdiet risus eleifend tristique. Fusce fringilla purus eget laoreet luctus. Vivamus vel erat non justo malesuada pretium. Mauris rhoncus dui molestie tellus dictum condimentum. Nam quis cursus orci, et luctus leo. Suspendisse aliquet finibus consequat. Aliquam a rhoncus turpis. Cras ac urna in tellus laoreet hendrerit accumsan vitae turpis. Nullam quis lorem sed risus eleifend venenatis ut eget augue. Morbi lacus nibh, aliquam aliquam imperdiet non, mattis id turpis.',

    'Phasellus lobortis tortor in leo tincidunt, malesuada dapibus neque dignissim. Morbi et massa hendrerit, malesuada dui at, scelerisque urna. Aliquam eros tortor, pretium non volutpat sit amet, varius vel mauris. Suspendisse dapibus velit a commodo cursus. Etiam et consequat turpis, vitae consectetur est. Nullam varius est eu nisl scelerisque, vel feugiat urna egestas. Suspendisse in elementum eros.',

    'Quisque eget quam non lacus gravida efficitur eu sit amet elit. Quisque in nisl aliquet, egestas ipsum non, mattis ex. Donec pretium mi eu nibh cursus, vitae aliquet enim suscipit. Curabitur nibh odio, semper at diam id, euismod iaculis ante. Maecenas vel molestie metus, sit amet semper neque. Aliquam dictum neque sit amet nisl mattis viverra. Aenean volutpat condimentum neque, ut mattis mauris aliquam non. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',

    'Fusce at suscipit est, non congue odio. Vestibulum lectus nisl, pulvinar id hendrerit eget, elementum vel metus. Morbi eu ante lacinia, sodales orci in, volutpat orci. Sed id venenatis eros. Integer elementum fringilla ante id vehicula. Fusce ac dolor a massa laoreet vulputate. Suspendisse sapien urna, placerat vel rutrum vel, hendrerit id purus. Vivamus vel bibendum elit. Quisque egestas congue consequat. Mauris non sodales diam.',

    'Vestibulum id purus massa. Aenean hendrerit dapibus felis et scelerisque. Aenean quis posuere nulla. Proin mattis ultrices purus. Nulla imperdiet venenatis lacinia. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent ex risus, vulputate a ultricies in, vulputate ac purus. Aliquam vehicula quam id varius tincidunt. Vivamus nec feugiat ex, in tristique nisl.',

    'Nullam nec dolor non ante auctor pulvinar. In teger consequat, lacus eu lobortis venenatis, metus mi auctor mauris, eget tincidunt nulla ex vel lorem. Nunc mollis, magna ac mattis condimentum, mi orci ultricies enim, sed pharetra ante felis ac tortor. Fusce ac lacinia erat, sit amet maximus metus. Nullam hendrerit metus ac lacus dapibus, nec laoreet dolor interdum. Phasellus pretium dui non nulla facilisis accumsan. Ut eu justo dui. Sed nisl velit, laoreet consectetur risus vel, varius pharetra mauris. Phasellus sem quam, consequat eget condimentum nec, posuere eget nisl. Cras lobortis elit luctus, pharetra nibh in, gravida est. Nulla eleifend odio vitae aliquet tincidunt. Sed id faucibus sapien. Etiam ultricies in turpis et dapibus. Proin efficitur, tortor et pharetra sagittis, magna sem commodo ex, sed convallis neque lacus malesuada ligula.',

    'Mauris id accumsan metus. Cla ss aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed risus velit, venenatis vitae arcu id, eleifend ultricies leo. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Pellentesque tincidunt felis eu sapien efficitur, non ultrices eros viverra. Ut laoreet neque at blandit euismod. Sed vehicula sem eu tortor imperdiet, vitae accumsan nisl dignissim. Vestibulum vel urna ac ligula volutpat malesuada sed in diam. Cras id ipsum quis metus hendrerit accumsan.',

    'Interdum et malesuada fames ac ante ipsum primis in faucibus. Cras in porta lacus, et facilisis tortor. Cras sollicitudin, justo vel sagittis sollicitudin, neque erat ullamcorper ex, in faucibus odio ante vitae dui. Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec at auctor enim. Curabitur orci ante, ultricies et auctor ac, condimentum non nisi. Nulla nec quam purus. Integer sit amet placerat mauris. Donec venenatis eu diam ac auctor. Donec et nunc eget erat vestibulum finibus quis a turpis. Morbi id lacus dignissim, pretium lorem quis, pulvinar nunc. Nulla ac porttitor lorem. Donec dapibus bibendum purus, id ullamcorper odio congue et. Quisque vel enim libero. Cras venenatis pellentesque nibh id hendrerit.',

    'Nunc pulvinar augue eros, non sagittis erat pellentesque et. Fusce sollicitudin enim at sollicitudin tincidunt. Etiam maximus erat vitae iaculis tincidunt. Suspendisse efficitur sapien quam, vitae iaculis massa euismod vitae. Pellentesque cursus orci ut ornare ornare. Duis vestibulum, orci ut efficitur sollicitudin, velit diam dignissim ante, id molestie arcu nisi nec tellus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin congue fermentum arcu at ornare. Nam congue lacus id scelerisque finibus. Praesent est tellus, luctus ut ullamcorper ac, consectetur in velit.',

    'In hac habitasse platea dictumst. Aenean faucibus nisl ut eros consequat molestie. Maecenas faucibus dui sapien, vel fringilla metus lobortis non. In hac habitasse platea dictumst. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Vestibulum scelerisque metus sem, vel ultrices tellus blandit id. Curabitur venenatis, nulla vel egestas tincidunt, diam ante dictum nibh, eu consectetur tortor nisl non turpis. Maecenas in porttitor ligula. Vestibulum convallis ante a feugiat pulvinar.',

    'Nulla faucibus nulla id felis porttitor, eget pellentesque libero aliquam. Nam eget elementum magna, sed pretium justo. Nunc molestie aliquam ante, ut vestibulum dolor congue quis. Quisque sit amet venenatis elit, eu vehicula mauris. Sed id quam at mi dictum mollis. Cras scelerisque, nulla sed viverra tempor, eros ex venenatis nisl, a ultricies nulla dolor ut urna. Quisque ornare sed diam in finibus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec malesuada sapien eget nisi egestas pharetra. Donec rhoncus porta rhoncus. Nunc quis vulputate lacus. Integer consectetur enim vel est semper facilisis. Nullam tristique vitae purus in fermentum.',

    'Nulla ligula metus, iaculis ac mattis sed, finibus eu leo. Sed placerat augue ac sem convallis ultrices. Phasellus non mauris id tortor congue ornare. Suspendisse fermentum, ipsum a accumsan volutpat, eros ipsum vestibulum diam, in tincidunt enim ipsum in velit. Integer fringilla dolor quis sagittis finibus. Nam congue eros id interdum convallis. Quisque quis commodo nisl. Suspendisse tincidunt ipsum sem, vel aliquet ante luctus ac. Aliquam congue vulputate ante eget commodo.',

    'In lorem quam, cursus et justo nec, vulputate porttitor sapien. Mauris suscipit, ex nec placerat gravida, massa risus sagittis magna, vitae faucibus tellus nibh vel leo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam accumsan ornare molestie. Phasellus blandit odio a massa sodales, sit amet molestie tellus fringilla. Donec at sem maximus, elementum risus eget, mollis massa. Ut aliquam diam non ipsum blandit, et luctus diam aliquam. Ut sagittis at ipsum non volutpat. Aenean consequat imperdiet magna non condimentum. Quisque elementum diam massa, non faucibus tortor cursus nec. Donec porta tincidunt mi, quis ultricies nibh mollis sit amet. Pellentesque suscipit varius tellus, pharetra aliquam tellus ultrices nec. Nam ornare sed nunc a sodales. Sed vel risus id nisl viverra tincidunt vel sed elit.',

    'Proin consectetur imperdiet ullamcorper. Sed tincidunt ultrices dignissim. Curabitur dapibus massa non lorem lacinia, quis iaculis enim feugiat. Cras consequat velit at felis varius consequat. Maecenas nec nisi tincidunt, dictum eros id, gravida risus. In at neque augue. Sed in gravida mauris, sed volutpat ipsum.',

    'Praesent ut tellus nisl. Cras laoreet ante nec libero sodales tempor. Maecenas in feugiat quam. Sed aliquet diam ut congue dignissim. Donec id dictum nisi. Suspendisse lacinia sem et porta gravida. Nam varius cursus arcu vel pellentesque. Cras accumsan convallis nisl at aliquam. Maecenas suscipit massa sit amet nunc consectetur ornare. Vivamus urna neque, pellentesque non sem vitae, sollicitudin dictum tellus. Vestibulum vel rhoncus libero, eget ullamcorper lorem. Donec mauris ipsum, fermentum quis iaculis sit amet, bibendum quis tortor.',

    'In ut dolor arcu. Nulla iaculis malesuada tortor semper auctor. Fusce id volutpat risus. Cras a imperdiet purus. Cras fermentum eros a sem sagittis, et aliquam tellus tempor. Ut quis leo dolor. Vivamus vitae ipsum laoreet, efficitur neque eget, ornare risus.',

    'Curabitur et arcu pretium, tempor nulla nec, vestibulum mi. Nullam sit amet lorem diam. Donec feugiat ullamcorper cursus. Vivamus sed libero euismod, finibus purus id, posuere lectus. Maecenas tincidunt in nisl id pharetra. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed id dignissim risus. Donec ac justo vulputate, posuere ipsum ac, mattis dolor. Fusce aliquet dictum eros, quis sollicitudin arcu pellentesque sed. Ut at lobortis nulla. Ut a dictum nunc. Cras finibus, ex a pharetra lacinia, magna arcu facilisis ante, sit amet tristique est magna nec est. Phasellus a tempus lectus.',

    'Quisque efficitur, purus at porttitor finibus, sapien quam mattis odio, eu fermentum sem est in nulla. Pellentesque ultricies orci at sapien tempor, sed fermentum felis accumsan. Suspendisse ullamcorper orci sed lectus venenatis lacinia. Donec dictum quis turpis ac blandit. Mauris quis feugiat dui, et egestas quam. Proin euismod congue semper. Vivamus scelerisque elit a erat sollicitudin, eget euismod quam venenatis. Etiam eu pretium dui, id consequat tellus. Praesent malesuada lectus eget commodo interdum. Integer turpis odio, faucibus a sodales eget, venenatis id purus. Cras id facilisis urna, vel pharetra erat. Integer dui lacus, viverra vitae mollis vitae, laoreet sit amet odio. Morbi dictum rutrum lorem, congue vehicula enim mattis mattis.',

    'Donec finibus, ipsum in suscipit pulvinar, lectus quam faucibus est, et congue lectus nibh eget arcu. Phasellus vulputate feugiat mauris, nec dignissim augue ornare vel. Donec est dui, pretium euismod finibus in, finibus nec sem. Mauris ultrices aliquet lorem. Phasellus vestibulum egestas sagittis. Ut dolor lectus, ullamcorper nec ultricies ut, scelerisque in massa. Fusce faucibus nec nulla ut facilisis. Praesent vel interdum nisl. Etiam ornare aliquet odio non pellentesque.',

    'Donec condimentum suscipit ex sit amet faucibus. Vestibulum mattis aliquet sagittis. Proin ut felis nec nisl vestibulum auctor sit amet aliquet massa. Nulla interdum augue arcu, quis ultrices metus suscipit sed. Nulla facilisi. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In augue elit, aliquet vitae fermentum a, placerat at sapien. Pellentesque non felis id massa mattis vehicula. Duis varius ante sit amet quam fringilla dignissim.',

    'Vestibulum ullamcorper, nulla quis pulvinar sagittis, risus eros sollicitudin arcu, eu fringilla dui libero eget ligula. Nunc sodales sed metus quis consectetur. Duis urna urna, ullamcorper eget maximus ac, porta id lacus. Maecenas viverra tortor nec sem consequat, vel scelerisque quam porttitor. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Etiam eu risus vel nulla aliquet porta sed eget leo. Quisque non convallis arcu, at tincidunt nulla.',

    'Ut quis dui ultricies, eleifend magna at, iaculis velit. Aliquam interdum cursus tortor vitae malesuada. Etiam pharetra imperdiet libero, sed facilisis nunc. Nunc scelerisque semper odio interdum aliquam. Fusce euismod quam erat, sit amet bibendum massa dictum vel. Proin quis vehicula diam, a vulputate diam. Curabitur non mauris ut enim vestibulum ultrices sed quis erat. Phasellus mattis auctor mattis. Nunc porta mauris in fringilla malesuada. Nullam in massa nec eros luctus sodales at efficitur lorem.',

    'Quisque sit amet vestibulum odio, sit amet euismod purus. Integer posuere orci at cursus sagittis. Morbi quis turpis viverra ipsum blandit molestie ut eget leo. Suspendisse sit amet nunc vestibulum, ornare lectus a, condimentum tortor. Nam ultrices, enim non posuere ultricies, tortor orci dignissim magna, at cursus tortor dui nec justo. Curabitur quis iaculis lacus, id placerat est. Sed sit amet aliquam diam, at tincidunt ligula. Suspendisse vulputate justo vel magna vehicula iaculis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Proin consectetur varius euismod.',

    'Maecenas vel mauris placerat, mattis nisl sit amet, gravida dolor. Integer lorem mauris, egestas a ligula eget, malesuada mollis est. Fusce ac ipsum sed tortor mattis ornare quis ut metus. Nunc iaculis nisl at enim dictum, nec elementum leo bibendum. Donec ultricies dapibus molestie. Vivamus blandit laoreet hendrerit. Pellentesque malesuada et urna non consectetur. Aliquam pretium feugiat justo, id dapibus augue aliquam vitae. Aliquam nisi justo, pulvinar cursus dui eu, vestibulum pretium purus.',

    'Curabitur id sagittis libero. Vivamus pulvinar convallis erat, lobortis aliquam arcu auctor fringilla. Aenean congue fermentum auctor. Fusce nisl nulla, condimentum non placerat tristique, viverra eu justo. Suspendisse ultricies hendrerit facilisis. Phasellus dui lorem, facilisis at consectetur quis, aliquam suscipit diam. Ut sagittis libero non augue condimentum gravida. Fusce pulvinar tincidunt nisi sed maximus. Aliquam eget augue gravida turpis efficitur posuere nec sed lacus. Nulla sit amet nunc non sapien convallis fermentum. Praesent rhoncus mi eu quam pulvinar tincidunt. In vel nulla iaculis quam commodo vestibulum vel sit amet nibh. Nunc sed tellus ipsum. Nulla ultricies elit sed pulvinar suscipit.',

    'Aenean semper nibh et massa iaculis condimentum. Sed vel quam pharetra, rutrum neque sed, congue orci. Nunc placerat vestibulum lorem et ornare. Morbi pharetra tortor in nunc dignissim, vitae rutrum tellus elementum. Integer varius pellentesque laoreet. Ut diam nisl, sollicitudin nec libero nec, semper auctor turpis. Etiam tempor lorem eu gravida dignissim. Phasellus a pulvinar nisl. Sed sagittis aliquam bibendum. Aliquam fringilla, turpis eget gravida lacinia, neque lacus convallis mi, vitae fermentum libero neque vel sapien. Duis non risus odio. Aliquam fermentum, nisi ac pharetra posuere, lectus tortor porttitor mauris, blandit dictum sem nulla et dui.',

    'Aenean nec nulla rutrum, malesuada augue fermentum, mattis nibh. Vivamus elementum molestie massa nec blandit. Fusce non aliquet magna. Curabitur ut accumsan massa. Vivamus convallis nisl libero, nec sagittis arcu auctor ut. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse molestie felis eu nulla rhoncus, a scelerisque mi tempor. Nam efficitur libero non pellentesque malesuada. Interdum et malesuada fames ac ante ipsum primis in faucibus.',

    'Proin eu blandit enim, sit amet tincidunt ligula. Aliquam faucibus eleifend laoreet. Sed pulvinar diam lacus, non scelerisque purus efficitur sit amet. In accumsan dui ut erat luctus molestie. Integer mattis, lectus nec egestas dignissim, ex felis bibendum turpis, a hendrerit purus urna nec urna. Aliquam sed egestas neque, ac tempor libero. Quisque rhoncus risus eget enim tempus lobortis. Maecenas erat ex, dictum in sagittis quis, lobortis eget purus. Pellentesque condimentum felis vitae velit eleifend, ac rhoncus elit feugiat.',

    'Nullam non elementum augue, sed aliquam velit. Nam at varius eros. Curabitur ac quam risus. Curabitur quam justo, luctus bibendum leo sit amet, pellentesque tempus ligula. Integer nec diam vel metus tempus bibendum ac tristique ex. Morbi lacinia ultrices magna ac pretium. Sed id nibh ornare, accumsan ligula nec, volutpat velit. Vestibulum id quam hendrerit, blandit nunc nec, viverra orci. Sed sagittis, erat in interdum convallis, dolor augue imperdiet nisl, rutrum congue ex elit non tellus. Vivamus nec nisi pharetra, finibus purus vitae, interdum lectus. Cras et est nec ligula vulputate luctus congue vel nisi. Fusce quis felis purus. Cras scelerisque arcu sit amet nulla dapibus euismod. Etiam congue risus vel vehicula pretium. Morbi dapibus mollis orci sit amet suscipit. Ut iaculis dolor turpis, at pharetra est laoreet pulvinar.',

    'Vivamus placerat metus quis congue dapibus. Cras quis tincidunt quam. Proin felis mauris, rhoncus a efficitur id, consectetur a nisl. Pellentesque consequat rutrum ligula non ultrices. In massa enim, ultrices pharetra blandit eget, pretium vel justo. Nunc vel mauris at erat lobortis placerat. In scelerisque diam dui, eu ultrices odio lacinia vitae.',

    'Sed fermentum tellus ut lectus condimentum accumsan. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis luctus, nunc non condimentum vehicula, magna ligula congue orci, sed sollicitudin urna quam et enim. Nulla facilisi. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce consequat metus lacinia vulputate posuere. Sed eu urna nibh. Aliquam iaculis tempor odio, eget volutpat massa blandit at. Proin sollicitudin congue commodo. Sed aliquet, tellus vitae laoreet tristique, nisl nisl ornare sapien, id semper est eros non sapien. Sed laoreet libero lectus. Suspendisse pharetra commodo sapien ac auctor. Nunc volutpat iaculis mi vel tempor. Vestibulum dapibus lectus at dictum commodo. Quisque in leo velit.',

    'In ut ipsum sem. Nulla ligula metus, tincidunt ac mi vel, dignissim lacinia ante. Praesent auctor lectus et lorem convallis, id dignissim tortor pellentesque. In cursus in neque quis pellentesque. Vivamus commodo mi id cursus iaculis. Nulla sit amet leo quis sapien lacinia dignissim. Quisque vehicula vehicula eros, ac sagittis ante sollicitudin at. '
];

/*
foreach ($coments as $key => $c) {
    $recordtoinsert = new stdClass();
    $recordtoinsert->rating = random_int(1, 5);
    $recordtoinsert->message = $c;
    $recordtoinsert->courseid = 4;
    $recordtoinsert->userid = $key + 5;

    $DB->insert_record('course_rating', $recordtoinsert);
}
*/