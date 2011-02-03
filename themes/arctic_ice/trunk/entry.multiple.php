{hi:display:header}
<div id="content">
	<!--begin loop-->
	{hi:posts}
		<article id="post-{hi:id}" class="{hi:statusname}">
			<header>
				<h2><a href="{hi:permalink}" rel="bookmark" title="{hi:title}">{hi:title_out}</a></h2>
				<time class="pubmeta" datetime="{hi:pubdate_datetime}" pubdate >{hi:pubdate_out}</time>
				{hi:?loggedin}
					<div class="edit"><a href="{hi:editlink}" title="{hi:"Edit post"}">{hi:"Edit"}</a></div>
				{/hi:?}
			</header>
			<div class="entry">
				{hi:content_excerpt}
			</div>
			<footer class="meta">
				{hi:?count(tags)}
					<div class="tags">{hi:"Tagged:"} {hi:tags_out}</div>
				{/hi:?}
				<div class="commentCount"><a href="{hi:permalink}#comments" title="{hi:"Comments on this post"}">{hi:"{hi:comments.approved.count} Comment" "{hi:comments.approved.count} Comments" comments.approved.count}</a></div>
			</footer><br>
		</article>
	{/hi:posts}
	<!--end loop-->

	<div id="pagenav">
		{hi:@prev_page_link} {hi:@page_selector} {hi:@next_page_link}
	</div>
</div>
<!-- #content -->
{hi:display:sidebar}
{hi:display:footer}
