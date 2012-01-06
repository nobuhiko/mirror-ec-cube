<?php
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

// {{{ requires
require_once CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php';

/**
 * 管理者ログイン のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin extends LC_Page_Ex {

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        $this->template = MAIN_FRAME;

        //IP制限チェック
        $allow_hosts = unserialize(ADMIN_ALLOW_HOSTS);
        if (is_array($allow_hosts) && count($allow_hosts) > 0) {
            if (array_search($_SERVER["REMOTE_ADDR"],$allow_hosts) === FALSE) {
                SC_Utils_Ex::sfDispError(AUTH_ERROR);
            }
        }

        //SSL制限チェック
        if(ADMIN_FORCE_SSL == TRUE){
            if (SC_Utils_Ex::sfIsHTTPS() === false){
                SC_Response_Ex::sendRedirect($_SERVER["REQUEST_URI"], $_GET, FALSE, TRUE);
            }
        }

        $this->tpl_authority = $_SESSION['authority'];

        // ディスプレイクラス生成
        $this->objDisplay = new SC_Display_Ex();

        // プラグインクラス生成
        $this->objPlagin = new SC_Helper_Plugin_Ex();
        $this->objPlagin->preProcess($this);

        // トランザクショントークンの検証と生成
        $this->doValidToken(true);
        $this->setTokenTo();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
    }

    /**
     * Page のレスポンス送信.
     *
     * @return void
     */
    function sendResponse() {
        if (isset($this->objPlagin)) { // FIXME モバイルエラー応急対応
            // post-prosess処理(暫定的)
            $this->objPlagin->process($this);
        }
        $this->objDisplay->prepare($this, true);
        $this->objDisplay->response->write();
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }

    /**
     * ログ出力を行う.
     *
     * ログイン中の管理者IDを含めてログ出力します.
     *
     * @access protected
     * @param string $mess ログメッセージ
     * @param string $log_level ログレベル("Info" or "Debug")
     * @return void
     */
    function log($mess, $log_level) {
        $mess = $mess . " id=" . $_SESSION['login_id'] . "(" . $_SESSION['authority'] . ")" . "[" . session_id() . "]";

        GC_Utils_Ex::gfAdminLog($mess, $log_level);
    }

}
?>
