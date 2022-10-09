<link href="app/views/default/assets/css/{*page*}.css" rel="stylesheet" /
<div class="table-responsive">
    <div id="no-more-tables">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Дата публикации</th>
                <th scope="col">Группа</th>
                <th scope="col">Пост</th>
            </tr>
            </thead>
            <tbody>
            {%*post*}
            <tr>
                <td data-title="Последнее посещение:" nowrap>{*post:datepublic*}</td>
                <td data-title="Визиты:">{*post:id_group*}</td>
                <td data-title="Визиты:">{*post:content*}</td>
            </tr>
            {%}
            </tbody>
        </table>
    </div>
</div>