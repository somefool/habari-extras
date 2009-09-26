{hi:display:header}
<!--begin content-->
<div id="content">
	<div id="post-{hi:post.id}" class="{hi:post.statusname}">
		<h1><a href="{hi:post.permalink}" rel="bookmark" title="{hi:post.title}">{hi:post.title_out}</a></h1>

		{hi:?loggedin}
			<div class="edit">
				<a href="{hi:post.editlink}" title="{hi:"Edit post"}">{hi:"Edit page"}</a>
			</div>
		{/hi:?}

		<div class="entry">
			{hi:post.content_out}
		</div>
	</div>
</div>
<!--end content content-->
{hi:display:sidebar}
{hi:display:footer}