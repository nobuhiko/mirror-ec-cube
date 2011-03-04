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

/**
 * 商品購入関連のヘルパークラス.
 *
 * TODO 購入時強制会員登録機能(#521)の実装を検討
 * TODO dtb_customer.buy_times, dtb_customer.buy_total の更新
 *
 * @package Helper
 * @author Kentaro Ohkouchi
 * @version $Id$
 */
class SC_Helper_Purchase {

    /**
     * 受注を完了する.
     *
     * 下記のフローで受注を完了する.
     *
     * 1. トランザクションを開始する
     * 2. カートの内容を検証する.
     * 3. 受注一時テーブルから受注データを読み込む
     * 4. ユーザーがログインしている場合はその他の発送先へ登録する
     * 5. 受注データを受注テーブルへ登録する
     * 6. トランザクションをコミットする
     *
     * 実行中に, 何らかのエラーが発生した場合, 処理を中止しエラーページへ遷移する
     *
     * 決済モジュールを使用する場合は受注ステータスを「決済処理中」に設定し,
     * 決済完了後「新規受付」に変更すること
     *
     * @param integer $orderStatus 受注処理を完了する際に設定する受注ステータス
     * @return void
     */
    function completeOrder($orderStatus = ORDER_NEW) {
        $objQuery =& SC_Query::getSingletonInstance();
        $objSiteSession = new SC_SiteSession_Ex();
        $objCartSession = new SC_CartSession_Ex();
        $objCustomer = new SC_Customer_Ex();
        $customerId = $objCustomer->getValue('customer_id');

        $objQuery->begin();
        if (!$objSiteSession->isPrePage()) {
            SC_Utils_Ex::sfDispSiteError(PAGE_ERROR, $objSiteSession);
        }

        $uniqId = $objSiteSession->getUniqId();
        $this->verifyChangeCart($uniqId, $objCartSession);

        $orderTemp = $this->getOrderTemp($uniqId);

        $orderTemp['status'] = $orderStatus;
        $orderId = $this->registerOrderComplete($orderTemp, $objCartSession,
                                                $objCartSession->getKey());
        $shippingTemp =& $this->getShippingTemp();
        if (count($shippingTemp) > 1) {
            foreach ($shippingTemp as $shippingId => $val) {
                $this->registerShipmentItem($orderId, $shippingId,
                                            $val['shipment_item']);
            }
        }

        $this->registerShipping($orderId, $shippingTemp);
        $objQuery->commit();
        $this->unsetShippingTemp();
        $objCustomer->updateSession();
    }

    /**
     * カートに変化が無いか検証する.
     *
     * ユニークIDとセッションのユニークIDを比較し, 異なる場合は
     * エラー画面を表示する.
     *
     * カートが空の場合, 購入ボタン押下後にカートが変更された場合は
     * カート画面へ遷移する.
     *
     * @param string $uniqId ユニークID
     * @param SC_CartSession $objCartSession
     * @return void
     */
    function verifyChangeCart($uniqId, &$objCartSession) {
        $cartKeys = $objCartSession->getKeys();

        // カート内が空でないか
        if (SC_Utils_Ex::isBlank($cartKeys)) {
            SC_Response_Ex::sendRedirect(CART_URLPATH);
            exit;
        }

        foreach ($cartKeys as $cartKey) {
            // 初回のみカートの内容を保存
            $objCartSession->saveCurrentCart($uniqid, $cartKey);

            /*
             * POSTのユニークIDとセッションのユニークIDを比較
             *(ユニークIDがPOSTされていない場合はスルー)
             */
            if(!SC_SiteSession_Ex::checkUniqId()) {
                SC_Utils_Ex::sfDispSiteError(CANCEL_PURCHASE);
                exit;
            }

            // 購入ボタンを押してから変化がないか
            $quantity = $objCartSession->getTotalQuantity($cartKey);
            if($objCartSession->checkChangeCart($cartKey) || !($quantity > 0)) {
                SC_Response_Ex::sendRedirect(CART_URLPATH);
                exit;
            }
        }
    }

    /**
     * 受注一時情報を取得する.
     *
     * @param integer $uniqId 受注一時情報ID
     * @return array 受注一時情報の配列
     */
    function getOrderTemp($uniqId) {
        $objQuery =& SC_Query::getSingletonInstance();
        return $objQuery->getRow("*", "dtb_order_temp", "order_temp_id = ?",
                                 array($uniqId));
    }

