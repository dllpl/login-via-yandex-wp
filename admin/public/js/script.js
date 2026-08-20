const client_id_error = document.getElementById('client_id_error');
const client_secret_error = document.getElementById('client_secret_error');
const ajax_url = (typeof LVYID_Admin !== 'undefined' && LVYID_Admin.ajax_url) ? LVYID_Admin.ajax_url : '/wp-admin/admin-ajax.php';
const ajax_nonce = (typeof LVYID_Admin !== 'undefined' && LVYID_Admin.nonce) ? LVYID_Admin.nonce : '';

// --------------------------------------------------------------------------
// 1. Кастомная система Toast-уведомлений
// --------------------------------------------------------------------------
function showNotify(title, text, status = 'success') {
    let container = document.getElementById('lvyid-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'lvyid-toast-container';
        container.className = 'lvyid-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `lvyid-toast lvyid-toast-${status}`;

    const iconSymbol = status === 'success' ? '✓' : (status === 'error' ? '✕' : 'ℹ');

    toast.innerHTML = `
        <div class="lvyid-toast-icon">${iconSymbol}</div>
        <div class="lvyid-toast-content">
            <h4 class="lvyid-toast-title">${title}</h4>
            <p class="lvyid-toast-text">${text}</p>
        </div>
        <button type="button" class="lvyid-toast-close" title="Закрыть">×</button>
        <div class="lvyid-toast-progress"></div>
    `;

    container.appendChild(toast);

    // Анимация появления
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    const closeToast = () => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 350);
    };

    const closeBtn = toast.querySelector('.lvyid-toast-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeToast);
    }

    // Автоматическое скрытие через 3.5 секунды
    setTimeout(closeToast, 3500);
}

// --------------------------------------------------------------------------
// 2. Интерактивный Конфигуратор Кнопки Яндекс ID (Live Button Constructor)
// --------------------------------------------------------------------------
function getConstructorValues() {
    const viewInput = document.getElementById('button_view') || document.querySelector('input[name="button_view"]:checked');
    const themeInput = document.getElementById('button_theme') || document.querySelector('input[name="button_theme"]:checked');
    const sizeInput = document.getElementById('button_size') || document.querySelector('input[name="button_size"]:checked');
    const radiusInput = document.getElementById('button_border_radius') || document.getElementById('lvyid-radius-range');
    const iconInput = document.getElementById('button_icon') || document.querySelector('input[name="button_icon"]:checked');

    return {
        view: viewInput ? viewInput.value : 'main',         // main, additional, icon
        theme: themeInput ? themeInput.value : 'light',     // light, dark
        size: sizeInput ? sizeInput.value : 'm',            // xs, s, m, l, xl, xxl
        radius: radiusInput ? radiusInput.value : '8',      // 0..14
        icon: iconInput ? iconInput.value : 'ya'            // ya, yaEng
    };
}

function renderButtonPreview() {
    const values = getConstructorValues();

    // 1. Обновляем текстовый индикатор скругления
    const radiusValEl = document.getElementById('lvyid-radius-val');
    if (radiusValEl) {
        radiusValEl.innerText = `${values.radius} px`;
    }

    // 2. Обновляем сгенерированный шорткод
    const shortcodeCodeEl = document.getElementById('lvyid-code-shortcode');
    if (shortcodeCodeEl) {
        let shortcodeStr = '[login_via_yandex';
        if (values.view !== 'main') shortcodeStr += ` view="${values.view}"`;
        if (values.theme !== 'light') shortcodeStr += ` theme="${values.theme}"`;
        if (values.size !== 'm') shortcodeStr += ` size="${values.size}"`;
        if (values.radius !== '8') shortcodeStr += ` radius="${values.radius}"`;
        if (values.icon !== 'ya') shortcodeStr += ` icon="${values.icon}"`;
        shortcodeStr += ']';
        shortcodeCodeEl.innerText = shortcodeStr;
    }

    const previewContainer = document.getElementById('lvyid-constructor-preview');
    if (!previewContainer) return;

    // 3. Отрисовываем визуальный макет кнопки по спецификации Яндекс ID
    previewContainer.innerHTML = '';

    const btn = document.createElement('div');
    btn.className = `lvyid-mockup-btn view-${values.view} theme-${values.theme} size-${values.size}`;
    btn.style.borderRadius = `${values.radius}px`;

    // Размеры иконки и шрифта по размерам (xs..xxl)
    const iconSizes = { xs: 16, s: 20, m: 24, l: 28, xl: 32, xxl: 36 };
    const fontSizes = { xs: '10px', s: '12px', m: '14px', l: '16px', xl: '18px', xxl: '20px' };
    const iconSize = iconSizes[values.size] || 24;
    const fontSize = fontSizes[values.size] || '14px';

    const letter = values.icon === 'yaEng' ? 'Y' : 'Я';
    const textLabel = values.icon === 'yaEng' ? 'Sign in with Yandex ID' : 'Войти с Яндекс ID';

    const iconHtml = `
        <span class="lvyid-mockup-icon-circle" style="width: ${iconSize}px; height: ${iconSize}px; font-size: ${fontSize};">
            ${letter}
        </span>`;

    if (values.view === 'icon') {
        btn.innerHTML = iconHtml;
        btn.title = textLabel;
    } else {
        btn.innerHTML = `${iconHtml}<span class="lvyid-mockup-text">${textLabel}</span>`;
    }

    previewContainer.appendChild(btn);
}

