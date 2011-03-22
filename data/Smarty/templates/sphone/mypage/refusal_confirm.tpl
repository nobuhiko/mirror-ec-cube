<!--{*
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2011 LOCKON CO.,LTD. All Rights Reserved.
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
<!--▼CONTENTS-->
<div id="mypagecolumn">
  <h2 class="title"><!--{$tpl_title|h}--></h2>
  <!--{include file=$tpl_navi}-->
  <form name="form1" method="post" action="?">
  <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
  <input type="hidden" name="mode" value="complete" />
  <div id="mycontentsarea">
    <h2><!--{$tpl_subtitle|h}--></h2>
    <div id="completetext">
      <p>退会手続きを実行してもよろしいでしょうか？</p>
      <div class="tblareabtn">
        <a class="spbtn spbtn-shopping" href="./refusal.php">いいえ、退会しません</a><br />
        <input class="spbtn spbtn-medeum" type="submit" value="はい、退会します" name="refuse_do" id="refuse_do" />
      </div>

      <p class="mini"><em>※退会手続きが完了した時点で、現在保存されている購入履歴や、お届け先等の情報はすべてなくなりますのでご注意ください。</em></p>
    </div>
  </div>
  </form>
</div>
<!--▲CONTENTS-->
