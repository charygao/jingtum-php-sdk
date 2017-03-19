<?php
/**
 * PHP SDK for Jingtum network; Constants used
 * @version 1.0.0
 * Copyright (C) 2016 by Jingtum Inc.
 * or its affiliates. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with
 * the License. A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JingtumSDK;
/**
 *  CONSTANTS
 */
define('ISSUE_TUM', 'IssueTum');
define('QUERY_ISSUE', 'QueryIssue');
define('QUERY_TUM', 'QueryTum');
define('MIN_ACT_AMOUNT','25');

/**
 * 命令常量定义
 */

define('BALANCES', '/accounts/{0}/balances/');
define('PAYMENT_PATHS', '/accounts/{0}/payments/paths/');
define('PAYMENTS', '/accounts/{0}/payments/');
define('ORDERS', '/accounts/{0}/orders/');
define('ORDER_BOOK', '/accounts/{0}/order_book/');
define('TRUST_LINES', '/accounts/{0}/trustlines/');
define('RELATIONS', '/accounts/{0}/relations/');
define('CORELATIONS', '/accounts/{0}/co-relations/');
define('TRANSACTIONS', '/accounts/{0}/transactions/');
define('OPERATIONS', '/accounts/{0}/operations/');
define('MESSAGES', '/accounts/{0}/messages/');
define('SETTINGS', '/accounts/{0}/settings/');
define('SIGNERS', '/accounts/{0}/signers/');

?>
