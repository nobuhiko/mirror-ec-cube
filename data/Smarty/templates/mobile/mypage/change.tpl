<!--{*
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2010 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
*}-->
<div align="center">登録内容変更 1/3</div>
<hr>
<form name="form1" method="post" action="?">
	<input type="hidden" name="mode" value="set1">

	<font color="#FF0000">*は必須項目です。</font><br>
	PCと携帯のメールアドレスが登録できます。<br>
	<br>

	【メールアドレス】<font color="#FF0000">※</font><br>
	<font color="#FF0000"><!--{$arrErr.email}--></font>
	<input type="text" name="email" value="<!--{$arrForm.email|h}-->" istyle="3">
	<br>

	【携帯メールアドレス】<br>
	<font color="#FF0000"><!--{$arrErr.email_mobile}--></font>
	<input type="text" name="email_mobile" value="<!--{$arrForm.email_mobile|h}-->" istyle="3">
	<br>

	【パスワード】<font color="#FF0000">※</font><br>
	（半角英数字<!--{$smarty.const.PASSWORD_LEN1}-->文字以上<!--{$smarty.const.PASSWORD_LEN2}-->文字以内）<br>
	<font color="#FF0000"><!--{$arrErr.password}--></font>
	<!--{assign var="size" value="`$smarty.const.PASSWORD_LEN2+2`"}-->
	<input type="password" name="password" value="<!--{$arrForm.password}-->" istyle="4" maxlength="<!--{$smarty.const.PASSWORD_LEN2}-->" size="<!--{$size}-->"><br>

	【パスワード確認用の質問】<font color="#FF0000">※</font><br>
	<font color="#FF0000"><!--{$arrErr.reminder}--></font>
	<select name="reminder">
		<option value="">選択してください</option>
		<!--{html_options options=$arrReminder selected=$arrForm.reminder}-->
	</select><br>

	【質問の答え】<font color="#FF0000">※</font><br>
	<font color="#FF0000"><!--{$arrErr.reminder_answer}--></font>
	<input type="text" name="reminder_answer" value="<!--{$arrForm.reminder_answer|h}-->" istyle="1"><br>

	【お名前】<font color="#FF0000">※</font><br>
	<font color="#FF0000"><!--{$arrErr.name01}--><!--{$arrErr.name02}--></font>
	姓（例：渋谷）<br>
	<input type="text" name="name01" value="<!--{$arrForm.name01|h}-->" maxlength="<!--{$smarty.const.STEXT_LEN}-->" istyle="1"><br>

	名（例：花子）<br>
	<input type="text" name="name02" value="<!--{$arrForm.name02|h}-->" maxlength="<!--{$smarty.const.STEXT_LEN}-->" istyle="1"><br>
	<font color="#FF0000"><!--{$arrErr.kana01}--><!--{$arrErr.kana02}--></font>

	カナ/姓（例：シブヤ）<br>
	<input type="text" name="kana01" value="<!--{$arrForm.kana01|h}-->" maxlength="<!--{$smarty.const.STEXT_LEN}-->" istyle="2"><br>

	カナ/名（例：ハナコ）<br>
	<input type="text" name="kana02" value="<!--{$arrForm.kana02|h}-->" maxlength="<!--{$smarty.const.STEXT_LEN}-->" istyle="2"><br>

	<input type="submit" name="confirm" value="次へ">

	<!--{foreach from=$list_data key=key item=item}-->
		<input type="hidden" name="<!--{$key|h}-->" value="<!--{$item|h}-->">
	<!--{/foreach}-->
</form>

<br>
<hr>

<a href="<!--{$smarty.const.MOBILE_CART_URL_PATH}-->" accesskey="9"><!--{9|numeric_emoji}-->かごを見る</a><br>
<a href="<!--{$smarty.const.MOBILE_TOP_URL_PATH}-->" accesskey="0"><!--{0|numeric_emoji}-->TOPページへ</a><br>

<br>

<!-- ▼フッター ここから -->
<!--{include file='footer.tpl'}-->
<!-- ▲フッター ここまで -->
