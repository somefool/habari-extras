{hi:display:header}
<!--begin content-->
<div id="content">
	<!--begin loop-->
	<h1>{hi:"Posts Tagged with"} {hi:tag}</h1>
	{hi:posts}
		<article id="post-{hi:id}" class="{hi:statusname}">
			<header>
				<h2><a href="{hi:permalink}" rel="bookmark" title="{hi:title}">{hi:title_out}</a></h2>
				<div class="pubMeta">{hi:pubdate_out}</div>
				{hi:?loggedin}
					<div class="edit">
					<a href="{hi:editlink}" title="{hi:"Edit post"}">{hi:"Edit post"}</a>
					</div>
				{/hi:?}
			</header>
			<div class="entry">
				{hi:content_excerpt}
			</div>

			<footer class="meta">
				{hi:?count(tags)}
					<div class="tags">{hi:"Tagged:"} {hi:tags_out}</div>
				{/hi:?}
				<div class="commentCount"><a href="{hi:permalink}#commentlist" title="{hi:"Comments on this post"}">{hi:"{hi:comments.approved.count} Comment" "{hi:comments.approved.count} Comments" comments.approved.count}</a></div>
			</footer>
		</article>
	{/hi:posts}
	<!--end loop-->

	<?php if ( count( $posts ) === 0 ) { ?>
		<h5>{hi:"There were no articles found tagged"} <em>{hi:tag}</em>.</h5>
	<?php } ?>

	<div id="pagenav">
		{hi:@prev_page_link} {hi:@page_selector} {hi:@next_page_link}
	</div>
</div>
<!--end content-->
{hi:display:sidebar}
{hi:display:footer}