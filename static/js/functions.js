function _t(string, replace)
{
    if (typeof (aws_lang) != 'undefined')
    {
        if (typeof (aws_lang[string]) != 'undefined')
        {
            string = aws_lang[string];
        }
    }

    if (replace)
    {
        string = string.replace('%s', replace);
    }

    return string;
}

function ajax_request(url, params)
{
	$.loading('show');
	
    if (params)
    {
        $.post(url, params + '&_post_type=ajax', function (result)
        {
        	$.loading('hide');
        	
        	if (!result)
        	{
	        	return false;
        	}
        	
            if (result.err)
            {
                $.alert(result.err);
            }
            else if (result.rsm && result.rsm.url)
            {
                window.location = decodeURIComponent(result.rsm.url);
            }
            else
            {
                window.location.reload();
            }
        }, 'json').error(function (error)
        {
        	$.loading('hide');
        	
            if ($.trim(error.responseText) != '')
            {
                alert(_t('发生错误, 返回的信息:') + ' ' + error.responseText);
            }
        });
    }
    else
    {
        $.get(url, function (result)
        {
        	$.loading('hide');
        	
        	if (!result)
        	{
	        	return false;
        	}
        	
            if (result.err)
            {
                $.alert(result.err);
            }
            else if (result.rsm && result.rsm.url)
            {
                window.location = decodeURIComponent(result.rsm.url);
            }
            else
            {
                window.location.reload();
            }
        }, 'json').error(function (error)
        {
        	$.loading('hide');
        	
            if ($.trim(error.responseText) != '')
            {
                alert(_t('发生错误, 返回的信息:') + ' ' + error.responseText);
            }
        });
    }

    return false;
}

function ajax_post(formEl, processer) // 表单对象，用 jQuery 获取，回调函数名
{
    if (typeof (processer) != 'function')
    {
        processer = _ajax_post_processer;
    }

    var custom_data = {
        _post_type: 'ajax'
    };

    formEl.ajaxSubmit(
    {
        dataType: 'json',
        data: custom_data,
        success: processer,
        error: function (error)
        {
            if ($.trim(error.responseText) != '')
            {
                alert(_t('发生错误, 返回的信息:') + ' ' + error.responseText);
            }
        }
    });
}

function _ajax_post_processer(result)
{
    if (typeof (result.errno) == 'undefined')
    {
        $.alert(result);
    }
    else if (result.errno != 1)
    {
        $.alert(result.err);
    }
    else
    {
        if (result.rsm && result.rsm.url)
        {
            window.location = decodeURIComponent(result.rsm.url);
        }
        else
        {
            window.location.reload();
        }
    }
}

function _ajax_post_modal_processer(result)
{
    if (typeof (result.errno) == 'undefined')
    {
        alert(result);
    }
    else if (result.errno != 1)
    {
        alert(result.err);
    }
    else
    {
        if (result.rsm && result.rsm.url)
        {
            window.location = decodeURIComponent(result.rsm.url);
        }
        else
        {
            $('#aw-ajax-box div.modal').modal('hide');
        }
    }
}

function _ajax_post_alert_processer(result)
{
    if (typeof (result.errno) == 'undefined')
    {
        alert(result);
    }
    else if (result.errno != 1)
    {
        alert(result.err);
    }
    else
    {
        if (result.rsm && result.rsm.url)
        {
            window.location = decodeURIComponent(result.rsm.url);
        }
        else
        {
            window.location.reload();
        }
    }
}

function _ajax_post_background_processer(result)
{
    if (typeof (result.errno) == 'undefined')
    {
        alert(result);
    }
    else if (result.errno != 1)
    {
        $.alert(result.err);
    }
}

function _ajax_post_confirm_processer(result)
{
    if (typeof (result.errno) == 'undefined')
    {
        alert(result);
    }
    else if (result.errno != 1)
    {
        if (!confirm(result.err))
        {
            return false;
        }
    }

    if (result.errno == 1 && result.err)
    {
        alert(result.err);
    }

    if (result.rsm && result.rsm.url)
    {
        window.location = decodeURIComponent(result.rsm.url);
    }
    else
    {
        window.location.reload();
    }
}

function _error_message_form_processer(result)
{
    if (typeof (result.errno) == 'undefined')
    {
        alert(result);
    }
    else if (result.errno != 1)
    {
    	if ($('.error-message em').length)
    	{
	    	$('.error-message em').html(result.err);
    	}
    	else
    	{
	    	 $('.error-message').html(result.err);
    	}
    	
    	if ($('.error-message').css('display') != 'none')
    	{
	    	shake($('.error-message'));
    	}
    	else
    	{
	    	$('.error-message').fadeIn();
    	}
    }
    else
    {
        if (result.rsm && result.rsm.url)
        {
            window.location = decodeURIComponent(result.rsm.url);
        }
        else
        {
            window.location.reload();
        }
    }
}

function shake(element)
{
    element.css('margin-left',element.css('margin-left'));
    for (var i = 1; i <= 3; i++)
    {
        element.animate({ 'left': (30 - 10 * i) }, 20);
        element.animate({ 'left': (2 * (30 - 10 * i)) }, 20);
    }
}

function focus_question(el, question_id)
{
    if (el.html())
    {
        if (!el.hasClass('aw-active'))
        {
            el.html(_t('关注'));
        }
        else
        {
            el.html(_t('取消关注'));
        }
    }
    else
    {
        if (!el.hasClass('aw-active'))
        {
            el.attr('data-original-title', _t('关注'));
        }
        else
        {
            el.attr('data-original-title', _t('取消关注'));
        }
    }

    el.addClass('loading').addClass('disabled');

    $.get(G_BASE_URL + '/question/ajax/focus/question_id-' + question_id, function (data)
    {
        if (data.errno == 1)
        {
            if (data.rsm.type == 'add')
            {
                el.removeClass('aw-active');
            }
            else
            {
                el.addClass('aw-active');
            }
        }
        else
        {
            if (data.err)
            {
                $.alert(data.err);
            }

            if (data.rsm.url)
            {
                window.location = decodeURIComponent(data.rsm.url);
            }
        }

        el.removeClass('loading').removeClass('disabled');
    }, 'json');
}

function focus_topic(el, topic_id)
{
    if (el.html())
    {
        if (!el.hasClass('aw-active'))
        {
            el.html(_t('关注'));
        }
        else
        {
            el.html(_t('取消关注'));
        }
    }
    else
    {
        if (!el.hasClass('aw-active'))
        {
            el.attr('data-original-title', _t('关注'));
        }
        else
        {
            el.attr('data-original-title', _t('取消关注'));
        }
    }

    el.addClass('loading').addClass('disabled');

    $.get(G_BASE_URL + '/topic/ajax/focus_topic/topic_id-' + topic_id, function (data)
    {
        if (data.errno == 1)
        {
            if (data.rsm.type == 'add')
            {
                el.removeClass('aw-active');
            }
            else
            {
                el.addClass('aw-active');
            }
        }
        else
        {
            if (data.err)
            {
                $.alert(data.err);
            }

            if (data.rsm.url)
            {
                window.location = decodeURIComponent(data.rsm.url);
            }
        }

        el.removeClass('loading').removeClass('disabled');
    }, 'json');
}

// Modify by wecenter
function follow_people(el, uid)
{
    if (el.html())
    {
        if (!el.hasClass('aw-active'))
        {
            el.html('<i class="icon-ok-sign aw-active"></i> ' + _t('关注'));
        }
        else
        {
            el.html('<i class="icon-ok-sign"></i> ' + _t('取消关注'));
        }
    }
    else
    {
        if (!el.hasClass('aw-active'))
        {
            el.attr('data-original-title', _t('关注'));
        }
        else
        {
            el.attr('data-original-title', _t('取消关注'));
        }
    }

    el.addClass('loading').addClass('disabled');

    $.get(G_BASE_URL + '/follow/ajax/follow_people/uid-' + uid, function (data)
    {
        if (data.errno == 1)
        {
            if (data.rsm.type == 'add')
            {
                el.removeClass('aw-active');
            }
            else
            {
                el.addClass('aw-active');
            }
        }
        else
        {
            if (data.err)
            {
                $.alert(data.err);
            }

            if (data.rsm.url)
            {
                window.location = decodeURIComponent(data.rsm.url);
            }
        }

        el.removeClass('loading').removeClass('disabled');
    }, 'json');
}

