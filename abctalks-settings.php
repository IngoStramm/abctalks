<?php

/**
 * abctalks_settings_metabox
 *
 * @return void
 */
function abctalks_settings_metabox()
{

    $cmb_options = new_cmb2_box(array(
        'id'           => 'abctalks_settings_page',
        'title'        => esc_html__('ABC Talk', 'abctalks'),
        'object_types' => array('options-page'),
        'option_key'      => 'abctalks_settings', // The option key and admin menu page slug.
        'icon_url'        => 'dashicons-microphone', // Menu icon. Only applicable if 'parent_slug' is left empty.
        'capability'        => 'edit_others_pages'
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('Preencha as informações abaixo para que os vídeos sejam exibidos no site.', 'abctalks'),
        'id'      => 'title_1',
        'type'    => 'title',
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('API Key do Youtube', 'abctalks'),
        'id'      => 'youtube_api_key',
        'type'    => 'text',
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('ID da Playlist dos episódios inteiros do podcast', 'abctalks'),
        'id'      => 'main_playlist_id',
        'type'    => 'text',
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('ID da Playlist dos cortes do podcast', 'abctalks'),
        'id'      => 'cuts_playlist_id',
        'type'    => 'text',
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('Shorcodes', 'abctalks'),
        'id'      => 'title_2',
        'type'    => 'title',
        'after_field'  => 'abctalks_shortcodes_after_field',
    ));

    $cmb_options->add_field(array(
        'name'    => esc_html__('Sincronização dos vídeos', 'abctalks'),
        'id'      => 'title_3',
        'type'    => 'title',
        'after_field'  => 'abctalks_video_sync_after_field',
    ));
}

add_action('cmb2_admin_init', 'abctalks_settings_metabox');

/**
 * abctalks_get_option
 *
 * @param  mixed $key
 * @param  mixed $default
 * @return void
 */
function abctalks_get_option($key = '', $default = false)
{
    if (function_exists('cmb2_get_option')) {
        return cmb2_get_option('abctalks_settings', $key, $default);
    }

    $opts = get_option('abctalks_settings', $default);

    $val = $default;

    if ('all' == $key) {
        $val = $opts;
    } elseif (is_array($opts) && array_key_exists($key, $opts) && false !== $opts[$key]) {
        $val = $opts[$key];
    }

    return $val;
}

function abctalks_shortcodes_after_field()
{
?>
    <p>Use as opções de shortcodes abaixo para exibir os vídeos. Clique nos códigos para copiá-los.</p>
    <ul class="abctalks-shortcodes">
        <li>Exibir o vídeo mais recente da playlist dos episódios inteiros: <code title="Clique para copiar">[abctalks_last_video_main_playlist]</code></li>
        <li>Exibir a descrição do vídeo mais recente da playlist dos episódios inteiros: <code title="Clique para copiar">[abctalks_playlist_description]</code></li>
        <li>Exibir os últimos 4 vídeos mais recentes da playlist dos episódios inteiros: <code title="Clique para copiar">[abctalks_main_playlist]</code></li>
        <li>Exibir os últimos 3 vídeos mais recentes da playlist dos cortes: <code title="Clique para copiar">[abctalks_cuts_playlist]</code></li>
    </ul>
    <style>
        .abctalks-shortcodes code {
            cursor: pointer;
        }
    </style>
    <script>
        const abctalksShortcodes = document.querySelectorAll('.abctalks-shortcodes')
        for (abctalksShortcode of abctalksShortcodes) {
            const codes = abctalksShortcode.querySelectorAll('code')
            for (code of codes) {
                code.addEventListener('click', (e) => {
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(e.target.innerText)
                        alert(`Shortcode "${e.target.innerText}" copiado!`)
                    }
                })
            }
        }
    </script>
<?php
}

function abctalks_video_sync_after_field()
{
?>
    <p>Por padrão, os vídeos são sincronizados com o Youtube automaticamente a cada hora.</p>
    <p>Caso necessário, clique no botão "<strong>Sincronizar vídeos</strong>", na barra no topo, para executar a sincronização neste momento (<em>vide imagem abaixo<em>).</p>
    <img src="<?php echo ABC_TALK_URL ?>/assets/images/print-video-sync-btn.jpg" />
<?php
}
