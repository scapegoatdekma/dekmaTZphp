<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель Task API</title>
    @vite('resources/scss/docs.scss')
</head>
<body>
<div class="container">
    <header class="header">
        <h1>Панель проверки Task API</h1>
        <p>Слева консоль ответов, справа аккордион запросов. У каждого метода свой цвет, как в Postman.</p>
    </header>

    <main class="layout">
        <section class="console">
            <div class="console-head">
                <strong>Консоль ответа</strong>
                <span class="status" id="response-status">Статус: ожидание запроса</span>
            </div>
            <pre id="response-body">Выполните любой запрос из правой панели.</pre>
        </section>

        <section class="accordion-wrap">
            <div class="accordion-head">
                <strong>Запросы</strong>
                <span class="status">/api/tasks</span>
            </div>

            <div class="accordion">
                <details class="request request-post" open>
                    <summary>
                        <span class="method-chip method-post">POST</span>
                        <span class="summary-title">Создать задачу</span>
                    </summary>
                    <div class="content">
                        <label for="create-title">Заголовок</label>
                        <input id="create-title" value="Подготовить демо проекта">

                        <label for="create-description">Описание</label>
                        <textarea id="create-description">Показать работу API со страницы /docs</textarea>

                        <label for="create-status">Статус</label>
                        <select id="create-status">
                            <option value="pending">pending</option>
                            <option value="in_progress">in_progress</option>
                            <option value="done">done</option>
                        </select>

                        <button class="method-post" id="btn-create">Отправить POST</button>
                    </div>
                </details>

                <details class="request request-get">
                    <summary>
                        <span class="method-chip method-get">GET</span>
                        <span class="summary-title">Список задач</span>
                    </summary>
                    <div class="content">
                        <label for="list-status">Статус (необязательно)</label>
                        <select id="list-status">
                            <option value="">любой</option>
                            <option value="pending">pending</option>
                            <option value="in_progress">in_progress</option>
                            <option value="done">done</option>
                        </select>

                        <label for="list-search">Поиск (необязательно)</label>
                        <input id="list-search" placeholder="например: отчет">

                        <label for="list-per-page">На страницу</label>
                        <input id="list-per-page" type="number" min="1" max="100" value="10">

                        <button class="method-get" id="btn-list">Отправить GET</button>
                    </div>
                </details>

                <details class="request request-get">
                    <summary>
                        <span class="method-chip method-get">GET</span>
                        <span class="summary-title">Одна задача (/{id})</span>
                    </summary>
                    <div class="content">
                        <label for="show-id">ID задачи</label>
                        <input id="show-id" type="number" min="1" value="1">

                        <button class="method-get" id="btn-show">Получить по ID</button>
                    </div>
                </details>

                <details class="request request-put">
                    <summary>
                        <span class="method-chip method-put">PUT</span>
                        <span class="summary-title">Обновить задачу</span>
                    </summary>
                    <div class="content">
                        <label for="update-id">ID задачи</label>
                        <input id="update-id" type="number" min="1" value="1">

                        <label for="update-title">Заголовок</label>
                        <input id="update-title" value="Подготовить демо v2">

                        <label for="update-description">Описание</label>
                        <textarea id="update-description">Добавить описание API и примеры запросов</textarea>

                        <label for="update-status">Статус</label>
                        <select id="update-status">
                            <option value="pending">pending</option>
                            <option value="in_progress" selected>in_progress</option>
                            <option value="done">done</option>
                        </select>

                        <button class="method-put" id="btn-update">Отправить PUT</button>
                    </div>
                </details>

                <details class="request request-delete">
                    <summary>
                        <span class="method-chip method-delete">DELETE</span>
                        <span class="summary-title">Удалить задачу (/{id})</span>
                    </summary>
                    <div class="content">
                        <label for="delete-id">ID задачи</label>
                        <input id="delete-id" type="number" min="1" value="1">

                        <button class="method-delete" id="btn-delete">Отправить DELETE</button>
                    </div>
                </details>
            </div>
        </section>
    </main>
</div>

<script>
    const responseBody = document.getElementById('response-body');
    const responseStatus = document.getElementById('response-status');

    function pretty(value) {
        return JSON.stringify(value, null, 2);
    }

    async function callApi(method, url, payload = null) {
        responseStatus.textContent = `Статус: загрузка... ${method} ${url}`;
        responseBody.textContent = 'Выполняется запрос...';

        try {
            const options = {
                method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };

            if (payload !== null) {
                options.body = JSON.stringify(payload);
            }

            const res = await fetch(url, options);
            const text = await res.text();
            let parsed = text;

            try {
                parsed = text ? JSON.parse(text) : null;
            } catch (_) {
            }

            responseStatus.textContent = `Статус: ${res.status} ${res.statusText}`;
            responseBody.textContent = typeof parsed === 'string' ? parsed : pretty(parsed);
        } catch (error) {
            responseStatus.textContent = 'Статус: ошибка запроса';
            responseBody.textContent = String(error);
        }
    }

    document.getElementById('btn-create').addEventListener('click', () => {
        callApi('POST', '/api/tasks', {
            title: document.getElementById('create-title').value,
            description: document.getElementById('create-description').value,
            status: document.getElementById('create-status').value
        });
    });

    document.getElementById('btn-list').addEventListener('click', () => {
        const params = new URLSearchParams();
        const status = document.getElementById('list-status').value;
        const search = document.getElementById('list-search').value;
        const perPage = document.getElementById('list-per-page').value;

        if (status) params.set('status', status);
        if (search) params.set('search', search);
        if (perPage) params.set('per_page', perPage);

        const query = params.toString();
        callApi('GET', `/api/tasks${query ? `?${query}` : ''}`);
    });

    document.getElementById('btn-show').addEventListener('click', () => {
        const id = document.getElementById('show-id').value;
        callApi('GET', `/api/tasks/${id}`);
    });

    document.getElementById('btn-update').addEventListener('click', () => {
        const id = document.getElementById('update-id').value;
        callApi('PUT', `/api/tasks/${id}`, {
            title: document.getElementById('update-title').value,
            description: document.getElementById('update-description').value,
            status: document.getElementById('update-status').value
        });
    });

    document.getElementById('btn-delete').addEventListener('click', () => {
        const id = document.getElementById('delete-id').value;
        callApi('DELETE', `/api/tasks/${id}`);
    });
</script>
</body>
</html>
