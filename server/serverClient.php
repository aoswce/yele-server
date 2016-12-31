<?php
/**
 * Created by PhpStorm.
 * User: Avine
 * Date: 2016/12/21
 * Time: 16:10
 */

define("ROOTPATH",dirname(dirname(__FILE__)));
require_once ROOTPATH . '/server/config/config.php';
require_once ROOTPATH . '/server/function/function.php';



class TcpClient
{
    private $client;


    public function __construct()
    {
        global $config;
        //var_dump($config);
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);


        $this->client->on('connect',function($cli){
            self::register();
            sleep(2);
            self::login();

            //从Redis获取要发送的数据
            $redis = getRedis();
            //循环检测队列，将通知触发至服务
            while(true){
                sleep(1);
                $sends = $redis->keys('S:*:*:*');
                //如果有数据将数据发送动作发送给服务端
                if(count($sends)){
                    $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'sendClient','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
                    $re = $this->client->send(json_encode($data));
                    if($re){continue;}else{//失败重发
                        $this->client->send(json_encode($data));
                    }
                }
            }
        });

        $this->client->on('receive',function($cli,$data){
            echo "you got your data:".$data;
        });

        //$this->client->on('task',array($this,'onTask'));

        $this->client->on('close',function($cli){
            echo "Client Closed ...\n";
        });

        $this->client->on("error", function($cli){
            echo "Connect failed\n";
        });
    }

    public function send(){
        //从Redis获取要发送的数据
        $redis = self::getRedis();
        //循环检测队列，将通知触发至服务
        while(true){
            //sleep(1);
            $sends = $redis->keys('S:*:*:*');
            //如果有数据将数据发送动作发送给服务端
            if(count($sends)){
                $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'sendClient','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
                $re = $this->client->send(json_encode($data));
                if($re){continue;}else{//失败重发
                    $this->client->send(json_encode($data));
                }
            }
        }
    }

    public function run(){
        global $config;
        $this->client->connect(
          $config['client']['host'],
          $config['client']['port'],
          $config['client']['timeout']
      );
    }

    function register(){
      $data = array('fd'=>'B999999_','cmd'=>'register');
      $re = $this->client->send(json_encode($data));
      var_dump($re);
      while(!$re){
        sleep(2);
        $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'register','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
        $re = $this->client->send(json_encode($data));
        if(!$re){
          break;
        }
      }
    }

    function login(){
      $data = array('fd'=>'B999999_','cmd'=>'login');
      $re = $this->client->send(json_encode($data));
      while(!$re){
        sleep(2);
        $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'login','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
        $re = $this->client->send(json_encode($data));
        if(!$re){
          break;
        }
      }
    }

    private function getRedis(){
        global $config;
        $redis = new Redis;
        $re = $redis->connect(
            $config['redis']['master']['host'],
            $config['redis']['master']['port']
        );

        return $redis;
    }
}

$client = new TcpClient();
$client->run();
