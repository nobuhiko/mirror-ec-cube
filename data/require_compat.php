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

require_once(DATA_REALDIR . "module/Compat/Compat.php");

//TODO: ���̃��C�u�������g���̂��ǂ��̂��APEAR/Crypt_HMAC2���g���ׂ�������������
//      Crypt_HMAC2��5.0.0�ȏ�ł��������߁A4.0.0����̓��삪�\�ȉ��L�����p�B

// hash_algos (PHP 5 >= 5.1.2, PECL hash >= 1.1)
// �p�X���[�h�E���}�C���_�[�̃n�b�V���Í����ɗ��p
PHP_Compat::loadFunction("hash_algos");

// hash_hmac (PHP 5 >= 5.1.2, PECL hash >= 1.1)
// �p�X���[�h�E���}�C���_�[�̃n�b�V���Í����ɗ��p
// http://pear.php.net/bugs/bug.php?id=16521 ���PHP_Compat�݊��d�l��hash�֘A�֐��ǉ�
PHP_Compat::loadFunction("hash_hmac");

?>
