var selectors = {
    post                 : '.js-post',
    sub_panel            : '.js-sub-panel',
    btn_status           : '.js-btn-status',
    btn_set_post_status  : '.js-btn-set-post-status',
    btn_set_group_status : '.js-btn-set-group-status',
    form_post_edit       : '.js-form-post-edit',
    form_panel           : '.js-form-panel',
    get_images           : '.js-get-images',
    images               : '.js-images',
    form_container       : '.js-form-container',
    submit               : '.js-submit',
    tags                 : '.js-tags',
    body                 : 'html, body',
    preloader            : '.js-preloader',
    post_preview         : '.js-post-preview',
    next                 : '.next',
    prev                 : '.prev'
};

var statusId = getUrlParameter('status') ? getUrlParameter('status') : 'new';

var statusIcons = {
    'rejected'  : 'fa-times-circle',
    'new'       : 'fa-circle',
    'pending'   : 'fa-dot-circle',
    'approved'  : 'fa-check-circle',
    'published' : 'fa-arrow-alt-circle-up',
    'paused'    : 'fa-pause-circle'
};

var statusSetButtons = {
    'rejected'  : ['new'],
    'new'       : ['rejected', 'approved'],
    'pending'   : ['new', 'approved'],
    'approved'  : ['pending'],
    'paused'    : ['rejected', 'approved']
};

var tagsInterval;

function toUrl(key, value)
{
    const url = new URL(window.location.href);
    url.searchParams.set(key, value);
    window.history.replaceState(null, null, url);
    location.reload();
}

function getUrlParameter(key)
{
    let sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === key) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
}

function setActive(elements, element)
{
    elements.removeClass('active');
    element.addClass('active');
}

function hideRow(element)
{
    element.remove();
}

function hideGroupRows(group_id, statusId)
{
    let btnSetGroupStatus = $(selectors.btn_set_group_status);
    let count = 0;
    btnSetGroupStatus.each(function (i) {
        let e = {};

        e.target = $(this);
        e.parent = e.target.parents(selectors.post);

        if (e.parent.data('group-id') == group_id)  {
            count = count + 1;
            e.parent.remove();
        }
    });
    renderStatusButtons(3, count, statusId);
    initStatusButtons(statusId);
}

function showProgressBtn(element)
{
    let icon = element.find(".fa");
    element.prop("disabled", true);
    icon.addClass( "fa-sun fa-spin-hover" );
}

function hideProgressBtn(element)
{
    let icon = element.find(".fa");
    element.prop("disabled", false);
    icon.removeClass( "fa-sun fa-spin-hover" );
}

function showEditForm()
{
    $(selectors.form_container).show();
}

function showImageControls()
{
    $(selectors.next).show();
    $(selectors.prev).show();
}

function hideImageControls()
{
    $(selectors.next).hide();
    $(selectors.prev).hide();
}


function hideEditForm()
{
    $(selectors.form_container).hide();
}

function send(url, method, values, callback, callbackSuccess, e)
{
    $.ajax({
        url: url,
        type: method,
        dataType: 'json',
        data: $.extend({}, values),
        success: function (data) {
            //console.log(data);
            if (e) {
                callback(e.target, e.parent);
            }
            if(data.success === true) {
                callbackSuccess(data);
            }
        },
        error: function(response) {
            //console.log(response);
            if (e) {
                callback(e.target, e.parent);
            }
        }
    });
}

function renderTemplate(name, data) {
    var template = document.getElementById(name).innerHTML;

    for (var property in data) {
        if (data.hasOwnProperty(property)) {
            var search = new RegExp('{' + property + '}', 'g');
            template = template.replace(search, data[property]);
        }
    }
    return template;
}

function renderStatusButtons(id= 'new', count = 0, $active= 'new')
{
    let subPanel = $(selectors.sub_panel);
    subPanel.empty();
    for (const [key, value] of Object.entries(statuses)) {
        statuses[key] = (id == key ? parseInt(value) + count : ( $active == key ? parseInt(value) - count : value ));
        subPanel.append(
            renderTemplate('statusBtn', {
                status_title: key,
                status_active: $active == key ? 'active' : '',
                status_icon: statusIcons[key],
                status_count: statuses[key]
            })
        );
    }
}

function renderFormPostEdit(post)
{
    let formPanel = $(selectors.form_panel);
    formPanel.empty();
    formPanel.append(
        renderTemplate('formPostEdit', {
            post_discription: post.event_discription ? post.event_discription : post.post_discription,
            group_id: post.id_group,
            post_id: post.post_id
        })
    );
}

function renderImages(images)
{
    let imagesContainer = $(selectors.images);
    imagesContainer.empty();
    for (const [key, value] of Object.entries(images)) {
        imagesContainer.append(
            renderTemplate('imageItem', {
                src: value
            })
        );
    }
}

function renderTags(tags)
{
    let tagsContainer = $(selectors.tags);
    tagsContainer.empty();
    for (const [key, value] of Object.entries(tags)) {
        tagsContainer.append(
            renderTemplate('tagItem', {
                tag: value
            })
        );
    }
}

function getTags()
{
    let tagsContainer = $(selectors.tags);
    let tags = tagsContainer.find('span').map(function(){
        return $.trim($(this).text());
    }).get();
    return tags ? tags : [];
}

function initImages()
{
    $(selectors.images).removeAttr('style');
    $(selectors.images).unbind();
    $(selectors.images).removeAttr("style");
    $('.next').unbind();
    $('.prev').unbind();
    $(selectors.images).simplecarousel({
        width: 300,
        height: 225,
        slidespeed: 100,
        visible: 1,
        next: $('.next'),
        prev: $('.prev')
    });
}