    /**
     * 受注一時情報を保存する.
     *
     * 既存のデータが存在しない場合は新規保存. 存在する場合は更新する.
     * 既存のデータが存在せず, ユーザーがログインしている場合は,
     * 会員情報をコピーする.
     *
     * @param integer $uniqId 受注一時情報ID
     * @param array $params 登録する受注情報の配列
     * @param SC_Customer $objCustomer SC_Customer インスタンス
     * @return array void
     */
    function saveOrderTemp($uniqId, $params, &$objCustomer) {
        if (SC_Utils_Ex::isBlank($uniqId)) {
            return;
        }
        $params['device_type_id'] = SC_Display::detectDevice();
        $objQuery =& SC_Query::getSingletonInstance();
        // 存在するカラムのみを対象とする
        $cols = $objQuery->listTableFields('dtb_order_temp');
        foreach ($params as $key => $val) {
            if (in_array($key, $cols)) {
                $sqlval[$key] = $val;
            }
        }

        $sqlval['session'] = serialize($_SESSION);
        $exists = $this->getOrderTemp($uniqId);
        if (SC_Utils_Ex::isBlank($exists)) {
            $this->copyFromCustomer($sqlval, $objCustomer);
            $sqlval['order_temp_id'] = $uniqId;
            $sqlval['create_date'] = "now()";
            $objQuery->insert("dtb_order_temp", $sqlval);
        } else {
            $objQuery->update("dtb_order_temp", $sqlval, 'order_temp_id = ?',
                              array($uniqId));
        }
    }

    /**
     * セッションの配送情報を取得する.
     */
    function getShippingTemp() {
        return $_SESSION['shipping'];
    }

    /**
     * 配送商品を設定する.
     *
     * @param integer $shipping_id 配送先ID
     * @param integer $product_class_id 商品規格ID
     * @param integer $quantity 数量
     * @return void
     */
    function setShipmentItemTemp($shipping_id, $product_class_id, $quantity) {
        // 配列が長くなるので, リファレンスを使用する
        $arrItems =& $_SESSION['shipping'][$shipping_id]['shipment_item'];

        $arrItems[$product_class_id]['shipping_id'] = $shipping_id;
        $arrItems[$product_class_id]['product_class_id'] = $product_class_id;
        $arrItems[$product_class_id]['quantity'] += $quantity;

        $objProduct = new SC_Product_Ex();
        if (empty($arrItems[$product_class_id]['productsClass'])) {
            $product =& $objProduct->getDetailAndProductsClass($product_class_id);
            $arrItems[$product_class_id]['productsClass'] = $product;
        }
        $incTax = SC_Helper_DB_Ex::sfCalcIncTax($arrItems[$product_class_id]['productsClass']['price02']);
        $arrItems[$product_class_id]['total_inctax'] = $incTax * $arrItems[$product_class_id]['quantity'];
    }

    /**
     * 配送商品の情報でカートを更新する.
     *
     * @param SC_CartSession $objCartSession SC_CartSession インスタンス
     */
    function shippingItemTempToCart(&$objCartSession) {
        $arrShipmentItems = array();

        foreach (array_keys($_SESSION['shipping']) as $shipping_id) {
            foreach (array_keys($_SESSION['shipping'][$shipping_id]['shipment_item']) as $product_class_id) {
                $arrShipmentItems[$product_class_id] += $_SESSION['shipping'][$shipping_id]['shipment_item'][$product_class_id]['quantity'];
           }
        }
        foreach ($arrShipmentItems as $product_class_id => $quantity) {
            $objCartSession->setProductValue($product_class_id, 'quantity',
                                             $quantity,$objCartSession->getKey());
        }
    }

    /**
     * 配送先都道府県の配列を返す.
     */
    function getShippingPref() {
        $results = array();
        foreach ($_SESSION['shipping'] as $val) {
            $results[] = $val['shipping_pref'];
        }
        return $results;
    }

    /**
     * 複数配送指定の購入かどうか.
     *
     * @return boolean 複数配送指定の購入の場合 true
     */
    function isMultiple() {
        return (count($this->getShippingTemp()) > 1);
    }

    /**
     * 配送情報をセッションに保存する.
     *
     * @param array $arrSrc 配送情報の連想配列
     * @param integer $shipping_id 配送先ID
     * @return void
     */
    function saveShippingTemp(&$arrSrc, $shipping_id = 0) {
        if (empty($_SESSION['shipping'][$shipping_id])) {
            $_SESSION['shipping'][$shipping_id] = $arrSrc;
            $_SESSION['shipping'][$shipping_id]['shipping_id'] = $shipping_id;
        } else {
            $_SESSION['shipping'][$shipping_id] = array_merge($_SESSION['shipping'][$shipping_id], $arrSrc);
            $_SESSION['shipping'][$shipping_id]['shipping_id'] = $shipping_id;
        }
    }

    /**
     * セッションの配送情報を破棄する.
     */
    function unsetShippingTemp() {
        unset($_SESSION['shipping']);
    }

