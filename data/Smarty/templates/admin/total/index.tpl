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

<script type="text/javascript">
$(function(){
	$.datepicker.setDefaults( $.datepicker.regional[ "<!--{$smarty.const.LANG_CODE}-->" ] );
	
	$( "#datepickersearch_start_m" ).datepicker({
	beforeShowDay: function(date) {
		if(date.getDay() == 0) {
			return [true,"date-sunday"]; 
		} else if(date.getDay() == 6){
			return [true,"date-saturday"];
		} else {
			return [true];
		}
	},changeMonth: 'true'
	,changeYear: 'true'
	,onSelect: function(dateText, inst){
		setDatesearch_start_m(dateText);
	},
	showButtonPanel: true,
	beforeShow: showAdditionalButtonsearch_start_m,       
	onChangeMonthYear: showAdditionalButtonsearch_start_m
	});
	
	$("#datepickersearch_start_m").blur( function() {
		var dateText = $(this).val();
		setDatesearch_start_m(dateText);
	});
	
	$( "#datepickersearch_start" ).datepicker({
	beforeShowDay: function(date) {
		if(date.getDay() == 0) {
			return [true,"date-sunday"]; 
		} else if(date.getDay() == 6){
			return [true,"date-saturday"];
		} else {
			return [true];
		}
	},changeMonth: 'true'
	,changeYear: 'true'
	,onSelect: function(dateText, inst){
		setDatesearch_start(dateText);
	},
	showButtonPanel: true,
	beforeShow: showAdditionalButtonsearch_start,       
	onChangeMonthYear: showAdditionalButtonsearch_start
	});
	
	$("#datepickersearch_start").blur( function() {
		var dateText = $(this).val();
		setDatesearch_start(dateText);
	});
	
	$( "#datepickersearch_end" ).datepicker({
	beforeShowDay: function(date) {
		if(date.getDay() == 0) {
			return [true,"date-sunday"]; 
		} else if(date.getDay() == 6){
			return [true,"date-saturday"];
		} else {
			return [true];
		}
	},changeMonth: 'true'
	,changeYear: 'true'
	,onSelect: function(dateText, inst){
		setDatesearch_end(dateText);
	},
	showButtonPanel: true,
	beforeShow: showAdditionalButtonsearch_end,       
	onChangeMonthYear: showAdditionalButtonsearch_end
	});
	
	$("#datepickersearch_end").blur( function() {
		var dateText = $(this).val();
		setDatesearch_end(dateText);
	});

});

var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">Clear</button>');

var showAdditionalButtonsearch_start_m = function (input) {
	setTimeout(function () {
		var buttonPane = $(input)
				 .datepicker("widget")
				 .find(".ui-datepicker-buttonpane");
		btn
				.unbind("click")
				.bind("click", function () {
					$.datepicker._clearDate(input);
					$("*[name=search_startyear_m]").val("");
					$("*[name=search_startmonth_m]").val("");
				});
		btn.appendTo(buttonPane);
	}, 1);
};

var showAdditionalButtonsearch_start = function (input) {
	setTimeout(function () {
		var buttonPane = $(input)
				 .datepicker("widget")
				 .find(".ui-datepicker-buttonpane");
		btn
				.unbind("click")
				.bind("click", function () {
					$.datepicker._clearDate(input);
					$("*[name=search_startyear]").val("");
					$("*[name=search_startmonth]").val("");
					$("*[name=search_startday]").val("");
				});
		btn.appendTo(buttonPane);
	}, 1);
};

var showAdditionalButtonsearch_end = function (input) {
	setTimeout(function () {
		var buttonPane = $(input)
				 .datepicker("widget")
				 .find(".ui-datepicker-buttonpane");
		btn
				.unbind("click")
				.bind("click", function () {
					$.datepicker._clearDate(input);
					$("*[name=search_endyear]").val("");
					$("*[name=search_endmonth]").val("");
					$("*[name=search_endday]").val("");
				});
		btn.appendTo(buttonPane);
	}, 1);
};

function setDatesearch_start_m(dateText){
var dates = dateText.split('/');
$("*[name=search_startyear_m]").val(dates[0]);
$("*[name=search_startmonth_m]").val(dates[1]);
}

