<!--{*
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2012 LOCKON CO.,LTD. All Rights Reserved.
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

<!--★★メインコンテンツ★★-->
<form name="form1" id="form1" method="post" action="?" enctype="multipart/form-data" onSubmit="winSubmit('','form1', 'upload', 500, 400)">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="csv_upload" />
<div id="products" class="contents-main">
    <!--{if $tpl_errtitle != ""}-->
    <p>
        <span class="attention"><!--{$tpl_errtitle}--><br />
        <!--{foreach key=key item=item from=$arrCSVErr}-->
        <!--{$item}-->
        <!--{if $key != 'blank'}-->
        <!--{t string="tpl_[Value:T_ARG1]_01" T_ARG1=$arrParam[$key]}-->
        <!--{/if}-->
        <br />
        <!--{/foreach}-->
        </span>
    </p>
    <!--{/if}-->

    <!--▼登録テーブルここから-->
    <table class="form">
        <tr>
            <th><!--{t string="tpl_CSV file_01"}--></th>
            <td>
                <!--{if $arrErr.csv_file}--><span class="attention"><!--{$arrErr.csv_file}--></span><!--{/if}-->
                <input type="file" name="csv_file" size="60" class="box60" /><span class="attention"> <!--{t string="tpl_(First line is title)_01"}--></span>
            </td>
        </tr>
        <tr>
            <th><!--{t string="tpl_Registration information_01"}--></th>
            <td>
                <!--{foreach name=title key=key item=item from=$arrTitle}-->
                <!--{t string="tpl_T_ARG1 Item: T_ARG2_01" T_ARG1=$smarty.foreach.title.iteration T_ARG2=$item}--><br>
                <!--{/foreach}-->
            </td>
        </tr>
    </table>
    <!--▲登録テーブルここまで-->
    <div class="btn-area">
        <ul>
            <li><a class="btn-action" href="javascript:;" onclick="fnFormModeSubmit('form1', 'csv_upload', '', ''); return false;"><span class="btn-next"><!--{t string="tpl_Register with these contents_01"}--></span></a></li>
        </ul>
    </div>
    <!--{if $arrRowErr}-->
    <table class="form">
        <tr>
            <td>
                <!--{foreach item=err from=$arrRowErr}-->
                <span class="attention"><!--{$err}--></span>
                <!--{/foreach}-->
            </td>
        </tr>
    </table>
    <!--{/if}-->
    <!--{if $arrRowResult}-->
    <table class="form">
        <tr>
            <td>
                <!--{foreach item=result from=$arrRowResult}-->
                <span><!--{$result}--><br/></span>
                <!--{/foreach}-->
            </td>
        </tr>
    </table>
    <!--{/if}-->
</div>
</form>