    /**
     * 会員情報を受注情報にコピーする.
     *
     * ユーザーがログインしていない場合は何もしない.
     * 会員情報を $dest の order_* へコピーする.
     * customer_id は強制的にコピーされる.
     *
     * @param array $dest コピー先の配列
     * @param SC_Customer $objCustomer SC_Customer インスタンス
     * @param string $prefix コピー先の接頭辞. デフォルト order
     * @param array $keys コピー対象のキー
     * @return void
     */
    function copyFromCustomer(&$dest, &$objCustomer, $prefix = 'order',
                              $keys = array('name01', 'name02', 'kana01', 'kana02',
                                            'sex', 'zip01', 'zip02', 'pref',
                                            'addr01', 'addr02',
                                            'tel01', 'tel02', 'tel03', 'job',
                                            'birth', 'email')) {
        if ($objCustomer->isLoginSuccess(true)) {

            foreach ($keys as $key) {
                if (in_array($key, $keys)) {
                    $dest[$prefix . '_' . $key] = $objCustomer->getValue($key);
                }
            }

            if (Net_UserAgent_Mobile::isMobile()
                && in_array('email', $keys)) {
                $email_mobile = $objCustomer->getValue('email_mobile');
                if (empty($email_mobile)) {
                    $dest[$prefix . '_email'] = $objCustomer->getValue('email');
                } else {
                    $dest[$prefix . '_email'] = $email_mobile;
                }
            }

            $dest['customer_id'] = $objCustomer->getValue('customer_id');
            $dest['update_date'] = 'Now()';
        }
    }

    /**
     * 受注情報を配送情報にコピーする.
     *
     * 受注情報($src)を $dest の order_* へコピーする.
     *
     * TODO 汎用的にして SC_Utils へ移動
     *
     * @param array $dest コピー先の配列
     * @param array $src コピー元の配列
     * @param array $keys コピー対象のキー
     * @param string $prefix コピー先の接頭辞. デフォルト shipping
     * @param string $src_prefix コピー元の接頭辞. デフォルト order
     * @return void
     */
    function copyFromOrder(&$dest, $src,
                           $prefix = 'shipping', $src_prefix = 'order',
                           $keys = array('name01', 'name02', 'kana01', 'kana02',
                                         'sex', 'zip01', 'zip02', 'pref',
                                         'addr01', 'addr02',
                                         'tel01', 'tel02', 'tel03')) {
        if (!SC_Utils_Ex::isBlank($prefix)) {
            $prefix = $prefix . '_';
        }
        if (!SC_Utils_Ex::isBlank($src_prefix)) {
            $src_prefix = $src_prefix . '_';
        }
        foreach ($keys as $key) {
            if (in_array($key, $keys)) {
                $dest[$prefix . $key] = $src[$src_prefix . $key];
            }
        }
    }

    /**
     * 購入金額に応じた支払方法を取得する.
     *
     * @param integer $total 購入金額
     * @param integer $deliv_id 配送業者ID
     * @return array 購入金額に応じた支払方法の配列
     */
    function getPaymentsByPrice($total, $deliv_id) {

        $arrPaymentIds = $this->getPayments($deliv_id);
        $objQuery =& SC_Query::getSingletonInstance();

        // 削除されていない支払方法を取得
        $where = 'del_flg = 0 AND payment_id IN (' . implode(', ', array_pad(array(), count($arrPaymentIds), '?')) . ')';
        $objQuery->setOrder("rank DESC");
        $payments = $objQuery->select("payment_id, payment_method, rule, upper_rule, note, payment_image, charge", "dtb_payment", $where, $arrPaymentIds);

        foreach ($payments as $data) {
            // 下限と上限が設定されている
            if (strlen($data['rule']) != 0 && strlen($data['upper_rule']) != 0) {
                if ($data['rule'] <= $total_inctax && $data['upper_rule'] >= $total_inctax) {
                    $arrPayment[] = $data;
                }
            }
            // 下限のみ設定されている
            elseif (strlen($data['rule']) != 0) {
                if($data['rule'] <= $total_inctax) {
                    $arrPayment[] = $data;
                }
            }
            // 上限のみ設定されている
            elseif (strlen($data['upper_rule']) != 0) {
                if($data['upper_rule'] >= $total_inctax) {
                    $arrPayment[] = $data;
                }
            }
            // いずれも設定なし
            else {
                $arrPayment[] = $data;
            }
          }
        return $arrPayment;
    }

