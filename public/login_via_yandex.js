if (!yaWpData.error) {
    document.addEventListener("DOMContentLoaded", () => {

        const oauthQueryParams = {
            client_id: yaWpData.client_id,
            response_type: yaWpData.alternative ? 'code' : 'token',
            redirect_uri: location.origin + "/wp-json/login_via_yandex/webhook"
        }

        const tokenPageOrigin = location.origin
        const authUserUri = "/wp-json/login_via_yandex/authUser"

        function redirect_handler() {
            if (yaWpData.woo_active || location.pathname !== '/wp-login.php') {
                window.location.reload()
            } else {
                window.location.href = location.origin
            }
        }

        if (yaWpData.button_default && document.getElementById('lvyid_auth_default')) {
            YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin,
                {
                    view: "button",
                    parentId: 'lvyid_auth_default',
                    buttonSize: 'xl',
                    buttonView: 'main',
                    buttonTheme: 'light',
                    buttonBorderRadius: "0",
                    buttonIcon: 'ya',
                }
            )
                .then(({handler}) => handler())
                .then(data => {
                    if (!yaWpData.alternative) {
                        fetch(authUserUri, {
                            method: "POST",
                            headers: {"Content-Type": "application/json",},
                            body: JSON.stringify({access_token: data.access_token})
                        }).then(() => redirect_handler())
                    }
                })
                .catch(error => console.log('Обработка ошибки', error))
        }

        if (yaWpData.button) {
            if (yaWpData.container_id) {
                if (document.getElementById(yaWpData.container_id)) {
                    setTimeout(() => {
                        YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin,
                            {
                                view: "button",
                                parentId: yaWpData.container_id,
                                buttonSize: 'xl',
                                buttonView: 'main',
                                buttonTheme: 'light',
                                buttonBorderRadius: "0",
                                buttonIcon: 'ya',
                            }
                        )
                            .then(({handler}) => handler())
                            .then(data => {
                                if (!yaWpData.alternative) {
                                    fetch(authUserUri, {
                                        method: "POST",
                                        headers: {"Content-Type": "application/json",},
                                        body: JSON.stringify({access_token: data.access_token})
                                    }).then(() => redirect_handler())
                                }
                            })
                            .catch(error => console.log('Обработка ошибки', error))
                    }, yaWpData.button_default ? 500 : 0)
                }
            } else {
                console.log('Не указан ID контейнера для кнопки авторизации через Яндекс ID')
            }
        }
        if (yaWpData.widget) {
            setTimeout(() => {
                YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin)
                    .then(({handler}) => handler())
                    .then(data => {
                        if (!yaWpData.alternative) {
                            fetch(authUserUri, {
                                method: "POST",
                                headers: {"Content-Type": "application/json",},
                                body: JSON.stringify({access_token: data.access_token})
                            }).then(() => redirect_handler())
                        }
                    })
                    .catch(error => console.log('Обработка ошибки', error));
            }, yaWpData.button || yaWpData.button_default ? 1500 : 0)
        }
    })
} else {
    console.log(yaWpData.error)
}
