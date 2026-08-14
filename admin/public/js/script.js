const client_id_error = document.getElementById('client_id_error');
const client_secret_error = document.getElementById('client_secret_error');
const ajax_url = (typeof LVYID_Admin !== 'undefined' && LVYID_Admin.ajax_url) ? LVYID_Admin.ajax_url : '/wp-admin/admin-ajax.php';
const ajax_nonce = (typeof LVYID_Admin !== 'undefined' && LVYID_Admin.nonce) ? LVYID_Admin.nonce : '';

function showNotify(title, text, status = 'success') {
    alert(text);
}

// 1. Сохранение настроек плагина
const saveBtn = document.querySelector('.save-btn');
if (saveBtn) {
    saveBtn.addEventListener('click', () => {
        let errors = false;

        if (client_id_error) client_id_error.innerText = '';
        if (client_secret_error) client_secret_error.innerText = '';

        const client_id = document.getElementById('client_id') ? document.getElementById('client_id').value.trim() : '';
        const client_secret = document.getElementById('client_secret') ? document.getElementById('client_secret').value.trim() : '';

        const widget_checked = document.getElementById('check-widget') ? document.getElementById('check-widget').checked : false;
        const button_default = document.getElementById('button_default') ? document.getElementById('button_default').checked : false;
        const alternative_checked = document.getElementById('alternative') ? document.getElementById('alternative').checked : false;
        const copyright_checked = document.getElementById('copyright') ? document.getElementById('copyright').checked : true;
        const use_ajax_webhook = document.getElementById('use_ajax_webhook') ? document.getElementById('use_ajax_webhook').checked : false;

        if (client_id.length !== 32) {
            if (client_id_error) {
                client_id_error.innerText = 'ClientID должен содержать 32 символа';
                client_id_error.classList.remove('hidden');
            }
            errors = true;
        }

        if (client_secret.length !== 32) {
            if (client_secret_error) {
                client_secret_error.innerText = 'ClientSecret должен содержать 32 символа';
                client_secret_error.classList.remove('hidden');
            }
            errors = true;
        }

        if (!errors) {
            const formData = new FormData();
            formData.append('action', 'lvyid_update_settings');
            formData.append('nonce', ajax_nonce);
            formData.append('client_id', client_id);
            formData.append('client_secret', client_secret);
            formData.append('widget', widget_checked);
            formData.append('button_default', button_default);
            formData.append('alternative', alternative_checked);
            formData.append('copyright', copyright_checked);
            formData.append('use_ajax_webhook', use_ajax_webhook);

            fetch(ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        showNotify('Произошла ошибка', data.data || 'Проверьте правильность введённых данных', 'error');
                    } else {
                        showNotify('Успешно сохранено', data.data || 'Данные сохранены', 'success');
                    }
                })
                .catch(error => {
                    showNotify('Произошла ошибка', 'Не удалось сохранить настройки. Попробуйте ещё раз.', 'error');
                });
        } else {
            showNotify('Внимание', 'Проверьте поля на ошибки', 'error');
        }
    });
}

// 2. Интерактивное переключение подсказки Redirect URI
const ajaxWebhookToggle = document.getElementById('use_ajax_webhook');
const stepRedirectUri = document.getElementById('step-redirect-uri');
if (ajaxWebhookToggle && stepRedirectUri) {
    ajaxWebhookToggle.addEventListener('change', (e) => {
        if (e.target.checked) {
            stepRedirectUri.innerText = stepRedirectUri.getAttribute('data-ajax-uri');
        } else {
            stepRedirectUri.innerText = stepRedirectUri.getAttribute('data-rest-uri');
        }
    });
}

// 3. Полноэкранное модальное окно "Что нового в 2.0.0"
const welcomeModal = document.getElementById('lvyid-welcome-modal');
const openModalBtn = document.getElementById('lvyid-open-whats-new-btn');
const closeModalBtn = document.getElementById('lvyid-modal-close-btn');
const okModalBtn = document.getElementById('lvyid-modal-ok-btn');

function openWelcomeModal() {
    if (welcomeModal) {
        welcomeModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeWelcomeModal(saveDismiss = true) {
    if (welcomeModal) {
        welcomeModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    if (saveDismiss) {
        try {
            localStorage.setItem('lvyid_v200_modal_seen', '1');
        } catch (e) {}

        const formData = new FormData();
        formData.append('action', 'lvyid_dismiss_welcome');
        formData.append('nonce', ajax_nonce);

        fetch(ajax_url, {
            method: 'POST',
            body: formData
        }).catch(() => {});
    }
}

if (openModalBtn) {
    openModalBtn.addEventListener('click', () => openWelcomeModal());
}

if (closeModalBtn) {
    closeModalBtn.addEventListener('click', () => closeWelcomeModal(true));
}

if (okModalBtn) {
    okModalBtn.addEventListener('click', () => closeWelcomeModal(true));
}

if (welcomeModal) {
    welcomeModal.addEventListener('click', (e) => {
        if (e.target === welcomeModal) {
            closeWelcomeModal(true);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && welcomeModal.style.display === 'flex') {
            closeWelcomeModal(true);
        }
    });
}

// Автоматический показ при первом открытии версии 2.0.0
if (typeof LVYID_Admin !== 'undefined' && LVYID_Admin.show_welcome) {
    const locallySeen = localStorage.getItem('lvyid_v200_modal_seen');
    if (!locallySeen) {
        setTimeout(openWelcomeModal, 200);
    }
}
