(() => {
    'use strict';

    const
        btnUpdate    = document.querySelector('.update-sjb-form-options'),
        div_messages = document.querySelector('.ajax-save-result'),
        tabButtons   = document.querySelectorAll('.sjb-tab'),
        panels       = document.querySelectorAll('.sjb-tab-panel');

    // 1 si el switch esta marcado, 0 si no
    const valorSwitch = (id) => {
        const input = document.getElementById(id);
        return input && input.checked ? 1 : 0;
    };

    tabButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.tab;
            tabButtons.forEach((b) => b.classList.toggle('is-active', b === btn));
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

    const toggleSaveMessage = (exito = true) => {
        div_messages.textContent = exito ? 'Saved!!' : 'Error!!';
        div_messages.classList.remove('hidden', 'exito', 'error');
        div_messages.classList.add(exito ? 'exito' : 'error');
        setTimeout(() => div_messages.classList.add('hidden'), 1000);
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
                toggleSaveMessage(jsonData.exito);
            },
            error: function () {
                toggleSaveMessage(false);
            }
        });
    };

})();
