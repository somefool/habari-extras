{hi:display:header}
<!--begin content-->
<div id="content">
	<div class="postnav">
		<div class="postnext">{hi:@next_post_link}</div>
		<div class="postprev">{hi:@prev_post_link}</div>
	</div>

	<article id="post-{hi:post.id}" class="{hi:post.statusname}">
		<header>
		<h1><a href="{hi:post.permalink}" rel="bookmark" title="{hi:post.title}">{hi:post.title_out}</a></h1>
		<div class="pubMeta">{hi:"Posted by"} {hi:post.author.displayname} {hi:"on"} {hi:post.pubdate_out}</div>
		{hi:?loggedin}
			<div class="edit"><a href="{hi:post.editlink}" title="{hi:"Edit post"}">{hi:"Edit post"}</a></div>
		{/hi:?}
		</header>
		<div class="entry">
			{hi:post.content_out}
		</div><!-- end entry -->
		<footer class="entryMeta">
		<p>
			{hi:?count(post.tags)}
				{hi:"This entry is filed under"} {hi:post.tags_out}. 
			{/hi:?}

			{hi:"You can follow any responses to this entry through the"}
			<a href="{hi:post.comment_feed_link}"> {hi:"feed"}</a>
			{hi:?post.info.comments_disabled = 0}
				{hi:"or leave you own"} <a href="#comments_form">{hi:"comment"}</a>.
			{hi:?else?}
				. {hi:"New comments are currently closed."}
			{/hi:?}
		</p>
		</footer>
	<!-- end entryMeta -->

	{hi:display:comments}
</div><!--end content-->
{hi:display:sidebar}
{hi:display:footer}