function check_notifications()
{
    if (G_USER_ID == 0)
    {
        return false;
    }

    $.get(G_BASE_URL + '/home/ajax/notifications/', function (result)
    {

        $('#inbox_unread').html(Number(result.rsm.inbox_num));

        last_unread_notification = G_UNREAD_NOTIFICATION;

        G_UNREAD_NOTIFICATION = Number(result.rsm.notifications_num);

        if (G_UNREAD_NOTIFICATION > 0)
        {
            if (G_UNREAD_NOTIFICATION != last_unread_notification)
            {
                reload_notification_list();

                $('#notifications_unread').html(G_UNREAD_NOTIFICATION);
            }
        }
        else
        {
            if ($('#header_notification_list').length > 0)
            {
                $("#header_notification_list").html('<p style="padding: 0" align="center">' + _t('没有未读通知') + '</p>');
            }

            if ($("#index_notification").length > 0)
            {
                $("#index_notification").fadeOut();
            }

            if (('#tab_all_notifications').length > 0)
            {
                $('#tab_all_notifications').click();
            }
        }

        if (Number(result.rsm.notifications_num) > 0)
        {
            document.title = '(' + (Number(result.rsm.notifications_num) + Number(result.rsm.inbox_num)) + ') ' + document_title;

            $('#notifications_unread').show();
        }
        else
        {
            document.title = document_title;

            $('#notifications_unread').hide();
        }

        if (Number(result.rsm.inbox_num) > 0)
        {
            $('#inbox_unread').show();
        }
        else
        {
            $('#inbox_unread').hide();
        }

        if (((Number(result.rsm.notifications_num) + Number(result.rsm.inbox_num))) > 0)
        {
            document.title = '(' + (Number(result.rsm.notifications_num) + Number(result.rsm.inbox_num)) + ') ' + document_title;
        }
        else
        {
            document.title = document_title;
        }
    }, 'json');
}

function reload_notification_list()
{
    if ($("#index_notification").length > 0)
    {
        $("#index_notification").fadeIn().find('[name=notification_unread_num]').html(G_UNREAD_NOTIFICATION);

        $('#index_notification ul#notification_list').html('<p align="center" style="padding: 15px 0"><img src="' + G_STATIC_URL + '/common/loading_b.gif"/></p>');

        $.get(G_BASE_URL + '/notifications/ajax/list/flag-0__page-0', function (response)
        {
            $('#index_notification ul#notification_list').html(response);

            notification_show(5);
        });
    }

    if ($("#header_notification_list").length > 0)
    {
        $("#header_notification_list").html('<p align="center">Loading...</p>');

        $.get(G_BASE_URL + '/notifications/ajax/list/flag-0__limit-5__template-header_list', function (response)
        {
            if (response.length)
            {
                $("#header_notification_list").html(response);

            }
            else
            {
                $("#header_notification_list").html('<p style="padding: 0" align="center">' + _t('没有未读通知') + '</p>');
            }
        });
    }
}

function read_notification(notification_id, el, reload)
{
    if (notification_id)
    {
        el.remove();

        notification_show(5);

        if ($("#announce_num").length > 0)
        {
            $("#announce_num").html(String(G_UNREAD_NOTIFICATION - 1));
        }

        if ($("#notifications_num").length > 0)
        {
            $("#notifications_num").html(String(G_UNREAD_NOTIFICATION - 1));
        }

        var url = G_BASE_URL + '/notifications/ajax/read_notification/notification_id-' + notification_id + '__read_type-1';
    }
    else
    {
        if ($("#index_notification").length > 0)
        {
            $("#index_notification").fadeOut();
        }

        var url = G_BASE_URL + '/notifications/ajax/read_notification/read_type-0';
    }

    $.get(url, function (respose)
    {
        check_notifications();

        if (reload)
        {
            window.location.reload();
        }
    });
}

function notification_show(length)
{
    if ($('#index_notification').length > 0)
    {
        var n_count = 0;

        $('#index_notification ul#notification_list li').each(function (i, e)
        {
            if (i < length)
            {
                $(e).show();
            }
            else
            {
                $(e).hide();
            }

            n_count++;
        });

        if ($('#index_notification ul#notification_list li').length == 0)
        {
            $('#index_notification').fadeOut();
        }
    }
}

function ajax_load(url, target)
{
    $(target).html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif" alt="" /></p>');

    $.get(url, function (response)
    {
        if (response.length)
        {
            $(target).html(response);
        }
        else
        {
            $(target).html('<p style="padding: 15px 0" align="center">' + _t('没有内容') + '</p>');
        }
    });
}

var _bp_more_o_inners = new Array();
var _bp_more_pages = new Array();

function bp_more_load(url, bp_more_o_inner, target_el, start_page, callback_func)
{
    if (!bp_more_o_inner.attr('id'))
    {
        return false;
    }

    if (!start_page)
    {
        start_page = 0
    }

    _bp_more_pages[bp_more_o_inner.attr('id')] = start_page;

    _bp_more_o_inners[bp_more_o_inner.attr('id')] = bp_more_o_inner.html();

    bp_more_o_inner.unbind('click');

    bp_more_o_inner.bind('click', function ()
    {
        var _this = this;

        $(this).addClass('loading');

        $(this).find('span').html(_t('正在载入') + '...');

        $.get(url + '__page-' + _bp_more_pages[bp_more_o_inner.attr('id')], function (response)
        {
            if ($.trim(response) != '')
            {
                if (_bp_more_pages[bp_more_o_inner.attr('id')] == start_page && $(_this).attr('auto-load') != 'false')
                {
                    target_el.html(response);
                }
                else
                {
                    target_el.append(response);
                }

                _bp_more_pages[bp_more_o_inner.attr('id')]++;

                $(_this).html(_bp_more_o_inners[bp_more_o_inner.attr('id')]);
            }
            else
            {
                if (_bp_more_pages[bp_more_o_inner.attr('id')] == start_page && $(_this).attr('auto-load') != 'false')
                {
                    target_el.html('<p style="padding: 15px 0" align="center">' + _t('没有内容') + '</p>');
                }

                $(_this).addClass('disabled').unbind('click').bind('click', function () { return false; });

                $(_this).find('span').html(_t('没有更多了'));
            }

            $(_this).removeClass('loading');

            if (callback_func != null)
            {
                callback_func();
            }
        });

        return false;
    });

    if (bp_more_o_inner.attr('auto-load') != 'false')
    {
        bp_more_o_inner.click();
    }
}

function content_switcher(hide_el, show_el)
{
    hide_el.hide();
    show_el.fadeIn();
}

function hightlight(el, class_name)
{
    if (el.hasClass(class_name))
    {
        return true;
    }

    var hightlight_timer_front = setInterval(function ()
    {
        el.addClass(class_name);
    }, 500);

    var hightlight_timer_background = setInterval(function ()
    {
        el.removeClass(class_name);
    }, 600);

    setTimeout(function ()
    {
        clearInterval(hightlight_timer_front);
        clearInterval(hightlight_timer_background);

        el.addClass(class_name);
    }, 1200);

    setTimeout(function ()
    {
        el.removeClass(class_name);
    }, 6000);
}

function nl2br(str)
{
    return str.replace(new RegExp("\r\n|\n\r|\r|\n", "g"), "<br />");
}

function init_img_uploader(upload_url, upload_name, upload_element, upload_status_elememt, perview_element)
{
    return new AjaxUpload(upload_element,
    {
        action: upload_url,
        name: upload_name,
        responseType: 'json',

        onSubmit: function (file, ext)
        {
            if (!new RegExp('(png|jpe|jpg|jpeg|gif)$', 'i').test(ext))
            {
                alert(_t('上传失败: 只支持 jpg、gif、png 格式的图片文件'));

                return false;
            }

            this.disable();

            if (upload_status_elememt)
            {
                upload_status_elememt.show();
            }
        },

        onComplete: function (file, response)
        {
            this.enable();

            if (upload_status_elememt)
            {
                upload_status_elememt.hide();
            }

            if (response.errno == -1)
            {
                alert(response.err);
            }
            else
            {
                if (typeof (perview_element.attr('src')) != 'undefined')
                {
                    perview_element.attr('src', response.rsm.preview + '?' + Math.floor(Math.random() * 10000));
                }
                else
                {
                    perview_element.css('background-image', 'url(' + response.rsm.preview + '?' + Math.floor(Math.random() * 10000) + ')');
                }
            }
        }
    });
}

function init_avatar_uploader(upload_element, upload_status_elememt, avatar_element)
{
    return init_img_uploader(G_BASE_URL + '/account/ajax/avatar_upload/', 'user_avatar', upload_element, upload_status_elememt, avatar_element);
}

function init_fileuploader(element_id, action_url)
{
    if (!document.getElementById(element_id))
    {
        return false;
    }
    
    if (G_UPLOAD_ENABLE == 'Y')
    {
    	$('.aw-upload-tips').show();
    }

    return new _ajax_uploader.FileUploader(
    {
        element: document.getElementById(element_id),
        action: action_url,
        debug: false
    });
}