    /**
     * お届け日一覧を取得する.
     */
    function getDelivDate(&$objCartSess, $productTypeId) {
        $cartList = $objCartSess->getCartList($productTypeId);
        $delivDateIds = array();
        foreach ($cartList as $item) {
            $delivDateIds[] = $item['productsClass']['deliv_date_id'];
        }
        $max_date = max($delivDateIds);
        //発送目安
        switch($max_date) {
        //即日発送
        case '1':
            $start_day = 1;
            break;
            //1-2日後
        case '2':
            $start_day = 3;
            break;
            //3-4日後
        case '3':
            $start_day = 5;
            break;
            //1週間以内
        case '4':
            $start_day = 8;
            break;
            //2週間以内
        case '5':
            $start_day = 15;
            break;
            //3週間以内
        case '6':
            $start_day = 22;
            break;
            //1ヶ月以内
        case '7':
            $start_day = 32;
            break;
            //2ヶ月以降
        case '8':
            $start_day = 62;
            break;
            //お取り寄せ(商品入荷後)
        case '9':
            $start_day = "";
            break;
        default:
            //お届け日が設定されていない場合
            $start_day = "";
        }
        //お届け可能日のスタート値から、お届け日の配列を取得する
        $arrDelivDate = $this->getDateArray($start_day, DELIV_DATE_END_MAX);
        return $arrDelivDate;
    }

    /**
     * お届け可能日のスタート値から, お届け日の配列を取得する.
     */
    function getDateArray($start_day, $end_day) {
        $masterData = new SC_DB_MasterData();
        $arrWDAY = $masterData->getMasterData("mtb_wday");
        //お届け可能日のスタート値がセットされていれば
        if($start_day >= 1) {
            $now_time = time();
            $max_day = $start_day + $end_day;
            // 集計
            for ($i = $start_day; $i < $max_day; $i++) {
                // 基本時間から日数を追加していく
                $tmp_time = $now_time + ($i * 24 * 3600);
                list($y, $m, $d, $w) = explode(" ", date("Y m d w", $tmp_time));
                $val = sprintf("%04d/%02d/%02d(%s)", $y, $m, $d, $arrWDAY[$w]);
                $arrDate[$val] = $val;
            }
        } else {
            $arrDate = false;
        }
        return $arrDate;
    }

    /**
     * 配送業者IDからお届け時間の配列を取得する.
     *
     * @param integer $deliv_id 配送業者ID
     * @return array お届け時間の配列
     */
    function getDelivTime($deliv_id) {
        $objQuery =& SC_Query::getSingletonInstance();
        $objQuery->setOrder('time_id');
        $results = $objQuery->select('time_id, deliv_time',
                                     'dtb_delivtime',
                                     'deliv_id = ?', array($deliv_id));
        $arrDelivTime = array();
        foreach ($results as $val) {
            $arrDelivTime[$val['time_id']] = $val['deliv_time'];
        }
        return $arrDelivTime;
    }

    /**
     * 商品種別ID から配送業者を取得する.
     *
     * @param integer $product_type_id 商品種別ID
     * @return array 配送業者の配列
     */
    function getDeliv($product_type_id) {
        $objQuery =& SC_Query::getSingletonInstance();
        $objQuery->setOrder('rank');
        return $objQuery->select('*', 'dtb_deliv', 'product_type_id = ?',
                                 array($product_type_id));
    }

    /**
     * 配送業者ID から, 有効な支払方法IDを取得する.
     *
     * @param integer $deliv_id 配送業者ID
     * @return array 有効な支払方法IDの配列
     */
    function getPayments($deliv_id) {
        $objQuery =& SC_Query::getSingletonInstance();
        $objQuery->setOrder('rank');
        return $objQuery->getCol('payment_id', 'dtb_payment_options',
                                 'deliv_id = ?',
                                 array($deliv_id), MDB2_FETCHMODE_ORDERED);
    }

    /**
     * 配送情報の登録を行う.
     *
     * $arrParam のうち, dtb_shipping テーブルに存在するカラムのみを登録する.
     *
     * TODO UPDATE/INSERT にする
     *
     * @param integer $order_id 受注ID
     * @param array $arrParams 配送情報の連想配列
     * @param boolean $convert_shipping_date yyyy/mm/dd(EEE) 形式の配送日付を変換する場合 true
     * @return void
     */
    function registerShipping($order_id, $arrParams, $convert_shipping_date = true) {
        $objQuery =& SC_Query::getSingletonInstance();
        $table = 'dtb_shipping';
        $where = 'order_id = ?';
        $objQuery->delete($table, $where, array($order_id));

         foreach ($arrParams as $arrShipping) {

            $arrValues = $objQuery->extractOnlyColsOf($table, $arrShipping);

            // 配送日付を timestamp に変換
            if (!SC_Utils_Ex::isBlank($arrValues['shipping_date'])
                && $convert_shipping_date) {
                $d = mb_strcut($arrValues["shipping_date"], 0, 10);
                $arrDate = explode("/", $d);
                $ts = mktime(0, 0, 0, $arrDate[1], $arrDate[2], $arrDate[0]);
                $arrValues['shipping_date'] = date("Y-m-d", $ts);
            }

            $arrValues['order_id'] = $order_id;
            $arrValues['create_date'] = 'Now()';
            $arrValues['update_date'] = 'Now()';
            $objQuery->insert($table, $arrValues);
        }
    }

