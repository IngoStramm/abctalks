<?php

/**
 * abctalks_get_live_next_video
 *
 * @return array (or string: error message)
 */
function abctalks_get_live_next_video()
{
    $api_key = abctalks_get_option('youtube_api_key');

    if (!$api_key)
        return __('API Key não encontrada', 'abctalks');

    $channel_id = abctalks_get_option('channel_id');

    if (!$channel_id)
        return __('API Key não encontrada', 'abctalks');

    $youtube_channel_url = 'https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId=' . $channel_id . '&maxResults=50&type=video&eventType=upcoming&key=' . $api_key;
    $youtube_channel_response = wp_remote_get($youtube_channel_url);
    $youtube_channel_array = json_decode($youtube_channel_response['body']);
    $youtube_videos_data = $youtube_channel_array->items;
    $youtube_next_video_data = $youtube_channel_array->items[count($youtube_videos_data) - 1];
    $youtube_next_video = array(
        'title'                 => $youtube_next_video_data->snippet->title,
        'video_id'              => $youtube_next_video_data->id->videoId,
        'video_description'     => $youtube_next_video_data->snippet->description,
        'thumbnail'             => $youtube_next_video_data->snippet->thumbnails->high->url,
        'url'                   => 'https://www.youtube.com/watch?v=' . $youtube_next_video_data->id->videoId
    );
    return $youtube_next_video;
}

function abctalks_get_live_next_video_full_description()
{
    $live_next_video = abctalks_get_live_next_video();

    if (!$live_next_video)
        return __('Não foi possível encontrar o vídeo da próxima live nos transientes.', 'abctalks');

    $api_key = abctalks_get_option('youtube_api_key');

    if (!$api_key)
        return __('API Key não encontrada', 'abctalks');

    $channel_id = abctalks_get_option('channel_id');

    if (!$channel_id)
        return __('API Key não encontrada', 'abctalks');

    $youtube_channel_url = 'https://www.googleapis.com/youtube/v3/videos?id=' . $live_next_video['video_id'] . '&key=' . $api_key . '&part=snippet';
    $youtube_channel_response = wp_remote_get($youtube_channel_url);
    $youtube_next_video_data = json_decode($youtube_channel_response['body']);
    $youtube_next_video_array = $youtube_next_video_data->items[0];
    return $youtube_next_video_array->snippet->description;
}


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
function abctalks_set_youtube_videos_transients()
{
    if (false === ($live_next_video = get_transient('live_next_video'))) {
        $live_next_video = abctalks_get_live_next_video();
        set_transient('live_next_video', $live_next_video, HOUR_IN_SECONDS);
    }
    if (false === ($live_next_video_full_description = get_transient('live_next_video_full_description'))) {
        $live_next_video_full_description = abctalks_get_live_next_video_full_description();
        set_transient('live_next_video_full_description', $live_next_video_full_description, HOUR_IN_SECONDS);
    }
    if (false === ($main_playlist_videos = get_transient('main_playlist_videos'))) {
        $main_playlist_videos = abctalks_get_playlist_videos(null);
        set_transient('main_playlist_videos', $main_playlist_videos, HOUR_IN_SECONDS);
    }
    if (false === ($cuts_playlist_videos = get_transient('cuts_playlist_videos'))) {
        $playlist_id = abctalks_get_option('cuts_playlist_id');
        $cuts_playlist_videos = abctalks_get_playlist_videos($playlist_id);
        set_transient('cuts_playlist_videos', $cuts_playlist_videos, HOUR_IN_SECONDS);
    }
}

add_action('init', 'abctalks_set_youtube_videos_transients');


/**
 * abctalks_delete_transients
 *
 * Apaga os transientes
 * 
 * @return array
 */
function abctalks_delete_transients()
{
    $delete_live_next_video = delete_transient('live_next_video');
    $delete_live_next_video_full_description = delete_transient('live_next_video_full_description');
    $delete_main_playlist_videos = delete_transient('main_playlist_videos');
    $delete_cuts_playlist_videos = delete_transient('cuts_playlist_videos');
    $response = '';
    $$msg = '';
    if ($delete_main_playlist_videos && $delete_cuts_playlist_videos && $delete_live_next_video && $delete_live_next_video_full_description) {
        $msg = __('Vídeos sincronizados com sucesso!', 'abctalks');
        $response = array('success' => true, 'msg' => $msg);
    } else {
        $msg = __('Ocorreu um erro ao tentar sincronizar os vídeos.', 'abctalks');
        $data = array(
            'live_next_video'      => $delete_delete_live_next_video,
            'live_next_video_full_description'      => $delete_live_next_video_full_description,
            'main_playlist_videos'      => $delete_main_playlist_videos,
            'cuts_playlist_videos'      => $delete_cuts_playlist_videos
        );
        $response = array('success' => false, 'msg' => $msg, 'data' => $data);
    }
    wp_send_json($response);
}

add_action('wp_ajax_abctalks_delete_transients', 'abctalks_delete_transients');
// add_action('wp_ajax_nopriv_abctalks_delete_transients', 'abctalks_delete_transients');

function abctalks_next_upcoming_video_output($return_video = true)
{
    $live_next_video = get_transient('live_next_video');
    $output = '';
    if ($return_video) {
        $output = '<div class="abctalks-playlist">';
        $output .= '<div class="abctalks-playlist-item abctalks-embed-container">';
        $output .= '<iframe src="https://www.youtube.com/embed/' . $live_next_video['video_id'] . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        $output .= '</div>';
        $output .= '</div>';
    } else {
        $output  = $live_next_video['video_description'];
    }
    return $output;
}

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
