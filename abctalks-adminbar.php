<?php
function abctalks_adminbar_delete_transients_button(WP_Admin_Bar $admin_bar)
{
    $admin_bar->add_menu(array(
        'id'    => 'abctalks-delete-transients-btn-wrapper',
        'parent' => null,
        'group'  => null,
        'title' => __('Sincronizar vídeos', 'abctalks'), //you can use img tag with image link. it will show the image icon Instead of the title.
        'href'  => '#',
        // 'meta' => [
        //     'title' => __('Limpar cache do site', 'abctalks'), //This title will show on hover
        // ]
    ));
}

add_action('admin_bar_menu', 'abctalks_adminbar_delete_transients_button', 999);

function abctalks_adminbar_style()
{
?>
    <style>
        #wp-admin-bar-abctalks-delete-transients-btn-wrapper .ab-item:before {
            content: '\f19b';
            top: 2px;
        }
    </style>
<?php
}

add_action('wp_head', 'abctalks_adminbar_style');
add_action('admin_head', 'abctalks_adminbar_style');
