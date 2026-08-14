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
     * Авторизация через admin-ajax.php вместо WP REST API.
     * admin-ajax.php практически никогда не блокируется плагинами безопасности.
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
            .catch(error => console.log('Ошибка авторизации', error));
    }

    function initButton(container) {
        if (!container || container.getAttribute('data-lvyid-inited') === 'true') {
            return;
        }

        if (!container.id) {
            container.id = 'lvyid_btn_' + Math.random().toString(36).substring(2, 9);
        }

        container.setAttribute('data-lvyid-inited', 'true');

        if (typeof YaAuthSuggest === 'undefined') {
            return;
        }

        YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin, {
            view: "button",
            parentId: container.id,
            buttonSize: 'xl',
            buttonView: 'main',
            buttonTheme: 'light',
            buttonBorderRadius: "0",
            buttonIcon: 'ya',
        })
            .then(({ handler }) => handler())
            .then(data => {
                if (!yaWpData.alternative && data && data.access_token) {
                    authUser(data.access_token);
                }
            })
            .catch(error => console.log('Ошибка инициализации кнопки ' + container.id, error));
    }

    function initAllButtons() {
        if (typeof YaAuthSuggest === 'undefined') {
            return;
        }

        const containers = [];

        // 1. Поиск всех стандартных кнопок и шорткодов
        document.querySelectorAll('.lvyid_auth_button, .lvyid_auth_default, .lvyid_shortcode_button, [id^="lvyid_auth_default"], [id="lvyid_auth_default"], [data-lvyid-button]').forEach(el => {
            if (!containers.includes(el)) {
                containers.push(el);
            }
        });

        // 2. Поиск пользовательского контейнера
        if (yaWpData.button && yaWpData.container_id) {
            const customContainer = document.getElementById(yaWpData.container_id);
            if (customContainer && !containers.includes(customContainer)) {
                containers.push(customContainer);
            }
        }

        // Инициализируем каждый найденный контейнер
        containers.forEach(container => {
            initButton(container);
        });
    }

    function initWidget() {
        if (window._yaWpWidgetInited || !yaWpData.widget || typeof YaAuthSuggest === 'undefined') {
            return;
        }

        window._yaWpWidgetInited = true;

        setTimeout(() => {
            YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin)
                .then(({ handler }) => handler())
                .then(data => {
                    if (!yaWpData.alternative && data && data.access_token) {
                        authUser(data.access_token);
                    }
                })
                .catch(error => console.log('Ошибка инициализации виджета', error));
        }, 1000);
    }

    // Экспортируем глобальную функцию для динамических окон / AJAX попапов
    window.initLoginViaYandexButtons = initAllButtons;
    window.initLoginViaYandexWidget = initWidget;

    function startInit() {
        initAllButtons();
        initWidget();
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", startInit);
    } else {
        startInit();
    }

    // Дополнительный запуск по полной загрузке страницы (на случай отложенной подгрузки SDK)
    window.addEventListener('load', startInit);

} else if (typeof yaWpData !== 'undefined' && yaWpData.error) {
    console.log(yaWpData.error);
}
