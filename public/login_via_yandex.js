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

    function initButton() {
        if (window._yaWpButtonInited || typeof YaAuthSuggest === 'undefined') {
            return;
        }

        // Поиск первого доступного контейнера кнопки на странице
        let container = document.querySelector('.lvyid_auth_button, .lvyid_auth_default, .lvyid_shortcode_button, [id^="lvyid_auth_default"], [id="lvyid_auth_default"], [data-lvyid-button]');

        if (!container && yaWpData.button && yaWpData.container_id) {
            container = document.getElementById(yaWpData.container_id);
        }

        if (!container) {
            return;
        }

        window._yaWpButtonInited = true;

        if (!container.id) {
            container.id = 'lvyid_btn_' + Math.random().toString(36).substring(2, 9);
        }

        const bSize = container.getAttribute('data-size') || yaWpData.button_size || 'm';
        const bView = container.getAttribute('data-view') || yaWpData.button_view || 'main';
        const bTheme = container.getAttribute('data-theme') || yaWpData.button_theme || 'light';
        const bRadius = container.getAttribute('data-radius') || yaWpData.button_border_radius || '8';
        const bIcon = container.getAttribute('data-icon') || yaWpData.button_icon || 'ya';

        YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin, {
            view: "button",
            parentId: container.id,
            buttonSize: bSize,
            buttonView: bView,
            buttonTheme: bTheme,
            buttonBorderRadius: bRadius,
            buttonIcon: bIcon,
        })
            .then(({ handler }) => {
                container.setAttribute('data-lvyid-inited', 'true');
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
                if (error && (error.code === 'in_progress' || error.code === 'cancelled')) return;
                console.warn('[LoginViaYandex] Ошибка инициализации кнопки:', error);
            });
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
                    if (error && (error.code === 'in_progress' || error.code === 'cancelled')) return;
                    console.warn('[LoginViaYandex] Ошибка инициализации виджета:', error);
                });
        }, 800);
    }

    // Экспортируем функции для глобального доступа
    window.initLoginViaYandexButton = initButton;
    window.initLoginViaYandexWidget = initWidget;

    function startInit() {
        initButton();
        initWidget();
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", startInit, { once: true });
    } else {
        startInit();
    }

} else if (typeof yaWpData !== 'undefined' && yaWpData.error) {
    console.log(yaWpData.error);
}
