document.addEventListener('DOMContentLoaded', function () {

    function abctalks_delete_transients_evt() {
        const btnWrapper = document.getElementById('wp-admin-bar-abctalks-delete-transients-btn-wrapper');

        if (!btnWrapper) {
            return;
        }

        const btn = btnWrapper.getElementsByTagName('a')[0];

        if (!btn) {
            return;
        }

        btn.addEventListener('click', function (e) {

            e.preventDefault();
            let is_disabled = btn.dataset.disabled;
            if (is_disabled) {
                return;
            }
            btn.dataset.disabled = '';
            const action = 'abctalks_delete_transients';

            const xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.open('POST', ajax_object.ajax_url + '?action=' + action);
            xhr.onload = function () {
                const response = xhr.response;
                // console.log(response);
                if (xhr.status === 200) {
                    alert(response.msg);
                }
                delete btn.dataset.disabled;
            };

            xhr.send();
        });

    }
    abctalks_delete_transients_evt();
});