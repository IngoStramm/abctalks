<?php

add_action('wp_enqueue_scripts', 'abc_talk_frontend_scripts');

function abc_talk_frontend_scripts()
{

    $min = (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '10.0.0.3'))) ? '' : '.min';

    if (empty($min)) :
        wp_enqueue_script('abctalks-livereload', 'http://localhost:35729/livereload.js?snipver=1', array(), null, true);
    endif;

    wp_register_script('abctalks-script', ABC_TALK_URL . 'assets/js/abctalks' . $min . '.js', array('jquery'), '1.0.0', true);

    wp_enqueue_script('abctalks-script');

    wp_localize_script('abctalks-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_style('abctalks-style', ABC_TALK_URL . 'assets/css/abctalks.css', array(), false, 'all');
}
