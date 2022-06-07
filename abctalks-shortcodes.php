<?php

/**
 * abctalks_last_video_main_playlist_shortcode
 *
 * Retorna o vídeo mais recente da playlist principal
 * 
 * @param  array $atts
 * @return string
 */
function abctalks_next_live_video_shortcode()
{
    return abctalks_next_upcoming_video_output();
}

/**
 * abctalks_get_youtube_playlist_description
 *
 * Retorna a descrição do vídeo mais recente da playlist principal
 * 
 * @return string
 */
function abctalks_get_next_live_video_full_description_shortcode()
{
    $live_next_video_full_description = get_transient('live_next_video_full_description');
    // Verifica se existe nos transientes
    if (!$live_next_video_full_description) {
        // abctalk_debug('Transiente \'live_next_video_full_description\' não encontrado');
        $live_next_video_full_description = abctalks_get_live_next_video_full_description();
        set_transient('live_next_video_full_description', $live_next_video_full_description, HOUR_IN_SECONDS);
    }
    return $live_next_video_full_description;
}

/**
 * abctalks_main_playlist_shortcode
 *
 * Retorna os útlimos 4 quatro vídeos da playlist principal
 * 
 * @param  array $atts
 * @return string
 */
function abctalks_main_playlist_shortcode($atts)
{
    $playlist_id = abctalks_get_option('main_playlist_id');
    $atts = shortcode_atts(array(
        'playlist_id' => $playlist_id ? $playlist_id : '',
        'max_results' => 4,
        'layout'        => 'two-columns'
    ), $atts);

    return abctalks_playlist_output($atts);
}

/**
 * abctalks_cuts_playlist_shortcode
 *
 * Retorna os útlimos 4 quatro vídeos da playlist de cortes
 * 
 * @param  array $atts
 * @return string
 */
function abctalks_cuts_playlist_shortcode($atts)
{
    $playlist_id = abctalks_get_option('cuts_playlist_id');
    $atts = shortcode_atts(array(
        'playlist_id' => $playlist_id ? $playlist_id : '',
        'max_results' => 3,
        'layout'        => 'three-columns'
    ), $atts);

    return abctalks_playlist_output($atts);
}

add_shortcode('abctalks_next_live_video', 'abctalks_next_live_video_shortcode');
add_shortcode('abctalks_next_live_video_description', 'abctalks_get_next_live_video_full_description_shortcode');
add_shortcode('abctalks_main_playlist', 'abctalks_main_playlist_shortcode');
add_shortcode('abctalks_cuts_playlist', 'abctalks_cuts_playlist_shortcode');
