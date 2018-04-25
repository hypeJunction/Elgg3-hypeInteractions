<?php

namespace hypeJunction\Interactions;

return [
	/**
	 * SETTINGS
	 */
	'interactions:settings:max_comment_depth' => 'Comment tree depth',
	'interactions:settings:max_comment_depth:help' => 'How deep can replies to comments go? 1 = no replies, 5 = replies allowed up to 4th descendant',
	'interactions:settings:comment_form_position' => 'Position of the comments form',
	'interactions:settings:comment_form_position:help' => 'Where should the form be positioned in relation to the comments list?',
	'interactions:settings:comment_form_position:before' => 'Before the list',
	'interactions:settings:comment_form_position:after' => 'After the list',
	'interactions:settings:comments_order' => 'Comments ordering',
	'interactions:settings:comments_order:help' => 'In which order should the comments be displayed',
	'interactions:settings:comments_order:chronological' => 'Chronological',
	'interactions:settings:comments_order:reverse_chronological' => 'Reverse chronological',
	'interactions:settings:comments_load_style' => 'Viewing and loading of comments',
	'interactions:settings:comments_load_style:load_older' => 'Show newest comments with a link to load older comments',
	'interactions:settings:comments_load_style:load_newer' => 'Show older comments with a link to load newer comments',
	'interactions:settings:comments_limit' => 'Number of comments to show in a partial/summary listing',
	'interactions:settings:comments_load_limit' => 'Number of comments to show in a full listing',
	'interactions:settings:comments_visual_editor' => 'Enable visual editor by default',
	'interactions:settings:default_expand' => 'Expand comments tab',
	'interactions:settings:default_expand:help' => 'By default, comments are not shown in summary/river listings until user selects a tab',
	'interactions:settings:comment_sort' => 'Allow searching and filtering',
	'interactions:settings:comment_sort:help' => 'Add Search & Filter interface to individual comment blocks',
	'interactions:settings:enable_attachments' => 'Enable file attachments',
	'interactions:settings:enable_attachments:help' => 'Allow users to attach files to comments',
	'interactions:settings:enable_url_preview' => 'Enable URL previews',
	'interactions:settings:enable_url_preview:help' => 'Scrape URLs from comment text and add URL previews',
	'hypeInteractions:usersettings:title' => 'Comments',
	/**
	 * PAGES
	 */
	'interactions:comments:title' => 'Comments on %s',
	'interactions:likes:title' => 'People who like %s',
	'interactions:comments:edit:title' => 'Edit comment',
	/**
	 * COMMENT ENTITY
	 */
	'interactions:comment:create' => 'Comment',
	'interactions:reply:create' => 'Reply',
	'interactions:likes:before' => 'Like',
	'interactions:likes:after' => 'Unlike',
	'interactions:comment:subject' => 'comment by %s',
	'interactions:comment:body' => '%s<span>: </span>%s',
	/**
	 * NOTIFICATIONS
	 */
	'interactions:attachments:labelled' => 'Attachments: ',
	'interactions:response:email:subject' => '%s %s %s',
	'interactions:response:email:body' => "%s %s %s:

%s

To reply, click here:
%s

To view the original item, click here:
%s

To view %s's profile, click here:
%s

Please do not reply to this email.",
	'interactions:likes:notifications:subject' => '%s likes %s',
	'interactions:likes:notifications:body' =>
		'%s likes %s

See original post here:
%s

or view %s\'s profile here:
%s
',
	'interactions:post' => 'post',
	'interactions:comment' => 'comment',
	'interactions:comment:reply_to' => 'on %s',
	'interactions:comment:in_thread' => 'in %s',
	'interactions:ownership:own' => 'their %s',
	'interactions:ownership:your' => 'your %s',
	'interactions:ownership:owner' => '%s\'s %s',
	'interactions:action:comment:on:object' => 'commented on',
	'interactions:action:comment:on:object:comment' => 'replied to',
	'interactions:action:comment:on:object:discussion_reply' => 'replied to',
	'interactions:action:discussion_reply:on:object:discussion' => 'replied to',

	'interactions:object:comment' => 'comment',
	'interactions:object:discussion' => 'discussion topic',
	'interactions:object:discussion_reply' => 'discussion reply',
	'interactions:object:page_top' => 'page',
	'interactions:object:page' => 'page',
	'interactions:object:blog' => 'blog post',
	'interactions:object:file' => 'file',
	'interactions:object:hjwall' => 'wall post',
	'interactions:object:bookmarks' => 'bookmarked item',
	'interactions:group:default' => 'group',
	'interactions:user:default' => 'user',
	/**
	 * ACTIONS
	 */
	'interactions:detach' => 'Detach',
	'interactions:detach:success' => 'Item has been successfully detached',
	'interactions:detach:error' => 'Item could not be detached',
	/**
	 * RIVER
	 */
	'interactions:like:object:default' => '%s liked %s',
	'interactions:comments:no_results' => 'No comments have been made yet',
	'interactions:likes:no_results' => 'This item hasn\'t been liked yet',
	/**
	 * STREAM OBJECT
	 */
	'interactions:settings:actionable_stream_object' => 'Detached comment threads',
	'interactions:settings:actionable_stream_object:help' => '
		By default, only river items with an object can be commented on or liked.
		Here you can enable commenting for specific views, which do not have a commentable object,
		or where an object is a user or a group (e.g. User x has a new avatar),
		or where river comments should be kept separate from main object comments.
	',
	'interactions:river_object:title' => 'activity',
	'interactions:river_object:title_subject' => 'activity by %s',
];

