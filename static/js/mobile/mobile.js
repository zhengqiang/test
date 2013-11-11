jQuery.fn.extend({
	highText : function (searchWords, htmlTag, tagClass) {
		return this.each(function() {
			$(this).html(function high(replaced, search, htmlTag, tagClass) {
				var pattarn = search.replace(/\b(\w+)\b/g, "($1)").replace(/\s+/g, "|");
				
				return replaced.replace(new RegExp(pattarn, "ig"), function(keyword) {
					return $("<" + htmlTag + " class=" + tagClass + ">" + keyword + "</" + htmlTag + ">").outerHTML();
				});
			}($(this).text(), searchWords, htmlTag, tagClass));
		});
	},
	outerHTML : function(s) {
		return (s) ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
	}
});

var loading_timer;
var loading_bg_count = 12;

$.loading = function (s) {
	if (s == 'show')
	{
		$('#loading').fadeIn();
	
		loading_timer = setInterval(function () {
			loading_bg_count = loading_bg_count - 1;
			
			$('#loading_box').css('background-position', '0px ' + loading_bg_count * 40 + 'px');
			
			if (loading_bg_count == 1)
			{
				loading_bg_count = 12;
			}
		}, 100);
	}
	else
	{
		$('#loading').fadeOut();
	
		clearInterval(loading_timer);
	}
};

function _t(string, replace)
{	
	if (typeof(aws_lang) == 'undefined')
	{
		if (replace)
		{
			string = string.replace('%s', replace);
		}
		
		return string;
	}
	
	if (aws_lang[string])
	{
		string = aws_lang[string];
		
		if (replace)
		{
			string = string.replace('%s', replace);
		}
		
		return string;
	}	
}

var _list_view_pages = new Array();

function load_list_view(url, list_view, ul_button, start_page, callback_func)
{	
	if (!ul_button.attr('id'))
	{
		return false;
	}
	
	if (!start_page)
	{
		start_page = 0
	}
	
	_list_view_pages[ul_button.attr('id')] = start_page;
	
	ul_button.unbind('click');
	
	ul_button.bind('click', function () {
		var _this = this;
			
		$.loading('show');
	
		$(_this).addClass('disabled');
			
		$.get(url + '__page-' + _list_view_pages[ul_button.attr('id')], function (response)
		{			
			if ($.trim(response) != '')
			{
				if (_list_view_pages[ul_button.attr('id')] == start_page)
				{
					list_view.html(response);
				}
				else
				{
					list_view.append(response);
				}
					
				_list_view_pages[ul_button.attr('id')]++; 
				
				$(_this).removeClass('disabled');
			}
			else
			{
				if ($.trim(list_view.html()) == '')
				{
					list_view.append('<p align="center">没有相关内容</p>');
				}
							
				$(_this).unbind('click').bind('click', function () { return false; });
			}
				
			$.loading('hide');
			
			if (callback_func != null)
			{
				callback_func();
			}
		});
			
		return false;
	});
	
	ul_button.click();
}

function ajax_post(formEl, processer)	// 表单对象，用 jQuery 获取，回调函数名
{	
	if (typeof(processer) != 'function')
	{
		processer = _ajax_post_processer;
	}
	
	var custom_data = {
		_post_type:'ajax',
		_is_mobile:'true'
	};
	
	$.loading('show');
	
	formEl.ajaxSubmit({
		dataType: 'json',
		data: custom_data,
		success: processer,
		error:	function (error) { if ($.trim(error.responseText) != '') { $.loading('hide'); alert(_t('发生错误, 返回的信息:') + ' ' + error.responseText); } }
	});
}

