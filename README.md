# Wordpress плагин для авторизации через Яндекс  ID

*Как интегрировать на свой сайт WordPress авторизацию через Яндекс. Я разработал и выложил в открытый доступ плагин Login Via Yandex. Вы можете ознакомиться с текстовым руководством по интеграции ниже, либо посмотреть видео-руководство в конце данной статьи.*

## Содержание
1. [Создание приложения в Яндекс ID](#создание-приложения-в-яндекс-id)
2. [Скачать плагин](#скачать-плагин)
3. [Установка и настройка плагина для авторизации через Яндекс ID](#установка-и-настройка-плагина-для-авторизации-через-яндекс-id)
4. [Какие данные получаем от Яндекса и куда записываем](#какие-данные-получаем-от-яндекса-и-куда-записываем)
5. [PS](#ps)

## Создание приложения в Яндекс ID
Для начала, вам понадобится перейти по [ссылке](https://oauth.yandex.ru/client/new/id/) и создать своё приложение в Яндекс, заполнив анкету из трёх шагов. В поле Webhook URI необходимо обязательно указать адрес, заменив «вашсайт.ру» на ваше доменное имя:
```text
https://вашсайт.ру/wp-json/login_via_yandex/webhook
```
(пример для нашего сайта https://webseed.ru/wp-json/login_via_yandex/webhook)

После заполнения, вы получите ClientID и Client secret, эти токены пригодятся нам при настройки плагина на стороне WordPress.

После создания приложения в Яндексе, необходимо скачать, установить и настроить сам плагин.
## Скачать плагин
Скачать плагин для интеграции авторизации с Яндекс ID на WordPress и Woocommerce можно на нашем сайте, а также в официальном репозитории плагинов WordPress.

[Скачать плагин из репозитория Wordpress](https://wordpress.org/plugins/login-via-yandex/)

## Установка и настройка плагина для авторизации через Яндекс ID
После скачивания и установки плагина, переходим в "Меню админки"->"Вход через Яндекс" и заполняем обязательные поля ClientID и Client secret, а также выбираем тип отображения на сайте. 
Настройки плагина
:-------------------------:|
![image](https://github.com/user-attachments/assets/2c7e4113-9a85-4edb-8c3c-7dc6cf500ab3)


Доступны следующие варианты отображения:
1. Виджет — всплывающее окно поверх всех ваших окон. Более конверсионный вариант, симпатично смотрится на десктопе. Но занимает половину экрана на мобильном адаптиве. Зато не промахнешься!
2. Кнопка — должна находится в определенном блоке (скрипт полностью заменяет содержимое блока на кнопку входа через Яндекс). При выборе кнопки — обязательно необходимо указать «ID — контейнера кнопки».
3. Виджет и Кнопка — при выборе такого варианта на сайте будет отображен и виджет и кнопка, но не забудьте про заполнение обязательного поля «ID — контейнера кнопки».
4. Ни виджет ни кнопка — при таком варианте ни виджет ни кнопка отображены не будут (реализовали такой вариант на случай необходимости отключения входа через Яндекс ID без деактивации плагина).


## Какие данные получаем от Яндекса и куда записываем
После авторизации, вы получаете доступ до следующих данных пользователя:
```json
{
   "first_name": "Иван",
   "last_name": "Иванов",
   "display_name": "ivan",
   "emails": [
      "test@yandex.ru",
      "other-test@yandex.ru"
   ],
   "default_email": "test@yandex.ru",
   "default_phone": {
      "id": 12345678,
      "number": "+79037659418"
   },
   "real_name": "Иван Иванов",
   "is_avatar_empty": false,
   "birthday": "1987-03-12",
   "default_avatar_id": "131652443",
   "login": "ivan",
   "old_social_login": "uid-mmzxrnry",
   "sex": "male",
   "id": "1000034426",
   "client_id": "4760187d81bc4b7799476b42b5103713",
   "psuid": "1.AAceCw.tbHgw5DtJ9_zeqPrk-Ba2w.qPWSRC5v2t2IaksPJgnge"
}
```
Этими данными заполняются поля профиля пользователя (те, которые по умолчанию существуют в Wordpress: email, login, first_name, last_name). Остальные данные попадают в мета-значения профиля пользователя с приставкой yandex_* (yandex_phone, yandex_birthday, yandex_gender и т.д.).

Таблица wp_usermeta             | Какие данные сохраняем
:-------------------------:|:-------------------------:
![Screenshot_3](https://github.com/user-attachments/assets/afe6849c-f67a-49dc-8790-d619da2968f3) | ![image](https://github.com/user-attachments/assets/f8fc51c7-1303-437e-92a7-61474dc037b2)




Соотв. вы также можете работать и с ними.

На этом всё. Авторизация через Яндекс ID интегрирована на ваш сайт. Если у вас возникли ошибки создавайте новую issues

## PS
*Ставьте звездочки этому репозиторию и присоединяйтесь к разработке и улучшению данного плагина. Пишите в [мой](https://t.me/dllpl) Telegram если у вас есть вопросы/предложения*

Плагин распространяется бесплатно. Плагин защищен лицензией [GPL-2.0 license](https://github.com/dllpl/login-via-yandex-wp?tab=GPL-2.0-1-ov-file)