    /**
     * 配送商品を登録する.
     *
     * @param integer $order_id 受注ID
     * @param integer $shipping_id 配送先ID
     * @param array $arrParams 配送商品の配列
     * @return void
     */
    function registerShipmentItem($order_id, $shipping_id, $arrParams) {
        $objQuery =& SC_Query::getSingletonInstance();
        $table = 'dtb_shipment_item';
        $where = 'order_id = ? AND shipping_id = ?';
        $objQuery->delete($table, $where, array($order_id, $shipping_id));

        $objProduct = new SC_Product_Ex();
        foreach ($arrParams as $arrValues) {
            if (SC_Utils_Ex::isBlank($arrValues['product_class_id'])) {
                continue;
            }
            $d = $objProduct->getDetailAndProductsClass($arrValues['product_class_id']);
            $name = SC_Utils_Ex::isBlank($arrValues['product_name'])
                ? $d['name']
                : $arrValues['product_name'];

            $code = SC_Utils_Ex::isBlank($arrValues['product_code'])
                ? $d['code']
                : $arrValues['product_code'];

            $cname1 = SC_Utils_Ex::isBlank($arrValues['classcategory_name1'])
                ? $d['classcategory_name1']
                : $arrValues['classcategory_name1'];

            $cname2 = SC_Utils_Ex::isBlank($arrValues['classcategory_name2'])
                ? $d['classcategory_name2']
                : $arrValues['classcategory_name2'];

            $price = SC_Utils_Ex::isBlank($arrValues['price'])
                ? $d['price02']
                : $arrValues['price'];

            $arrValues['order_id'] = $order_id;
            $arrValues['shipping_id'] = $shipping_id;
            $arrValues['product_name'] = $name;
            $arrValues['product_code'] = $code;
            $arrValues['classcategory_name1'] = $cname1;
            $arrValues['classcategory_name2'] = $cname2;
            $arrValues['price'] = $price;

            $arrExtractValues = $objQuery->extractOnlyColsOf($table, $arrValues);
            $objQuery->insert($table, $arrExtractValues);
        }
    }

    /**
     * 受注登録を完了する.
     *
     * 引数の受注情報を受注テーブル及び受注詳細テーブルに登録する.
     * 登録後, 受注一時テーブルに削除フラグを立て, カートの内容を削除する.
     *
     * @param array $orderParams 登録する受注情報の配列
     * @param SC_CartSession $objCartSession カート情報のインスタンス
     * @param integer $cartKey 登録を行うカート情報のキー
     * @param integer 受注ID
     */
    function registerOrderComplete($orderParams, &$objCartSession, $cartKey) {
        $objQuery =& SC_Query::getSingletonInstance();

        // 不要な変数を unset
        $unsets = array('mailmaga_flg', 'deliv_check', 'point_check', 'password',
                        'reminder', 'reminder_answer', 'mail_flag', 'session');
        foreach ($unsets as $unset) {
            unset($orderParams[$unset]);
        }

        // 注文ステータスの指定が無い場合は新規受付
        if(SC_Utils_Ex::isBlank($orderParams['status'])) {
            $orderParams['status'] = ORDER_NEW;
        }

        $orderParams['create_date'] = 'Now()';
        $orderParams['update_date'] = 'Now()';

        $this->registerOrder($orderParams['order_id'], $orderParams);

        // 詳細情報を取得
        $cartItems = $objCartSession->getCartList($cartKey);

        // 詳細情報を生成
        $objProduct = new SC_Product_Ex();
        $i = 0;
        foreach ($cartItems as $item) {
            $p =& $item['productsClass'];
            $arrDetail[$i]['order_id'] = $orderParams['order_id'];
            $arrDetail[$i]['product_id'] = $p['product_id'];
            $arrDetail[$i]['product_class_id'] = $p['product_class_id'];
            $arrDetail[$i]['product_name'] = $p['name'];
            $arrDetail[$i]['product_code'] = $p['product_code'];
            $arrDetail[$i]['classcategory_name1'] = $p['classcategory_name1'];
            $arrDetail[$i]['classcategory_name2'] = $p['classcategory_name2'];
            $arrDetail[$i]['point_rate'] = $item['point_rate'];
            $arrDetail[$i]['price'] = $item['price'];
            $arrDetail[$i]['quantity'] = $item['quantity'];

            // 在庫の減少処理
            if (!$objProduct->reduceStock($p['product_class_id'], $item['quantity'])) {
                $objQuery->rollback();
                SC_Utils_Ex::sfDispSiteError(SOLD_OUT, "", true);
            }
            $i++;
        }
        $this->registerOrderDetail($orderParams['order_id'], $arrDetail);

        $objQuery->update("dtb_order_temp", array('del_flg' => 1),
                          "order_temp_id = ?",
                          array(SC_SiteSession_Ex::getUniqId()));

        $objCartSession->delAllProducts($cartKey);
        SC_SiteSession_Ex::unsetUniqId();
        return $orderParams['order_id'];
    }

