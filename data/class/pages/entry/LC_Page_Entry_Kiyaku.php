<?php
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

// {{{ requires
require_once(CLASS_REALDIR . "pages/LC_Page.php");

/**
 * ご利用規約 のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Entry_Kiyaku extends LC_Page {

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_title = "ご利用規約";
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {

        // 規約内容の取得
        $objQuery = new SC_Query();
        $objQuery->setOrder("rank DESC");
        $arrRet = $objQuery->select("kiyaku_title, kiyaku_text", "dtb_kiyaku", "del_flg <> 1");
        $this->max = count($arrRet);
        
        // mobile時はGETでページ指定
        if ( Net_UserAgent_Mobile::isMobile() === true ){
        	$this->offset = is_numeric($_GET['offset'])===true ? $offset = intval($_GET['offset']) : 1;
        }

        // 規約文の作成
        $this->tpl_kiyaku_text = "";
        for ($i = 0; $i < $this->max; $i++) {
        	if ($this->offset !== null && ($this->offset - 1) <> $i) continue;
            $this->tpl_kiyaku_text.=$arrRet[$i]['kiyaku_title'] . "\n\n";
            $this->tpl_kiyaku_text.=$arrRet[$i]['kiyaku_text'] . "\n\n";
        }
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }
}
?>
