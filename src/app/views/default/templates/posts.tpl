<link href="app/views/default/assets/css/{*page*}.css" rel="stylesheet" />
<div class="row js-form-container form-container">
    <div class="seven columns">
        <div class="js-form-panel">
        </div>
    </div>
    <div class="five columns images">
        <div class="js-preloader preloader-wrap">
            <div class="preloader">
                <img src="app/views/default/assets/img/load.gif" alt="NILA">
            </div>
        </div>
        <ul class="js-images">
        </ul>
        <span class="prev"><i class="fa fa-arrow-alt-circle-left" aria-hidden="true"></i></span>
        <span class="next"><i class="fa fa-arrow-alt-circle-right" aria-hidden="true"></i></span>
    </div>
</div>

<div class="js-sub-panel sub-panel"></div>
{?* post *}
<div class="table-responsive">
    <div id="no-more-tables">
        <table class="table">
            <thead>
            <tr>
                <th scope="col"><i class="fa fa-image" aria-hidden="true"></i></th>
                <th scope="col">Событие</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            {%*post*}
            <tr class="js-post" data-id="{*post:id*}" data-group-id="{*post:id_group*}">
                <td data-title="Превью:" class="post-preview" {?* post:post_image *}style="background-image: url('../../../app/images/{*post:post_image*}')"{?}>
                </td>
                <td data-title="Событие:">
                    <div>
                        <button class="js-btn-set-group-status button alt status icon rejected active" href="#"><i class="fa fa-times-circle" aria-hidden="true"></i></button>
                        <span class="post-group">{*post:group_title*}</span>
                    </div>
                    <span class="post-date">{*post:post_date*}</span><br>
                    {?* post:status_title = "approved" *}{*post:event_discription*}{?!}{*post:post_discription*}{?}<br>
                    <a href="https://vk.com/{*post:group_id*}?w=wall-{*post:post_id*}" target="_blank">подробнее</a>
                </td>
                <td>
                    <button class="js-btn-set-post-status button alt status list pending" data-id="pending" href="#"><i class="fa fa-dot-circle" aria-hidden="true"></i> pend</button>
                </td>
                <td>
                    <button class="js-btn-set-post-status button alt status list rejected" data-id="rejected" href="#"><i class="fa fa-times-circle" aria-hidden="true"></i> reject</button>
                </td>
                <td>
                    <button class="js-btn-set-post-status button alt status list new" data-id="new" href="#"><i class="fa fa-circle" aria-hidden="true"></i> new</button>
                </td>
                <td>
                    <button class="js-btn-set-post-status button alt status list approved" data-id="approved" href="#"><i class="fa fa-check-circle" aria-hidden="true"></i> approve</button>
                </td>
            </tr>
            {%}
            </tbody>
        </table>
    </div>
</div>
{?!}
<p>нет записей</p>
{?}

<script>
    var statuses = JSON.parse('{*statuses*}');
</script>
<script type="html/tpl" id="statusBtn">
    <button class="js-btn-status button alt status default {status_title} {status_active}" data-id="{status_title}" href="#"><i class="fa {status_icon}" aria-hidden="true"></i> {status_title} (<span>{status_count}</span>)</button>
</script>
<script type="html/tpl" id="statusBtns">
    <button class="js-btn-status button alt status default rejected" data-id="rejected" href="#"><i class="fa fa-times-circle" aria-hidden="true"></i> rejected (<span>0</span>)</button>
    <button class="js-btn-status button alt status default new active" data-id="new" href="#"><i class="fa fa-circle" aria-hidden="true"></i> new (<span>0</span>)</button>
    <button class="js-btn-status button alt status default pending" data-id="pending" href="#"><i class="fa fa-dot-circle" aria-hidden="true"></i> pending (<span>0</span>)</button>
    <button class="js-btn-status button alt status default approved" data-id="approved" href="#"><i class="fa fa-check-circle" aria-hidden="true"></i> approved (<span>0</span>)</button>
    <button class="js-btn-status button alt status default published" data-id="published" href="#"><i class="fa fa-arrow-alt-circle-up" aria-hidden="true"></i> published (<span>0</span>)</button>
</script>
<script type="html/tpl" id="formPostEdit">
    <form class="js-form-post-edit form-post-edit animated flash sub-panel-on">
        <textarea class="u-full-width" rows="10" placeholder="Текст события" name="event_discription">{post_discription}</textarea>
        <a class="js-submit button" type="submit"><i class="fa fa-check-square" aria-hidden="true"></i> confirm</a>
        <a class="get-images button" href="https://vk.com/{group_id}?w=wall-{post_id}" target="_blank" >...</a>
        <div class="js-tags tags">
        </div>
    </form>
</script>
<script type="html/tpl" id="imageItem">
    <li class="js-post-preview post-preview" data-image="{src}" style="background-image: url('{src}')"></li>
</script>
<script type="html/tpl" id="tagItem">
    <span>{tag}</span>
</script>
