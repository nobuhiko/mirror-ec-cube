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
<script type="text/javascript">
<!--

function func_return(){
    document.form1.mode.value = "return";
    document.form1.submit();
}

//-->
</script>


<form name="form1" id="form1" method="post" action="?">
    <input type="hidden" name="mode" value="complete" />
    <!--{foreach from=$arrForm key=key item=item}-->
        <!--{if $key ne "mode" && $key ne "subm"}-->
            <input type="hidden" name="<!--{$key|h}-->" value="<!--{$item|h}-->" />
        <!--{/if}-->
    <!--{/foreach}-->
    <!-- 検索条件の保持 -->
    <!--{foreach from=$arrSearchData key="key" item="item"}-->
        <!--{if $key ne "job" && $key ne "sex"}--><input type="hidden" name="search_data[<!--{$key|h}-->]" value="<!--{$item|h}-->"><!--{/if}-->
    <!--{/foreach}-->
    <!--{foreach from=$arrSearchData.job key="key" item="item"}-->
        <input type="hidden" name="search_data[job][]" value=<!--{$item}--> />
    <!--{/foreach}-->
    <!--{foreach from=$arrSearchData.sex key="key" item="item"}-->
        <input type="hidden" name="search_data[sex][]" value=<!--{$item}--> />
    <!--{/foreach}-->
    <div id="customer" class="contents-main">
        <h2>顧客編集</h2>
        <table class="form">
            <tr>
                <th>顧客ID<span class="attention"> *</span></th>
                <td><!--{$arrForm.customer_id|h}--></td>
            </tr>
            <tr>
                <th>会員状態<span class="attention"> *</span></th>
                <td><!--{if $arrForm.status == 1}-->仮会員<!--{else}-->本会員<!--{/if}--></td>
            </tr>
            <tr>
                <th>お名前<span class="attention"> *</span></th>
                <td><!--{$arrForm.name01|h}--><!--{$arrForm.name02|h}-->　様</td>
            </tr>
            <tr>
                <th>お名前(フリガナ)<span class="attention"> *</span></th>
                <td><!--{$arrForm.kana01|h}--><!--{$arrForm.kana02|h}-->　様</td>
            </tr>
            <tr>
                <th>郵便番号<span class="attention"> *</span></th>
                <td>〒 <!--{$arrForm.zip01|h}--> - <!--{$arrForm.zip02|h}--></td>
            </tr>
            <tr>
                <th>住所<span class="attention"> *</span></td>
                <td><!--{$arrPref[$arrForm.pref]|h}--><!--{$arrForm.addr01|h}--><!--{$arrForm.addr02|h}--></td>
            </tr>
            <tr>
                <th>メールアドレス<span class="attention"> *</span></th>
                <td><!--{$arrForm.email|h}--></td>
            </tr>
            <tr>
                <th>携帯メールアドレス</th>
                <td><!--{$arrForm.email_mobile|h}--></td>
            </tr>
            <tr>
                <th>お電話番号<span class="attention"> *</span></th>
                <td><!--{$arrForm.tel01|h}--> - <!--{$arrForm.tel02|h}--> - <!--{$arrForm.tel03|h}--></td>
            </tr>
            <tr>
                <th>FAX</th>
                <td><!--{if strlen($arrForm.fax01) > 0}--><!--{$arrForm.fax01|h}--> - <!--{$arrForm.fax02|h}--> - <!--{$arrForm.fax03|h}--><!--{else}-->未登録<!--{/if}--></td>
            </tr>
            <tr>
                <th>ご性別<span class="attention"> *</span></th>
                <td><!--{$arrSex[$arrForm.sex]|h}--></td>
            </tr>
            <tr>
                <th>ご職業</th>
                <td><!--{$arrJob[$arrForm.job]|default:"未登録"|h}--></td>
            </tr>
            <tr>
                <th>生年月日</th>
                <td><!--{if strlen($arrForm.year) > 0 && strlen($arrForm.month) > 0 && strlen($arrForm.day) > 0}--><!--{$arrForm.year|h}-->年<!--{$arrForm.month|h}-->月<!--{$arrForm.day|h}-->日<!--{else}-->未登録<!--{/if}--></td>
            </tr>
            <tr>
                <th>パスワード<span class="attention"> *</span></th>
                <td><!--{$passlen}--></td>
            </tr>
            <tr>
                <th>パスワードを忘れたときのヒント<span class="attention"> *</span></th>
                <td>
                    質問： <!--{$arrReminder[$arrForm.reminder]|h}--><br />
                    答え： <!--{$arrForm.reminder_answer|h}-->
                </td>
            </tr>
            <tr>
                <th>メールマガジン<span class="attention"> *</span></th>
                <td><!--{if $arrForm.mailmaga_flg eq 1}-->HTML<!--{elseif $arrForm.mailmaga_flg eq 2}-->テキスト<!--{else}-->希望しない<!--{/if}--></td>
            </tr>
            <tr>
                <th>SHOP用メモ</th>
                <td><!--{$arrForm.note|h|nl2br|default:"未登録"}--></td>
            </tr>
            <tr>
                <th>所持ポイント</td>
                <td><!--{$arrForm.point|default:"0"|h}--> pt</td>
            </tr>
        </table>
        <div class="btn">
            <a class="btn-normal" href="javascript:;" onclick="func_return(); return false;"><span>編集画面に戻る</span></a>
            <a class="btn-normal" href="javascript:;" onclick="fnFormModeSubmit('form1', 'complete', '', '');"><span>この内容で登録する</span></a>
        </div>
    </div>
</form>
