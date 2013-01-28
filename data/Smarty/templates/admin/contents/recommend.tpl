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
<!--
function lfnCheckSubmit( fm ){

    var err = '';
    /*
    if ( ! fm["title"].value ){
        err += '見出しコメントを入力して下さい。';
    }
    */
    if ( ! fm["comment"].value ){
        if ( err ) err += '';
        err += '<!--{t string="tpl_Enter a comment._01"}-->';
    }
    if ( err ){
        alert(err);
        return false;
    } else {
        if(window.confirm('<!--{t string="tpl_Register and continue?_01"}-->')){
                fm.submit();
                return true;
        }
    }
}

function lfnCheckSetItem( rank ){
    var flag = true;
    var checkRank = '<!--{$checkRank|h}-->';
    if ( checkRank ){
        if ( rank != checkRank ){
            if( ! window.confirm('<!--{t string="tpl_The selected field T_ARG1 will be discarded. Continue? _01" T_ARG1=$checkRank|h}-->')){
                flag = false;
            }
        }
    }

    if ( flag ){
        win03('./recommend_search.php?rank=' + rank,'search','615','600');
    }
}

function lfnSortItem(mode,data){
    var flag = true;
    var checkRank = '<!--{$checkRank|h}-->';
    if ( checkRank ){
        if( ! window.confirm('<!--{t string="tpl_The selected field T_ARG1 will be discarded. Continue? _01" T_ARG1=$checkRank|h}-->')){
            flag = false;
        }
    }
    
    if ( flag ){
        document.form1["mode"].value = mode;
        document.form1["rank"].value = data;
        document.form1.submit();
    }
}

//-->
</script>

        <!--{section name=cnt loop=$tpl_disp_max}-->

