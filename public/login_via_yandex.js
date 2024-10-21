if (!yaWpData.error) {
    document.addEventListener("DOMContentLoaded", () => {

        const oauthQueryParams = {
            client_id: yaWpData.client_id,
            response_type: yaWpData.alternative ? 'code' : 'token',
            redirect_uri: location.origin + "/wp-json/login_via_yandex/webhook"
        }

        const tokenPageOrigin = location.origin

        if (yaWpData.button) {
            if (yaWpData.container_id) {
                if (document.getElementById(yaWpData.container_id)) {
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
                            if(!yaWpData.alternative) {
                                console.log('Сообщение с токеном', data)
                            }
                        })
                        .catch(error => console.log('Обработка ошибки', error))
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
                        if(!yaWpData.alternative) {
                            console.log('Сообщение с токеном', data)
                        }
                    })
                    .catch(error => console.log('Обработка ошибки', error));
            }, yaWpData.button ? 500 : 0)
        }

        const link = document.createElement('a');
        link.href = `https://webseed.ru/?utm_source=${location.hostname}&utm_medium=login_via_yandex&utm_campaign=login_via_yandex`;
        link.target = '_blank';
        link.classList.add('login_via_yandex')
        link.title = 'Разработка сайтов и плагинов на WordPress от Webseed.ru';
        link.text = 'Заказать разработку сайта или плагина на Wordpress'
        document.body.appendChild(link);
    })
} else {
    console.log(yaWpData.error)
}
