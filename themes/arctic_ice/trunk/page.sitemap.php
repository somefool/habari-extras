{hi:display:header}
<!--begin content-->
<div id="content" >
	<h1><a href="{hi:post.permalink}" rel="bookmark" title="{hi:post.title}">{hi:option:title} {hi:post.title_out}</a></h1>

	<section>
	<h2>{hi:"All Pages"}</h2>
	{hi:pages}
		<p><a href="{hi:permalink}" rel="bookmark" title="{hi:title}">{hi:title}</a></p>
	{/hi:pages}
	</section>
	<section>
	<h2>{hi:"All Posts"}</h2>
	{hi:all_entries}
		<p><a href="{hi:permalink}" rel="bookmark" title="{hi:title}">{hi:title}</a> ( {hi:comments.approved.count} )</p>
	{/hi:all_entries}
	</section>
</div>
<!--end content content-->
{hi:display:sidebar}
{hi:display:footer}