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
    const CACHE_EXPIRED_TIME = 259200;

    /** 临时文档IP-DOC_ID */
    const DOC_TMP_DOC_ID = 'mrcdoc:doc:tmp:id:' ;

    /** 客户端ID 对应的 UID */
    const DOC_CLIENT_UID = 'mrcdoc:doc:client:uid:';

    /** 某文档对应的登录的客户端ID */
    const SET_DOC_CLIENTS = 'mrcdoc:doc:client:';

    /** 所有登录的客户端ID */
    const SET_ALL_CLIENTS = 'mrcdoc:all_clients';

    /** 文档内容 */
    const HASH_DOC_INFO = 'mrcdoc:doc:info:';

    /** 用户 Token */
    const TOKEN = 'mrcdoc:token:';
}