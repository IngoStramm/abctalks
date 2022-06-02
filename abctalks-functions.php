<?php

/**
 * abctalks_get_playlist_videos
 *
 * Retorna os 50 vídeos mais recentes de uma playlist
 * Se o $playlist_id não for passado, será usado o id do playlist principal
 * 
 * @param  string $playlist_id
 * @return string (or string: error message)
 */
function abctalks_get_playlist_videos($playlist_id)
{
    $api_key = abctalks_get_option('youtube_api_key');

    if (!$api_key)
        return __('API Key não encontrada', 'abctalks');

    $playlist_id = is_null($playlist_id) || empty($playlist_id) ?
        abctalks_get_option('main_playlist_id') :
        $playlist_id;

    if (!$playlist_id)
        return __('ID da playlist de podcasts não encontrada', 'abctalks');

    $playlist_videos = array();
    // Removido o parâmetro "max_results" para retornar todos os vídeos da playlist
    $url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=' . $playlist_id . '&maxResults=50&key=' . $api_key;
    $youtube_playlist_response = wp_remote_get($url);
    if (is_array($youtube_playlist_response)) {
        $youtube_playlist_response = json_decode($youtube_playlist_response['body']);
        if (isset($youtube_playlist_response->items)) {
            foreach ($youtube_playlist_response->items as $playlist_item) {
                // Previne que vídeos privados sejam exibidos
                if ($playlist_item->snippet->title !== 'Private video') {
                    $playlist_videos[] = array(
                        'id'                    => $playlist_item->id,
                        'title'                 => $playlist_item->snippet->title,
                        'video_id'              => $playlist_item->snippet->resourceId->videoId,
                        'video_description'     => $playlist_item->snippet->description,
                        'thumbnail'             => $playlist_item->snippet->thumbnails->high->url,
                        'playlistId'            => $playlist_item->snippet->playlistId
                    );
                }
            }
        }
    }

    return $playlist_videos;
}

/**
 * abctalks_set_playlist_videos_transients
 * 
 * verifica se os transientes existem e
 * caso não existam, cria-os
 *
 * @return void
 */
function abctalks_set_playlist_videos_transients()
{
    if (false === ($main_playlist_videos = get_transient('main_playlist_videos'))) {
        $main_playlist_videos = abctalks_get_playlist_videos(null);
        set_transient('main_playlist_videos', $main_playlist_videos, HOUR_IN_SECONDS);
        // abctalk_debug('main_playlist_videos');
    }
    if (false === ($cuts_playlist_videos = get_transient('cuts_playlist_videos'))) {
        $playlist_id = abctalks_get_option('cuts_playlist_id');
        $cuts_playlist_videos = abctalks_get_playlist_videos($playlist_id);
        set_transient('cuts_playlist_videos', $cuts_playlist_videos, HOUR_IN_SECONDS);
        // abctalk_debug('cuts_playlist_videos');
    }
}

add_action('init', 'abctalks_set_playlist_videos_transients');


/**
 * abctalks_delete_transients
 *
 * Apaga os transientes
 * 
 * @return array
 */
function abctalks_delete_transients()
{
    $delete_main_playlist_videos = delete_transient('main_playlist_videos');
    $delete_cuts_playlist_videos = delete_transient('cuts_playlist_videos');
    $response = '';
    $$msg = '';
    if ($delete_main_playlist_videos && $delete_cuts_playlist_videos) {
        $msg = __('Vídeos sincronizados com sucesso!', 'abctalks');
        $response = array('success' => true, 'msg' => $msg);
    } else {
        $msg = __('Ocorreu um erro ao tentar sincronizar os vídeos.', 'abctalks');
        $data = array(
            'main_playlist_videos'      => $delete_main_playlist_videos,
            'cuts_playlist_videos'      => $delete_cuts_playlist_videos
        );
        $response = array('success' => false, 'msg' => $msg, 'data' => $data);
    }
    wp_send_json($response);
}

add_action('wp_ajax_abctalks_delete_transients', 'abctalks_delete_transients');
// add_action('wp_ajax_nopriv_abctalks_delete_transients', 'abctalks_delete_transients');

/**
 * abctalks_playlist_output
 *
 * Retorna o HTML dos vídeos, conforme os parâmetros passados (playlist ID e quantidade de vídeos)
 * 
 * @param  array $atts
 * @return string
 */
function abctalks_playlist_output($atts)
{
    // apesar desta função não ser um shortcode, 
    // mantive o método shortcode_atts para tratar 
    // os atributos de shortcode que foram passados
    $atts = shortcode_atts(array(
        'playlist_id' => '',
        'max_results' => 4,
        'layout' => ''
    ), $atts);

    $main_playlist_id = abctalks_get_option('main_playlist_id');
    $cuts_playlist_id = abctalks_get_option('cuts_playlist_id');
    $main_playlist_videos = null;
    switch ($atts['playlist_id']) {
        case null:
        case '':
        case $main_playlist_id:
            $main_playlist_videos = get_transient('main_playlist_videos');
            break;

        case $cuts_playlist_id:
            $main_playlist_videos = get_transient('cuts_playlist_videos');
            break;

        default:
            $main_playlist_videos = abctalks_get_playlist_videos($atts['playlist_id']);
            break;
    }

    if (!$main_playlist_videos)
        return __('Não foi possível encontrar a playlist.', 'abctalks');

    if (is_string($main_playlist_videos))
        return $main_playlist_videos;

    $playlist_videos = [];
    $count = 0;
    foreach ($main_playlist_videos as $all_playlist_video) {
        if ($count < $atts['max_results'])
            $playlist_videos[] = $all_playlist_video;
        $count++;
    }

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
