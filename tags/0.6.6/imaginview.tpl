<!-- START BLOCK : table_start -->
<table style="width:100%;overflow:never"><tr>
<!-- END BLOCK : table_start -->

<!-- START BLOCK : thumb_nail -->
<td style="width:{td_width}%;text-align:center">
<div style="height:{thumb_max}px">
<a href="{PHP_SELF}?{tn_link}">
<img src="{thumbnail}" style="border:0px;"></a><br />
</div>
<span style="font-size:12px;font-family:Verdana;">{tn_caption}</span>
<br /><br />
</td>

<!-- START BLOCK : new_row -->
</tr><tr>


<!-- END BLOCK : new_row -->
<!-- END BLOCK : thumb_nail -->

<!-- START BLOCK : image -->
<div style="text-align:center;padding:5px">
<a href="{img_src}">
<img src="{img_src}" style="border:1px solid silver" width="{img_width}" height="{img_height}"></a>
<br />
<span style="font-size:14px;font-family:Verdana;">
{img_nice_name}<br />
Original Size: {img_orig_size}
<pre style="font-size:12px;font-family:Verdana;">{img_description}</pre>
</span>
</div>
<!-- END BLOCK : image -->

<!-- START BLOCK : img_exif -->
</tr><tr>
<td>
exif
</td>
<!-- END BLOCK : img_exif -->

<!-- START BLOCK : table_end -->
</tr></table>
<!-- END BLOCK : table_end -->

<!-- START BLOCK : navigation -->
<div style="padding:5px;text-align:center">
	<span style="float:left;width:33%">
	<!-- START BLOCK : go_prev -->
	<a href="{PHP_SELF}?{action}={fargument}{floffset}">&lt;&lt; First</a>
	<a href="{PHP_SELF}?{action}={argument}{loffset}">&lt; Prev</a>
	<!-- END BLOCK : go_prev -->

	<!-- START BLOCK : no_prev -->
	&lt; Prev
	<!-- END BLOCK : no_prev -->
	</span>


	<span style="float:left;width:33%">
	<!-- START BLOCK : go_home -->
	<a href="{home_link}">Up</a>
	<!-- END BLOCK : go_home -->

	<!-- START BLOCK : no_home -->
	Up
	<!-- END BLOCK : no_home -->
	</span>


	<span style="float:left;width:33%;padding-right:3px;">
	<!-- START BLOCK : go_next -->
	<a href="{PHP_SELF}?{action}={argument}{loffset}">Next &gt;</a>
	<a href="{PHP_SELF}?{action}={fargument}{floffset}">Last &gt;&gt;</a>
	<!-- END BLOCK : go_next -->

	<!-- START BLOCK : no_next -->
	Next &gt;
	<!-- END BLOCK : no_next -->
	</span>
</div>
<!-- END BLOCK : navigation -->

<!-- Please leave the next line in -->
<div style="text-align:center;margin-top:20px;">Powered by <a href="http://www.nullsense.net/development/imaginview/">ImaginView v{iv_version}</a>.</div>
