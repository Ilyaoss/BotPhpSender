<?php
require_once './vendor/autoload.php';
require_once __DIR__ . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
require_once __DIR__ .'/functions.php';
use VK\Client\Enums\VKLanguage;
use VK\Client\VKApiClient;

const MAX_LENGHT = 40;
const VK_TOKEN = '887f275780153f8d0a42339e542ecb1f1b6a47bce9385aea12ada07d3a459095800074da66b418d5911c9';

$group_id = 169930012;
myLog("open?");

$vk = new VKApiClient('5.80', VKLanguage::RUSSIAN);
$request = $vk->groups()->getLongPollServer(VK_TOKEN ,['group_id'=>$group_id]);
myLog("request: ".json_encode($request,JSON_UNESCAPED_UNICODE));

$server = $request["server"];
$key = $request["key"];
$ts = $request["ts"];

while (true) {
    $response = file_get_contents("{$server}?act=a_check&key={$key}&ts={$ts}&wait=30");
	myLog("response: ".$response);
    $updates = $response['updates'];
    if ($updates){  # проверка, были ли обновления
        foreach( $updates as $element){  # проход по всем обновлениям в ответе
            myLog("element: ".$element);
		}
	}
    $ts = $response["ts"];  # обновление номера последнего обновления
}
// отправление запроса на Long Poll сервер со временем ожидания 90 секунд
/*--Парсим xls с категориями--*/
//$def_mas = read_XLS(__DIR__ . '/categories.xlsx') ;
/*while(true)
{
	
}

$keys_1 = array_keys($array); /*Кнопки 1-го уровня*/

/*$cat_array = [];

switch ($type) {
	case 'message_new':
		$message = $data['object'] ?? [];
		$userId = $message['from_id'] ?? 0; //user_id
		$payload = $message['payload'] ?? '';
		$text = $message['text'] ?? '';
		
		$link = connect_db();
		$db = read_db($link);
		foreach($db as $user=>$subs)
		{
			myLog("user: $user, subs: $subs");							
		}
		myLog("db: ".json_encode($db,JSON_UNESCAPED_UNICODE));
		
		myLog("MSG: ".$text." PAYLOAD string:".$payload);
		if ($payload) {
			$payload = json_decode($payload, true);
		}
		myLog("MSG: ".$text." PAYLOAD:".$payload);
		switch($payload){
			case(''):
			//Админ прислал новый документ 
			if(is_admin($vk,$group_id,$userId)) {
				$attachment = $message['attachments'][0]["doc"] ?? '';
				myLog("attachment: ".json_encode($attachment,JSON_UNESCAPED_UNICODE));
				if($attachment)
				{
					$url = $attachment["url"];
					$path = __DIR__ . '/test.xlsx';
					
					$cat_array_old = read_XLS($path);
					
					//--Создаём ассоц. массив--
					$array_old = array();
					for($i=1;$i<count($cat_array_old);++$i) {
						$value = $cat_array_old[$i];
						$array_old[$value[6]][$value[0]] = $value[5]; //в категории создаём массивы асоц номер-статус
					}
					//myLog("cat_array_old: ".json_encode($array_old,JSON_UNESCAPED_UNICODE));	
					file_put_contents($path, file_get_contents($url));
					
					$cat_array = read_XLS($path);
						
					
					//--Создаём ассоц. массив--
					$array = array();
					for($i=1;$i<count($cat_array);++$i) {
						$value = $cat_array[$i];
						$array[$value[6]][$value[0]] = $value[5]; //в категории создаём массивы асоц номер-статус
					}
					//myLog("cat_array: ".json_encode($array,JSON_UNESCAPED_UNICODE));
					
					$keys = array_keys($array);
					//могут новые ключи появиться НЕ ЗАБУДЬ!
					
					$upd_array = [];
					for($i=0;$i<count($array);++$i) {
						$update = array_diff($array[$keys[$i]],$array_old[$keys[$i]]);
						if($update) 
						{
							$upd_array[$keys[$i]]=$update;
						}
						myLog("updates: ".json_encode($update,JSON_UNESCAPED_UNICODE));
					}
					
					$keys = array_keys($upd_array);

					$data = read_db($link);
					
					foreach($data as $user=>$subs)
					{
						send_subs($vk,$user,$subs,$keys,$upd_array);							
					}
					$msg = null;
				}
				break;
			}
		sendMsg($vk,$userId,$msg);
		echo  "OK";
		break;
	case 'confirmation': 
		//...отправляем строку для подтверждения 
		echo $confirmation_token; 
		break; 
}*/