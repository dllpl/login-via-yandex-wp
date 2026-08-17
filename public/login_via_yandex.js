if (typeof yaWpData !== 'undefined' && !yaWpData.error) {
    const oauthQueryParams = {
        client_id: yaWpData.client_id,
        response_type: yaWpData.alternative ? 'code' : 'token',
        redirect_uri: yaWpData.redirect_uri || (location.origin + "/wp-json/login_via_yandex/webhook")
    };

    const tokenPageOrigin = location.origin;

    function redirect_handler() {
        if (yaWpData.woo_active || location.pathname !== '/wp-login.php') {
            window.location.reload();
        } else {
            window.location.href = location.origin;
        }
    }

    /**
     * Авторизация через admin-ajax.php
     */
    function authUser(access_token) {
        const formData = new FormData();
        formData.append('action', 'lvyid_auth_user');
        formData.append('nonce', yaWpData.ajax_nonce);
        formData.append('access_token', access_token);

        return fetch(yaWpData.ajaxurl, {
            method: 'POST',
            body: formData
        }).then(() => redirect_handler())
            .catch(error => console.warn('[LoginViaYandex] Ошибка авторизации:', error));
    }

    async function initButton(container) {
        if (!container || container.getAttribute('data-lvyid-inited') === 'true' || container.getAttribute('data-lvyid-loading') === 'true') {
            return;
        }

        if (!container.id) {
            container.id = 'lvyid_btn_' + Math.random().toString(36).substring(2, 9);
        }

        container.setAttribute('data-lvyid-loading', 'true');

        if (typeof YaAuthSuggest === 'undefined') {
            container.removeAttribute('data-lvyid-loading');
            return;
        }

        const bSize = container.getAttribute('data-size') || yaWpData.button_size || 'm';
        const bView = container.getAttribute('data-view') || yaWpData.button_view || 'main';
        const bTheme = container.getAttribute('data-theme') || yaWpData.button_theme || 'light';
        const bRadius = container.getAttribute('data-radius') || yaWpData.button_border_radius || '8';
        const bIcon = container.getAttribute('data-icon') || yaWpData.button_icon || 'ya';

        try {
            const { handler } = await YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin, {
                view: "button",
                parentId: container.id,
                buttonSize: bSize,
                buttonView: bView,
                buttonTheme: bTheme,
                buttonBorderRadius: bRadius,
                buttonIcon: bIcon,
            });

            // Помечаем контейнер как полностью инициализированный только после настройки стилей
            container.setAttribute('data-lvyid-inited', 'true');
            container.removeAttribute('data-lvyid-loading');

            if (typeof handler === 'function') {
                handler()
                    .then(data => {
                        if (!yaWpData.alternative && data && data.access_token) {
                            authUser(data.access_token);
                        }
                    })
                    .catch(err => {
                        if (err && (err.code === 'in_progress' || err.code === 'cancelled')) {
                            return;
                        }
                        console.warn('[LoginViaYandex] Ошибка авторизации кнопки:', err);
                    });
            }
        } catch (error) {
            container.removeAttribute('data-lvyid-loading');
            if (error && (error.code === 'in_progress' || error.code === 'cancelled')) {
                return;
            }
            console.warn('[LoginViaYandex] Ошибка инициализации кнопки ' + container.id, error);
        }
    }

    let isInitializing = false;

    async function initAllButtons() {
        if (typeof YaAuthSuggest === 'undefined') {
            // Если SDK еще подгружается, ждем его готовности
            let attempts = 0;
            const checkSdk = setInterval(() => {
                attempts++;
                if (typeof YaAuthSuggest !== 'undefined') {
                    clearInterval(checkSdk);
                    initAllButtons();
                } else if (attempts > 30) {
                    clearInterval(checkSdk);
                }
            }, 100);
            return;
        }

        if (isInitializing) {
            return;
        }

        isInitializing = true;

        const containers = [];

        // 1. Поиск всех стандартных кнопок и шорткодов
        document.querySelectorAll('.lvyid_auth_button, .lvyid_auth_default, .lvyid_shortcode_button, [id^="lvyid_auth_default"], [id="lvyid_auth_default"], [data-lvyid-button]').forEach(el => {
            if (!containers.includes(el) && el.getAttribute('data-lvyid-inited') !== 'true') {
                containers.push(el);
            }
        });

        // 2. Поиск пользовательского контейнера
        if (yaWpData.button && yaWpData.container_id) {
            const customContainer = document.getElementById(yaWpData.container_id);
            if (customContainer && !containers.includes(customContainer) && customContainer.getAttribute('data-lvyid-inited') !== 'true') {
                containers.push(customContainer);
            }
        }

        // Последовательная инициализация кнопок
        for (const container of containers) {
            await initButton(container);
        }

        isInitializing = false;
    }

    function initWidget() {
        if (window._yaWpWidgetInited || !yaWpData.widget || typeof YaAuthSuggest === 'undefined') {
            return;
        }

        window._yaWpWidgetInited = true;

        setTimeout(() => {
            YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin)
                .then(({ handler }) => {
                    if (typeof handler === 'function') {
                        return handler();
                    }
                })
                .then(data => {
                    if (!yaWpData.alternative && data && data.access_token) {
                        authUser(data.access_token);
                    }
                })
                .catch(error => {
                    if (error && (error.code === 'in_progress' || error.code === 'cancelled')) {
                        return;
                    }
                    console.warn('[LoginViaYandex] Ошибка инициализации виджета:', error);
                });
        }, 800);
    }

    // Экспортируем глобальные функции для динамических окон / AJAX попапов
    window.initLoginViaYandexButtons = initAllButtons;
    window.initLoginViaYandexWidget = initWidget;

    function startInit() {
        initAllButtons().then(() => {
            initWidget();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", startInit, { once: true });
    } else {
        startInit();
    }

} else if (typeof yaWpData !== 'undefined' && yaWpData.error) {
    console.log(yaWpData.error);
}