function htmlspecialchars(text)
{
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function delete_draft(item_id, type)
{
    $.post(G_BASE_URL + '/account/ajax/delete_draft/', 'item_id=' + item_id + '&type=' + type, function (result)
    {
        if (result.errno != 1)
        {
            $.alert(result.err);
        }
    }, 'json');
}

function agree_vote(answer_id, value)
{
    $.post(G_BASE_URL + '/question/ajax/answer_vote/', "answer_id=" + answer_id + "&value=" + value, function (result)
    {
        if (result.errno == -1)
        {
            $.alert(result.err);
        }
    }, 'json');
}

function question_uninterested(el, question_id)
{
    el.fadeOut();

    $.post(G_BASE_URL + '/question/ajax/uninterested/', 'question_id=' + question_id, function (result)
    {
        if (result.errno != '1')
        {
            alert(result.err);
        }
    }, 'json');
}

function question_invite_delete(block_el, question_invite_id)
{
    $.post(G_BASE_URL + '/question/ajax/question_invite_delete/', 'question_invite_id=' + question_invite_id, function (result)
    {
        if (result.errno == 1)
        {
            block_el.fadeOut();
        }
        else
        {
            alert(result.rsm.err);
        }
    }, 'json');
}

function reload_comments_list(item_id, element_id, type_name)
{
    $('#aw-comment-box-' + type_name + '-' + element_id + ' .aw-comment-list').html('<p align="center" class="aw-padding10"><i class="aw-loading"></i></p>');

    $.get(G_BASE_URL + '/question/ajax/get_' + type_name + '_comments/' + type_name + '_id-' + item_id, function (data)
    {
        $('#aw-comment-box-' + type_name + '-' + element_id + ' .aw-comment-list').html(data);
    });
}

function save_comment(save_button_el)
{
    $(save_button_el).attr('_onclick', $(save_button_el).attr('onclick')).addClass('disabled').removeAttr('onclick').addClass('_save_comment');

    ajax_post($(save_button_el).parents('form'), _comments_form_processer);
}

function _comments_form_processer(result)
{
    $.each($('a._save_comment.disabled'), function (i, e)
    {

        $(e).attr('onclick', $(this).attr('_onclick')).removeAttr('_onclick').removeClass('disabled').removeClass('_save_comment');
    });

    if (result.errno != 1)
    {
        $.alert(result.err);
    }
    else
    {
        reload_comments_list(result.rsm.item_id, result.rsm.item_id, result.rsm.type_name);

        $('#aw-comment-box-' + result.rsm.type_name + '-' + result.rsm.item_id + ' form input').val('');
        $('#aw-comment-box-' + result.rsm.type_name + '-' + result.rsm.item_id + ' form').fadeOut();
    }
}

function remove_comment(el, type, comment_id)
{
	$.get(G_BASE_URL + '/question/ajax/remove_comment/type-' + type + '__comment_id-' + comment_id);
	
	$(el).parents('.aw-comment-box li').fadeOut();
}

function insert_attach(element, attach_id, attach_tag)
{
    $(element).parents('form').find('textarea').insertAtCaret("\n[" + attach_tag + "]" + attach_id + "[/" + attach_tag + "]\n");
}

function question_thanks(question_id, element)
{
    $.post(G_BASE_URL + '/question/ajax/question_thanks/', 'question_id=' + question_id, function (result)
    {
        if (result.errno != 1)
        {
            $.alert(result.err);
        }
        else if (result.rsm.action == 'add')
        {
            $(element).html($(element).html().replace(_t('喜欢'), _t('已喜欢')));
            $(element).removeAttr('onclick');
            $(element).find('.icon-heart-empty').addClass('active');
            $(element).find('b').html(parseInt($(element).find('b').html()) + 1);
        }
        else
        {
            $(element).html($(element).html().replace(_t('已喜欢'), _t('喜欢')));
            $(element).find('.icon-heart').removeClass('active');
        }
    }, 'json');
}

function answer_user_rate(answer_id, type, element)
{
    $.post(G_BASE_URL + '/question/ajax/question_answer_rate/', 'type=' + type + '&answer_id=' + answer_id, function (result)
    {
        if (result.errno != 1)
        {
            $.alert(result.err);
        }
        else if (result.errno == 1)
        {
            switch (type)
            {
            case 'thanks':
                if (result.rsm.action == 'add')
                {
                    $(element).html($(element).html().replace(_t('感谢'), _t('已感谢')));
                    $(element).removeAttr('onclick');
                }
                else
                {
                    $(element).html($(element).html().replace(_t('已感谢'), _t('感谢')));
                }
                break;

            case 'uninterested':
                if (result.rsm.action == 'add')
                {
                    $(element).html(_t('撤消没有帮助'));
                }
                else
                {
                    $(element).html(_t('没有帮助'));
                }
                break;
            }
        }
    }, 'json');
}

function init_comment_box(selecter)
{
    $(document).on('click', selecter, function ()
    {
        if (!$(this).attr('data-type') || !$(this).attr('data-id'))
        {
            return true;
        }

        var comment_box_id = '#aw-comment-box-' + $(this).attr('data-type') + '-' + 　$(this).attr('data-id');
		
        if ($(comment_box_id).length > 0)
        {
            if ($(comment_box_id).css('display') == 'none')
            {
                $(comment_box_id).fadeIn();
            }
            else
            {
                $(comment_box_id).fadeOut();
            }
        }
        else
        {
            // 动态插入commentBox
            switch ($(this).attr('data-type'))
            {
	            case 'question':
	                var comment_form_action = G_BASE_URL + '/question/ajax/save_question_comment/question_id-' + $(this).attr('data-id');
	                var comment_data_url = G_BASE_URL + '/question/ajax/get_question_comments/question_id-' + $(this).attr('data-id');
	                break;
	
	            case 'answer':
	                var comment_form_action = G_BASE_URL + '/question/ajax/save_answer_comment/answer_id-' + $(this).attr('data-id');
	                var comment_data_url = G_BASE_URL + '/question/ajax/get_answer_comments/answer_id-' + $(this).attr('data-id');
	                break;
            }

            if (G_USER_ID && $(this).attr('data-close') != 'true')
            {
                $(this).parents('.aw-item').append(Hogan.compile(AW_TEMPLATE.commentBox).render(
                {
                    'comment_form_id': comment_box_id.replace('#', ''),
                    'comment_form_action': comment_form_action
                }));
				
                $(comment_box_id).find('.aw-comment-txt').bind(
                {
                    focus: function ()
                    {
                        $(this).css('height', parseInt($(this).css('line-height')) * 5);

                        $(comment_box_id).find('.aw-comment-box-btn').show();
                    },

                    blur: function ()
                    {
                        if ($(this).val() == '')
                        {
                            $(this).css('height', '');

                            $(comment_box_id).find('.aw-comment-box-btn').hide();
                        }
                    }
                });

                $(comment_box_id).find('.close-comment-box').click(function ()
                {
                    $(comment_box_id).fadeOut();
                    $(comment_box_id).find('.aw-comment-txt').css('height', $(this).css('line-height'));
                });
            }
            else
            {
                $(this).parent().parent().append(Hogan.compile(AW_TEMPLATE.commentBoxClose).render(
                {
                    'comment_form_id': comment_box_id.replace('#', ''),
                    'comment_form_action': comment_form_action
                }));
            }

            //判断是否有评论数据
            $.get(comment_data_url, function (result)
            {
                if (!result)
                {
                    result = '<div align="center" class="aw-padding10">' + _t('暂无评论') + '</div>';
                }

                $(comment_box_id).find('.aw-comment-list').html(result);
            });

            var left = $(this).width()/2 + $(this).prev().width();
            /*给三角形定位*/
            $(comment_box_id).find('.i-comment-triangle').css('left', $(this).width() / 2 + $(this).prev().width() + 15);
        }

        at_user_lists($(this).parents('.aw-item').find('.aw-comment-txt'));
    });
}

function insertVoteBar(data)
{
    // {element:this,agree_count:20,flag:0,user_name:G_USER_NAME,answer_id:1230};
    switch (data.flag)
    {
    case 1:
        up_class = 'active';
        down_class = '';
        break;

    case -1:
        up_class = '';
        down_class = 'active';
        break;

    case 0:
        up_class = '';
        down_class = '';
        break;
    }

    $(data.element).parent().prepend(Hogan.compile(AW_TEMPLATE.voteBar).render(
    {
        'agree_count': data.agree_count,
        'up_class': up_class,
        'down_class': down_class,
        'user_name': data.user_name,
        'answer_id': data.answer_id
    }));

    $(data.element).detach();
}

// Modify by wecenter
//赞成投票
function agreeVote(element, user_name, answer_id)
{
    //判断是否投票过
    if ($(element).find('i').hasClass('active'))
    {
        $(element).find('i').removeClass('active');
        $(element).parents('dl').find('b').html(parseInt($(element).parents('dl').find('b').html()) - 1);
    }
    else
    {
        $(element).parents('dl').find('b').html(parseInt($(element).parents('dl').find('b').html())+1);
        $(element).find('i').addClass('active');
        $(element).parents('dl').find('.disagree i').removeClass('active');
    }
    $.post(G_BASE_URL + '/question/ajax/answer_vote/', 'answer_id=' + answer_id + '&value=1', function (result) {});
}

// Modify by wecenter
//反对投票
function disagreeVote(element, user_name, answer_id)
{
    //判断是否投票过
    if (!$(element).find('i').hasClass('active'))//没点亮反对
    {
        $(element).parents('dl').find('.agree i').removeClass('active');
        $(element).parents('dl').find('.agree b').html(parseInt($(element).parents('dl').find('.agree b').html()) - 1);
        $(element).find('i').addClass('active');
    }
    else
    {
        $(element).removeClass('active');
    }
    $.post(G_BASE_URL + '/question/ajax/answer_vote/', 'answer_id=' + answer_id + '&value=-1', function (result) {});

}

//插入话题编辑box

function init_topic_edit_box(selecter) //selecter -> .aw-edit-topic
{
    $(selecter).click(function ()
    {
    	$(this).parents('.aw-item').css('z-index',1000);
        var _aw_topic_editor_element = $(this).parents('.aw-topic-editor');
        var data_id = _aw_topic_editor_element.attr('data-id');
        var data_type = _aw_topic_editor_element.attr('data-type');

        if (!_aw_topic_editor_element.find('.aw-topic-name').children().children().hasClass('aw-close'))
        {
            _aw_topic_editor_element.find('.aw-topic-name').children().append('<button type="button" class="close aw-close">×</button>');

            $.each(_aw_topic_editor_element.find('.aw-close'), function (i, e)
            {
                $(e).click(function ()
                {

                    switch (data_type)
                    {
                    case 'question':
                        $.post(G_BASE_URL + '/question/ajax/delete_topic/', 'question_id=' + data_id + '&topic_id=' + $(this).parents('.aw-topic-name').attr('data-id'),function(){
                            $('#aw-ajax-box').empty();
                        });
                        break;

                    case 'topic':
                        $.get(G_BASE_URL + '/topic/ajax/remove_related_topic/related_id-' + $(this).parents('.aw-topic-name').attr('data-id') + '__topic_id-' + data_id);
                        break;

                    case 'favorite':
                        $.post(G_BASE_URL + '/favorite/ajax/remove_favorite_tag/', 'answer_id=' + data_id + '&tags=' + $(this).parents('.aw-topic-name').text());
                        break;
                    }

                    $(this).parents('.aw-topic-name').remove();

                    return false;
                });
            });
        }
        else
        {
            _aw_topic_editor_element.find('.aw-close').show();
        }

        /*判断插入编辑box*/
        if (_aw_topic_editor_element.find('.aw-edit-topic-box').length == 0)
        {
            _aw_topic_editor_element.append(AW_TEMPLATE.editTopicBox);

            //给编辑box取消按钮添加事件
            _aw_topic_editor_element.find('.close-edit').click(function ()
            {
                _aw_topic_editor_element.find('.aw-edit-topic-box').hide();
                _aw_topic_editor_element.find('.aw-close').hide();
                _aw_topic_editor_element.find('.aw-edit-topic').show();
                $(this).parents('.aw-item').attr('style','');
            });

            _aw_topic_editor_element.find('.submit-edit').click(function ()
            {
                if (_aw_topic_editor_element.find('#aw_edit_topic_title').val() != ''){
                    switch (data_type)
                    {
                    case 'publish':
                        _aw_topic_editor_element.prepend('<a href="javascript:;" class="aw-topic-name"><span>' + _aw_topic_editor_element.find('#aw_edit_topic_title').val() + '<button class="close aw-close" type="button" onclick="$(this).parents(\'.aw-topic-name\').remove();">x</button></span><input type="hidden" value="' + _aw_topic_editor_element.find('#aw_edit_topic_title').val() + '" name="topics[]" /></a>').hide().fadeIn();

                        _aw_topic_editor_element.find('#aw_edit_topic_title').val('');
                        break;

                    case 'question':
                        $.post(G_BASE_URL + '/question/ajax/save_topic/question_id-' + data_id, 'topic_title=' + _aw_topic_editor_element.find('#aw_edit_topic_title').val(), function (result)
                        {
                            if (result.errno != 1)
                            {
                                $.alert(result.err);

                                return false;
                            }

                            _aw_topic_editor_element.prepend('<a href="' + G_BASE_URL + '/topic/' + result.rsm.topic_id + '" class="aw-topic-name"><span>' + _aw_topic_editor_element.find('#aw_edit_topic_title').val() + '<button class="close aw-close" onclick="$.post(G_BASE_URL + \'/question/ajax/delete_topic/\', \'question_id=' + data_id + '&topic_id=\' + $(this).parents(\'.aw-topic-name\').attr(\'data-id\'));$(this).parents(\'.aw-topic-name\').remove();return false;">x</button></span></a>').hide().fadeIn();

                            _aw_topic_editor_element.find('#aw_edit_topic_title').val('');
                        }, 'json');
                        break;

                    case 'topic':
                        $.post(G_BASE_URL + '/topic/ajax/save_related_topic/topic_id-' + data_id, 'topic_title=' + _aw_topic_editor_element.find('#aw_edit_topic_title').val(), function (result)
                        {
                            if (result.errno != 1)
                            {
                                $.alert(result.err);

                                return false;
                            }

                            _aw_topic_editor_element.prepend('<a href="' + G_BASE_URL + '/favorite/tag-' + encodeURIComponent(_aw_topic_editor_element.find('#aw_edit_topic_title').val()) + '" class="aw-topic-name"><span>' + _aw_topic_editor_element.find('#aw_edit_topic_title').val() + '<button class="close aw-close" onclick="$.post(G_BASE_URL + \'/topic/ajax/remove_related_topic/related_id-\' + $(this).parents(\'.aw-topic-name\').text() + \'&topic_id=' + data_id + '\');$(this).parents(\'.aw-topic-name\').remove();return false;">x</button></span></a>').hide().fadeIn();

                            _aw_topic_editor_element.find('#aw_edit_topic_title').val('');
                        }, 'json');
                        break;

                    case 'favorite':
                        $.post(G_BASE_URL + '/favorite/ajax/update_favorite_tag/', 'answer_id=' + data_id + '&tags=' + _aw_topic_editor_element.find('#aw_edit_topic_title').val(), function (result)
                        {
                            if (result.errno != 1)
                            {
                                $.alert(result.err);

                                return false;
                            }

                            _aw_topic_editor_element.prepend('<a href="' + G_BASE_URL + '/favorite/tag-' + encodeURIComponent(_aw_topic_editor_element.find('#aw_edit_topic_title').val()) + '" class="aw-topic-name"><span>' + _aw_topic_editor_element.find('#aw_edit_topic_title').val() + '<button class="close aw-close" onclick="$.post(G_BASE_URL + \'/favorite/ajax/remove_favorite_tag/\', \'answer_id=' + data_id + '&topic_id=\' + $(this).parents(\'.aw-topic-name\').text());$(this).parents(\'.aw-topic-name\').remove();return false;">x</button></span></a>').hide().fadeIn();
                            
                            _aw_topic_editor_element.find('#aw_edit_topic_title').val('');
                        }, 'json');
                        break;
                    }
                }
            });
        }

        $(this).parent().find('.aw-edit-topic-box').fadeIn();

        /*隐藏话题编辑按钮*/
        $(this).hide();
    });
}

/*box拖拽*/

function dragBox(selecter)
{
    $(selecter).mousedown(function (e)
    {
        var dragX = e.clientX - $(this).offset().left,
            dragY = e.clientY - $(this).offset().top,
            _this = $(this);
        $(document).on(
        {
            mousemove: function (e)
            {
                var left = e.clientX - dragX,
                    top = e.clientY - dragY;
                _this.parents('.modal-dialog').css(
                {
                    'left': left,
                    'top': top
                });
            },
            mouseup: function ()
            {
                $(document).unbind('mousemove');
                $(document).unbind('mouseup');
            }
        });
    });
}

/*
 **	功能: 用户头像提示box效果
 **
 *   type : user/topic
 *	nTop    : 焦点到浏览器上边距
 *	nRight  : 焦点到浏览器右边距
 *	nBottom : 焦点到浏览器下边距
 *	left    : 焦点距离文档左偏移量
 *	top     : 焦点距离文档上偏移量
 **
 */
var cashUserData = [],
    cashTopicData = [],
    cardBoxTimeout;

function show_card_box(selecter, type, time) //selecter -> .aw-user-name/.aw-topic-name
{
    if (time)
    {
        var time = time;
    }
    else
    {
        var time = 300;
    }
    $(document).on('mouseover', selecter, function ()
    {
        clearTimeout(cardBoxTimeout);
        var _this = $(this),
            nTop = _this.offset().top - $(window).scrollTop(),
            nRight = $(window).width() - _this.offset().left,
            nBottom = $(window).height() - _this.height() - nTop;
        card_box_show = setTimeout(function ()
        {
            //用户头像box
            if (type == 'user')
            {
                //判断用户id是否存在
                if (_this.attr('data-id'))
                {
                    //检查是否有缓存
                    if (cashUserData.length == 0)
                    {
                        //发送请求
                        _getdata('user', '/people/ajax/user_info/uid-');

                    }
                    else
                    {
                        var flag = 0;
                        //遍历缓存中是否含有此id的数据
                        _checkcash('user');
                        if (flag == 0)
                        {
                            _getdata('user', '/people/ajax/user_info/uid-');
                        }
                    }
                }
            }
            //话题box
            if (type == 'topic')
            {
                //存在topic_id
                if (_this.attr('data-id'))
                {
                    //检查是否有缓存
                    if (cashTopicData.length == 0)
                    {
                        _getdata('topic', '/topic/ajax/topic_info/topic_id-');
                    }
                    else
                    {
                        var flag = 0;
                        //遍历缓存中是否含有此id的数据
                        _checkcash('topic');
                        if (flag == 0)
                        {
                            _getdata('topic', '/topic/ajax/topic_info/topic_id-');
                        }
                    }
                }
            }

            //通用获取数据

            function _getdata(type, url)
            {
                if (type == 'user')
                {
                    $.ajax(
                    {
                        type: 'GET',
                        url: G_BASE_URL + url + _this.attr('data-id'),
                        dataType: 'json',
                        success: function (result)
                        {
                            var focus = result.focus,
                                focusTxt,
                                verified = result.verified;
                           
                            if (focus == 1)
                            {
                                focus = '';
                                focusTxt = '取消关注';
                            }
                            else
                            {
                                focus = 'aw-active';
                                focusTxt = '关注';
                            }
                            
                            if (verified)
                            {
                                verified = 'active';
                            }
                            
                            if (result.verified == 'enterprise')
                            {
                                verified_enterprise = 'i-ve';
                                verified_title = '企业认证';
                            }else
                            {
                                verified_enterprise = '';
                                verified_title = '个人认证';
                            }
                            
                            //动态插入盒子
                            $('#aw-ajax-box').html(Hogan.compile(AW_TEMPLATE.userCard).render(
                            {
                                'verified' : verified,
                                'verified_enterprise' : verified_enterprise,
                                'verified_title' : verified_title,
                                'uid': result.uid,
                                'avatar_file': result.avatar_file,
                                'user_name': result.user_name,
                                'reputation': result.reputation,
                                'agree_count': result.agree_count,
                                'signature': result.signature,
                                'url' : result.url,
                                'category_enable' : result.category_enable,
                                'focus': focus,
                                'focusTxt': focusTxt
                            }));
                            //判断是否为游客or自己
                            if (G_USER_ID == 0 || G_USER_ID == result.uid)
                            {
                                $('#aw-card-tips .aw-mod-footer').hide();
                            }
                            _init();
                            //缓存
                            cashUserData.push($('#aw-ajax-box').html());
                        }
                    });
                }
                if (type == 'topic')
                {
                    $.ajax(
                    {
                        type: 'GET',
                        url: G_BASE_URL + url + _this.attr('data-id'),
                        dataType: 'json',
                        success: function (result)
                        {
                            var focus = result.focus,
                                focusTxt;
                            if (focus == 1)
                            {
                                focus = '';
                                focusTxt = _t('取消关注');
                            }
                            else
                            {
                                focus = 'aw-active';
                                focusTxt = _t('关注');
                            }
                            //动态插入盒子
                            $('#aw-ajax-box').html(Hogan.compile(AW_TEMPLATE.topicCard).render(
                            {
                                'topic_id': result.topic_id,
                                'topic_pic': result.topic_pic,
                                'topic_title': result.topic_title,
                                'topic_description': result.topic_description,
                                'discuss_count': result.discuss_count,
                                'focus_count': result.focus_count,
                                'focus': focus,
                                'focusTxt': focusTxt,
                                'url' : result.url
                            }));
                            //判断是否为游客
                            if (G_USER_ID == 0)
                            {
                                $('#aw-card-tips .aw-mod-footer .focus').hide();
                            }
                            _init();
                            //缓存
                            cashTopicData.push($('#aw-ajax-box').html());
                        }
                    });
                }
            }
            //检测缓存

            function _checkcash(type)
            {
                if (type == 'user')
                {
                    $.each(cashUserData, function (i, a)
                    {
                        if (a.match('data-id="' + _this.attr('data-id') + '"'))
                        {
                            $('#aw-ajax-box').html(a);
                            $('#aw-card-tips').removeAttr('style');
                            _init();
                            flag = 1;
                        }
                    });
                }
                if (type == 'topic')
                {

                    $.each(cashTopicData, function (i, a)
                    {
                        if (a.match('data-id="' + _this.attr('data-id') + '"'))
                        {
                            $('#aw-ajax-box').html(a);
                            $('#aw-card-tips').removeAttr('style');
                            _init();
                            flag = 1;
                        }
                    });
                }
            }
            //card-box初始化

            function _init()
            {
                //正常情况下box显示状态
                $('#aw-card-tips').css(
                {
                    left: _this.offset().left,
                    top: _this.offset().top + _this.height() + 2
                }).fadeIn();
                //非正常状态下判断
                if ($('#aw-card-tips').height() + 32 > nBottom) //判断下边距不足的情况
                {
                    if ($('#aw-card-tips').width() + 32 > nRight) //下边距右边距同时不足的情况
                    {
                        $('#aw-card-tips').css(
                        {
                            left: _this.offset().left - $('#aw-card-tips').width() + 32,
                            top: _this.offset().top - ($('#aw-card-tips').height() + 32) - _this.height() / 3
                        }).fadeIn();
                    }
                    else
                    {
                        $('#aw-card-tips').css(
                        {
                            left: _this.offset().left,
                            top: _this.offset().top - ($('#aw-card-tips').height() + 32) - _this.height() / 3
                        }).fadeIn();
                    }
                }
                if ($('#aw-card-tips').width() + 32 > nRight) //判断右边距不足的情况
                {
                    if ($('#aw-card-tips').height() + 32 > nBottom) //右边距下边距同时不足的情况
                    {
                        $('#aw-card-tips').css(
                        {
                            left: _this.offset().left - $('#aw-card-tips').width() + 32,
                            top: _this.offset().top - ($('#aw-card-tips').height() + 32) - _this.height() / 3
                        }).fadeIn();
                    }
                    else
                    {
                        $('#aw-card-tips').css(
                        {
                            left: _this.offset().left - $('#aw-card-tips').width() + 32,
                            top: _this.offset().top + _this.height() + 3
                        }).fadeIn();
                    }
                }
            }
        }, time);
    });

    $(document).on('mouseout', selecter, function ()
    {
        clearTimeout(card_box_show);
        cardBoxTimeout = setTimeout(function ()
        {
            $('#aw-card-tips').fadeOut();
        }, 600); 
    });
}

/*搜索下拉*/

function search_tips(selecter, limit)
{
    var keyword;

    $(selecter).on(
    {
        focus: function ()
        {
            //显示下拉列表,清空列表数据
            $('.aw-search-dropdown-box').show().children('.aw-search-dropdown-list').empty();
            /*给三角形定位*/
            $('.aw-search-dropdown-box .i-dropdown-triangle').css('left', ($(this).width()) / 2 + 50);
            $('.aw-search-dropdown-box .title').text(_t('输入关键字进行搜索')).show();
            $('.aw-search-dropdown-box .search').hide().children('a').text('');
            $('.aw-search-dropdown-box .txt').text('');
        },

        keyup: function ()
        {
            if ($(this).val().length >= 2)
            {
                //请求获取数据
                $.ajax(
                {
                    type: 'GET',
                    url: G_BASE_URL + '/search/ajax/search/?q=' + encodeURIComponent($(this).val()) + '&limit=' + limit,
                    dataType: 'json',
                    success: function (result)
                    {
                        //清空内容
                        $('.aw-search-dropdown-box .aw-search-dropdown-list').html('');
                        for (var i = 0; i < result.length; i++)
                        {
                            add_search_dropdown_list('.aw-search-dropdown-box .aw-search-dropdown-list', parseInt(result[i].type), result[i], keyword);
                        }
                    }

                });

                $('.aw-search-dropdown-box .search').show().children('a').text($(this).val());
                $('.aw-search-dropdown-box .title').hide();

            }
            else
            {
                $('.aw-search-dropdown-box .title').text(_t('请输入两个以上关键字...')).show();
                $('.aw-search-dropdown-box .search').hide();
                $('.aw-search-dropdown-box .aw-search-dropdown-list').empty();
            }
            
            $('.aw-search-dropdown-box .txt').text($(this).val());
            
            keyword = $(this).val();
        },
        blur: function ()
        {
            searchtimeout = setTimeout(function ()
            {
                $('.aw-search-dropdown-box').hide();
            }, 300);
        }
    });

}

/*搜索下拉菜单插入*/

function add_search_dropdown_list(selecter, type, json, keyword)
{
    switch (type) // type1 : 问题 , type2 : 话题 best_answer最佳回答, type3 : 用户
    {
    case 1:
        if (json.detail.best_answer > 0)
        {
            var active = 'active';
        }
        else
        {
            var active = ''
        }

        $(selecter).append(Hogan.compile(AW_TEMPLATE.searchDropdownList1).render(
        {
            'url': json.url,
            'active': active,
            'content': json.name,
            'discuss_count': json.detail.answer_count
        }));
        //高亮显示
        $('.aw-search-dropdown-list .question a').highText(keyword, 'span', 'active');
        break;
    case 2:
        $(selecter).append(Hogan.compile(AW_TEMPLATE.searchDropdownList2).render(
        {
            'url': json.url,
            'name': json.name,
            'discuss_count': json.detail.discuss_count,
            'topic_id': json.detail.topic_id
        }));
        break;
    case 3:
        if (json.detail.signature == '')
        {
            var signature = _t('暂无介绍');
        }
        else
        {
            var signature = json.detail.signature;
        }
        
        $(selecter).append(Hogan.compile(AW_TEMPLATE.searchDropdownList3).render(
        {
            'url': json.url,
            'uid': json.uid,
            'img': json.detail.avatar_file,
            'name': json.name,
            'intro': signature
        }));
        break;
    }
}

/*话题编辑下拉菜单*/
function get_topic_list_data(e,data)
{
    //按,号自动添加话题
    if (e.which == 188)
    {
        if ($('.aw-edit-topic-box #aw_edit_topic_title').val() != ',')
        {
            $('.aw-edit-topic-box #aw_edit_topic_title').val( $('.aw-edit-topic-box #aw_edit_topic_title').val().substring(0,$('.aw-edit-topic-box #aw_edit_topic_title').val().length-1));
            $('.aw-edit-topic-box .aw-topic-dropdown').hide();
            $('.aw-edit-topic-box .submit-edit').click(); 
        }else
        {
            $('.aw-edit-topic-box #aw_edit_topic_title').val('');
        }
    }else
    {
         $('.aw-edit-topic-box .aw-topic-dropdown').css('width', $('.aw-edit-topic-box #aw_edit_topic_title').width() + 12);
        if (data.length >= 2)
        {
            $.get(G_BASE_URL + '/search/ajax/search/?type-topic__q-' + encodeURIComponent(data) + '__limit-10', function (result)
            {
                if (result.length != 0)
                {
                    $('.aw-edit-topic-box .aw-topic-dropdown-list').empty();

                    $.each(result, function (i, a)
                    {
                        $('.aw-edit-topic-box .aw-topic-dropdown .aw-topic-dropdown-list').append(Hogan.compile(AW_TEMPLATE.editTopicDorpdownList).render(
                        {
                            'name': a['name']
                        }));
                    });
                    $('.aw-edit-topic-box .aw-topic-dropdown').show().children().show();
                    $('.aw-edit-topic-box .aw-topic-dropdown .title').hide();
                    //关键词高亮
                    $('.aw-edit-topic-box .aw-topic-dropdown-list li a').highText(data, 'span', 'active');

                }
                else
                {
                    $('.aw-edit-topic-box .aw-topic-dropdown').show().children('.title').html(_t('没有找到相关结果')).show();
                    $('.aw-edit-topic-box .aw-topic-dropdown-list').hide();
                }
            }, 'json');
        }
        else if (data.length > 0 && data.length < 2)
        {
            $('.aw-edit-topic-box .aw-topic-dropdown-list').hide();
            $('.aw-edit-topic-box .aw-topic-dropdown').show().children('.title').html(_t('请输入两个以上关键字...')).show();
        }
        else
        {
            $('.aw-edit-topic-box .aw-topic-dropdown').hide();
        }
    }
   
}

/*话题编辑下拉菜单失去焦点*/
function hide_topic_list()
{
    setTimeout(function ()
    {
        $('.aw-edit-topic-box .aw-topic-dropdown').hide();
    }, 300);
}

/*问题重定向下拉*/
function get_question_list_data(data)
{
    $('.aw-question-drodpwon .aw-topic-dropdown').css('width', $('.aw-question-drodpwon #question-input').width() + 12);

    if (data.length >= 2)
    {
        $.get(G_BASE_URL + '/search/ajax/search/?q=' + encodeURIComponent(data) + '&type=question&limit-30', function (result)
        {
            if (result.length != 0)
            {
                $('.aw-question-drodpwon .aw-question-dropdown-list').empty();
                
                $.each(result, function (i, a)
                {
                    $('.aw-question-drodpwon .aw-question-dropdown-list').append(Hogan.compile(AW_TEMPLATE.questionRedirectList).render(
                    {
                        'url': "'" + G_BASE_URL + "/question/ajax/redirect/', 'item_id=" + $('.aw-question-drodpwon #question-input').attr('data-id') + "&target_id=" + a['sno'] + "'",
                        'name': a['name']
                    }));
                });
                $('.aw-question-drodpwon .aw-topic-dropdown').show().children().show();
                $('.aw-question-drodpwon .aw-topic-dropdown .title').hide();
                //关键词高亮
                $('.aw-question-drodpwon .aw-question-dropdown-list li a').highText(data, 'span', 'active');

            }
            else
            {
                $('.aw-question-drodpwon .aw-topic-dropdown').show().children('.title').html(_t('没有找到相关结果')).show();
                $('.aw-question-drodpwon .aw-topic-dropdown .aw-question-dropdown-list').hide();
            }
        }, 'json');
    }
    else if (data.length > 0 && data.length < 2)
    {
        $('.aw-question-drodpwon .aw-topic-dropdown').show().children('.title').html(_t('请输入两个以上关键字...')).show();
   		$('.aw-question-drodpwon .aw-topic-dropdown .aw-question-dropdown-list').hide();     
    }
    else
    {
        $('.aw-question-drodpwon .aw-topic-dropdown').hide();
    }
}

function hide_question_list(obj)
{
	setTimeout(function() {
		obj.next().hide();
	},300);
}

/*话题页面搜索下拉*/
function get_topic_question_list_data(data)
{
    $('.aw-question-drodpwon .aw-question-dropdown').css('width', $('.aw-topic-search').width());
    if (data.length >= 2)
    {
        $('.aw-question-drodpwon .aw-question-dropdown-list').html('');
        $.get(G_BASE_URL + '/search/ajax/search/?type=question&q=' + encodeURIComponent(data) + '&topic_ids=' + CONTENTS_TOPIC_ID, function (result)
        {
            if (result.length != 0)
            {
                $.each(result, function (i, a)
                {
                    $('.aw-topic-search .aw-question-dropdown-list').append(Hogan.compile(AW_TEMPLATE.questionDropdownList).render(
                    {
                        'url': G_BASE_URL + "/" + a.url,
                        'name': a.name
                    }));
                });
                $('.aw-question-drodpwon .aw-topic-dropdown').show().children().show();
                $('.aw-question-drodpwon .aw-topic-dropdown .title').hide();
                //关键词高亮
                $('.aw-topic-search .aw-question-dropdown-list li a').highText(data, 'span', 'active');

            }
            else
            {
                $('.aw-question-drodpwon .aw-topic-dropdown').show().children('.title').html(_t('没有找到相关结果')).show();
                $('.aw-question-drodpwon .aw-topic-dropdown .aw-question-dropdown-list').hide();
            }
        }, 'json');
    }
    else if (data.length > 0 && data.length < 2)
    {
        $('.aw-question-drodpwon .aw-topic-dropdown .aw-question-dropdown-list').hide();
        $('.aw-question-drodpwon .aw-topic-dropdown').show().children('.title').html(_t('请输入两个以上关键字...')).show();
    }
    else
    {
        $('.aw-question-drodpwon .aw-topic-dropdown').hide();
    }
}

/*话题页面搜索下拉隐藏*/
function hide_topic_question_list()
{
    $('.aw-question-drodpwon .aw-topic-dropdown').delay(300).hide(0);
}

var qDropdownTimeout;

/*快速发起问题搜索列表*/
function get_quick_publich_question_list(obj,data)
{
    clearTimeout(qDropdownTimeout);
    
    var _obj = obj;
    
    obj.next().css('width', obj.width() + 16);
    
    if (data.length >= 2)
    {
        $.get(G_BASE_URL + '/search/ajax/search/?type=question&q=' + encodeURIComponent(data) + '&limit-10', function (result)
        {
            if (result.length != 0)
            {
                obj.next().find('.aw-question-dropdown-list').html('');
                $.each(result, function (i, a)
                {
                    obj.next().find('.aw-question-dropdown-list').append(Hogan.compile(AW_TEMPLATE.questionDropdownList).render(
                    {
                        'url': G_BASE_URL + "/" + a.url,
                        'name': a.name
                    }));
                });
                obj.next().show().children().show();
                obj.next().find('.title').hide();
                //关键词高亮
                $('.aw-publish-box .aw-question-dropdown-list li a').highText(data, 'span', 'active');
                
            }
            else
            {
                obj.next().show().children('.title').html(_t('没有找到相关结果')).show();
                obj.next().find('.aw-question-dropdown-list').hide();
            }
            //两秒后自动隐藏
            qDropdownTimeout = setTimeout(function(){
                _obj.next().hide();
            },2000);
        }, 'json');
    }
    else if (data.length > 0 && data.length < 2)
    {
        obj.next().find('.aw-question-dropdown-list').hide();
        obj.next().show().children('.title').html(_t('请输入两个以上关键字...')).show();
    }
    else
    {
       obj.next().hide();
    }
}

/*邀请回复下拉菜单*/
function get_user_list_data(obj,data)
{
    
    obj.next().css({'width': obj.width() + 16});

    if (data.length >= 2)
    {
        $.get(G_BASE_URL + '/search/ajax/search/?type-user__q-' + encodeURIComponent(data) + '__limit-10', function (result)
        {
            if (result.length != 0)
            {
            	obj.next().find('.aw-user-dropdown-list').html('');
                $.each(result, function (i, a)
                {
                    obj.next().find('.aw-user-dropdown-list').append(Hogan.compile(AW_TEMPLATE.inviteDropdownList).render(
                    {
                        'uid': a.uid,
                        'name': a.name,
                        'img': a.detail.avatar_file
                    }));
                });
                obj.next().show().children().show();
                obj.next().find('.title').hide();
            }
            else
            {
                obj.next().show().children('.title').html(_t('没有找到相关结果')).show();
                obj.next().find('.aw-user-dropdown-list').hide();
            }
        }, 'json');
    }
    else if (data.length > 0 && data.length < 2)
    {
        obj.next().find('.aw-user-dropdown-list').hide();
        obj.next().show().children('.title').html(_t('请输入两个以上关键字...')).show();
    }
    else
    {
       obj.next().hide();
    }
}

/*邀请*/
function invite_user(obj,name,img)
{
	var _this = obj;
	
    $.post(G_BASE_URL + '/question/ajax/save_invite/',
    {
        'question_id': QUESTION_ID,
        'uid': obj.attr('data-id')
    }, function (result)
    {
        if (result.errno != -1)
        {
            $('.aw-side-bar-invite-box .aw-side-bar-invite-list').append(Hogan.compile(AW_TEMPLATE.inviteUserList).render(
            {
                'uid': _this.attr('data-id'),
                'img': img,
                'name': name
            }));
            $('.aw-side-bar-invite-replay .aw-message-tooltip').text(parseInt($('.aw-side-bar-invite-replay .aw-message-tooltip').text()) + 1);
            $('.aw-side-bar-invite-replay .aw-side-bar-invite-box div').eq(0).show();
        }
        else if (result.errno == -1)
        {
        	$('.aw-side-bar-invite-box .error-message').html('<em>'+result.err+"</em>").show();
        }
    }, 'json');
}

/*取消邀请*/
function disinvite_user(obj, uid)
{
    $.get(G_BASE_URL + '/question/ajax/cancel_question_invite/question_id-' + QUESTION_ID + "__recipients_uid-" + uid);

    $('.aw-side-bar-invite-replay .aw-message-tooltip').text(parseInt($('.aw-side-bar-invite-replay .aw-message-tooltip').text()) - 1);
}

// Modify by wecenter
function add_category_list (selecter, data, selected)
{
    $(selecter).append(Hogan.compile(AW_TEMPLATE.categoryList).render(
    {
        'items': data
    }));
}

/*动态插入下拉菜单模板*/
function add_dropdown_list(selecter, data, selected)
{
    $(selecter).append(Hogan.compile(AW_TEMPLATE.dropdownList).render(
    {
        'items': data
    }));

    $(selecter + ' .dropdown-menu li a').click(function ()
    {
        $('#aw-topic-tags-select').html($(this).text());
    });

    if (selected)
    {
        $(selecter + " .dropdown-menu li a[data-value='" + selected + "']").click();
    }
}

function _quick_publish_processer(result)
{
    if (typeof (result.errno) == 'undefined')
    {
        alert(result);
    }
    else if (result.errno != 1)
    {
        $('#quick_publish_error em').html(result.err);
        $('#quick_publish_error').fadeIn();
    }
    else
    {
        if (result.rsm && result.rsm.url)
        {
            window.location = decodeURIComponent(result.rsm.url);
        }
        else
        {
            window.location.reload();
        }
    }
}

/*修复focus时光标位置*/
function _fix_textarea_focus_cursor_position(elTextarea)
{
    if (/MSIE/.test(navigator.userAgent) || /Opera/.test(navigator.userAgent))
    {
        var rng = elTextarea.createTextRange();
        rng.text = elTextarea.value;
        rng.collapse(false);
    }
    else if (/WebKit/.test(navigator.userAgent))
    {
        elTextarea.select();
        window.getSelection().collapseToEnd();
    }
}

function verify_register_form(element)
{
    $(element).find(':text, :input').on({
        focus : function()
        {
            if ($(this).attr('tips') != 'undefined' || $(this).attr('tips') != '')
            {
                $(this).parent().append('<span class="aw-reg-tips">' + $(this).attr('tips') + '</span>');
            }
        },
        blur : function()
        {
            switch ($(this).attr('name'))
            {
                case 'user_name' : 
                    var _this = $(this);
                    $(this).parent().find('.aw-reg-tips').detach();
                    if ($(this).val().length >= 0 && $(this).val().length < 2)
                    {
                        $(this).parent().find('.aw-reg-tips').detach();
                        $(this).parent().append('<span class="aw-reg-tips aw-reg-err"><i class="aw-icon i-err"></i>' + $(this).attr('errortips') + '</span>');
                        return;
                    }
                    if ($(this).val().length > 17)
                    {
                        $(this).parent().find('.aw-reg-tips').detach();
                        $(this).parent().append('<span class="aw-reg-tips aw-reg-err"><i class="aw-icon i-err"></i>' + $(this).attr('errortips') + '</span>');
                        return;
                    }
                    else
                    {
                        $.get(G_BASE_URL + '/account/ajax/check_username/username' + '-' + encodeURIComponent($(this).val()), function (result)
                        {
                            if (result.errno == -1)
                            {
                                _this.parent().find('.aw-reg-tips').detach();
                                _this.parent().append('<span class="aw-reg-tips aw-reg-err"><i class="aw-icon i-err"></i>' + result.err + '</span>');
                            }
                            else
                            {
                                _this.parent().find('.aw-reg-tips').detach();
                                _this.parent().append('<span class="aw-reg-tips aw-reg-right"><i class="aw-icon i-followed"></i></span>');
                            }
                        }, 'json');
                    }
                    return;

                case 'email' : 
                    $(this).parent().find('.aw-reg-tips').detach();
                    var emailreg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
                    if (!emailreg.test($(this).val()))
                    {
                        $(this).parent().find('.aw-reg-tips').detach();
                        $(this).parent().append('<span class="aw-reg-tips aw-reg-err"><i class="aw-icon i-err"></i>' + $(this).attr('errortips') + '</span>');
                        return;
                    }
                    else
                    {
                        $(this).parent().find('.aw-reg-tips').detach();
                        $(this).parent().append('<span class="aw-reg-tips aw-reg-right"><i class="aw-icon i-followed"></i></span>');
                    }        
                    return;

                case 'password' :
                    $(this).parent().find('.aw-reg-tips').detach();
                    if ($(this).val().length >= 0 && $(this).val().length < 6)
                    {
                        $(this).parent().find('.aw-reg-tips').detach();
                        $(this).parent().append('<span class="aw-reg-tips aw-reg-err"><i class="aw-icon i-err"></i>' + $(this).attr('errortips') + '</span>');
                        return;
                    }
                    if ($(this).val().length > 17)
                    {
                        $(this).parent().find('.aw-reg-tips').detach();
                        $(this).parent().append('<span class="aw-reg-tips aw-reg-err"><i class="aw-icon i-err"></i>' + $(this).attr('errortips') + '</span>');
                        return;
                    }
                    else
                    {
                        $(this).parent().find('.aw-reg-tips').detach();
                        $(this).parent().append('<span class="aw-reg-tips aw-reg-right"><i class="aw-icon i-followed"></i></span>');
                    }
                    return;

            }
        }
    });
}

var at_user_lists_flag = 0, at_user_lists_index = 0;

function at_user_lists(selecter) {
    $(selecter).keyup(function (e) {
        init();
        var _this = $(this),
             flag = getCursorPosition($(this)[0]).start,
            key = e.which,
            cursor = $(this).get(0);
        if ($(this).val().charAt(flag - 1) == '@') {
            $('.content_cursor').html('').append($(this).val().substring(0, cursor.selectionStart).replace(/\n/g, '<br>') + '<b class="cursor_flag">flag</b>');
            if (!$('.aw-invite-dropdown')[0]) {
                $('#aw-ajax-box').append('<ul class="aw-invite-dropdown"></ul>');
            }
        } else {
            switch (key) {
                case 38:
                    if (at_user_lists_index == $('.aw-invite-dropdown li').length) {
                        at_user_lists_index--;
                    }
                    if (at_user_lists_index == 0) {
                        $('.aw-invite-dropdown li:last').addClass('active').siblings().removeClass('active');
                        at_user_lists_index = $('.aw-invite-dropdown li').length;
                    } else {
                        $('.aw-invite-dropdown li').eq(at_user_lists_index - 1).addClass('active').siblings().removeClass('active');
                        at_user_lists_index--;
                    }
                    break;
                case 40:
                    if (at_user_lists_flag == 0) {
                        $('.aw-invite-dropdown li:first').addClass('active').siblings().removeClass('active');
                        at_user_lists_flag = 1;
                    } else {
                        if (at_user_lists_index + 1 >= $('.aw-invite-dropdown li').length) {
                            $('.aw-invite-dropdown li:first').addClass('active').siblings().removeClass('active');
                            at_user_lists_index = 0;
                        } else {
                            $('.aw-invite-dropdown li').eq(at_user_lists_index + 1).addClass('active').siblings().removeClass('active');
                            at_user_lists_index++;
                        }
                    }
                    break;
                case 13:
                    $('.aw-invite-dropdown li').eq(at_user_lists_index).click();
                    break;
                default:
                    if ($('.aw-invite-dropdown')[0])
                    {
                        var ti = 0;
                        for (var i = flag; i--;) {
                            if ($(this).val().charAt(i) == "@") {
                                ti = i;
                                break;
                            }
                        }
                        if ($(this).val().substring(flag, ti).replace('@', '').match(/\s/)) {
                            $('.aw-invite-dropdown, .i-invite-triangle').addClass('hide');
                            return false;
                        }
                        $.get(G_BASE_URL + '/search/ajax/search/?type-user__q-' + encodeURIComponent($(this).val().substring(flag, ti).replace('@', '')) + '__limit-10', function (result) {
                            if ($('.aw-invite-dropdown')[0]) {
                                if (result.length != 0) {
                                    $('.aw-invite-dropdown').html('');
                                    $.each(result, function (i, a) {
                                        $('.aw-invite-dropdown').append('<li><img src="' + a.detail.avatar_file + '"/><a>' + a.name + '</a></li>')
                                    });
                                    display();
                                    $('.aw-invite-dropdown').removeClass('hide');
                                    $('.aw-invite-dropdown li').click(function () {
                                        _this.val(_this.val().substring(0, ti) + '@' + $(this).find('a').html() + " ").focus();
                                        _fix_textarea_focus_cursor_position(_this);
                                        at_user_lists_index = 0;
                                        at_user_lists_flag = 0;
                                        $('.aw-invite-dropdown').detach();
                                    });
                                } else {
                                    $('.aw-invite-dropdown').addClass('hide');
                                }
                            }
                            if (_this.val().length == 0) {
                                $('.aw-invite-dropdown').addClass('hide');
                            }
                        }, 'json');
                    }
            }
        }
        if (selecter == '#advanced_editor')
        {
            if ($(this).val() == '')
            {
                $('#markItUpPreviewFrames').html('');
            }
        }
    });

    $(selecter).keydown(function (e) {
        var key = e.which;
        if ($('.aw-invite-dropdown').is(':visible')) {
            if (key == 38 || key == 40 || key == 13) {
                return false;
            }
        }else
        {
            return true;
        }
    });

    //初始化插入定位符
    function init() {
        if (!$('.content_cursor')[0]) {
            $('#aw-ajax-box').append('<span class="content_cursor"></span>');
        }
        $('#aw-ajax-box').find('.content_cursor').css({
            'left': parseInt($(selecter).offset().left + parseInt($(selecter).css('padding-left')) + 2),
            'top': parseInt($(selecter).offset().top + parseInt($(selecter).css('padding-left')))
        });
    }

    //初始化列表和三角型
    function display() {
        $('.aw-invite-dropdown').css({
            'left': $('.cursor_flag').offset().left,
            'top': $('.cursor_flag').offset().top + 20
        });
    }
}

function getCursorPosition(textarea) {
    var rangeData = {
        text: "",
        start: 0,
        end: 0
    };
    textarea.focus();
    if (textarea.setSelectionRange) { // W3C
        rangeData.start = textarea.selectionStart;
        rangeData.end = textarea.selectionEnd;
        rangeData.text = (rangeData.start != rangeData.end) ? textarea.value.substring(rangeData.start, rangeData.end) : "";
    } else if (document.selection) { // IE
        var i,
            oS = document.selection.createRange(),
            // Don't: oR = textarea.createTextRange()
            oR = document.body.createTextRange();
        oR.moveToElementText(textarea);

        rangeData.text = oS.text;
        rangeData.bookmark = oS.getBookmark();

        // object.moveStart(sUnit [, iCount])
        // Return Value: Integer that returns the number of units moved.
        for (i = 0; oR.compareEndPoints('StartToStart', oS) < 0 && oS.moveStart("character", -1) !== 0; i++) {
            // Why? You can alert(textarea.value.length)
            if (textarea.value.charAt(i) == '\n') {
                i++;
            }
        }
        rangeData.start = i;
        rangeData.end = rangeData.text.length + rangeData.start;
    }

    return rangeData;
}

// Modify by wecenter
// 海淘首页展开阅读/收缩
function toggle_detail(element, type, scroll_id)
{
    switch (type)
    {
        case 'show' : 
            $(element).parents('li').find('.ht-dynamic-list-content-box').addClass('active').find('.aw-comment-upload-img-list, .aw-comment-upload-file-list').show();
            $(element).parents('li').find('.ht-dynamic-list-img').hide();
            if (!$(element).parents('li').find('.hide-details')[0])
            {
                $(element).after('<a class="hide-details pull-right" onclick="toggle_detail($(this), \'hide\', \''+ $(element).parents('li').attr('id') +'\');">收起<i class="aw-icon i-triangle-up"></i></a>');
            }else
            {
                $(element).parents('li').find('.hide-details').show();
            }
            $(element).hide();
        break;
        case 'hide' : 
            $(element).parents('li').find('.ht-dynamic-list-content-box').removeClass('active').find('.aw-comment-upload-img-list, .aw-comment-upload-img-list, .aw-comment-upload-file-list').hide();
            $(element).find('.aw-comment-upload-img-list').next().hide();
            $(element).parents('li').find('.ht-dynamic-list-img, .more-details').show(); 
            $(element).hide();
        break;
    }
    if (scroll_id)
    {
        $('html,body').animate({
            'scrollTop' : $('#'+scroll_id).offset().top
        });
    }
}

// Modify by wecenter
// 检测列表内容是否超出高度
function check_detail_height()
{
    $.each($('.ht-dynamic-list-content'),function(i, e){
        if ($(e).innerHeight() > 140)
        {
            if (!$(e).parents('li').find('.more-details')[0]) 
            {
                $(e).parents('li').find('.ht-dynamic-list-opera').append('<a class="more-details pull-right" onclick="toggle_detail($(this),\'show\');">展开阅读<i class="icon-chevron-down"></i></a>');
            }
            $(e).find('.aw-comment-upload-img-list, .aw-comment-upload-file-list').hide();
            $(e).find('.aw-comment-upload-img-list').next().hide(); 
        }
    });
}
// Modify by wecenter
function _DoLinks(text)
{
    var reg = /(((?:[^"\'(=(?&gt;)(?&lt;)])|^|\s*))?(\[([^\]]+)\])?\(?(http[s]?:\/\/[-a-zA-Z0-9@:;%_\+.~#?\\&/=]+)\)?/gm;
    text = text.replace(reg, function($0, $1, $2, $3, $4, $5){
      if ($1 == undefined)
      {
        $1 = '';
      }
      if ($3 == '' || $3 == undefined && $5 != '')
      {
        var str = $1 + '<a href="' + $5 + '" target="_blank">' + $5 + '<\/a>';
        return str;
      }else
      {
        var str = $1 + '<a href="' + $5 + '" target="_blank">' + $4 + '<\/a>';
        return str;
      }
    });

    return text;
}

// 中秋专题滚动功能
function topic_rolling()
{
    $('.ht-news-rolling ul li').eq(parseInt(Math.random() * $('.ht-news-rolling ul li').length)).fadeIn().siblings().fadeOut();
    setInterval(function()
    {
        var num = parseInt(Math.random() * $('.ht-news-rolling ul li').length);
        $('.ht-news-rolling ul li').eq(num).fadeIn().siblings().fadeOut();
    }, '5000');
}