function setDatesearch_start(dateText){
var dates = dateText.split('/');
$("*[name=search_startyear]").val(dates[0]);
$("*[name=search_startmonth]").val(dates[1]);
$("*[name=search_startday]").val(dates[2]);
}

function setDatesearch_end(dateText){
var dates = dateText.split('/');
$("*[name=search_endyear]").val(dates[0]);
$("*[name=search_endmonth]").val(dates[1]);
$("*[name=search_endday]").val(dates[2]);
}

</script>

<div id="total" class="contents-main">
    <!--{* 検索条件設定テーブルここから *}-->
    <table summary="Search condition settings" class="input-form form">
        <tr>
            <th><!--{t string="tpl_687"}--></th>
            <td>
                <form name="search_form1" id="search_form1" method="post" action="?">
                    <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
                    <input type="hidden" name="mode" value="search" />
                    <input type="hidden" name="search_form" value="1" />
                    <input type="hidden" name="page" value="<!--{$arrForm.page.value|h}-->" />
                    <input type="hidden" name="type" value="<!--{$smarty.post.type|h}-->" />
                    <!--{if $arrErr.search_startyear_m || $arrErr.search_startmonth_m}-->
                        <span class="attention"><!--{$arrErr.search_startyear_m}--></span>
                        <span class="attention"><!--{$arrErr.search_startmonth_m}--></span>
                    <!--{/if}-->
                    <input id="datepickersearch_start_m" type="text" value="<!--{if $arrForm.search_startyear_m.value != "" && $arrForm.search_startmonth_m.value != ""}--><!--{$arrForm.search_startyear_m.value|h}-->/<!--{$arrForm.search_startmonth_m.value|h|string_format:'%02d'}--><!--{/if}-->" <!--{if $arrErr.search_startyear_m != ""}--><!--{sfSetErrorStyle}--><!--{/if}--> />月度 (<!--{if $smarty.const.CLOSE_DAY == 31}-->末<!--{else}--><!--{$smarty.const.CLOSE_DAY}--><!--{/if}-->日締め)
                    <input type="hidden" name="search_startyear_m" value="<!--{$arrForm.search_startyear_m.value}-->" />
                    <input type="hidden" name="search_startmonth_m" value="<!--{$arrForm.search_startmonth_m.value}-->" />
                    <a class="btn-normal" href="javascript:;" onclick="fnFormModeSubmit('search_form1', 'search', '', ''); return false;" name="subm"><!--{t string="tpl_688"}--></a>
                </form>
            </td>
        </tr>
        <tr>
            <th><!--{t string="tpl_689"}--></th>
            <td>
                <form name="search_form2" id="search_form2" method="post" action="?">
                    <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
                    <input type="hidden" name="mode" value="search" />
                    <input type="hidden" name="search_form" value="2" />
                    <input type="hidden" name="page" value="<!--{$arrForm.page.value|h}-->" />
                    <input type="hidden" name="type" value="<!--{$smarty.post.type|h}-->" />
                    <!--{if $arrErr.search_startyear || $arrErr.search_endyear}-->
                        <span class="attention"><!--{$arrErr.search_startyear}--></span>
                        <span class="attention"><!--{$arrErr.search_endyear}--></span>
                    <!--{/if}-->
                    <input id="datepickersearch_start" type="text" value="<!--{if $arrForm.search_startyear.value != "" && $arrForm.search_startmonth.value != "" && $arrForm.search_startday.value != ""}--><!--{$arrForm.search_startyear.value|h}-->/<!--{$arrForm.search_startmonth.value|h|string_format:'%02d'}-->/<!--{$arrForm.search_startday.value|h|string_format:'%02d'}--><!--{/if}-->" <!--{if $arrErr.search_startyear != ""}--><!--{sfSetErrorStyle}--><!--{/if}--> />
                    <input type="hidden" name="search_startyear" value="<!--{$arrForm.search_startyear.value}-->" />
                    <input type="hidden" name="search_startmonth" value="<!--{$arrForm.search_startmonth.value}-->" />
                    <input type="hidden" name="search_startday" value="<!--{$arrForm.search_startday.value}-->" />
                    <!--{t string="-"}-->
                    <input id="datepickersearch_end" type="text" value="<!--{if $arrForm.search_endyear.value != "" && $arrForm.search_endmonth.value != "" && $arrForm.search_endday.value != ""}--><!--{$arrForm.search_endyear.value|h}-->/<!--{$arrForm.search_endmonth.value|h|string_format:'%02d'}-->/<!--{$arrForm.search_endday.value|h|string_format:'%02d'}--><!--{/if}-->" <!--{if $arrErr.search_endyear != ""}--><!--{sfSetErrorStyle}--><!--{/if}--> />
                    <input type="hidden" name="search_endyear" value="<!--{$arrForm.search_endyear.value}-->" />
                    <input type="hidden" name="search_endmonth" value="<!--{$arrForm.search_endmonth.value}-->" />
                    <input type="hidden" name="search_endday" value="<!--{$arrForm.search_endday.value}-->" />
                    <a class="btn-normal" href="javascript:;" onclick="fnFormModeSubmit('search_form2', 'search', '', ''); return false;" name="subm"><!--{t string="tpl_690"}--></a>
                </form>
            </td>
        </tr>
    </table>
    <!--{* 検索条件設定テーブルここまで *}-->


    <!--{* 検索結果一覧ここから *}-->
    <!--{if count($arrResults) > 0}-->
        <form name="form1" id="form1" method="post" action="?">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="search" />
        <input type="hidden" name="type" value="<!--{$arrForm.type.value|h}-->" />
        <input type="hidden" name="page" value="<!--{$arrForm.page.value|h}-->" />
        <!--{foreach key=key item=item from=$arrHidden}-->
            <input type="hidden" name="<!--{$key}-->" value="<!--{$item|h}-->" />
        <!--{/foreach}-->

            <!--検索結果表示テーブル-->
            <h2><!--{include file=$tpl_graphsubtitle}--></h2>

            <div class="btn">
                <a class="btn-normal" href="javascript:;" onclick="fnModeSubmit('csv','',''); return false;"><span>CSVダウンロード</span></a>
            </div>

            <!--{* グラフ表示 *}-->
                <!--{if $install_GD}-->
                <div id="graph-image">
                    <!--{* <img src="<!--{$tpl_image}-->?<!--{$cashtime}-->" alt="Graph"> *}-->
                    <img src="?draw_image=true&amp;type=<!--{$smarty.post.type|h}-->&amp;mode=search&amp;search_form=<!--{$smarty.post.search_form|h}-->&amp;page=<!--{$smarty.post.page|h}-->&amp;search_startyear_m=<!--{$smarty.post.search_startyear_m|h}-->&amp;search_startmonth_m=<!--{$smarty.post.search_startmonth_m|h}-->&amp;search_startyear=<!--{$smarty.post.search_startyear|h}-->&amp;search_startmonth=<!--{$smarty.post.search_startmonth|h}-->&amp;search_startday=<!--{$smarty.post.search_startday|h}-->&amp;search_endyear=<!--{$smarty.post.search_endyear|h}-->&amp;search_endmonth=<!--{$smarty.post.search_endmonth|h}-->&amp;search_endday=<!--{$smarty.post.search_endday|h}-->" alt="Graph" />
                </div>
                <!--{/if}-->
            <!--{* グラフ表示 *}-->

            <!--{* ▼検索結果テーブルここから *}-->
            <!--{include file=$tpl_page_type}-->
            <!--{* ▲検索結果テーブルここまで *}-->
            <!--検索結果表示テーブル-->
        </form>
    <!--{else}-->
        <!--{if $smarty.post.mode == 'search'}-->
            <form name="form1" id="form1" method="post" action="?">
            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
            <input type="hidden" name="mode" value="search" />
            <input type="hidden" name="type" value="<!--{$arrForm.type.value|h}-->" />
            <input type="hidden" name="page" value="<!--{$arrForm.page.value|h}-->" />
            <!--{foreach key=key item=item from=$arrHidden}-->
                <input type="hidden" name="<!--{$key}-->" value="<!--{$item|h}-->" />
            <!--{/foreach}-->
            <!--検索結果表示テーブル-->
            <h2><!--{include file=$tpl_graphsubtitle}--></h2>
            <div class="message">
                <!--{t string="tpl_437"}-->
            </div>
            <!--検索結果表示テーブル-->
            </form>
        <!--{/if}-->
    <!--{/if}-->
    <!--{* 検索結果一覧ここまで *}-->
</div>