function _ajax_post_processer(result)
{
	$.loading('hide');
	
	if (typeof(result.errno) == 'undefined')
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

function ajax_request(url, params)
{
	$.loading('show');
	
	if (params)
	{
		$.post(url, params, function (result) {
			$.loading('hide');
			
			if (result.err)
			{
				alert(result.err);
			}
			else if (result.rsm && result.rsm.url)
			{
				window.location = decodeURIComponent(result.rsm.url);
			}
			else
			{
				window.location.reload();
			}
		}, 'json').error(function (error) { if ($.trim(error.responseText) != '') {  $.loading('hide'); alert(_t('发生错误, 返回的信息:') + ' ' + error.responseText); } });
	}
	else
	{
		$.get(url, function (result) {
			$.loading('hide');
			
			if (result.err)
			{
				alert(result.err);
			}
			else if (result.rsm && result.rsm.url)
			{
				window.location = decodeURIComponent(result.rsm.url);
			}
			else
			{
				window.location.reload();
			}
		}, 'json').error(function (error) { if ($.trim(error.responseText) != '') { $.loading('hide'); alert(_t('发生错误, 返回的信息:') + ' ' + error.responseText); } });
	}
	
	return false;
}

function focus_question(el, text_el, question_id)
{
	if (el.hasClass('aw-active'))
	{
		text_el.html(_t('取消关注'));
		
		el.removeClass('aw-active');
	}
	else
	{
		text_el.html(_t('关注'));
		
		el.addClass('aw-active');
	}
	
	$.loading('show');
	
	$.get(G_BASE_URL + '/question/ajax/focus/question_id-' + question_id, function (data)
	{
		$.loading('hide');
		
		if (data.errno != 1)
		{
			if (data.err)
			{
				alert(data.err);
			}
			
			if (data.rsm.url)
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
	}, 'json');
}

function focus_topic(el, text_el, topic_id)
{
	if (el.hasClass('aw-active'))
	{
		text_el.html(_t('取消关注'));
		
		el.addClass('aw-active');
	}
	else
	{
		text_el.html(_t('关注'));

		el.removeClass('aw-active');
	}
	
	$.loading('show');
	
	$.get(G_BASE_URL + '/topic/ajax/focus_topic/topic_id-' + topic_id, function (data)
	{
		$.loading('hide');
		
		if (data.errno != 1)
		{
			if (data.err)
			{
				alert(data.err);
			}
			
			if (data.rsm.url)
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
	}, 'json');
}

function follow_people(el, text_el, uid)
{
	if (el.hasClass('aw-active'))
	{
		text_el.html(_t('取消关注'));
		
		el.addClass('aw-active');
	}
	else
	{
		text_el.html(_t('关注'));
		
		el.removeClass('aw-active');
	}
	
	$.loading('show');
	
	$.get(G_BASE_URL + '/follow/ajax/follow_people/uid-' + uid, function (data)
	{
		$.loading('hide');
		
		if (data.errno != 1)
		{
			if (data.err)
			{
				alert(data.err);
			}
			
			if (data.rsm.url)
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
	}, 'json');
}

function answer_user_rate(answer_id, type, element)
{
	$.loading('show');
	
	$.post(G_BASE_URL + '/question/ajax/question_answer_rate/', 'type=' + type + '&answer_id=' + answer_id, function (result) {
		
		$.loading('hide');
		
		if (result.errno != 1)
		{
			alert(result.err);
		}
		else if (result.errno == 1)
		{
			switch (type)
			{
				case 'thanks':
					if (result.rsm.action == 'add')
					{
						$(element).find('span.ui-btn-text').html(_t('已感谢'));
						$(element).removeAttr('onclick');
					}
					else
					{
						$(element).html(_t('感谢'));
					}
				break;
				
				case 'uninterested':
					if (result.rsm.action == 'add')
					{
						$(element).find('span.ui-btn-text').html(_t('撤消没有帮助'));
					}
					else
					{
						$(element).find('span.ui-btn-text').html(_t('没有帮助'));
					}
				break;
			}
		}
	}, 'json');
}

function _ajax_post_confirm_processer(result)
{
	$.loading('hide');
	
	if (typeof(result.errno) == 'undefined')
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

// Modify by wecenter
function question_thanks(question_id, element)
{
    $.post(G_BASE_URL + '/question/ajax/question_thanks/', 'question_id=' + question_id, function (result)
    {
        if (result.errno != 1)
        {
            alert(result.err);
        }
        else if (result.rsm.action == 'add')
        {
           $(element).find('.icon-heart-empty').addClass('active');
           $(element).find('b').html(parseInt($(element).find('b').html()) + 1);
        }
        // else
        // {
        //      $(element).html($(element).html().replace(parseInt($(element).text()), (parseInt($(element).text()) - 1)));
        //      $(element).find('i').removeClass('active');
        // }
    }, 'json');
}

// Modify by wecenter
function agreeVote(element, user_name, answer_id)
{
	$.post(G_BASE_URL + '/question/ajax/answer_vote/', 'answer_id=' + answer_id + '&value=1', function (result) {});
	
    //判断是否投票过
    if ($(element).hasClass('active'))
    {
        // $.each($(element).parents('.aw-item').find('.aw-user-name'), function (i, e)
        // {
        //     if ($(e).html() == user_name)
        //     {
        //         if ($(e).prev())
        //         {
        //             $(e).prev().remove();
        //         }
        //         else
        //         {
        //             $(e).next().remove();
        //         }

        //         $(e).remove();
        //     }
        // });

        $(element).removeClass('active');

        
        if (parseInt($(element).find('b').html()) != 0)
        {
            $(element).find('b').html($(element).find('b').html()-1);
        }

        // if ($(element).parents('.aw-item').find('.aw-agree-by a').length == 0)
        // {
        //     $(element).parents('.aw-item').find('.aw-agree-by').hide();
        // }
    }
    else
    {
        // 判断是否第一个投票
        // if ($(element).parents('.aw-item').find('.aw-agree-by .aw-user-name').length == 0)
        // {
        //     $(element).parents('.aw-item').find('.aw-agree-by').append('<a class="aw-user-name">' + user_name + '</a>');
        // }
        // else
        // {
        //     //插入动画效果
        //     $(element).parents('.aw-item').find('.aw-agree-by').append('<em>、</em><a class="aw-user-name">' + user_name + '</a>');
        // }

        $(element).find('b').html(parseInt($(element).find('b').html())+1);

        $(element).parents('.aw-item').find('.aw-agree-by').show();
        $(element).parents('.ht-comment-opera').find('a.active').removeClass('active');
        $(element).addClass('active');
    }
}

//反对投票

function disagreeVote(element, user_name, answer_id)
{
    $.post(G_BASE_URL + '/question/ajax/answer_vote/', 'answer_id=' + answer_id + '&value=-1', function (result) {});

    //判断是否投票过
    if (!$(element).hasClass('active'))
    {
        //删除赞同操作
        $.each($(element).parents('.aw-item').find('.aw-user-name'), function (i, e)
        {
            if ($(e).html() == user_name)
            {
                if ($(e).prev())
                {
                    $(e).prev().remove();
                }
                else
                {
                    $(e).next().remove();
                }

                $(e).remove();
            }
        });

        if (parseInt($(element).parents('.ht-comment-opera').find('b').html()) != 0)
        {
            $(element).parents('.ht-comment-opera').find('b').html(parseInt($(element).parents('.ht-comment-opera').find('b').html())-1);
        }
        $(element).parents('.ht-comment-opera').find('a.active').removeClass('active');
        $(element).addClass('active');

        // if ($(element).parents('.aw-item').find('.aw-agree-by a').length == 0)
        // {
        //     $(element).parents('.aw-item').find('.aw-agree-by').hide();
        // }
    }
    else
    {
        $(element).removeClass('active');
    }
}