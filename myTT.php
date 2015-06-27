<?php
/**
  * wechat php test
  * 6.28
  */
//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg();


class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
    
    public function record($receiveMsg,$replyMsg)
    {
        $fname = date("Y-m-d",time());
        
        //$fp = fopen("saestor://mylog/log/".$fname.".txt","a+");
        //  $dir = "./submissions/";
        //  $all = scandir($dir);
        //  $new_num = count($all)-2;
        
            //fwrite($receiveMsg."\r\n".$replyMsg."\r\n"."\r\n");
        //fclose($fp);
        
        $s = new SaeStorage();
		if($s->fileExists('mylog' , "./log/".$fname.".txt"))
		{
			$old = $s->read( 'mylog' , "./log/".$fname.".txt");
		}else{
			$old ="";
		}
        $s->write( 'mylog' , "./log/".$fname.".txt", $old.$receiveMsg."\r\n".$replyMsg."\r\n"."\r\n");
        //return  true;
    }

    public function responseMsg()
    {        
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            	
            if($postObj->MsgType == "event")
                {
                    if($postObj->Event == "subscribe")
                    {
                        $msgType = "text";
                        $contentStr =  "这是一只微信开发测试机器人，请不要调戏它?;
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                		echo $resultStr;
                        $this->record($keyword,$resultStr);
                        return;
                    }
                }
                    
				if(!empty( $keyword ))
                {                        
              		$msgType = "text";
                    $contentStr = $dd.$keyword."  @".date("Y-m-d H:i:s",time());
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    
                }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
        $this->record($keyword,$resultStr);
    }
		
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

?>