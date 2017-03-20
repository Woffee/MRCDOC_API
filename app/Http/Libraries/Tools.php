<?php

namespace App\Http\Libraries;


/**
 * 工具类库.
 *
 * @author bailu
 */
class Tools
{
    /**
     * 通过Token获取用户ＩＤ
     * @param $token
     * @return int
     */
    public static function getUserIdByToken($token)
    {
        $len = strlen($token);
        $uid = '';
        for( $i=0;$i<$len;$i++ ){
            if( $token[$i] == '_' )break;
            $uid .= $token[$i];
        }
        return (int)$uid;
    }

    /**
     * 生成Token
     * @param int $uid
     * @return string
     */
    public static  function generateToken( $uid = 0 )
    {
        return $uid.'_'.self::generateID();
    }

    /**
     * 生成随机ID
     * @param int $length
     * @return string
     */
    public static function generateID ($length = 32)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }

    /**
     * 截取字符串
     * @param $s
     * @param $maxLen
     * @param string $suffix
     * @return string
     */
    public static function trancateString ($s, $maxLen, $suffix = '…')
    {
        $s = (string)$s;
        $maxLen = 4 * (int)$maxLen;

        $mbLen = mb_strlen($s, 'utf-8');
        $binLen = strlen($s);

        if ($mbLen + $binLen <= $maxLen) {
            return $s;
        }

        $maxLen -= mb_strlen($suffix, 'utf-8') + strlen($suffix);

        $r = '';
        $rLen = 0;

        for ($i = 0; $i < $mbLen; ++$i) {
            $word = mb_substr($s, $i, 1, 'utf-8');

            $rLen += strlen($word) + 1;
            if ($rLen > $maxLen) {
                break;
            }

            $r .= $word;
        }

        return $r . $suffix;
    }

    /**
     * @param string $uid 用户id
     * @return int 返回整数int
     */
    public static function getUid($uid)
    {
        return Base62::decode($uid);
    }



    /**
     * 获取客户端IP, 参考zend frmaework
     *
     * @param  boolean $checkProxy 是否检查代理
     * @return string
     */
    public static function getClientIp($checkProxy = true)
    {
        if ($checkProxy && self::getServer('HTTP_CLIENT_IP') != null) {
            $ip = self::getServer('HTTP_CLIENT_IP');
        } else {
            if ($checkProxy
                && self::getServer('HTTP_X_FORWARDED_FOR') != null
            ) {
                $ip = self::getServer('HTTP_X_FORWARDED_FOR');
            } else {
                $ip = self::getServer('REMOTE_ADDR');
            }
        }

        return $ip;
    }

    /**
     * 获取$_SERVER中的信息
     * 如果不指定名称，则返回$_SERVER
     *
     * @param string $key
     * @param mixed  $default 默认值
     * @return mixed 不存在返回null
     */
    public static function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }

    /**
     * 根据键值$key 返回处理之后的用户id
     *
     * @param array  $data
     * @param string $key 根据这个字段返回一串加密后的uid
     * @return array  $data
     */
    public static function dealUid($data, $key = 'id')
    {
        foreach ($data['data'] as &$v) {
            $v['uid_short'] = Tools::getShortUid($v[$key]);
            $v['space'] = Tools::getUserSpace($v[$key], 'index');
        }

        return $data;
    }


    /**
     * 处理列表页面的图片和视频的展示
     * lifangfang@douyu.tv.
     *
     * @param array
     *
     * @return array
     */
    public static function havePicAndVedio($pic, $pic_size)
    {
        $havePicAndVedio = 0;
        if (isset($pic['img']) && !empty($pic['img'])) {
            foreach ($pic['img'] as $k => $img) {
                if (!empty($img)) {
                    if (!isset($pic['img_size'][$k]) || $pic['img_size'][$k] == '*') {
                        continue;
                    }
                    $widthHeight = explode('*', $pic['img_size'][$k]);  //原始上传图片的大小
                    //用来展示的图片的大小是经过缩放的
                    $oWidth = $widthHeight[0];
                    $oHeight = $widthHeight[1];
                    if ($oWidth > $pic_size) {
                        $thumbWidth = $pic_size;  //缩略图的宽度
                        $thumbHeight = floor(($thumbWidth * $oHeight) / $oWidth);
                    } else {
                        $thumbWidth = $oWidth;    //缩略图的宽度
                        $thumbHeight = $oHeight;
                    }

                    if ((($thumbWidth / $thumbHeight) > 5) || (($thumbHeight / $thumbWidth) > 5)) {
                        continue;
                    }
                    if (($thumbWidth < $pic_size) && ($thumbHeight < 90)) {
                        continue;
                    }
                    $havePicAndVedio = 1;
                    break;
                }
            }
        }
        if ($havePicAndVedio == 1 || !empty($pic['video'])) {
            return true;
        }

        return false;
    }

    /**
     * @abstract 时间显示 刚刚、几分钟前、几小时前
     */
    public static function human_time_diff($from)
    {
        $to = time();

        $per = $to >= $from ? '前' : '后';
        $diff = (int)abs($to - $from);

        $year1 = date('Y', $from);
        $year = date('Y', $to);
        $year_str = $year1 != $year ? 'Y-' : '';

        $today = strtotime('today');
        $tday = $today + 86400;

        if ($diff < 60) {
            //$since = $diff.'秒'.$per;
            $since = $to >= $from ? '刚刚' : '即将';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            //$seconds = $diff % 60;

            $since = $mins . '分钟' . $per;
        } elseif ($diff < 14400) {
            $hours = floor($diff / 3600);
            //$mins = floor(($diff % 3600) / 60);

            $since = $hours . '小时' . $per;
        } elseif ($from >= $today && $from < $tday) {
            $since = date('H:i', $from);

            if ($from < $today) {
            } elseif ($from >= $tday) {
                $since = '明日 ' . date('H:i', $from);
            } else {
                $since = date('H:i', $from);
            }
        } elseif ($from < $today && $from >= $today - 86400) {
            $since = '昨天 ' . date('H:i', $from);
        } elseif ($from >= $tday && $from < $tday + 86400) {
            $since = '明天 ' . date('H:i', $from);
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            $since = $days . '天' . $per;

            //$since = date($year_str.'m-d H:i', $from);
        } else {
            $since = date($year_str . 'm-d H:i', $from);
        }

        //return '<span class="human_time" rel="' . $from . '" title="' . date('Y-m-d H:i', $from) . '">' . $since . '</span>';
        return $since;
    }

    /**
     * 将图片标签替换成表情代码 lianlin@douyu.tv.
     *
     * @param string $content 要转换的字符串
     *
     * @return string mixed     返回转换后的字符串
     */
    public static function imgTagConvertEmotionCode($content)
    {
        $pattern = '#<img[^>]+src="[\w\.\:\-\/]*js/\w+/dialogs/emotion/img/(\d+)\.(png|jpg|bmp|gif)"[^>]*>#i';
        $content = preg_replace_callback($pattern, function ($m) {
            return '[default:dy0' . $m[1] . ']';
        }, $content);

        $pattern = '#<img[^>]+src="[\w\.\:\-\/]*js/[\w\/]+/dialogs/emotion/img/(\w*\d+)\.(png|jpg|bmp|gif)"[^>]*>#i';
        $content = preg_replace_callback($pattern, function ($m) {
            return '[default:' . $m[1] . ']';
        }, $content);

        $pattern = '#<img[^>]+src="[\w\.\:\/]*plugin/xheditor/xheditor_emot/default/(\d+).(png|jpg|bmp|gif)"[^>]*>#i';
        $content = preg_replace_callback($pattern, function ($m) {
            return '[default:dy0' . $m[1] . ']';
        }, $content);

        return $content;
    }



    /**
     * 生成签名
     *
     */
    public static function genSignature($appName, $appSecret, $timestamp)
    {
        $tmpArr = [$appName, $appSecret, $timestamp];
        sort($tmpArr, SORT_STRING);

        return sha1(implode($tmpArr));
    }

    /**
     * 去除文本中的emoji表情和特殊表情符号
     * @param $text
     * @return mixed|string
     * @author lianlin@douyu.tv
     */
    public static function removeEmoji($text)
    {
        $cleanText = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $cleanText = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $cleanText = preg_replace($regexSymbols, '', $cleanText);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $cleanText = preg_replace($regexTransport, '', $cleanText);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $cleanText = preg_replace($regexMisc, '', $cleanText);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $cleanText = preg_replace($regexDingbats, '', $cleanText);

        return $cleanText;
    }




    /**
     * 返回string个数 1个汉字按两个字符算
     * @param $str 要查询的字符串数字，大小写字母
     * @return int
     * @author lixin1@douyu.tv
     */
    public static function getStrLength($str)
    {
        $out = preg_replace('/[\x{4e00}-\x{9fa5}]/u','aa',$str);
        $mbStrLen = mb_strlen($out, 'UTF-8');
        return $mbStrLen;
    }



    /**
     * 将getMixedImgTxt图文混排的内容回复成html形式
     * @param $map array 内容数组
     * @return string
     */
    public static function setMixedImgTxt($map)
    {
        if (empty($map) || !is_array($map)) {
            return '';
        }
        $html = '';
        foreach ($map as $value) {
            switch ($value['style']) {
                case 'text' :
                    $text = trim($value['text']);
                    $html .= "<p>$text</p>";
                    break;
                case 'image' :
                    $html .= "<img src=\"{$value['src']}\" />";
                    break;
            }
        }
        return $html;
    }

    /**
     * email地址验证
     * @param string $email email地址
     * @return mixed|string
     * @author tangcheng@douyu.tv
     */
    public static function validEmail($email)
    {
        $reg = '/^[a-zA-Z0-9]([a-z0-9]*[-_]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?$/i';
        if (preg_match($reg, $email)) {
            return true;
        }
        return false;
    }

}
