<?php
require_once __DIR__ . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
require_once './vendor/autoload.php';
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
$keys = [];
$upd_array = [];
$date_before = date("Y-m-d H:i:s");
while (true) {
	$response = file_get_contents("{$server}?act=a_check&key={$key}&ts={$ts}&wait=30");
	myLog("response: ".$response);
	$response = json_decode($response,true);
	$updates = $response['updates'];
	if ($updates){  # проверка, были ли обновления
		$link = connect_db();
		foreach( $updates as $data_){  # проход по всем обновлениям в ответе
			$message = $data_['object'] ?? [];
			$userId = $message['from_id'] ?? 0; //user_id
			$payload = $message['payload'] ?? '';
			$text = $message['text'] ?? '';
			myLog("element: ".$element);
			
			if(is_admin($vk,$group_id,$userId)) {
				$attachment = $message['attachments'][0]["doc"] ?? '';
				myLog("attachment: ".json_encode($attachment,JSON_UNESCAPED_UNICODE));
				if($attachment) {
					$url = $attachment["url"];
					
					$path = __DIR__ . '/test.xlsx';
					$cat_array_old = read_XLS($path);
					#--Создаём ассоц. массив--
					$array_old = array();
					for($i=1;$i<count($cat_array_old);++$i) {
						$value = $cat_array_old[$i];
						$array_old[$value[6]][$value[0]] = $value[5]; //в категории создаём массивы асоц номер-статус
					}	
					file_put_contents($path, file_get_contents($url));
					
					$cat_array = read_XLS($path);
					#--Создаём ассоц. массив--
					$array = array();
					for($i=1;$i<count($cat_array);++$i) {
						$value = $cat_array[$i];
						$array[$value[6]][$value[0]] = $value[5]; //в категории создаём массивы асоц номер-статус
					}
					
					$keys = array_keys($array);
					#---могут новые ключи появиться НЕ ЗАБУДЬ!------
					
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
					
					$table = 'user_subs';
					$data = read_db($link,$table);//read_file();
					//mysqli_close($link);
					foreach($data as $user=>$subs)
					{
						send_subs($vk,$user,$subs,$upd_array);							
					}
					$msg = null;
				}
				
				break;
			}
			else {
				#--Смотрю новые подписки у пользователей за последние 30(31) секунд--
				$date_cur = date("Y-m-d H:i:s");
				$table = 'user_subs';
				$where = "date_start >= '$date_before' and date_start < '$date_cur'";//TO_SECONDS?
				$date_before = $date_cur;
				//$where = "TIME_TO_SEC(TIMEDIFF('$date',date_start))<31";//TO_SECONDS?
				$db = read_db($link,$table,$where);
				foreach($db as $user=>$subs)
				{
					//$table = 'MTS_DB';
					//$where = "TIME_TO_SEC(TIMEDIFF(CLOSE_DATE,'$date'))>0";
					//$update = read_db($link,$table,$where);
					send_subs($vk,$user,$subs,$upd_array);							
				}
				$msg = null;
				//mysqli_close($link);
			}
			
		}
		mysqli_close($link);
	}
	$ts = $response["ts"];  # обновление номера последнего обновления
}


while (true) {
	myLog("Попал в 2");
	$link = connect_db();
					
	
	#--Смотрю новые подписки у пользователей за последние 30(31) секунд--
	$date = date("Y-m-d H:i:s");
	$table = 'user_subs';
	$where = "TIME_TO_SEC(TIMEDIFF('$date',date_start))<31";//TO_SECONDS?
	$db = read_db($link,$table,$where);
	foreach($db as $user=>$subs)
	{
		//$table = 'MTS_DB';
		//$where = "TIME_TO_SEC(TIMEDIFF(CLOSE_DATE,'$date'))>0";
		//$update = read_db($link,$table,$where);
		send_subs($vk,$user,$subs,$upd_array);							
	}
	$msg = null;
	mysqli_close($link);
	sleep(30);
}

?>