    /**
     * 受注情報を登録する.
     *
     * 既に受注IDが存在する場合は, 受注情報を更新する.
     * 引数の受注IDが, 空白又は null の場合は, 新しく受注IDを発行して登録する.
     *
     * @param integer $order_id 受注ID
     * @param array $arrParams 受注情報の連想配列
     * @return integer 受注ID
     */
    function registerOrder($order_id, $arrParams) {
        $table = 'dtb_order';
        $where = 'order_id = ?';
        $objQuery = SC_Query::getSingletonInstance();
        $arrValues = $objQuery->extractOnlyColsOf($table, $arrParams);

        $exists = $objQuery->count($table, $where, array($order_id));
        if ($exists > 0) {

            $this->sfUpdateOrderStatus($order_id, $arrValues['status'],
                                       $arrValues['add_point'],
                                       $arrValues['use_point'],
                                       $arrValues);
            $this->sfUpdateOrderNameCol($order_id);

            $arrValues['update_date'] = 'now()';
            $objQuery->update($table, $arrValues, $where, array($order_id));
        } else {
            if (SC_Utils_Ex::isBlank($order_id)) {
                $order_id = $objQuery->nextVal('dtb_order_order_id');
            }
            $arrValues['order_id'] = $order_id;
            $arrValues['customer_id'] =
                    SC_Utils_Ex::isBlank($arrValues['customer_id'])
                    ? 0 : $arrValues['customer_id'];
            $arrValues['create_date'] = 'now()';
            $arrValues['update_date'] = 'now()';
            $objQuery->insert($table, $arrValues);

            $this->sfUpdateOrderStatus($order_id, $arrValues['status'],
                                       $arrValues['add_point'],
                                       $arrValues['use_point'],
                                       $arrValues);
            $this->sfUpdateOrderNameCol($order_id);

        }
        return $order_id;
    }

    /**
     * 受注詳細情報を登録する.
     *
     * 既に, 該当の受注が存在する場合は, 受注情報を削除し, 登録する.
     *
     * @param integer $order_id 受注ID
     * @param array $arrParams 受注情報の連想配列
     * @return void
     */
    function registerOrderDetail($order_id, $arrParams) {
        $table = 'dtb_order_detail';
        $where = 'order_id = ?';
        $objQuery = SC_Query::getSingletonInstance();

        $objQuery->delete($table, $where, array($order_id));
        foreach ($arrParams as $arrDetail) {
            $arrValues = $objQuery->extractOnlyColsOf($table, $arrDetail);
            $arrValues['order_detail_id'] = $objQuery->nextVal('dtb_order_detail_order_detail_id');
            $arrValues['order_id'] = $order_id;
            $objQuery->insert($table, $arrValues);
        }
    }

    /**
     * 受注情報を取得する.
     *
     * @param integer $order_id 受注ID
     * @param integer $customer_id 顧客ID
     * @return array 受注情報の配列
     */
    function getOrder($order_id, $customer_id = null) {
        $objQuery =& SC_Query::getSingletonInstance();
        $where = 'order_id = ?';
        $arrValues = array($order_id);
        if (!SC_Utils_Ex::isBlank($customer_id)) {
            $where .= ' AND customer_id = ?';
            $arrValues[] = $customer_id;
        }
        return $objQuery->getRow('*', 'dtb_order', $where, $arrValues);
    }

    /**
     * 受注詳細を取得する.
     *
     * @param integer $order_id 受注ID
     * @param boolean $has_order_status 受注ステータス, 入金日も含める場合 true
     * @return array 受注詳細の配列
     */
    function getOrderDetail($order_id, $has_order_status = true) {
        $objQuery =& SC_Query::getSingletonInstance();
        $dbFactory  = SC_DB_DBFactory_Ex::getInstance();
        $col = <<< __EOS__
            T3.product_id,
            T3.product_class_id as product_class_id,
            T3.product_type_id AS product_type_id,
            T2.product_code,
            T2.product_name,
            T2.classcategory_name1 AS classcategory_name1,
            T2.classcategory_name2 AS classcategory_name2,
            T2.price,
            T2.quantity,
            T2.point_rate,
__EOS__;
        if ($has_order_status) {
            $col .= 'T1.status AS status, T1.payment_date AS payment_date,';

        }
        $col .= <<< __EOS__

            CASE WHEN EXISTS(
                    SELECT 1 FROM dtb_products
                     WHERE product_id = T3.product_id
                       AND del_flg = 0
                       AND status = 1)
                 THEN '1' ELSE '0'
                  END AS enable,
__EOS__;
        $col .= $dbFactory->getDownloadableDaysWhereSql('T1') . ' AS effective';
        $from = <<< __EOS__
                 dtb_order T1
            JOIN dtb_order_detail T2
              ON T1.order_id = T2.order_id
            JOIN dtb_products_class T3
              ON T2.product_class_id = T3.product_class_id
__EOS__;
        $objQuery->setOrder('T2.order_detail_id');
        return $objQuery->select($col, $from, 'T1.order_id = ?', array($order_id));
    }

