<table border="0" cellpadding="0" cellspacing="0" class="ttb">
	<tr>
		<td><img src="templates/fiappleblue/images/tt12_l.gif" width="10" height="12" alt="" /></td>
		<td class="tt12bkg"><img src="images/spacer.gif" width="200" height="12" alt="" /></td>
		<td><img src="templates/fiappleblue/images/tt12_r.gif" width="10" height="12" alt="" /></td>
	</tr>
</table>
<table width="100%" cellpadding="3" cellspacing="1" class="forumline">
  <tr>
	<th class="thCornerL">{L_AUTHOR}</th>
	<th class="thCornerR">{L_COMMENTS}</th>
  </tr>
<!-- IF NO_COMMENTS -->
  <tr>
	<td colspan="2" class="row1" align="center"><span class="genmed">{L_NO_COMMENTS}</span></td>
  </tr>
<!-- ENDIF -->
<!-- BEGIN text -->
  <tr>
	<td width="150" align="left" valign="top" class="row1"><span class="name"><b>{text.POSTER}</b></span><br /><span class="postdetails">{text.POSTER_RANK}<br />{text.RANK_IMAGE}{text.POSTER_AVATAR}<br /><br />{text.POSTER_JOINED}<br />{text.POSTER_POSTS}<br />{text.POSTER_FROM}</span><br />&nbsp;</td>
	<td class="row1" height="28" valign="top">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td width="100%" valign="middle"><span class="postdetails"><img src="{text.ICON_MINIPOST_IMG}" width="12" height="9" border="0" />{L_POSTED}: {text.TIME}<span class="gen">&nbsp;</span>&nbsp; &nbsp;{L_COMMENT_SUBJECT}: {text.TITLE}</span></td>
				<td align="right"><!-- IF text.AUTH_COMMENT_DELETE --><a href="{text.U_COMMENT_DELETE}"><img src="{text.DELETE_IMG}" alt="{L_COMMENT_DELETE}" title="{L_COMMENT_DELETE}" border="0"></a><!-- ENDIF --><a href="#top"><img src="templates/fiappleblue/images/icon_up.gif" alt="{L_BACK_TO_TOP}" width="24" height="23" title="{L_BACK_TO_TOP}" /></a></td>
			</tr>
			<tr> 
				<td colspan="2"><hr /></td>
			</tr>
			<tr>
				<td colspan="2"valign="top"><span class="postbody">{text.TEXT}</span></td>
			</tr>
		</table>
	</td>
  </tr>
  <tr>
 	<td class="row1" width="150" align="left" valign="middle">&nbsp;</td>
	<td class="row1"></td>
  </tr>
  <tr> 
	<td class="spaceRow" colspan="2" height="1"><img src="{text.ICON_SPACER}" alt="" width="1" height="1" /></td>
  </tr>
<!-- END text -->
  <tr>
	<td colspan="2" class="cat">&nbsp;</td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="ttb">
	<tr>
		<td><img src="templates/fiappleblue/images/tb12_l.gif" width="10" height="12" alt="" /></td>
		<td class="tb12bkg"><img src="images/spacer.gif" width="200" height="12" alt="" /></td>
		<td><img src="templates/fiappleblue/images/tb12_r.gif" width="10" height="12" alt="" /></td>
	</tr>
</table>
<!-- IF AUTH_POST -->
<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
  <tr>
  	<td><img src="images/spacer.gif" alt="" width="1" height="26" /></td>
	<td align="left" valign="middle">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td><img src="templates/fiappleblue/images/lang_english/tabsp.gif" alt="" width="80" height="2" /></td>
		</tr>
		<tr>
		  <td class="postbkg"><a href="{U_COMMENT_DO}"><img src="{REPLY_IMG}" border="0" alt="{L_COMMENT_ADD}" align="middle" /></a></td>
		</tr>
		<tr>
			<td><img src="templates/fiappleblue/images/lang_english/tabb.gif" alt="" width="80" height="6" /></td>
		</tr>
	</table>
	</td>
  </tr>
</table>
<br clear="all" />
<!-- ENDIF -->