<div id="admin-contents" class="contents-main">
    <table class="list center" id="recommend-table">
        <col width="13%" />
        <col width="73%" />
        <col width="7%" />
        <col width="7%" />
        <tr>
            <th><!--{t string="tpl_Ranking_01"}--></th>
            <th><!--{t string="tpl_Product/comment_01"}--></th>
            <th><!--{t string="tpl_Edit_01"}--></th>
            <th><!--{t string="tpl_Remove_01"}--></th>
			<th><!--{t string="tpl_Rearrangement_01"}--></th>
        </tr>

        <tr>
            <td><!--{t string="tpl_Recommended products_01"}-->(<!--{$smarty.section.cnt.iteration}-->)</td>
                <!--{if $arrItems[$smarty.section.cnt.iteration].product_id}-->
                    <td>
                        <div id="table-wrap" class="clearfix">
                            <div class="table-img">
                                <!--{if $arrItems[$smarty.section.cnt.iteration].product_id}-->
                                    <img src="<!--{$smarty.const.IMAGE_SAVE_URLPATH}--><!--{$arrItems[$smarty.section.cnt.iteration].main_list_image|sfNoImageMainList|h}-->" alt="<!--{$arrItems[$smarty.section.cnt.iteration].name|h}-->" width="100" height="100" />
                                <!--{/if}-->
                            </div>
                            <div class="table-detail">
                                <div class="detail-name"><!--{t string="tpl_Product name_01"}--> <!--{$arrItems[$smarty.section.cnt.iteration].name|h}--></div>

                                    <div class="detail-form">
                                        <form name="form<!--{$smarty.section.cnt.iteration}-->" id="form<!--{$smarty.section.cnt.iteration}-->" method="post" action="?">
                                            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
                                            <input type="hidden" name="mode" value="regist" />
                                            <input type="hidden" name="product_id" value="<!--{$arrItems[$smarty.section.cnt.iteration].product_id|h}-->" />
                                            <input type="hidden" name="category_id" value="<!--{$category_id|h}-->" />
                                            <input type="hidden" name="rank" value="<!--{$arrItems[$smarty.section.cnt.iteration].rank|h}-->" />
                                            <span class="attention"><!--{$arrErr[$smarty.section.cnt.iteration].comment}--></span>
                                            <textarea class="top" name="comment" cols="45" rows="4" style="width: 586px; height: 80px; <!--{$arrErr[$smarty.section.cnt.iteration].comment|sfGetErrorColor}-->" <!--{$arrItems[$smarty.section.cnt.iteration].product_id|sfGetEnabled}-->><!--{"\n"}--><!--{$arrItems[$smarty.section.cnt.iteration].comment|h}--></textarea>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                <!--{else}-->
                    <td class="AlignLeft">
                        <a class="btn-action-m" href="javascript:;" onclick="lfnCheckSetItem('<!--{$smarty.section.cnt.iteration}-->'); return false;" target="_blank"><span class="btn-next"><!--{t string="tpl_Select a product_01"}--></span></a>
                        <form name="form<!--{$smarty.section.cnt.iteration}-->" id="form<!--{$smarty.section.cnt.iteration}-->" method="post" action="?">
                            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
                            <input type="hidden" name="mode" value="regist" />
                            <input type="hidden" name="product_id" value="<!--{$arrItems[$smarty.section.cnt.iteration].product_id|h}-->" />
                            <input type="hidden" name="category_id" value="<!--{$category_id|h}-->" />
                            <input type="hidden" name="rank" value="<!--{$arrItems[$smarty.section.cnt.iteration].rank|h}-->" />
                        </form>
                    </td>
                <!--{/if}-->
            <td>
                <!--{if $arrItems[$smarty.section.cnt.iteration].product_id}-->
                    <a href="javascript:;" onclick="lfnCheckSetItem('<!--{$smarty.section.cnt.iteration}-->'); return false;" target="_blank">
                        <!--{t string="tpl_Edit_01"}--></a>
                <!--{else}-->
                    - -
                <!--{/if}-->
            </td>
            <td>
                <!--{if $arrItems[$smarty.section.cnt.iteration].product_id}-->
                        <a href="javascript:;" onClick="return fnInsertValAndSubmit( document.form<!--{$smarty.section.cnt.iteration}-->, 'mode', 'delete', '<!--{t string="tpl_Deletion will be carried out. Is this okay?_01"}-->' )"><!--{t string="tpl_Remove_01"}--></a>
                <!--{else}-->
                    - -
                <!--{/if}-->
            </td>
            <td>
                <!--{* 移動 *}-->
                <!--{if $smarty.section.cnt.iteration != 1 && $arrItems[$smarty.section.cnt.iteration].product_id}-->
                    <a href="?" onclick="lfnSortItem('up',<!--{$arrItems[$smarty.section.cnt.iteration].rank}-->); return false;"><!--{t string="tpl_To top_01"}--></a><br>&nbsp;
                <!--{/if}-->
                <!--{if $smarty.section.cnt.iteration != $tpl_disp_max && $arrItems[$smarty.section.cnt.iteration].product_id}-->
                    <a href="?" onclick="lfnSortItem('down',<!--{$arrItems[$smarty.section.cnt.iteration].rank}-->); return false;"><!--{t string="tpl_To bottom_01"}--></a>
                <!--{/if}-->
            </td>
        </tr>

        <tr><td colspan="4" class="no-border-w" height="20"></td></tr>
        <!--{if $arrItems[$smarty.section.cnt.iteration].product_id}-->
        <tr><td colspan="4" class="no-border">
        <a class="btn-action" href="javascript:;" onclick="lfnCheckSubmit(document.form<!--{$smarty.section.cnt.iteration}-->); return false;"><span class="btn-next"><!--{t string="tpl_Save and continue_01"}--></span></a>
        </td>
        </tr>
        <!--{/if}-->
    <!--▲おすすめ商品<!--{$smarty.section.cnt.iteration}-->-->
    <!--{/section}-->
    </table>
</div>
