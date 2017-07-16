<?php
// Print the page header.
$PAGE->set_url('/mod/newmodule/view.php');
$PAGE->set_title(format_string($page_name));
$PAGE->set_heading(format_string("dddddd"));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('newmodule-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();
