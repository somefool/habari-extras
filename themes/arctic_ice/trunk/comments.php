<div id="comments">
	{hi:?post.comments.pingbacks.approved.count}
		<section id="pings">
		    <header>
			<h4>{hi:"{hi:post.comments.pingbacks.approved.count} Pingback" "{hi:post.comments.pingbacks.approved.count} Pingbacks" post.comments.pingbacks.approved.count} {hi:"to"} {hi:post.title}</h4>
		    </header>
			{hi:post.comments.pingbacks.approved}
			<article id="ping-{hi:id}">
				<div class="comment-content">{hi:content}</div>
				<footer class="ping-meta"><a href="{hi:url}" title="">{hi:name}</a></footer>
			</article>
			{/hi:post.comments.pingbacks.approved}
		</section>
	{/hi:?}

	{hi:?post.comments.comments.moderated.count > 0}
		<section id="commentlist">
		    <header>
			<h4 class="commentheading">{hi:"{hi:post.comments.comments.approved.count} Response" "{hi:post.comments.comments.approved.count} Responses" post.comments.comments.approved.count} {hi:"to"} {hi:post.title}</h4>
		    </header>
		    {hi:post.comments.comments.moderated}
			{hi:?status = Comment::STATUS_APPROVED}
			    <article id="comment-{hi:id}" class="comment">
			{hi:?else?}
			    <article id="comment-{hi:id}" class="comment-unapproved">
			{/hi:?}
			    <div class="comment_content">{hi:content_out}</div>
			    <footer class="comment-meta">#<a href="#comment-{hi:id}" class="counter" title="{hi:"Permanent Link to this Comment"}">{hi:id}</a> |
				<span class="commentauthor">{hi:"Comment by "}<a href="{hi:url}">{hi:name}</a></span>
				<span class="commentdate"> on <a href="#comment-{hi:id}" title="{hi:"Time of this comment"}">{hi:date_out}</a></span>
				{hi:?status = Comment::STATUS_UNAPPROVED}
				    <em> {hi:"In moderation"}</em>
				{/hi:?}
			    </footer>
			</article>
		    {/hi:post.comments.comments.moderated}
		</section>
	{/hi:?}

	{hi:?post.info.comments_disabled != 0}
	<section>
	    <header><h5 id="respond"><em>{hi:"What do you think?"}</em></h5></header>
		{hi:session:messages}
		{hi:@comment_form}
	</section>
	{/hi:?}
</div>
</article><!-- end post -->
<!-- end id comments -->