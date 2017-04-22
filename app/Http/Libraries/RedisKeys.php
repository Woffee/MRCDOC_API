<?php
/**
 * RedisKey.php
 *
 * Redis key
 * @author    Liye <liye@douyu.tv>
 * @version   ${VERSION}
 * @package   App\Libraries
 * @copyright Douyu TV
 */

namespace App\Http\Libraries;


class RedisKeys
{

    /** 公共缓存有效期 **/
    const CACHE_EXPIRED_TIME = 259200; //3600*24*3=259200

    /** 客户端ID */
    const SET_CLIENTS_ONE = 'mrcdoc:clients:';
    const SET_CLIENTS_ALL = 'mrcdoc:clients:all';

    /** 文档内容 */
    const HASH_DOC_INFO   = 'mrcdoc:doc:info:';
    const HASH_DOC_CONTENT= 'mrcdoc:doc:content:';

    /** 用户信息 */
    const HASH_USER_INFO  = 'mrcdoc:user:info:';

    /** 用户 Token */
    const TOKEN = 'mrcdoc:token:';

    /** 通知 */
    const LIST_NOTICES_OLD = 'mrcdoc:notices:old:';
    const LIST_NOTICES_NEW = 'mrcdoc:notices:new:';
}