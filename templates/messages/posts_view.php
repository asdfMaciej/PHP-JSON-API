<div class="post noborder">
<a class="post" href="">
<div class="post_rest">
<div class="post_title"><?=$post->title?></div>
<div class="post_date"><b><?=$post->author_nick?></b> - <?=$post->create_timestamp?> (<?=$post->class_name?>)<br></div>
</div>
</a>

<div class="post_iconr">
<span>
<a style="color: #131516; float: left; text-decoration: none;" href="/posts/delete/<?=$post->id?>"><?="&#x2620"?></a>
<a style="color: #131516; float:right; text-decoration: none;" href="/posts/edit/<?=$post->id?>"><?="&#x270e"?></a>
</span>
</div>
</div>
<div class="post_text">
<?=$post->text_formatted?>
</div>