    /**
     * 配送情報を取得する.
     *
     * @param integer $order_id 受注ID
     * @param boolean $has_items 結果に配送商品も含める場合 true
     * @return array 配送情報の配列
     */
    function getShippings($order_id, $has_items = true) {
        $objQuery =& SC_Query::getSingletonInstance();
        $arrResults = array();
        $objQuery->setOrder('shipping_id');
        $arrShippings = $objQuery->select("*", "dtb_shipping", "order_id = ?",
                                          array($order_id));
        // shipping_id ごとの配列を生成する
        foreach ($arrShippings as $shipping) {
            foreach ($shipping as $key => $val) {
                $arrResults[$shipping['shipping_id']][$key] = $val;
            }
        }

        if ($has_items) {
            $objProduct = new SC_Product_Ex();
            foreach (array_keys($arrResults) as $shipping_id) {
                $arrResults[$shipping_id]['shipment_item']
                        =& $this->getShipmentItems($order_id, $shipping_id);
            }
        }
        return $arrResults;
    }

    /**
     * 配送商品を取得する.
     *
     * @param integer $order_id 受注ID
     * @param integer $shipping_id 配送先ID
     * @param boolean $has_detail 商品詳細も取得する場合 true
     * @return array 商品規格IDをキーにした配送商品の配列
     */
    function getShipmentItems($order_id, $shipping_id, $has_detail = true) {
        $objQuery =& SC_Query::getSingletonInstance();
        $objProduct = new SC_Product_Ex();
        $arrResults = array();
        $arrItems = $objQuery->select("*", "dtb_shipment_item",
                                      "order_id = ? AND shipping_id = ?",
                                      array($order_id, $shipping_id));

        foreach ($arrItems as $key => $arrItem) {
            $product_class_id = $arrItem['product_class_id'];

            foreach ($arrItem as $detailKey => $detailVal) {
                $arrResults[$key][$detailKey] = $detailVal;
            }
            // 商品詳細を関連づける
            if ($has_detail) {
                $arrResults[$key]['productsClass']
                    =& $objProduct->getDetailAndProductsClass($product_class_id);
            }
        }
        return $arrResults;
    }

    /**
     * 受注完了メールを送信する.
     *
     * HTTP_USER_AGENT の種別により, 携帯電話の場合は携帯用の文面,
     * PC の場合は PC 用の文面でメールを送信する.
     *
     * @param integer $orderId 受注ID
     * @return void
     */
    function sendOrderMail($orderId) {
        $mailHelper = new SC_Helper_Mail_Ex();
        $mailHelper->sfSendOrderMail($orderId,
                                     SC_MobileUserAgent::isMobile() ? 2 : 1);
    }

