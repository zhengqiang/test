$(document).ready(function () {	
	bp_more_load(G_BASE_URL + '/people/ajax/likes/uid-' + PEOPLE_USER_ID, $('#bp_likes_more'), $('#contents_likes'));	// 参与的主题
	
	bp_more_load(G_BASE_URL + '/people/ajax/user_actions/uid-' + PEOPLE_USER_ID + '__actions-201', $('#bp_user_actions_answers_more'), $('#contents_user_actions_answers'));	// 参与的主题
			  
	bp_more_load(G_BASE_URL + '/people/ajax/user_actions/uid-' + PEOPLE_USER_ID + '__actions-101', $('#bp_user_actions_questions_more'), $('#contents_user_actions_questions'));	// 发起的主题
		
	bp_more_load(G_BASE_URL + '/people/ajax/topics/uid-' + PEOPLE_USER_ID, $('#bp_user_topics_more'), $('#contents_user_topics'));	// 标签
	
	bp_more_load(G_BASE_URL + '/people/ajax/follows/type-follows__uid-' + PEOPLE_USER_ID, $('#bp_user_follows_more'), $('#contents_user_follows'));	// 关注
	
	if (window.location.hash)
	{
		if (document.getElementById(window.location.hash.replace('#', '')))
		{
			document.getElementById(window.location.hash.replace('#', '')).click();
		}
	}
});