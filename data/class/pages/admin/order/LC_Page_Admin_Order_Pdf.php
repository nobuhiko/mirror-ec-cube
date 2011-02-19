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
require_once(CLASS_REALDIR . "pages/admin/LC_Page_Admin.php");
require_once(CLASS_REALDIR . "SC_Fpdf.php");

/**
 * 帳票出力 のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin_Order_Pdf extends LC_Page_Admin {

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage = 'order/pdf_input.tpl';
        $this->tpl_subnavi = 'order/subnavi.tpl';
        $this->tpl_mainno = 'order';
        $this->tpl_subno = 'pdf';
        $this->tpl_subtitle = '帳票出力';

        $this->SHORTTEXT_MAX = STEXT_LEN;
        $this->MIDDLETEXT_MAX = MTEXT_LEN;
        $this->LONGTEXT_MAX = LTEXT_LEN;

        $this->arrType[0]  = "納品書";

        $this->arrDownload[0] = "ブラウザに開く";
        $this->arrDownload[1] = "ファイルに保存";
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
        $objDb = new SC_Helper_DB_Ex();
        $objSess = new SC_Session();

        $objDate = new SC_Date(1901);
        $objDate->setStartYear(RELEASE_YEAR);
        $this->arrYear = $objDate->getYear();
        $this->arrMonth = $objDate->getMonth();
        $this->arrDay = $objDate->getDay();

        // 認証可否の判定
        SC_Utils_Ex::sfIsSuccess($objSess);

        // 画面遷移の正当性チェック用にuniqidを埋め込む
        $objPage->tpl_uniqid = $objSess->getUniqId();

        // パラメータ管理クラス
        $this->objFormParam = new SC_FormParam();
        // パラメータ情報の初期化
        $this->lfInitParam();
        $this->objFormParam->setParam($_POST);
        
        // どんな状態の時に isset($arrRet) == trueになるんだ?
        if (!isset($arrRet)) $arrRet = array();
        
        switch($this->getMode()) {
            case "confirm":
                // 入力値の変換
                $this->confirm($this->objFormParam);
                break;
            default:
                // ここが$arrFormの初登場ということを明示するため宣言する。
                $arrForm = array();
                // タイトルをセット
                $arrForm['title'] = "お買上げ明細書(納品書)";

                // 今日の日付をセット
                $arrForm['year']  = date("Y");
                $arrForm['month'] = date("m");
                $arrForm['day']   = date("d");

                // メッセージ
                $arrForm['msg1'] = 'このたびはお買上げいただきありがとうございます。';
                $arrForm['msg2'] = '下記の内容にて納品させていただきます。';
                $arrForm['msg3'] = 'ご確認くださいますよう、お願いいたします。';

                // 注文番号があったら、セットする
                if(SC_Utils_Ex::sfIsInt($_GET['order_id'])) {
                    $arrForm['order_id'][0] = $_GET['order_id'];
                } elseif (is_array($_POST['pdf_order_id'])) {
                    sort($_POST['pdf_order_id']);
                    foreach ($_POST['pdf_order_id'] AS $key=>$val) {
                        $arrForm['order_id'][] = $val;
                    }
                }

                $this->arrForm = $arrForm;
                break;
        }
        $this->setTemplate($this->tpl_mainpage);
    }

    function confirm(&$objFormParam){
        $this->objFormParam->convParam();
        $this->arrErr = $this->lfCheckError($arrRet);
        $arrRet = $this->objFormParam->getHashArray();
        
        $this->arrForm = $arrRet;
        // エラー入力なし
        if (count($this->arrErr) == 0) {
            $objFpdf = new SC_Fpdf($arrRet['download'], $arrRet['title']);
            foreach ($arrRet['order_id'] AS $key => $val) {
                $arrPdfData = $arrRet;
                $arrPdfData['order_id'] = $val;
                $objFpdf->setData($arrPdfData);
            }
            $objFpdf->createPdf();
            exit;
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

    /* パラメータ情報の初期化 */
    function lfInitParam() {
        $this->objFormParam->addParam("注文番号", "order_id", INT_LEN, "n", array("EXIST_CHECK", "MAX_LENGTH_CHECK", "NUM_CHECK"));
        $this->objFormParam->addParam("発行日", "year", INT_LEN, "n", array("EXIST_CHECK", "MAX_LENGTH_CHECK", "NUM_CHECK"));
        $this->objFormParam->addParam("発行日", "month", INT_LEN, "n", array("EXIST_CHECK", "MAX_LENGTH_CHECK", "NUM_CHECK"));
        $this->objFormParam->addParam("発行日", "day", INT_LEN, "n", array("EXIST_CHECK", "MAX_LENGTH_CHECK", "NUM_CHECK"));
        $this->objFormParam->addParam("帳票の種類", "type", INT_LEN, "n", array("EXIST_CHECK", "MAX_LENGTH_CHECK", "NUM_CHECK"));
        $this->objFormParam->addParam("ダウンロード方法", "download", INT_LEN, "n", array("EXIST_CHECK", "MAX_LENGTH_CHECK", "NUM_CHECK"));
        $this->objFormParam->addParam("帳票タイトル", "title", STEXT_LEN, "KVa", array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
        $this->objFormParam->addParam("帳票メッセージ1行目", "msg1", STEXT_LEN*3/5, "KVa", array("MAX_LENGTH_CHECK"));
        $this->objFormParam->addParam("帳票メッセージ2行目", "msg2", STEXT_LEN*3/5, "KVa", array("MAX_LENGTH_CHECK"));
        $this->objFormParam->addParam("帳票メッセージ3行目", "msg3", STEXT_LEN*3/5, "KVa", array("MAX_LENGTH_CHECK"));
        $this->objFormParam->addParam("備考1行目", "etc1", STEXT_LEN, "KVa", array("MAX_LENGTH_CHECK"));
        $this->objFormParam->addParam("備考2行目", "etc2", STEXT_LEN, "KVa", array("MAX_LENGTH_CHECK"));
        $this->objFormParam->addParam("備考3行目", "etc3", STEXT_LEN, "KVa", array("MAX_LENGTH_CHECK"));
        $this->objFormParam->addParam("ポイント表記", "disp_point", INT_LEN, "n", array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
    }

    /**
     *  入力内容のチェック
     *  @var SC_FormParam
     */
    
    function lfCheckError(&$objFormParam) {
        // 入力データを渡す。
        $arrRet = $objFormParam->getHashArray();
        $objFormParam->
        $objErr = new SC_CheckError($arrRet);
        
        $objErr->arrErr = $this->objFormParam->checkError();
        
        // 特殊項目チェック
        $objErr->doFunc(array("発行日", "year", "month", "day"), array("CHECK_DATE"));

        return $objErr->arrErr;
    }


}

