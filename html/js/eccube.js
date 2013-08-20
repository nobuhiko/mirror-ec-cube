/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2013 LOCKON CO.,LTD. All Rights Reserved.
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

(function( window, undefined ){


    // 名前空間の重複を防ぐ
    if (window.eccube === undefined) {
        window.eccube = {};
    }

    var eccube = window.eccube;

    eccube.win01 = function(URL,Winname,Wwidth,Wheight){
        var WIN;
        WIN = window.open(URL,Winname,"width="+Wwidth+",height="+Wheight+",scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=no");
        WIN.focus();
    };

    eccube.win02 = function(URL,Winname,Wwidth,Wheight){
        var WIN;
        WIN = window.open(URL,Winname,"width="+Wwidth+",height="+Wheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no");
        WIN.focus();
    };

    eccube.win03 = function(URL,Winname,Wwidth,Wheight){
        var WIN;
        WIN = window.open(URL,Winname,"width="+Wwidth+",height="+Wheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
        WIN.focus();
    };

    eccube.winSubmit = function(URL,formName,Winname,Wwidth,Wheight){
        var WIN = window.open(URL,Winname,"width="+Wwidth+",height="+Wheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
        document.forms[formName].target = Winname;
        WIN.focus();
    };

    eccube.openWindow = function(URL,name,width,height) {
        window.open(URL,name,"width="+width+",height="+height+",scrollbars=yes,resizable=no,toolbar=no,location=no,directories=no,status=no");
    };

    // 親ウィンドウの存在確認.
    eccube.isOpener = function() {
        var ua = navigator.userAgent;
        if( !!window.opener ) {
            if( ua.indexOf('MSIE 4')!=-1 && ua.indexOf('Win')!=-1 ) {
                return !window.opener.closed;
            } else {
                return typeof window.opener.document == 'object';
            }
        } else {
            return false;
        }
    };

    eccube.chgImg = function(fileName,img){
        if (typeof(img) == "object") {
            img.src = fileName;
        } else {
            document.images[img].src = fileName;
        }
    };

    eccube.chgImgImageSubmit = function(fileName,imgObj){
        imgObj.src = fileName;
    };

    // 郵便番号入力呼び出し.
    eccube.getAddress = function(php_url, tagname1, tagname2, input1, input2) {
        var zip1 = document.form1[tagname1].value;
        var zip2 = document.form1[tagname2].value;

        if(zip1.length == 3 && zip2.length == 4) {
            $.get(
                php_url,
                {zip1: zip1, zip2: zip2, input1: input1, input2: input2},
                function(data) {
                    var arrData = data.split("|");
                    if (arrData.length > 1) {
                        eccube.putAddress(input1, input2, arrData[0], arrData[1], arrData[2]);
                    } else {
                        alert(data);
                    }
                }
            );
        } else {
            alert("郵便番号を正しく入力して下さい。");
        }
    };

    // 郵便番号から検索した住所を渡す.
    eccube.putAddress = function(input1, input2, state, city, town) {
        if(state != "") {
            // 項目に値を入力する.
            document.form1[input1].selectedIndex = state;
            document.form1[input2].value = city + town;
        }
    };

    eccube.setFocus = function(name) {
        if(document.form1[name]) {
            document.form1[name].focus();
        }
    };

    // モードとキーを指定してSUBMITを行う。
    eccube.setModeAndSubmit = function(mode, keyname, keyid) {
        switch(mode) {
            case 'delete_category':
                if(!window.confirm('選択したカテゴリとカテゴリ内の全てのカテゴリを削除します')){
                    return;
                }
                break;
            case 'delete':
                if(!window.confirm('一度削除したデータは、元に戻せません。\n削除しても宜しいですか？')){
                    return;
                }
                break;
            case 'confirm':
                if(!window.confirm('登録しても宜しいですか')){
                    return;
                }
                break;
            case 'delete_all':
                if(!window.confirm('検索結果を全て削除しても宜しいですか')){
                    return;
                }
                break;
            default:
                break;
        }
        document.form1['mode'].value = mode;
        if(keyname != "" && keyid != "") {
            document.form1[keyname].value = keyid;
        }
        document.form1.submit();
    };

    eccube.fnFormModeSubmit = function(form, mode, keyname, keyid) {
        switch(mode) {
            case 'delete':
                if(!window.confirm('一度削除したデータは、元に戻せません。\n削除しても宜しいですか？')){
                    return;
                }
                break;
            case 'confirm':
                if(!window.confirm('登録しても宜しいですか')){
                    return;
                }
                break;
            case 'regist':
                if(!window.confirm('登録しても宜しいですか')){
                    return;
                }
                break;
            default:
                break;
        }
        document.forms[form]['mode'].value = mode;
        if(keyname != "" && keyid != "") {
            document.forms[form][keyname].value = keyid;
        }
        document.forms[form].submit();
    };

    eccube.setValueAndSubmit = function(form, key, val) {
        document.forms[form][key].value = val;
        document.forms[form].submit();
        return false;
    };

    eccube.setValue = function(key, val, form) {
        if (typeof form === 'undefined') {
            form = 'form1';
        }
        document.forms[form][key].value = val;
    };

    eccube.changeAction = function(url) {
        document.form1.action = url;
    };

    // ページナビで使用する。
    eccube.movePage = function(pageno, mode, form) {
        if (typeof form !== 'undefined') {
            form = 'form1';
        }
        document.forms[form]['pageno'].value = pageno;
        if (typeof mode !== 'undefined') {
            document.forms[form]['mode'].value = 'search';
        }
        document.forms[form].submit();
    };

    eccube.submitForm = function(form){
        if (typeof form !== 'undefined') {
            form = 'form1';
        }
        document.forms[form].submit();
    };

    // ポイント入力制限。
    eccube.togglePointForm = function() {
        if(document.form1['point_check']) {
            var list = ['use_point'];
            var color;
            var flag;

            if(!document.form1['point_check'][0].checked) {
                color = "#dddddd";
                flag = true;
            } else {
                color = "";
                flag = false;
            }

            var len = list.length;
            for(i = 0; i < len; i++) {
                if(document.form1[list[i]]) {
                    var current_color = document.form1[list[i]].style.backgroundColor;
                    if (color != "#dddddd" && (current_color == "#ffe8e8" || current_color == "rgb(255, 232, 232)"))
                    {
                        continue;
                    }
                    document.form1[list[i]].disabled = flag;
                    document.form1[list[i]].style.backgroundColor = color;
                }
            }
        }
    };

    // 別のお届け先入力制限。
    eccube.toggleDeliveryForm = function() {
        if(!document.form1) {
            return;
        }
        if(document.form1['deliv_check']) {
            var list = [
                'shipping_name01',
                'shipping_name02',
                'shipping_kana01',
                'shipping_kana02',
                'shipping_pref',
                'shipping_zip01',
                'shipping_zip02',
                'shipping_addr01',
                'shipping_addr02',
                'shipping_tel01',
                'shipping_tel02',
                'shipping_tel03'
            ];

            if(!document.form1['deliv_check'].checked) {
                eccube.changeDisabled(list, '#dddddd');
            } else {
                eccube.changeDisabled(list, '');
            }
        }
    };

    // 最初に設定されていた色を保存しておく。
    eccube.savedColor = [];

    eccube.changeDisabled = function(list, color) {
        var len = list.length;

        for(i = 0; i < len; i++) {
            if(document.form1[list[i]]) {
                if(color == "") {
                    // 有効にする。
                    document.form1[list[i]].disabled = false;
                    document.form1[list[i]].style.backgroundColor = eccube.savedColor[list[i]];
                } else {
                    // 無効にする。
                    document.form1[list[i]].disabled = true;
                    eccube.savedColor[list[i]] = document.form1[list[i]].style.backgroundColor;
                    document.form1[list[i]].style.backgroundColor = color;//"#f0f0f0";
                }
            }
        }
    };

    // ログイン時の入力チェック
    eccube.checkLoginFormInputted = function(form, emailKey, passKey) {
        var checkItems = [];

        if (typeof emailKey === 'undefined') {
            checkItems[0] = 'login_email';
        } else {
            checkItems[0] = emailKey;
        }
        if (typeof passKey === 'undefined') {
            checkItems[1] = 'login_pass';
        } else {
            checkItems[1] = passKey;
        }

        var max = checkItems.length;
        var errorFlag = false;

        //　必須項目のチェック
        for(var cnt = 0; cnt < max; cnt++) {
            if(document.forms[form][checkItems[cnt]].value == "") {
                errorFlag = true;
                break;
            }
        }

        // 必須項目が入力されていない場合
        if(errorFlag == true) {
            alert('メールアドレス/パスワードを入力して下さい。');
            return false;
        } else {
            return true;
        }
    };

    //親ウィンドウのページを変更する.
    eccube.changeParentUrl = function(url) {
        // 親ウィンドウの存在確認
        if(eccube.isOpener()) {
            window.opener.location.href = url;
        } else {
            window.close();
        }
    };

    //文字数をカウントする。
    //引数1：フォーム名称
    //引数2：文字数カウント対象
    //引数3：カウント結果格納対象
    eccube.countChars = function(form,sch,cnt) {
        document.forms[form][cnt].value= document.forms[form][sch].value.length;
    };

    // テキストエリアのサイズを変更する.
    eccube.toggleRows = function(buttonSelector, textAreaSelector, max, min) {
        if ($(textAreaSelector).attr('rows') <= min) {
            $(textAreaSelector).attr('rows', max);
            $(buttonSelector).text('縮小');
        } else {
            $(textAreaSelector).attr('rows', min);
            $(buttonSelector).text('拡大');
        }
    };

    /**
     * 規格2のプルダウンを設定する.
     */
    eccube.setClassCategories = function($form, product_id, $sele1, $sele2, selected_id2) {
        if ($sele1 && $sele1.length) {
            var classcat_id1 = $sele1.val() ? $sele1.val() : '';
            if ($sele2 && $sele2.length) {
                // 規格2の選択肢をクリア
                $sele2.children().remove();

                var classcat2;

                // 商品一覧時
                if (eccube.productsClassCategories !== undefined) {
                    classcat2 = eccube.productsClassCategories[product_id][classcat_id1];
                }
                // 詳細表示時
                else {
                    classcat2 = eccube.classCategories[classcat_id1];
                }

                // 規格2の要素を設定
                for (var key in classcat2) {
                    var id = classcat2[key]['classcategory_id2'];
                    var name = classcat2[key]['name'];
                    var option = $('<option />').val(id ? id : '').text(name);
                    if (id == selected_id2) {
                        option.attr('selected', true);
                    }
                    $sele2.append(option);
                }
                eccube.checkStock($form, product_id, $sele1.val() ? $sele1.val() : '__unselected2',
                    $sele2.val() ? $sele2.val() : '');
            }
        }
    };

    /**
     * 規格の選択状態に応じて, フィールドを設定する.
     */
    eccube.checkStock = function($form, product_id, classcat_id1, classcat_id2) {

        classcat_id2 = classcat_id2 ? classcat_id2 : '';

        var classcat2;

        // 商品一覧時
        if (typeof eccube.productsClassCategories != 'undefined') {
            classcat2 = eccube.productsClassCategories[product_id][classcat_id1]['#' + classcat_id2];
        }
        // 詳細表示時
        else {
            classcat2 = eccube.classCategories[classcat_id1]['#' + classcat_id2];
        }

        // 商品コード
        var $product_code_default = $form.find('[id^=product_code_default]');
        var $product_code_dynamic = $form.find('[id^=product_code_dynamic]');
        if (classcat2
            && typeof classcat2['product_code'] != 'undefined') {
            $product_code_default.hide();
            $product_code_dynamic.show();
            $product_code_dynamic.text(classcat2['product_code']);
        } else {
            $product_code_default.show();
            $product_code_dynamic.hide();
        }

        // 在庫(品切れ)
        var $cartbtn_default = $form.find('[id^=cartbtn_default]');
        var $cartbtn_dynamic = $form.find('[id^=cartbtn_dynamic]');
        if (classcat2 && classcat2['stock_find'] === false) {

            $cartbtn_dynamic.text('申し訳ございませんが、只今品切れ中です。').show();
            $cartbtn_default.hide();
        } else {
            $cartbtn_dynamic.hide();
            $cartbtn_default.show();
        }

        // 通常価格
        var $price01_default = $form.find('[id^=price01_default]');
        var $price01_dynamic = $form.find('[id^=price01_dynamic]');
        if (classcat2
            && typeof classcat2['price01'] != 'undefined'
            && String(classcat2['price01']).length >= 1) {

            $price01_dynamic.text(classcat2['price01']).show();
            $price01_default.hide();
        } else {
            $price01_dynamic.hide();
            $price01_default.show();
        }

        // 販売価格
        var $price02_default = $form.find('[id^=price02_default]');
        var $price02_dynamic = $form.find('[id^=price02_dynamic]');
        if (classcat2
            && typeof classcat2['price02'] != 'undefined'
            && String(classcat2['price02']).length >= 1) {

            $price02_dynamic.text(classcat2['price02']).show();
            $price02_default.hide();
        } else {
            $price02_dynamic.hide();
            $price02_default.show();
        }

        // ポイント
        var $point_default = $form.find('[id^=point_default]');
        var $point_dynamic = $form.find('[id^=point_dynamic]');
        if (classcat2
            && typeof classcat2['point'] != 'undefined'
            && String(classcat2['point']).length >= 1) {

            $point_dynamic.text(classcat2['point']).show();
            $point_default.hide();
        } else {
            $point_dynamic.hide();
            $point_default.show();
        }

        // 商品規格
        var $product_class_id_dynamic = $form.find('[id^=product_class_id]');
        if (classcat2
            && typeof classcat2['product_class_id'] != 'undefined'
            && String(classcat2['product_class_id']).length >= 1) {

            $product_class_id_dynamic.val(classcat2['product_class_id']);
        } else {
            $product_class_id_dynamic.val('');
        }
    };

    // グローバルに使用できるようにする
    window.eccube = eccube;

    /**
     * Initialize.
     */
    $(function() {
        // 規格1選択時
        $('select[name=classcategory_id1]')
            .change(function() {
                var $form = $(this).parents('form');
                var product_id = $form.find('input[name=product_id]').val();
                var $sele1 = $(this);
                var $sele2 = $form.find('select[name=classcategory_id2]');

                // 規格1のみの場合
                if (!$sele2.length) {
                    eccube.checkStock($form, product_id, $sele1.val(), '0');
                    // 規格2ありの場合
                } else {
                    eccube.setClassCategories($form, product_id, $sele1, $sele2);
                }
            });

        // 規格2選択時
        $('select[name=classcategory_id2]')
            .change(function() {
                var $form = $(this).parents('form');
                var product_id = $form.find('input[name=product_id]').val();
                var $sele1 = $form.find('select[name=classcategory_id1]');
                var $sele2 = $(this);
                eccube.checkStock($form, product_id, $sele1.val(), $sele2.val());
            });
    });
})(window);
