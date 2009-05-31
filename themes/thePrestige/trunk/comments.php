<div id="comments">
	{hi:?post.comments.pingbacks.approved.count > 0}
		<div id="pings">
			<h3>{hi:"{hi:post.comments.pingbacks.approved.count} Pingback" "{hi:post.comments.pingbacks.approved.count} Pingbacks" post.comments.pingbacks.approved.count} {hi:"to"} {hi:post.title}</h3>
			<ul id="pings-list">
			{hi:post.comments.pingbacks.approved}
				<li id="ping-{hi:id}">
					<div class="comment-content">
						{hi:content}
					</div>
					<div class="ping-meta"><a href="{hi:url}" title="">{hi:name}</a></div>
				</li>
			{/hi:post.comments.pingbacks.approved}
			</ul>
		</div>
	{/hi:?}

	<h3 class="commentheading">{hi:"{post.comments.comments.approved.count} Response" "{hi:post.comments.comments.approved.count} Responses" post.comments.comments.approved.countesponse} {hi:"to"} {hi:post.title}</h3>
	{hi:?post.comments.comments.moderated.count > 0}
		<ul id="commentlist">
			{hi:post.comments.comments.moderated}

			{hi:?status = Comment::STATUS_UNAPPROVED}
				<li id="comment-{hi:id}" class="comment-unapproved">
			{hi:?else?}
				<li id="comment-{hi:id}" class="comment">
			{/hi:?}
				<div class="comment-content">
					{hi:content_out}
				</div>
				<div class="comment-meta">#<a href="#comment-{hi:id}" class="counter" title="{hi:"Permanent Link to this Comment"}">{hi:id}</a> | 
					<span class="commentauthor">{hi:"Comment by "}<a href="{hi:url}">{hi:name}</a></span>
					<span class="commentdate"> {hi:"on"} <a href="#comment-{hi:id}" title="{hi:"Time of this comment"}">{hi:date_out}</a></span>
					{hi:?status = Comment::STATUS_UNAPPROVED}
						<em> {hi:"In moderation"}</em>
					{/hi:?}
				</div>
			</li>
			{/hi:post.comments.comments.moderated}
			
		</ul>
		<div class="clear"></div>
	{/hi:?}


	{hi:?post.info.comments_disabled != 0}
	
		{hi:@comment_form}
	{/hi:?}
</div>
<!-- end id comments -->