{hi:display:header}
<!--begin content-->
<div id="content">
	{hi:posts}
		<div id="post-{hi:id}" class="{hi:statusname}">
			<h1><a href="{hi:permalink}" rel="bookmark" title="{hi:title}">{hi:title_out}</a></h1>

			<div class="pubMeta">{hi:pubdate_out}</div>

			{hi:?loggedin}
				<div class="edit">
					<a href="{hi:editlink}" title="{hi:"Edit post"}" >{hi:"Edit Post"}</a>
				</div>
			{/hi:?}

			<div class="entry">
				{hi:?posts_index = 0}
					{hi:content_out}
				{hi:?else?}
					{hi:content_excerpt}
				{/hi:?}
			</div>

			<div class="meta">
				<div class="tags">
					{hi:?count(tags)}
						{hi:"Filed under"} {hi:tags_out}
					{/hi:?}
				</div>
				<div class="commentCount">
					<a href="{hi:permalink}#comments" title="{hi:"Comments on this post"}">{hi:"{hi:comments.approved.count} Comment" "{hi:comments.approved.count} Comments" comments.approved.count}</a>
				</div>
			</div>
		</div>
	{/hi:posts}

	<div id="pagenav">
		{hi:@prev_page_link} {hi:@page_selector} {hi:@next_page_link}
	</div>
</div>
<!--end content-->
{hi:display:sidebar}
{hi:display:footer}