function showPreload()
{
    $(selectors.preloader).show();
}

function hidePreload()
{
    $(selectors.preloader).hide();
}

function initSubmit() {
    let submit = $(selectors.submit);
    submit.unbind();
    submit.on("click", function (e) {
        e.preventDefault();
        let target = $(this);
        let values = {};
        let tags = getTags();
        let activeImage = getActiveImage();
        hideImageControls();
        if (tags.length > 0 && activeImage == '') {
            showPreload();
            values.q = tags.join(" ");
            send('api/images', 'POST', values, function (data) {
            }, function (data) {
                renderImages(data.data.images);
                initImages();
                initPostPreview();
                showImageControls();
                hidePreload();
            }, e);
        } else {
            if (activeImage != '') {
                target.parents('form').submit();
            }
        }
    });
}

function initFormPostEdit(parent, status_id, statusId, post)
{
    let target = $(selectors.form_post_edit);
    let images = $(selectors.images);

    clearInterval(tagsInterval);

    tagsInterval = setInterval(function ()  {
        let textarea = target.find('textarea');
        let value = textarea.val();
        let tags = getTags();

        if (value.length > 0) {
            let tag = value.substring(textarea[0].selectionStart, textarea[0].selectionEnd);
            if (tag.length > 0) {
                if (tags.includes(tag) === false &&
                    tags.length < 4 &&
                    tag.indexOf(' ') == -1
                ) {
                    tags.push(tag);
                    renderTags(tags);
                }
            }
        }
    }, 500);

    renderImages(['../../../app/images/' + post.post_image]);
    initPostPreview();
    hideImageControls();

    target.submit(function(e){
        e.preventDefault();
        let target = $(this);
        let values = {};
        let activeImage = getActiveImage();

        values.id = post.id;
        values.image = activeImage;
        values.event_discription = target.find('textarea').val();
        //if (values.event_discription) {
            send('api/post', 'POST', values, function () {
            }, function (data) {
                console.log('success');
                console.log(data);
                hideRow(parent);
                renderStatusButtons(status_id, 1, statusId);
                initStatusButtons(statusId);
            }, e);
        //}
        hideEditForm();
    });

    initSubmit();
}

function initStatusButtons(statusId)
{
    let btnStatus = $(selectors.btn_status);
    setActive(btnStatus, $('.' + statusId));
    btnStatus.on("click", function (btnStatus)
    {
        let target = $(this);
        let statusId = target.data('id');
        toUrl('status', statusId);
        setActive(btnStatus, target);
    });
}

function initSetGroupStatusButtons(statusId) {
    let btnSetGroupStatus = $(selectors.btn_set_group_status);

    btnSetGroupStatus.each(function (i) {
        let target = $(this);
        target.on("click", function () {
            let e = {};
            let values = {};

            e.target = $(this);
            e.parent = target.parents(selectors.post);

            values.group_id = e.parent.data('group-id');
            showProgressBtn(e.target);
            send('api/group', 'POST', values, function (data) {
            }, function (data) {
                hideGroupRows(values.group_id, statusId)
            }, e);
            hideProgressBtn(e.target);
        });
    });
}

function getActiveImage()
{
    let btnPostPreview = $(selectors.post_preview);
    let active = false;
    btnPostPreview.each(function (i) {
        let target = $(this);
        if (target.hasClass('active')) {
            active = target;
            return;
        }
    });
    if (active != false) {
        return active.data('image');
    } else {
        return '';
    }
}

function initPostPreview()
{
    let btnPostPreview = $(selectors.post_preview);

    btnPostPreview.each(function (i) {
        let target = $(this);
        target.unbind();
        target.on("click", function (e) {
            e.preventDefault();
            let target = $(this);
            if (target.hasClass('active')) {
                target.removeClass('active');
                if (btnPostPreview.length > 1) {
                    showImageControls();
                }
            } else {
                setActive(btnPostPreview, target)
                hideImageControls();
            }
        });
    });
}

function initSetStatusButtons(statusId)
{
    let btnSetStatus = $(selectors.btn_set_post_status);

    btnSetStatus.each(function (i) {
        let target = $(this);
        if (statusSetButtons[statusId].includes(target.data('id'))) {
            let columnTd = target.parents('td');
            let columnTh = target.parents('table').find('th').eq(columnTd.index());
            columnTd.show();
            columnTh.show();

            target.on("click", function () {
                let e = {};
                let values = {};
                let post = {};

                e.target = $(this);
                e.parent = target.parents(selectors.post);

                post.id = e.parent.data('id');
                post.status_id = target.data('id');


                    values.post = post;
                    console.log('test');
                    showProgressBtn(e.target);
                    send('api/apigroup', 'POST', values, function () {
                    }, function () {
                        hideRow(e.parent);
                        renderStatusButtons(post.status_id, 1, statusId);
                        initStatusButtons(statusId);
                    }, e);
                    hideProgressBtn(e.target);

            });
        }
    });
}

function initGetCount(statusId, table)
{
    let values = {};
    values.status = statusId;
    values.table = table;
    countInterval = setInterval(function ()  {
        if (statuses[statusId] == 0) {
            send('api/getcount', 'GET', values, function () {
            }, function (data) {
                if (data.data.count > 0) {
                    location.reload();
                }
            });
        }
    }, 5000);
}

$(document).ready(function ()
{
    initGetCount(statusId, 'groups');
    renderStatusButtons();
    initStatusButtons(statusId);
    initSetStatusButtons(statusId);
    initSetGroupStatusButtons(statusId);
});