    /**
     * 受注.対応状況の更新
     *
     * ・必ず呼び出し元でトランザクションブロックを開いておくこと。
     *
     * @param integer $orderId 注文番号
     * @param integer|null $newStatus 対応状況 (null=変更無し)
     * @param integer|null $newAddPoint 加算ポイント (null=変更無し)
     * @param integer|null $newUsePoint 使用ポイント (null=変更無し)
     * @param array $sqlval 更新後の値をリファレンスさせるためのパラメータ
     * @return void
     */
    function sfUpdateOrderStatus($orderId, $newStatus = null, $newAddPoint = null, $newUsePoint = null, &$sqlval) {
        $objQuery =& SC_Query::getSingletonInstance();

        $arrOrderOld = $objQuery->getRow('status, add_point, use_point, customer_id', 'dtb_order', 'order_id = ?', array($orderId));

        // 対応状況が変更無しの場合、DB値を引き継ぐ
        if (is_null($newStatus)) {
            $newStatus = $arrOrderOld['status'];
        }

        // 使用ポイント、DB値を引き継ぐ
        if (is_null($newUsePoint)) {
            $newUsePoint = $arrOrderOld['use_point'];
        }

        // 加算ポイント、DB値を引き継ぐ
        if (is_null($newAddPoint)) {
            $newAddPoint = $arrOrderOld['add_point'];
        }

        if (USE_POINT !== false) {
            // 顧客.ポイントの加減値
            $addCustomerPoint = 0;

            // ▼使用ポイント
            // 変更前の対応状況が利用対象の場合、変更前の使用ポイント分を戻す
            if (SC_Utils_Ex::sfIsUsePoint($arrOrderOld['status'])) {
                $addCustomerPoint += $arrOrderOld['use_point'];
            }

            // 変更後の対応状況が利用対象の場合、変更後の使用ポイント分を引く
            if (SC_Utils_Ex::sfIsUsePoint($newStatus)) {
                $addCustomerPoint -= $newUsePoint;
            }
            // ▲使用ポイント

            // ▼加算ポイント
            // 変更前の対応状況が加算対象の場合、変更前の加算ポイント分を戻す
            if (SC_Utils_Ex::sfIsAddPoint($arrOrderOld['status'])) {
                $addCustomerPoint -= $arrOrderOld['add_point'];
            }

            // 変更後の対応状況が加算対象の場合、変更後の加算ポイント分を足す
            if (SC_Utils_Ex::sfIsAddPoint($newStatus)) {
                $addCustomerPoint += $newAddPoint;
            }
            // ▲加算ポイント

            if ($addCustomerPoint != 0) {
                // ▼顧客テーブルの更新
                $sqlval = array();
                $where = '';
                $arrVal = array();
                $arrRawSql = array();
                $arrRawSqlVal = array();

                $sqlval['update_date'] = 'Now()';
                $arrRawSql['point'] = 'point + ?';
                $arrRawSqlVal[] = $addCustomerPoint;
                $where .= 'customer_id = ?';
                $arrVal[] = $arrOrderOld['customer_id'];

                $objQuery->update('dtb_customer', $sqlval, $where, $arrVal, $arrRawSql, $arrRawSqlVal);
                // ▲顧客テーブルの更新

                // 顧客.ポイントをマイナスした場合、
                if ($addCustomerPoint < 0) {
                    $sql = 'SELECT point FROM dtb_customer WHERE customer_id = ?';
                    $point = $objQuery->getOne($sql, array($arrOrderOld['customer_id']));
                    // 変更後の顧客.ポイントがマイナスの場合、
                    if ($point < 0) {
                        // ロールバック
                        $objQuery->rollback();
                        // エラー
                        SC_Utils_Ex::sfDispSiteError(LACK_POINT);
                    }
                }
            }
        }

        // ▼受注テーブルの更新
        if (empty($sqlval)) {
            $sqlval = array();
        }

        if (USE_POINT !== false) {
            $sqlval['add_point'] = $newAddPoint;
            $sqlval['use_point'] = $newUsePoint;
        }
        // ステータスが発送済みに変更の場合、発送日を更新
        if ($arrOrderOld['status'] != ORDER_DELIV && $newStatus == ORDER_DELIV) {
            $sqlval['commit_date'] = 'Now()';
        }
        // ステータスが入金済みに変更の場合、入金日を更新
        elseif ($arrOrderOld['status'] != ORDER_PRE_END && $newStatus == ORDER_PRE_END) {
            $sqlval['payment_date'] = 'Now()';
        }

        $sqlval['status'] = $newStatus;
        $sqlval['update_date'] = 'Now()';

        $cols = $objQuery->listTableFields('dtb_order');
        $dest = array();
        foreach ($sqlval as $key => $val) {
            if (in_array($key, $cols)) {
                $dest[$key] = $val;
            }
        }

        $objQuery->update('dtb_order', $dest, 'order_id = ?', array($orderId));
        // ▲受注テーブルの更新
    }

    /**
     * 受注の名称列を更新する
     *
     * @param integer $order_id 更新対象の注文番号
     * @param boolean $temp_table 更新対象は「受注_Temp」か
     * @static
     */
    function sfUpdateOrderNameCol($order_id, $temp_table = false) {
        $objQuery =& SC_Query::getSingletonInstance();

        if ($temp_table) {
            $tgt_table = 'dtb_order_temp';
            $sql_where = 'order_temp_id = ?';
        } else {
            $tgt_table = 'dtb_order';
            $sql_where = 'order_id = ?';

            $objQuery->update('dtb_shipping', array(),
                              $sql_where,
                              array($order_id),
                              array('shipping_time' =>
                                    "(SELECT deliv_time FROM dtb_delivtime WHERE time_id = dtb_shipping.time_id AND deliv_id = dtb_shipping.deliv_id)"));

        }

        $objQuery->update($tgt_table, array(),
                          $sql_where,
                          array($order_id),
                          array('payment_method' =>
                                "(SELECT payment_method FROM dtb_payment WHERE payment_id = " . $tgt_table . ".payment_id)"));
    }
}
