<?php


set_time_limit(0);


/**
 * @param $file
 * @param $text
 * @param int $level
 * @author Bill
 */
function saveLog($file, $text , $level = 0)
{
    $text ='[' . date("Y-m-d HH:ii:ss",time()) . '][' . $level .']'. $text . "\n";
    file_put_contents('Log/'.$file.'_'.date('Ymd').'.log', $text, FILE_APPEND);
}


/**
 * 进程队列轮询
 * @author Bill
 */
function startCMQ(){
    $conn_args = array(
        'host'=>'127.0.0.1',
        'port'=>5672,
        'login'=>'guest',
        'password'=>'guest',
        'vhost'=>'/'
    );
    $e_name = 'e_demo';
    $q_name = 'q_demo';
    $k_route = 'key_1';

    $conn = new AMQPConnection($conn_args);
    if(!$conn->connect()){
        die('Cannot connect to the broker');
    }
    $channel = new AMQPChannel($conn);
    $ex = new AMQPExchange($channel);
    $ex->setName($e_name);
    $ex->setType(AMQP_EX_TYPE_DIRECT);
    $ex->setFlags(AMQP_DURABLE);

    $q = new AMQPQueue($channel);
    $q->setName($q_name);
    $q->bind($e_name, $k_route);

    $response = $q->get();
    if($response != false){
        $message = $response->getBody();
        if(!empty($message)){
            $res = $q->ack($response->getDeliveryTag());
            echo "rev message!...";
            saveLog("queue",$message);
            var_dump($message);
        }
    }
}
while (true) {
    echo "start serv!....";
    startCMQ();
    sleep(2);
}
?>
