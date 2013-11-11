var ATTACH_ACCESS_KEY
var ITEM_IDS;
var COMMENT_UNFOLD;
var QUESTION_ID;
var UNINTERESTED_COUNT;

$(document).ready(function () {
	if ($('.aw-vote-count').length)
	{
		$.each($('.aw-vote-count'), function (i, e)
		{
			$(e).click();
		});
	}
	
	if ($('#c_log_list').attr('id'))
	{
		bp_more_load(G_BASE_URL + '/question/ajax/log/id-' + QUESTION_ID, $('#bp_log_more'), $('#c_log_list'));
	}
	else
	{
		ITEM_IDS = ITEM_IDS.split(',');
	
		if ($("#captcha").attr('id'))
		{
			$("#captcha").click();
		}
		
		init_fileuploader('file_uploader_answer', G_BASE_URL + '/publish/ajax/attach_upload/id-answer__attach_access_key-' + ATTACH_ACCESS_KEY);
		
		// 折叠回复
		$.each($('div.aw-item'), function (i, e) {
			if ($(this).attr('uninterested_count') >= UNINTERESTED_COUNT || $(this).attr('force_fold') == 1)
			{
				$('#uninterested_answers_list').append('<div class="aw-item">' + $(e).html() + '</div>');
				
				$(e).remove();
			}
		});
		
		if ($('#uninterested_answers_list div.aw-item').length > 0)
		{
			$('#load_uninterested_answers span.hide_answers_count').html($('#uninterested_answers_list div.aw-item').length);
			$('#load_uninterested_answers').fadeIn();
		}
		
		// 自动保存草稿
		if ($('textarea#advanced_editor').length)
		{
			$('textarea#advanced_editor').bind('blur', function() {
				if ($(this).val() != '')
				{
					$.post(G_BASE_URL + '/account/ajax/save_draft/item_id-' + QUESTION_ID + '__type-answer', 'message=' + $(this).val(), function (result) {
						$('#answer_content_message').html(result.err + ' <a href="#" onclick="$(\'textarea#advanced_editor\').attr(\'value\', \'\'); delete_draft(QUESTION_ID, \'answer\'); $(this).parent().html(\' \'); return false;">' + _t('删除草稿') + '</a>');
					}, 'json');
				}
			});
		}
		
		if (COMMENT_UNFOLD == 1 || COMMENT_UNFOLD == 'all')
		{
			$('.aw-question-detail-meta .aw-add-comment').click();
		}
		
		// 回复高亮
		$.each(ITEM_IDS, function (i, answer_id) {
			if ($('#answer_list_' + answer_id).attr('id'))
			{
				if (COMMENT_UNFOLD == 2 || COMMENT_UNFOLD == 'all')
				{
					$('#answer_list_' + answer_id).find('.aw-add-comment').click();
				}
				
				hightlight($('#answer_list_' + answer_id), 'active');
			}
		});
	}
	
	/* 关注用户列表 */
	$.get(G_BASE_URL + '/question/ajax/get_focus_users/question_id-' + QUESTION_ID, function (data) {
		$.each(data, function (i, d) {
			if (d['uid'])
			{
				$('#focus_users').append('<a href="' + d['url'] + '"><img src="' + d['avatar_file'] + '" class="aw-user-name" data-id="' + d['uid'] + '" alt="' + d['user_name'] + '" /></a> ');
			}
			else
			{
				$('#focus_users').append('<a href="javascript:;" title="' + _t('匿名用户') + '"><img src="' + d['avatar_file'] + '" alt="' + _t('匿名用户') + '" /></a> ');
			}
		});
	}, 'json');

	/* 站内邀请回复操作 */
    $('.aw-side-bar-invite-replay .aw-side-bar-mod-body p a').click(function ()
    {
        if ($('.aw-side-bar-invite-box .aw-item').eq($(this).index()).is(':hidden'))
        {
            $('.aw-side-bar-invite-box .aw-item').hide();
            $('.aw-side-bar-invite-box .aw-item').eq($(this).index()).show();
        }
        else
        {
            $('.aw-side-bar-invite-box .aw-item').eq($(this).index()).hide();
        }
    });

    /* 回复内容超链接新窗口打开 */
    $('.markitup-box a').attr('target','_blank');

    at_user_lists('#answer_content');

});

function answer_force_fold(answer_id, element)
{
	$.post(G_BASE_URL + '/question/ajax/answer_force_fold/', 'answer_id=' + answer_id, function (result) {
		if (result.errno != 1)
		{
			$.alert(result.err);
		}
		else if (result.errno == 1)
		{
			if (result.rsm.action == 'fold')
			{
				$(element).html($(element).html().replace(_t('折叠'), _t('撤消折叠')));
			}
			else
			{
				$(element).html($(element).html().replace(_t('撤消折叠'), _t('折叠')));
			}
		}
	}, 'json');
}

function one_click_add_topic(click_element, topic_title, question_id)
{
	$.post(G_BASE_URL + '/question/ajax/save_topic/question_id-' + question_id, 'topic_title=' + topic_title, function (result) {
		if (result.err)
		{
			$.alert(result.err);
		}
		else
		{
			$('.aw-topic-editor').prepend('<a href="topic/' + result.rsm.topic_id + '" class="aw-topic-name"><span>' + topic_title + '</span></a>').hide().fadeIn();
			
			$(click_element).hide();
		}
	}, 'json');
}

//邀请请回答问题点击事件
$(document).on('click', '.aw-side-bar-invite-box .aw-user-dropdown-list a', function () {
    invite_user($(this),$(this).text(),$(this).find('.img').attr('src'));
});