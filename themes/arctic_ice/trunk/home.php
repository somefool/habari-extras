{hi:display:header}
<!--begin content-->
<div id="content">
	{hi:posts}
		<article id="post-{hi:id}" class="{hi:statusname}">
			<header class="pubMeta">
				<h1><a href="{hi:permalink}" rel="bookmark" title="{hi:title}">{hi:title_out}</a></h1>
				<time class="pubmeta" datetime="{hi:pubdate_datetime}" pubdate >{hi:pubdate_out}</time>
				{hi:?loggedin}
					<div class="edit"><a href="{hi:editlink}" title="{hi:"Edit post"}" >{hi:"Edit Post"}</a></div>
				{/hi:?}
			</header>

			<div class="entry">
				{hi:?posts_index = 0}
					{hi:content_out}
				{hi:?else?}
					{hi:content_excerpt}
				{/hi:?}
			</div>

			<footer class="meta">
				<div class="tags">
					{hi:?count(tags)}
						{hi:"Filed under"} {hi:tags_out}
					{/hi:?}
				</div>
				<div class="commentCount">
					<a href="{hi:permalink}#comments" title="{hi:"Comments on this post"}">{hi:"{hi:comments.approved.count} Comment" "{hi:comments.approved.count} Comments" comments.approved.count}</a>
				</div>
			</footer>
		</article>
	{/hi:posts}

	<nav id="pagenav">
		{hi:@prev_page_link} {hi:@page_selector} {hi:@next_page_link}
	</nav>
</div>
<!--end content-->
{hi:display:sidebar}
{hi:display:footer}
