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
 * 受注履歴からカート遷移 のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Mypage_Order extends LC_Page {

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        parent::process();
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のAction.
     *
     * @return void
     */
    function action() {
        $objCustomer = new SC_Customer();
        $objCartSess = new SC_CartSession();

        //受注詳細データの取得
        $arrDisp = $this->lfGetOrderDetail($_POST['order_id']);

        //ログインしていない、またはDBに情報が無い場合
        if (!$objCustomer->isLoginSuccess(true) or count($arrDisp) == 0){
            SC_Utils_Ex::sfDispSiteError(CUSTOMER_ERROR);
        }

        for($num = 0; $num < count($arrDisp); $num++) {
            $product_class_id = $arrDisp[$num]['product_class_id'];
            $quantity = $arrDisp[$num]['quantity'];
            $product_type_id = $arrDisp[$num]['product_type_id'];

            $objCartSess->addProduct($product_class_id, $quantity, $product_type_id);
        }
        SC_Response_Ex::sendRedirect(CART_URLPATH);
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }

    // 受注詳細データの取得
    function lfGetOrderDetail($order_id) {
        $objQuery = new SC_Query();
        $objCustomer = new SC_Customer();
        //customer_idを検証
        $customer_id = $objCustomer->getValue("customer_id");
        $order_count = $objQuery->count("dtb_order", "order_id = ? and customer_id = ?", array($order_id, $customer_id));
        if ($order_count != 1) return array();
        $col = "product_class_id, quantity, product_type_id";
        $table = "dtb_order_detail LEFT JOIN dtb_products_class USING(product_class_id)";
        $where = "order_id = ?";
        $objQuery->setOrder("product_class_id");
        $arrRet = $objQuery->select($col, $table, $where, array($order_id));
        return $arrRet;
    }

}
?>
