<?php

namespace App\Socket;

use App\Http\Libraries\RedisKeys;
use swoole_websocket_server;

class Server
{

    private $socketServer;

    private $redis;


    public function __construct()
    {
        $host = env('WEB_SOCKET_HOST','192.168.233.100');
        $port = env('WEB_SOCKET_PORT','3890');
        echo ($host.' '.$port);
        $this->socketServer = new swoole_websocket_server($host, $port);
        $this->socketServer->set([
            'worker_num' => 8,
            'daemonize'  => false,
        ]);

        $this->redis = new Redis();
        //$this->writers = new Writers();
        //创建监听事件
        $this->socketServer->on('open', [$this, 'onOpen']);
        $this->socketServer->on('message', [$this, 'message']);
        $this->socketServer->on('close', [$this, 'onClose']);
    }


    public function message($socketServer, $frame)
    {
        $sender = $frame->fd;
        $data = json_decode($frame->data);

        switch ($data->type)
        {
            case 'login':

                break;

            case 'init':
                $this->join($sender,$data->doc_id);
                break;

            case 'message':
                $this->deliverMessage($sender,$data,$data->doc_id,$socketServer);
                break;

            case 'history':
                break;
            case 'refresh':
                break;
        }
    }

    public function onOpen($socketServer, $request)
    {
        echo "MRCDOC server: handshake success with fd {$request->fd}\n";
    }


    public function onClose($socketServer, $fd)
    {
        echo "MRCDOC server: client-{$fd} is closed.\n";
        $key = RedisKeys::SET_CLIENTS_ALL;
        $this->redis->getClient()->srem($key,$fd);
    }


    public function start()
    {
        $this->socketServer->start();
    }


    /**
     * 分发消息
     * @param $sender
     * @param $data
     * @param $docID
     * @param $socketServer
     * @internal param $delta
     * @internal param $content
     */
    protected function deliverMessage($sender, $data,  $docID, $socketServer)
    {
        $delta = json_encode( $data->delta );       //每一次的修改内容
        $content = json_encode( $data->content );   //文档所有内容

        $message = '{"type":"message","delta":'.$delta.',"doc_id":"'.$docID.'"}';

        //获取当前正在编辑文档的客户端ＩＤ
        $key = RedisKeys::SET_CLIENTS_ALL;
        $allMembers = $this->redis->getClient()->smembers($key);
        $key = RedisKeys::SET_CLIENTS_ONE.$docID;
        $docMembers = $this->redis->getClient()->smembers($key);
        $docLoginMembers = array_intersect($allMembers,$docMembers);

        //更新文档内容
        $key = RedisKeys::HASH_DOC_CONTENT.$docID;
        $this->redis->getClient()->set($key, $content);
        $this->redis->getClient()->expire($key, RedisKeys::CACHE_EXPIRED_TIME);

        //echo $content.'\n';

        foreach ($docLoginMembers as $member) {
            if ($member != $sender) {
                $socketServer->push($member, $message);
            }
        }
    }

    public function join( $senderID, $docID, $uid = 0  )
    {
        $key = RedisKeys::SET_CLIENTS_ALL;
        $arr = [];
        $arr[]=$senderID;
        $this->redis->getClient()->sadd($key,$arr);
        $this->redis->getClient()->expire($key, RedisKeys::CACHE_EXPIRED_TIME);

        $key = RedisKeys::SET_CLIENTS_ONE.$docID;
        $arr = [];
        $arr[]=$senderID;
        $this->redis->getClient()->sadd($key,$arr);
        $this->redis->getClient()->expire($key, RedisKeys::CACHE_EXPIRED_TIME);
    }

}
