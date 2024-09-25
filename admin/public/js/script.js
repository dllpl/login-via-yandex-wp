const client_id_error = document.getElementById('client_id_error')
const client_secret_error = document.getElementById('client_secret_error')
const container_id_error = document.getElementById('container_id_error')
const url = '/wp-json/login_via_yandex/updateSettings';

function showNotify(title, text, status = 'success') {
    new Notify({
        title, text, status,
        position: 'right top',
        effect: 'slide',
        customClass: 'notify-custom',
    });
}

document.querySelector('.save-btn').addEventListener('click', () => {

    let errors = false

    client_id_error.innerText = ''
    client_secret_error.innerText = ''
    container_id_error.innerText = ''

    const client_id = document.getElementById('client_id').value.trim()
    const client_secret = document.getElementById('client_secret').value.trim()
    const container_id = document.getElementById('container_id').value.trim()

    const widget_checked = document.getElementById('check-widget').checked
    const btn_checked = document.getElementById('check-btn').checked


    if (client_id.length !== 32) {
        client_id_error.innerText = 'ClientID должен содержать 32 символа'
        client_id_error.classList.remove('hidden')
        errors = true
    }

    if (client_secret.length !== 32) {
        client_secret_error.innerText = 'ClientSecret должен содержать 32 символа'
        client_secret_error.classList.remove('hidden')
        errors = true
    }

    if (btn_checked && container_id.length < 3) {
        container_id_error.innerText = 'ID - контейнера должен содержать 3 или более символов'
        container_id_error.classList.remove('hidden')
        errors = true
    }

    if (!errors) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': REST_API_data.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                client_id: client_id,
                client_secret: client_secret,
                widget: widget_checked,
                button: btn_checked,
                ...(btn_checked && {
                    container_id: container_id
                })
            })
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showNotify('Произошла ошибка', 'Напишите в Telegram, разберемся', 'error')
                } else {
                    showNotify('Успешно сохранено', data.data, 'success')
                }
            })
            .catch(error => {
                showNotify('Произошла ошибка', 'Напишите в Telegram, разберемся', 'error')
            })
    } else {
        showNotify('Внимание', 'Проверьте поля на ошибки', 'error')
    }
})

document.getElementById('check-btn').onchange = (event) => {
    if (event.target.checked) {
        document.getElementById('container_id').removeAttribute('disabled')
    } else {
        document.getElementById('container_id').setAttribute("disabled", "disabled");
    }
}
