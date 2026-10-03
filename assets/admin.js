(() => {
    'use strict';

    const
        btnUpdate    = document.querySelector('.update-sjb-form-options'),
        div_messages = document.querySelector('.ajax-save-result'),
        tabButtons   = document.querySelectorAll('.plugin-options .nav-tab'),
        panels       = document.querySelectorAll('.sjb-tab-panel');

    // 1 si el switch esta marcado, 0 si no
    const valorSwitch = (id) => {
        const input = document.getElementById(id);
        return input && input.checked ? 1 : 0;
    };

    tabButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.dataset.tab;
            tabButtons.forEach((b) => b.classList.toggle('nav-tab-active', b === btn));
            panels.forEach((panel) => {
                const activo = panel.dataset.panel === id;
                panel.classList.toggle('is-active', activo);
                panel.hidden = !activo;
            });
        });
    });

    if (btnUpdate) {
        btnUpdate.addEventListener('click', () => {
            saveOptionsForm();
        });
    }

    let toastTimer = null;

    const showToast = (exito, msg) => {
        div_messages.textContent = msg || (exito ? 'Saved' : 'Error');
        div_messages.classList.remove('hidden', 'exito', 'error');
        div_messages.classList.add(exito ? 'exito' : 'error');
        if (toastTimer) {
            clearTimeout(toastTimer);
        }
        toastTimer = setTimeout(() => div_messages.classList.add('hidden'), 3000);
    };

    const saveOptionsForm = () => {
        let data2Send = {
            sjb_noncename: SJB_BOARD.ajax_nonce,
            action: SJB_BOARD.ajax_action,
            quefasemos: 'backend',
            myplugindata: {
                moderation: valorSwitch('moderation'),
                in_footer: valorSwitch('in_footer'),
                delete_onuninstall: valorSwitch('delete_onuninstall'),
                debug_mode: valorSwitch('debug_mode'),
            }
        };

        jQuery.ajax({
            url: ajaxurl,
            dataType: 'json',
            type: 'post',
            cache: false,
            data: data2Send,
            success: function (jsonData) {
                const ok = !!(jsonData && jsonData.exito);
                showToast(ok, ok ? 'Saved' : (jsonData && jsonData.msg));
            },
            error: function () {
                showToast(false, 'Error');
            }
        });
    };

})();