// Слушатели контролов конфигуратора (select, range slider, radio)
const constructorControls = document.querySelectorAll('#lvyid-sec-constructor select, #lvyid-sec-constructor input');
if (constructorControls.length > 0) {
    constructorControls.forEach(control => {
        control.addEventListener('change', renderButtonPreview);
        control.addEventListener('input', renderButtonPreview);
    });
}

// Переключатель фона предпросмотра (Светлый / Тёмный)
const bgButtons = document.querySelectorAll('.lvyid-bg-btn');
const stage = document.getElementById('lvyid-stage');
if (bgButtons.length > 0 && stage) {
    bgButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            bgButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const bgType = btn.getAttribute('data-bg');
            if (bgType === 'dark') {
                stage.className = 'lvyid-preview-stage lvyid-stage-dark';
            } else {
                stage.className = 'lvyid-preview-stage lvyid-stage-light';
            }
        });
    });
}

// Копирование сгенерированного шорткода
const copyShortcodeBtn = document.getElementById('lvyid-copy-const-shortcode');
if (copyShortcodeBtn) {
    copyShortcodeBtn.addEventListener('click', () => {
        const shortcodeText = document.getElementById('lvyid-code-shortcode') ? document.getElementById('lvyid-code-shortcode').innerText : '[login_via_yandex]';
        navigator.clipboard.writeText(shortcodeText);
        copyShortcodeBtn.innerText = 'Скопировано!';
        setTimeout(() => {
            copyShortcodeBtn.innerText = 'Копировать';
        }, 1500);
    });
}

// Первичный рендер предпросмотра кнопки при загрузке страницы
renderButtonPreview();

// --------------------------------------------------------------------------
// 3. Сохранение настроек плагина с AJAX и Loading-статусом
// --------------------------------------------------------------------------
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

        // Параметры конфигуратора кнопки
        const constValues = getConstructorValues();

        if (client_id.length !== 32) {
            if (client_id_error) {
                client_id_error.innerText = 'ClientID должен содержать ровно 32 символа';
                client_id_error.classList.remove('hidden');
            }
            errors = true;
        }

        if (client_secret.length !== 32) {
            if (client_secret_error) {
                client_secret_error.innerText = 'ClientSecret должен содержать ровно 32 символа';
                client_secret_error.classList.remove('hidden');
            }
            errors = true;
        }

        if (!errors) {
            // Включаем состояние загрузки на кнопке
            const originalBtnHtml = saveBtn.innerHTML;
            saveBtn.classList.add('loading');
            saveBtn.innerHTML = '<span>⏳ Сохранение настроек...</span>';

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

            // Передаем параметры конфигуратора кнопки
            formData.append('button_view', constValues.view);
            formData.append('button_theme', constValues.theme);
            formData.append('button_size', constValues.size);
            formData.append('button_border_radius', constValues.radius);
            formData.append('button_icon', constValues.icon);

            fetch(ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    saveBtn.classList.remove('loading');
                    saveBtn.innerHTML = originalBtnHtml;

                    if (!data.success) {
                        showNotify('Ошибка сохранения', data.data || 'Проверьте правильность введённых данных', 'error');
                    } else {
                        showNotify('Успешно сохранено', data.data || 'Настройки плагина обновлены', 'success');

                        // Обновляем статус готовности плагина в сайдбаре
                        const statusBadge = document.querySelector('.lvyid-status-badge');
                        if (statusBadge && client_id.length === 32 && client_secret.length === 32) {
                            statusBadge.className = 'lvyid-status-badge lvyid-status-ready';
                            statusBadge.innerHTML = '<span class="lvyid-pulse"></span><span>Плагин настроен и активен</span>';
                        }
                    }
                })
                .catch(error => {
                    saveBtn.classList.remove('loading');
                    saveBtn.innerHTML = originalBtnHtml;
                    showNotify('Сбой соединения', 'Не удалось сохранить настройки. Попробуйте ещё раз.', 'error');
                });
        } else {
            showNotify('Внимание', 'Пожалуйста, проверьте заполненные поля на ошибки.', 'warning');
        }
    });
}

// --------------------------------------------------------------------------
// 4. Интерактивная навигация по разделам в шапке
// --------------------------------------------------------------------------
const navLinks = document.querySelectorAll('.lvyid-nav-link');
if (navLinks.length > 0) {
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });
    });

    // Отслеживание активного раздела при скролле (Intersection Observer)
    if ('IntersectionObserver' in window) {
        const sections = document.querySelectorAll('.lvyid-card[id]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    navLinks.forEach(link => {
                        if (link.getAttribute('data-target') === id) {
                            link.classList.add('active');
                        } else {
                            link.classList.remove('active');
                        }
                    });
                }
            });
        }, {
            root: null,
            rootMargin: '-20% 0px -60% 0px',
            threshold: 0
        });

        sections.forEach(sec => observer.observe(sec));
    }
}

// --------------------------------------------------------------------------
// 5. Интерактивное переключение подсказки Redirect URI
// --------------------------------------------------------------------------
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

// --------------------------------------------------------------------------
// 6. Полноэкранное модальное окно "Что нового в 2.0.0"
// --------------------------------------------------------------------------
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
