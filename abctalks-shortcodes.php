<?php

/**
 * abctalks_get_youtube_playlist_videos
 *
 * Retorna a quantidade de vídeo especificada da playlist definida
 * 
 * @param  string $playlist_id
 * @param  int $max_results
 * @return array or string (error message)
 */
function abctalks_get_youtube_playlist_videos($playlist_id, $max_results = 4)
{
    $api_key = abctalks_get_option('youtube_api_key');

    if (!$api_key)
        return __('API Key não encontrada', 'abctalks');

    $playlist_videos = array();
    // Removido o parâmetro "max_results" para retornar todos os vídeos da playlist
    $url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=' . $playlist_id . '&key=' . $api_key;
    $youtube_playlist_response = wp_remote_get($url);
    if (is_array($youtube_playlist_response)) {
        $youtube_playlist_response = json_decode($youtube_playlist_response['body']);
        if (isset($youtube_playlist_response->items)) {
            $count_videos = 0;
            foreach ($youtube_playlist_response->items as $playlist_item) {
                // Previne que vídeos privados sejam exibidos
                if ($playlist_item->snippet->title !== 'Private video' && $count_videos < $max_results) {
                    $playlist_videos[] = array(
                        'title' => $playlist_item->snippet->title,
                        'video_id' => $playlist_item->snippet->resourceId->videoId,
                        'thumbnail' => $playlist_item->snippet->thumbnails->high->url,
                        'video_description' => $playlist_item->snippet->description
                    );
                    $count_videos++;
                }
            }
        }
    }
    return $playlist_videos;
}


/**
 * abctalks_playlist_shortcode
 *
 * Retorna o HTML dos vídeos, conforme os parâmetros passados (playlist ID e quantidade de vídeos)
 * 
 * @param  array $atts
 * @return string
 */
function abctalks_playlist_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'playlist_id' => '',
        'max_results' => 4,
        'layout' => ''
    ), $atts);
    $playlist_videos = abctalks_get_youtube_playlist_videos($atts['playlist_id'], $atts['max_results']);

    if (!$playlist_videos)
        return __('Não foi possível encontrar a playlist.', 'abctalks');

    $extra_css_class = $atts['layout'] ? ' ' . $atts['layout'] : '';

    $output = '<div class="abctalks-playlist' . $extra_css_class . '">';
    if (is_iterable($playlist_videos)) {
        foreach ($playlist_videos as $playlist_video) {
            $output .= '<div class="abctalks-playlist-item abctalks-embed-container">';
            $output .= '<iframe src="https://www.youtube.com/embed/' . $playlist_video['video_id'] . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            $output .= '</div>';
        }
    } else {
        $output .= '<div class="abctalks-playlist-item abctalks-embed-container">';
        $output .= '<iframe src="https://www.youtube.com/embed/' . $playlist_videos['video_id'] . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        $output .= '</div>';
    }
    $output .= '</div>';
    return $output;
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

    return abctalks_playlist_shortcode($atts);
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

    return abctalks_playlist_shortcode($atts);
}

/**
 * abctalks_last_video_main_playlist_shortcode
 *
 * Retorna o vídeo mais recente da playlist principal
 * 
 * @param  array $atts
 * @return string
 */
function abctalks_last_video_main_playlist_shortcode($atts)
{
    $playlist_id = abctalks_get_option('main_playlist_id');
    $atts = shortcode_atts(array(
        'playlist_id' => $playlist_id ? $playlist_id : '',
        'max_results' => 1
    ), $atts);

    return abctalks_playlist_shortcode($atts);
}

/**
 * abctalks_get_youtube_playlist_description
 *
 * Retorna a descrição do vídeo mais recente da playlist principal
 * 
 * @return string
 */
function abctalks_get_youtube_playlist_description()
{
    $playlist_id = abctalks_get_option('main_playlist_id');
    $last_video = abctalks_get_youtube_playlist_videos($playlist_id, 1);
    return $last_video[0]['video_description'];
}

add_shortcode('abctalks_last_video_main_playlist', 'abctalks_last_video_main_playlist_shortcode');
add_shortcode('abctalks_playlist_description', 'abctalks_get_youtube_playlist_description');
add_shortcode('abctalks_main_playlist', 'abctalks_main_playlist_shortcode');
add_shortcode('abctalks_cuts_playlist', 'abctalks_cuts_playlist_shortcode');
