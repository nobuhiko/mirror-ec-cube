<?php
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

// {{{ requires
require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

/**
 * 帳票出力 のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin_Order_Pdf extends LC_Page_Admin_Ex {

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
        $this->tpl_mainno = 'order';
        $this->tpl_subno = 'pdf';
        $this->tpl_maintitle = SC_I18n_Ex::t('TPL_MAINTITLE_001');
        $this->tpl_subtitle = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_001');

        $this->SHORTTEXT_MAX = STEXT_LEN;
        $this->MIDDLETEXT_MAX = MTEXT_LEN;
        $this->LONGTEXT_MAX = LTEXT_LEN;

        $this->arrType[0]  = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_002');

        $this->arrDownload[0] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_003');
        $this->arrDownload[1] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_004');
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
        $objDate = new SC_Date_Ex(1901);
        $objDate->setStartYear(RELEASE_YEAR);
        $this->arrYear = $objDate->getYear();
        $this->arrMonth = $objDate->getMonth();
        $this->arrDay = $objDate->getDay();

        // パラメーター管理クラス
        $this->objFormParam = new SC_FormParam_Ex();
        // パラメーター情報の初期化
        $this->lfInitParam($this->objFormParam);
        $this->objFormParam->setParam($_POST);
        // 入力値の変換
        $this->objFormParam->convParam();

        // どんな状態の時に isset($arrRet) == trueになるんだ? これ以前に$arrRet無いが、、、、
        if (!isset($arrRet)) $arrRet = array();
        switch ($this->getMode()) {
            case 'confirm':

                $status = $this->createPdf($this->objFormParam);
                if ($status === true) {
                    SC_Response_Ex::actionExit();
                } else {
                    $this->arrErr = $status;
                }
                break;
            default:
                $this->arrForm = $this->createFromValues($_GET['order_id'],$_POST['pdf_order_id']);
                break;
        }
        $this->setTemplate($this->tpl_mainpage);

    }

    /**
     *
     * PDF作成フォームのデフォルト値の生成
     */
    function createFromValues($order_id,$pdf_order_id) {
        // ここが$arrFormの初登場ということを明示するため宣言する。
        $arrForm = array();
        // タイトルをセット
        $arrForm['title'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_005');

        // 今日の日付をセット
        $arrForm['year']  = date('Y');
        $arrForm['month'] = date('m');
        $arrForm['day']   = date('d');

        // メッセージ
        $arrForm['msg1'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_006');
        $arrForm['msg2'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_007');
        $arrForm['msg3'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_008');

        // 注文番号があったら、セットする
        if (SC_Utils_Ex::sfIsInt($order_id)) {
            $arrForm['order_id'][0] = $order_id;
        } elseif (is_array($pdf_order_id)) {
            sort($pdf_order_id);
            foreach ($pdf_order_id AS $key=>$val) {
                $arrForm['order_id'][] = $val;
            }
        }

        return $arrForm;
    }

    /**
     *
     * PDFの作成
     * @param SC_FormParam $objFormParam
     */
    function createPdf(&$objFormParam) {

        $arrErr = $this->lfCheckError($objFormParam);
        $arrRet = $objFormParam->getHashArray();

        $this->arrForm = $arrRet;
        // エラー入力なし
        if (count($arrErr) == 0) {
            $objFpdf = new SC_Fpdf_Ex($arrRet['download'], $arrRet['title']);
            foreach ($arrRet['order_id'] AS $key => $val) {
                $arrPdfData = $arrRet;
                $arrPdfData['order_id'] = $val;
                $objFpdf->setData($arrPdfData);
            }
            $objFpdf->createPdf();
            return true;
        } else {
            return $arrErr;
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

    /**
     *  パラメーター情報の初期化
     *  @param SC_FormParam
     */
    function lfInitParam(&$objFormParam) {
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_NUMBER'), 'order_id', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_NUMBER'), 'pdf_order_id', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_OUTPUT_DATE'), 'year', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_OUTPUT_DATE'), 'month', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_OUTPUT_DATE'), 'day', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_PDF_TYPE'), 'type', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_HOW_TO_DOWNLOAD'), 'download', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_PDF_TITLE'), 'title', STEXT_LEN, 'KVa', array('EXIST_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_PDF_MSG1'), 'msg1', STEXT_LEN*3/5, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_PDF_MSG2'), 'msg2', STEXT_LEN*3/5, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_PDF_MSG3'), 'msg3', STEXT_LEN*3/5, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_PDF_NOTE1'), 'etc1', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_PDF_NOTE2'), 'etc2', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_ORDER_PDF_NOTE3'), 'etc3', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam(SC_I18n_Ex::t('PARAM_LABEL_DISP_POINT'), 'disp_point', INT_LEN, 'n', array('EXIST_CHECK', 'MAX_LENGTH_CHECK'));
    }

    /**
     *  入力内容のチェック
     *  @var SC_FormParam
     */

    function lfCheckError(&$objFormParam) {
        // 入力データを渡す。
        $arrRet = $objFormParam->getHashArray();
        $arrErr = $objFormParam->checkError();

        $year = $objFormParam->getValue('year');
        if (!is_numeric($year)) {
            $arrErr['year'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_009');
        }

        $month = $objFormParam->getValue('month');
        if (!is_numeric($month)) {
            $arrErr['month'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_010');
        } else if (0 >= $month && 12 < $month) {

            $arrErr['month'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_011');
        }

        $day = $objFormParam->getValue('day');
        if (!is_numeric($day)) {
            $arrErr['day'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_012');
        } else if (0 >= $day && 31 < $day) {

            $arrErr['day'] = SC_I18n_Ex::t('LC_Page_Admin_Order_Pdf_013');
        }

        return $arrErr;
    }

}
