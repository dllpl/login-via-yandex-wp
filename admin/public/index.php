<?php
if (!defined('ABSPATH')) exit;

$woo_installed = function_exists('is_plugin_active') ? is_plugin_active('woocommerce/woocommerce.php') : class_exists('WooCommerce');
$is_configured = !empty($options['client_id']) && !empty($options['client_secret']);
?>

<div class="lvyid-app-container">
    <!-- Header Bar -->
    <header class="lvyid-header">
        <div class="lvyid-header-top">
            <div class="lvyid-header-left">
                <div class="lvyid-brand-logo">
                    <svg width="28" height="28" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="#FFCC00"/>
                        <path d="M26.5 13H22.2C17.7 13 14.5 16.2 14.5 20.7C14.5 24.3 16.6 27 20 28.1L14 36H19L24.8 28.3H26.5V36H31V13H26.5ZM26.5 24.2H22.5C20.3 24.2 18.9 22.8 18.9 20.7C18.9 18.5 20.3 17.1 22.5 17.1H26.5V24.2Z" fill="#111111"/>
                    </svg>
                </div>
                <div class="lvyid-brand-info">
                    <div class="lvyid-title-row">
                        <h1 class="lvyid-app-title">Вход через Яндекс ID</h1>
                        <span class="lvyid-version-tag">v2.0.0</span>
                        <?php if ($woo_installed): ?>
                            <span class="lvyid-badge lvyid-badge-woo">WooCommerce Active</span>
                        <?php endif; ?>
                    </div>
                    <p class="lvyid-app-desc">Разработка и поддержка: <a href="https://webseed.ru?utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-login-via-yandex" target="_blank" rel="noopener">Webseed.ru</a> — создание сайтов и плагинов</p>
                </div>
            </div>
            <div class="lvyid-header-right">
                <button type="button" id="lvyid-open-whats-new-btn" class="lvyid-btn-whats-new">
                    <span class="lvyid-btn-icon">✨</span> Что нового в 2.0.0
                </button>
                <a href="https://webseed.ru?utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-login-via-yandex" target="_blank" rel="noopener" class="lvyid-btn-social">
                    🌐 Webseed.ru
                </a>
            </div>
        </div>

        <!-- Section Navigation Bar in Header -->
        <nav class="lvyid-nav-bar" id="lvyid-nav-bar">
            <a href="#lvyid-sec-keys" class="lvyid-nav-link active" data-target="lvyid-sec-keys">
                <span class="lvyid-nav-icon">🔑</span> Ключи API
            </a>
            <a href="#lvyid-sec-constructor" class="lvyid-nav-link" data-target="lvyid-sec-constructor">
                <span class="lvyid-nav-icon">🎨</span> Конструктор
            </a>
            <a href="#lvyid-sec-options" class="lvyid-nav-link" data-target="lvyid-sec-options">
                <span class="lvyid-nav-icon">⚙️</span> Режимы работы
            </a>
            <a href="#lvyid-sec-shortcode" class="lvyid-nav-link" data-target="lvyid-sec-shortcode">
                <span class="lvyid-nav-icon">📋</span> Шорткод
            </a>
            <a href="#lvyid-sec-guide" class="lvyid-nav-link" data-target="lvyid-sec-guide">
                <span class="lvyid-nav-icon">📖</span> Инструкция
            </a>
            <a href="#lvyid-sec-faq" class="lvyid-nav-link" data-target="lvyid-sec-faq">
                <span class="lvyid-nav-icon">❓</span> FAQ
            </a>
        </nav>
    </header>

    <!-- Main 2-Column Layout -->
    <div class="lvyid-layout">
        
        <!-- Left Column: Settings & Documentation -->
        <main class="lvyid-main-col">
            
            <!-- Row: API Keys (Left) & Button Configurator (Right) in 2 Columns -->
            <div class="lvyid-cards-row">
                
                <!-- Card 1: API Keys -->
                <section class="lvyid-card" id="lvyid-sec-keys">
                    <div class="lvyid-card-header">
                        <div class="lvyid-card-icon" style="background: #fff8e1; color: #f57f17;">🔑</div>
                        <div>
                            <h2 class="lvyid-card-title">Данные приложения Яндекс ID</h2>
                            <p class="lvyid-card-subtitle">Введите ключи доступа из кабинета <a href="https://oauth.yandex.ru/client/new/id/" target="_blank" rel="noopener">oauth.yandex.ru</a></p>
                        </div>
                    </div>
                    <div class="lvyid-card-body">
                        <div class="lvyid-form-grid">
                            <div class="lvyid-input-group">
                                <label class="lvyid-label" for="client_id">
                                    ClientID <span class="lvyid-required">*</span>
                                </label>
                                <div class="lvyid-input-wrapper">
                                    <input class="lvyid-input" type="text" id="client_id" name="client_id"
                                           value="<?php echo sprintf('%s', esc_attr($options['client_id'] ?? '')) ?>"
                                           placeholder="32-значный идентификатор приложения" required maxlength="32" minlength="32">
                                    <button type="button" class="lvyid-input-action" onclick="navigator.clipboard.writeText(document.getElementById('client_id').value); this.innerText='✓'; setTimeout(()=>this.innerText='📋', 1500);" title="Копировать">📋</button>
                                </div>
                                <div class="lvyid-form-error hidden" id="client_id_error"></div>
                            </div>

                            <div class="lvyid-input-group">
                                <label class="lvyid-label" for="client_secret">
                                    Client Secret <span class="lvyid-required">*</span>
                                </label>
                                <div class="lvyid-input-wrapper">
                                    <input class="lvyid-input" type="password" id="client_secret" name="client_secret"
                                           value="<?php echo sprintf('%s', esc_attr($options['client_secret'] ?? '')) ?>"
                                           placeholder="32-значный пароль приложения" required maxlength="32" minlength="32">
                                    <button type="button" class="lvyid-input-action" id="lvyid-toggle-secret" onclick="const input = document.getElementById('client_secret'); if(input.type === 'password'){ input.type = 'text'; this.innerText = '🙈'; } else { input.type = 'password'; this.innerText = '👁️'; }" title="Показать/скрыть">👁️</button>
                                </div>
                                <div class="lvyid-form-error hidden" id="client_secret_error"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Card 2: Button Constructor / Configurator -->
                <section class="lvyid-card" id="lvyid-sec-constructor">
                    <div class="lvyid-card-header">
                        <div class="lvyid-card-icon" style="background: #fdf4ff; color: #c026d3;">🎨</div>
                        <div>
                            <h2 class="lvyid-card-title">Конфигуратор кнопки Яндекс ID</h2>
                            <p class="lvyid-card-subtitle">Настройка внешнего вида по параметрам <a href="https://yandex.ru/dev/id/doc/ru/suggest/but-const" target="_blank" rel="noopener">документации Яндекс ID</a></p>
                        </div>
                    </div>
                    <div class="lvyid-card-body">
                        <div class="lvyid-form-grid">
                            
                            <!-- 1. buttonView: main, additional, icon -->
                            <div class="lvyid-input-group">
                                <label class="lvyid-label" for="button_view">Вид кнопки (buttonView)</label>
                                <select class="lvyid-input" id="button_view" name="button_view">
                                    <option value="main" <?php selected($options['button_view'] ?? 'main', 'main'); ?>>main — Основная кнопка (с текстом)</option>
                                    <option value="additional" <?php selected($options['button_view'] ?? 'main', 'additional'); ?>>additional — Дополнительная кнопка</option>
                                    <option value="icon" <?php selected($options['button_view'] ?? 'main', 'icon'); ?>>icon — Только иконка</option>
                                </select>
                            </div>

                            <!-- 2. buttonTheme: light, dark -->
                            <div class="lvyid-input-group">
                                <label class="lvyid-label" for="button_theme">Тема кнопки (buttonTheme)</label>
                                <select class="lvyid-input" id="button_theme" name="button_theme">
                                    <option value="light" <?php selected($options['button_theme'] ?? 'light', 'light'); ?>>light — Светлая кнопка</option>
                                    <option value="dark" <?php selected($options['button_theme'] ?? 'light', 'dark'); ?>>dark — Тёмная кнопка</option>
                                </select>
                            </div>

                            <!-- 3. buttonSize: xs, s, m, l, xl, xxl -->
                            <div class="lvyid-input-group">
                                <label class="lvyid-label" for="button_size">Размер кнопки (buttonSize)</label>
                                <select class="lvyid-input" id="button_size" name="button_size">
                                    <option value="xs" <?php selected($options['button_size'] ?? 'm', 'xs'); ?>>xs (28px)</option>
                                    <option value="s" <?php selected($options['button_size'] ?? 'm', 's'); ?>>s (36px)</option>
                                    <option value="m" <?php selected($options['button_size'] ?? 'm', 'm'); ?>>m (44px, стандарт)</option>
                                    <option value="l" <?php selected($options['button_size'] ?? 'm', 'l'); ?>>l (52px)</option>
                                    <option value="xl" <?php selected($options['button_size'] ?? 'm', 'xl'); ?>>xl (60px)</option>
                                    <option value="xxl" <?php selected($options['button_size'] ?? 'm', 'xxl'); ?>>xxl (68px)</option>
                                </select>
                            </div>

                            <!-- 4. buttonIcon: ya, yaEng -->
                            <div class="lvyid-input-group">
                                <label class="lvyid-label" for="button_icon">Тип иконки (buttonIcon)</label>
                                <select class="lvyid-input" id="button_icon" name="button_icon">
                                    <option value="ya" <?php selected($options['button_icon'] ?? 'ya', 'ya'); ?>>ya — Русская буква «Я»</option>
                                    <option value="yaEng" <?php selected($options['button_icon'] ?? 'ya', 'yaEng'); ?>>yaEng — Латинская буква «Y»</option>
                                </select>
                            </div>

                            <!-- 5. buttonBorderRadius: Range Slider 0 to 14 -->
                            <div class="lvyid-input-group">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <label class="lvyid-label" for="button_border_radius" style="margin-bottom: 0;">Скругление углов (buttonBorderRadius)</label>
                                    <span class="lvyid-radius-badge" id="lvyid-radius-val"><?php echo esc_html($options['button_border_radius'] ?? '8'); ?> px</span>
                                </div>
                                <div class="lvyid-slider-box">
                                    <div class="lvyid-slider-wrap">
                                        <input type="range" id="button_border_radius" name="button_border_radius" min="0" max="14" step="1"
                                               value="<?php echo esc_attr($options['button_border_radius'] ?? '8'); ?>" class="lvyid-range-slider">
                                    </div>
                                    <div class="lvyid-slider-ticks">
                                        <span>0 (квадрат)</span>
                                        <span>7</span>
                                        <span>14 (капсула)</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>

            </div>

            <!-- Card 2: Options & Switches -->
            <section class="lvyid-card" id="lvyid-sec-options">
                <div class="lvyid-card-header">
                    <div class="lvyid-card-icon" style="background: #e8f5e9; color: #2e7d32;">⚙️</div>
                    <div>
                        <h2 class="lvyid-card-title">Режимы работы и отображение</h2>
                        <p class="lvyid-card-subtitle">Настройте способы вывода кнопок и механизмы авторизации</p>
                    </div>
                </div>
                <div class="lvyid-card-body">
                    <div class="lvyid-switches-list">

                        <!-- Toggle: Button Default Placement -->
                        <label class="lvyid-switch-row" for="button_default">
                            <div class="lvyid-switch-info">
                                <div class="lvyid-switch-title">
                                    <span>⚡ Автоматическое размещение кнопок</span>
                                    <span class="lvyid-pill-recommended">Рекомендуется</span>
                                </div>
                                <div class="lvyid-switch-desc">Автоматически добавляет кнопку «Войти с Яндекс ID» под стандартными формами входа и регистрации в WordPress и WooCommerce.</div>
                            </div>
                            <div class="lvyid-toggle-wrapper">
                                <input type="checkbox" id="button_default" <?php if (isset($options['button_default']) && $options['button_default']) echo 'checked' ?> />
                                <span class="lvyid-toggle-slider"></span>
                            </div>
                        </label>

                        <!-- Toggle: Widget -->
                        <label class="lvyid-switch-row" for="check-widget">
                            <div class="lvyid-switch-info">
                                <div class="lvyid-switch-title">
                                    <span>💬 Виджет «Мгновенного входа»</span>
                                </div>
                                <div class="lvyid-switch-desc">Всплывающий бейдж Яндекса в правом нижнем углу сайта для быстрого входа в один клик.</div>
                            </div>
                            <div class="lvyid-toggle-wrapper">
                                <input type="checkbox" id="check-widget" <?php if (isset($options['widget']) && $options['widget']) echo 'checked' ?> />
                                <span class="lvyid-toggle-slider"></span>
                            </div>
                        </label>

                        <!-- Toggle: Alternative Auth -->
                        <label class="lvyid-switch-row" for="alternative">
                            <div class="lvyid-switch-info">
                                <div class="lvyid-switch-title">
                                    <span>🔀 Альтернативная авторизация (Code Flow)</span>
                                </div>
                                <div class="lvyid-switch-desc">Обмен токена через серверный бэкенд. Включите, если у пользователей на вашем сервере не срабатывает всплывающее окно входа.</div>
                            </div>
                            <div class="lvyid-toggle-wrapper">
                                <input type="checkbox" id="alternative" <?php if (isset($options['alternative']) && $options['alternative']) echo 'checked' ?> />
                                <span class="lvyid-toggle-slider"></span>
                            </div>
                        </label>

                        <!-- Toggle: AJAX Webhook -->
                        <label class="lvyid-switch-row" for="use_ajax_webhook">
                            <div class="lvyid-switch-info">
                                <div class="lvyid-switch-title">
                                    <span>🛡️ Использовать AJAX Redirect URI (admin-ajax.php)</span>
                                </div>
                                <div class="lvyid-switch-desc">Переключает обработчик с <code>/wp-json/</code> на <code>admin-ajax.php</code>. Рекомендуется, если на хостинге или плагинами безопасности отключен WP REST API.</div>
                            </div>
                            <div class="lvyid-toggle-wrapper">
                                <input type="checkbox" id="use_ajax_webhook" <?php if (!empty($options['use_ajax_webhook'])) echo 'checked' ?> />
                                <span class="lvyid-toggle-slider"></span>
                            </div>
                        </label>

                        <!-- Toggle: Copyright -->
                        <label class="lvyid-switch-row" for="copyright">
                            <div class="lvyid-switch-info">
                                <div class="lvyid-switch-title">
                                    <span>❤️ Поддержать разработчиков ссылкой в подвале</span>
                                </div>
                                <div class="lvyid-switch-desc">Выводит аккуратную строчку в подвале сайта: <i>«Вход через Яндекс ID — webseed.ru»</i>. Спасибо за вашу поддержку!</div>
                            </div>
                            <div class="lvyid-toggle-wrapper">
                                <input type="checkbox" id="copyright" <?php if (!isset($options['copyright']) || $options['copyright']) echo 'checked' ?> />
                                <span class="lvyid-toggle-slider"></span>
                            </div>
                        </label>

                    </div>
                </div>
            </section>

            <!-- Card 3: Shortcode Placement -->
            <section class="lvyid-card" id="lvyid-sec-shortcode">
                <div class="lvyid-card-header">
                    <div class="lvyid-card-icon" style="background: #ede7f6; color: #512da8;">📋</div>
                    <div>
                        <h2 class="lvyid-card-title">Размещение через Шорткод</h2>
                        <p class="lvyid-card-subtitle">Выводите кнопку Яндекс ID в произвольном месте вашего сайта</p>
                    </div>
                </div>
                <div class="lvyid-card-body">
                    <div class="lvyid-shortcode-boxes">
                        <div class="lvyid-shortcode-item">
                            <div class="lvyid-shortcode-label">Шорткод для Elementor, Gutenberg и записей:</div>
                            <div class="lvyid-copy-box">
                                <code>[login_via_yandex]</code>
                                <button type="button" class="lvyid-copy-btn" onclick="navigator.clipboard.writeText('[login_via_yandex]'); this.innerText='Скопировано!'; setTimeout(()=>this.innerText='Копировать', 1500);">Копировать</button>
                            </div>
                        </div>
                        <div class="lvyid-shortcode-item">
                            <div class="lvyid-shortcode-label">PHP-код для вставки в файлы темы (header.php, попапы):</div>
                            <div class="lvyid-copy-box">
                                <code>&lt;?php echo do_shortcode('[login_via_yandex]'); ?&gt;</code>
                                <button type="button" class="lvyid-copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js("<?php echo do_shortcode('[login_via_yandex]'); ?>"); ?>'); this.innerText='Скопировано!'; setTimeout(()=>this.innerText='Копировать', 1500);">Копировать</button>
                            </div>
                        </div>
                    </div>
                    <div class="lvyid-tip-note">
                        💡 <b>Подсказка:</b> Кнопка через шорткод автоматически скрывается, если пользователь уже авторизован на сайте.
                    </div>
                </div>
            </section>

            <!-- Card 4: Step-by-Step Guide -->
            <section class="lvyid-card" id="lvyid-sec-guide">
                <div class="lvyid-card-header">
                    <div class="lvyid-card-icon" style="background: #e1f5fe; color: #0288d1;">📖</div>
                    <div>
                        <h2 class="lvyid-card-title">Пошаговая инструкция по настройке</h2>
                        <p class="lvyid-card-subtitle">Создайте приложение в Яндексе за 4 простых шага</p>
                    </div>
                </div>
                <div class="lvyid-card-body">
                    <div class="lvyid-steps">
                        
                        <div class="lvyid-step">
                            <div class="lvyid-step-number">1</div>
                            <div class="lvyid-step-content">
                                <h3>Создайте приложение в кабинете Яндекса</h3>
                                <p>Перейдите на <a href="https://oauth.yandex.ru/client/new/id/" target="_blank" rel="noopener" class="lvyid-link">oauth.yandex.ru/client/new/id</a>, войдите под своим Яндекс-аккаунтом, укажите название (например, <i>«Вход на сайте»</i>) и выберите платформу <b>«Веб-сервисы»</b>.</p>
                            </div>
                        </div>

                        <div class="lvyid-step">
                            <div class="lvyid-step-number">2</div>
                            <div class="lvyid-step-content">
                                <h3>Укажите Redirect URI и Разрешенный домен</h3>
                                <div class="lvyid-uri-block">
                                    <div class="lvyid-uri-item">
                                        <div class="lvyid-uri-label">Redirect URI:</div>
                                        <div class="lvyid-copy-box">
                                            <code id="step-redirect-uri" data-rest-uri="<?php echo esc_attr(home_url('/wp-json/login_via_yandex/webhook')); ?>" data-ajax-uri="<?php echo esc_attr(admin_url('admin-ajax.php') . '?action=lvyid_webhook'); ?>"><?php echo esc_html(!empty($options['use_ajax_webhook']) ? (admin_url('admin-ajax.php') . '?action=lvyid_webhook') : home_url('/wp-json/login_via_yandex/webhook')); ?></code>
                                            <button type="button" class="lvyid-copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('step-redirect-uri').innerText); this.innerText='Скопировано!'; setTimeout(()=>this.innerText='Копировать', 1500);">Копировать</button>
                                        </div>
                                    </div>
                                    <div class="lvyid-uri-item">
                                        <div class="lvyid-uri-label">Разрешенные источники (Web origin):</div>
                                        <div class="lvyid-copy-box">
                                            <code><?php echo esc_html(home_url()); ?></code>
                                            <button type="button" class="lvyid-copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js(home_url()); ?>'); this.innerText='Скопировано!'; setTimeout(()=>this.innerText='Копировать', 1500);">Копировать</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lvyid-step">
                            <div class="lvyid-step-number">3</div>
                            <div class="lvyid-step-content">
                                <h3>Отметьте права доступа (Скоупы)</h3>
                                <p>В блоке <b>«Яндекс ID (Паспорт)»</b> отметьте следующие галочки:</p>
                                <div class="lvyid-scopes-list">
                                    <span class="lvyid-scope-badge">📧 Доступ к адресу электронной почты (<code>login:email</code>)</span>
                                    <span class="lvyid-scope-badge">👤 Доступ к имени, фамилии и полу (<code>login:info</code>)</span>
                                    <span class="lvyid-scope-badge">📱 Доступ к номеру телефона (<code>login:phone</code>) <i>(для WooCommerce)</i></span>
                                </div>
                            </div>
                        </div>

                        <div class="lvyid-step">
                            <div class="lvyid-step-number">4</div>
                            <div class="lvyid-step-content">
                                <h3>Скопируйте ключи и сохраните изменения</h3>
                                <p>Скопируйте полученные <b>ClientID</b> и <b>ClientSecret</b> в поля формы выше и нажмите <b>«Сохранить изменения»</b>.</p>
                            </div>
                        </div>

                    </div>

                    <!-- Turnkey Service Callout Banner -->
                    <div class="lvyid-service-banner">
                        <div class="lvyid-service-banner-icon">🛠️</div>
                        <div class="lvyid-service-banner-content">
                            <h4>Нужна помощь в установке и доработке?</h4>
                            <p>Команда <b>Webseed.ru</b> зарегистрирует приложение в Яндексе, настроит плагин и аккуратно встроит кнопку под цветовую гамму вашей темы или попапа.</p>
                        </div>
                        <a href="https://webseed.ru/?utm_source=wp-admin&utm_medium=plugin_admin&utm_campaign=turnkey_service" target="_blank" rel="noopener" class="lvyid-service-banner-btn">
                            Заказать настройку под ключ →
                        </a>
                    </div>

                </div>
            </section>

            <!-- Card 5: FAQ -->
            <section class="lvyid-card" id="lvyid-sec-faq">
                <div class="lvyid-card-header">
                    <div class="lvyid-card-icon" style="background: #fce4ec; color: #c2185b;">❓</div>
                    <div>
                        <h2 class="lvyid-card-title">Часто задаваемые вопросы (FAQ)</h2>
                        <p class="lvyid-card-subtitle">Решения частых вопросов и тонкостей настройки</p>
                    </div>
                </div>
                <div class="lvyid-card-body">
                    <div class="lvyid-faq-list">
                        
                        <details class="lvyid-faq-item">
                            <summary class="lvyid-faq-question">Что делает переключатель «Автоматическое размещение кнопок»?</summary>
                            <div class="lvyid-faq-answer">
                                Кнопки авторизации автоматически встраиваются под формами входа и регистрации WordPress (<code>/wp-login.php</code>), а также на странице «Мой аккаунт» и при оформлении заказа в WooCommerce.
                            </div>
                        </details>

                        <details class="lvyid-faq-item">
                            <summary class="lvyid-faq-question">Как работает автозаполнение данных в WooCommerce?</summary>
                            <div class="lvyid-faq-answer">
                                При авторизации через Яндекс ID плагин автоматически подставляет в поля оформления заказа имя, фамилию, email и номер телефона покупателя. Это существенно ускоряет процесс покупки и снижает процент брошенных корзин.
                            </div>
                        </details>

                        <details class="lvyid-faq-item">
                            <summary class="lvyid-faq-question">Зачем нужен переключатель «AJAX Redirect URI»?</summary>
                            <div class="lvyid-faq-answer">
                                Если на вашем сервере отключен или блокируется WP REST API (<code>/wp-json/</code>), включите эту опцию и укажите в Яндекс OAuth адрес <code>admin-ajax.php?action=lvyid_webhook</code>.
                            </div>
                        </details>

                        <details class="lvyid-faq-item">
                            <summary class="lvyid-faq-question">После авторизации окно закрывается, но вход не выполняется?</summary>
                            <div class="lvyid-faq-answer">
                                Попробуйте включить переключатель <b>«Альтернативная авторизация»</b> выше в настройках, после чего сохраните изменения.
                            </div>
                        </details>

                    </div>
                </div>
            </section>

        </main>

        <!-- Right Column: Sidebar Actions & Monetization Services -->
        <aside class="lvyid-sidebar-col">
            
            <!-- Sticky Save Card -->
            <div class="lvyid-sticky-card">
                <div class="lvyid-card lvyid-card-action">
                    <div class="lvyid-status-indicator">
                        <?php if ($is_configured): ?>
                            <div class="lvyid-status-badge lvyid-status-ready">
                                <span class="lvyid-pulse"></span>
                                <span>Плагин настроен и активен</span>
                            </div>
                        <?php else: ?>
                            <div class="lvyid-status-badge lvyid-status-warning">
                                <span class="lvyid-pulse lvyid-pulse-yellow"></span>
                                <span>Требуется ввод ключей</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="lvyid-save-btn save-btn">
                        <span>💾 Сохранить изменения</span>
                    </button>
                </div>
            </div>

            <!-- Turnkey Setup & Customization Card (Lead Generation) -->
            <div class="lvyid-card lvyid-card-service">
                <div class="lvyid-service-header">
                    <div class="lvyid-service-badge">Услуга под ключ</div>
                    <h3 class="lvyid-service-title">Индивидуальная настройка и доработка</h3>
                    <div class="lvyid-service-price">от 1 990 ₽</div>
                </div>
                <div class="lvyid-card-body" style="padding-top: 14px;">
                    <ul class="lvyid-service-list">
                        <li><span class="lvyid-check-icon">✓</span> Регистрация и настройка приложения в Яндекс OAuth</li>
                        <li><span class="lvyid-check-icon">✓</span> Адаптация стиля кнопки под цвета и дизайн сайта</li>
                        <li><span class="lvyid-check-icon">✓</span> Внедрение в попапы, шапку и чекаут WooCommerce</li>
                        <li><span class="lvyid-check-icon">✓</span> Проверка и гарантия стабильной работы</li>
                    </ul>
                    <a href="https://webseed.ru/?utm_source=wp-admin&utm_medium=plugin_admin&utm_campaign=sidebar_setup_order" target="_blank" rel="noopener" class="lvyid-service-order-btn">
                        🚀 Заказать настройку под ключ
                    </a>
                </div>
            </div>

            <!-- Webseed Services Card -->
            <div class="lvyid-card">
                <div class="lvyid-card-header" style="border-bottom: none; padding-bottom: 0;">
                    <div class="lvyid-author-box">
                        <div class="lvyid-card-icon" style="background: #0f172a; color: #ffcc00; width: 44px; height: 44px; border-radius: 14px; font-weight: 800; font-size: 15px; display: flex; align-items: center; justify-content: center;">WS</div>
                        <div>
                            <h3 class="lvyid-author-name">Webseed.ru</h3>
                            <p class="lvyid-author-role">Разработка и продвижение</p>
                        </div>
                    </div>
                </div>
                <div class="lvyid-card-body">
                    <p class="lvyid-author-text">
                        Создание сайтов под ключ, разработка плагинов, API-интеграции и техническая оптимизация WordPress. Разработка AI агентов, продвижение в AI-поиске.
                    </p>
                    <div class="lvyid-agency-services">
                        <div class="lvyid-agency-item">🛍️ Интернет-магазины на WooCommerce</div>
                        <div class="lvyid-agency-item">🤖 Разработка AI-агентов и LLM-ботов</div>
                        <div class="lvyid-agency-item">🔌 API интеграции (1С, МойСклад, CRM)</div>
                        <div class="lvyid-agency-item">⚡ Ускорение сайтов (PageSpeed 95+)</div>
                    </div>
                    <div class="lvyid-author-links" style="margin-top: 16px;">
                        <a href="https://webseed.ru?utm_source=wp-admin&utm_medium=plugin&utm_campaign=wp-login-via-yandex" target="_blank" rel="noopener" class="lvyid-author-btn lvyid-btn-site">
                            🌐 Заказать проект на Webseed.ru
                        </a>
                        <a href="https://boosty.to/webseed/donate" target="_blank" rel="noopener" class="lvyid-author-btn lvyid-btn-boosty">
                            ☕ Поддержать проект (Boosty)
                        </a>
                    </div>
                </div>
            </div>

            <!-- Review Card -->
            <div class="lvyid-card lvyid-card-review">
                <div class="lvyid-card-body" style="text-align: center;">
                    <div class="lvyid-stars-row">⭐⭐⭐⭐⭐</div>
                    <h3 style="margin: 8px 0 6px 0; font-size: 16px; font-weight: 700;">Понравился плагин?</h3>
                    <p style="font-size: 13px; color: #64748b; margin: 0 0 14px 0; line-height: 1.45;">Поставьте 5 звёзд на WordPress.org — ваша поддержка помогает нам развивать проект!</p>
                    <a href="https://wordpress.org/support/plugin/login-via-yandex/reviews/#new-post" target="_blank" rel="noopener" class="lvyid-review-btn">
                        ⭐ Оставить отзыв на WordPress.org
                    </a>
                </div>
            </div>

        </aside>

    </div>
</div>

<!-- Full-screen What's New Modal -->
<div id="lvyid-welcome-modal" class="lvyid-modal-overlay" style="display: none;">
    <div class="lvyid-modal-card">
        <button type="button" class="lvyid-modal-close" id="lvyid-modal-close-btn" title="Закрыть">×</button>
        
        <div class="lvyid-modal-header">
            <div class="lvyid-modal-badge">Глобальный релиз v2.0.0</div>
            <h2 class="lvyid-modal-title">🚀 Встречайте Login via Yandex 2.0!</h2>
            <p class="lvyid-modal-subtitle">Масштабное обновление: современный интерфейс настроек, шорткоды без привязки к ID, автозаполнение WooCommerce, переход на AJAX и 100% стабильность.</p>
        </div>

        <div class="lvyid-modal-body">
            <div class="lvyid-features-grid">
                
                <div class="lvyid-feature-card">
                    <div class="lvyid-feature-icon" style="background: #fff8e1; color: #f57f17;">⚡</div>
                    <div class="lvyid-feature-content">
                        <h3>Шорткод [login_via_yandex]</h3>
                        <p>Больше никаких ID блоков и сложных настроек! Размещайте кнопки в Elementor, Gutenberg, сайдбарах, попапах и коде темы.</p>
                    </div>
                </div>

                <div class="lvyid-feature-card">
                    <div class="lvyid-feature-icon" style="background: #e3f2fd; color: #1976d2;">🛒</div>
                    <div class="lvyid-feature-content">
                        <h3>Автозаполнение в WooCommerce</h3>
                        <p>При авторизации на этапе оформления заказа поля покупателя (Имя, Телефон, Email) заполняются мгновенно из профиля Яндекс ID.</p>
                    </div>
                </div>

                <div class="lvyid-feature-card">
                    <div class="lvyid-feature-icon" style="background: #e8f5e9; color: #388e3c;">🛡️</div>
                    <div class="lvyid-feature-content">
                        <h3>Переход на WordPress AJAX</h3>
                        <p>Авторизация и сохранение переведены на <code>admin-ajax.php</code> — плагины безопасности (Wordfence, Clearfy) больше не блокируют работу.</p>
                    </div>
                </div>

                <div class="lvyid-feature-card">
                    <div class="lvyid-feature-icon" style="background: #f3e5f5; color: #7b1fa2;">✨</div>
                    <div class="lvyid-feature-content">
                        <h3>Множество кнопок на странице</h3>
                        <p>Инициализация любого количества кнопок одновременно (в шапке, меню и формах) без конфликтов скриптов и дублирования.</p>
                    </div>
                </div>

                <div class="lvyid-feature-card">
                    <div class="lvyid-feature-icon" style="background: #e0f7fa; color: #00838f;">🎨</div>
                    <div class="lvyid-feature-content">
                        <h3>Новый интерфейс управления</h3>
                        <p>Премиальная двухколоночная панель настроек, мгновенное копирование ключей в 1 клик, скрытие паролей и интерактивная инструкция.</p>
                    </div>
                </div>

                <div class="lvyid-feature-card">
                    <div class="lvyid-feature-icon" style="background: #fbe9e7; color: #d84315;">🔄</div>
                    <div class="lvyid-feature-content">
                        <h3>100% обратная совместимость</h3>
                        <p>Автоматическая плавная миграция базы данных и полная поддержка старых вебхуков без нарушения работы существующих сайтов.</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="lvyid-modal-footer">
            <button type="button" class="lvyid-modal-primary-btn" id="lvyid-modal-ok-btn">Отлично, перейти к настройкам →</button>
        </div>
    </div>
</div>

<!-- Custom Toast Notification Container -->
<div id="lvyid-toast-container" class="lvyid-toast-container"></div>

