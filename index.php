<?php
require_once('TelegramBot.php');

error_reporting(E_ALL);
mb_internal_encoding("UTF-8");
date_default_timezone_set('UTC');


$security_check = array(
	'enabled' => true,
	'key' => '' // vk callback secret key
);


$_DATETIME = new DateTime();

function get_file_by_url($url) {
    if (!function_exists('curl_init')) { 
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

$_INPUT = file_get_contents('php://input');
$_INPUT = json_decode($_INPUT, true);




/* 
if ($_INPUT['type'] == 'confirmation' and $_INPUT['group_id'] == '64325708') {
	echo 'fd13259e';
	die();
} 
*/

$api = new TelegramBot(''); // telegram bot key

$reciever = 829134;
if ($security_check['enabled'] === true) {
	if ($_INPUT['secret'] == $security_check['key']) {
		$is_ok = true;
	} else {
		$is_ok = false;
	}
} else {
	$is_ok = true;
}
if ($is_ok === true and $_INPUT['type'] == "wall_post_new" and $_INPUT['object']['post_type'] == "post") {
	$text_to_include = NULL;

	if (isset($_INPUT['object']['copy_history'])) {
		$_INPUT['object'] = $_INPUT['object']['copy_history'][0];
	}

	if (mb_strlen($_INPUT['object']['text']) > 0) {
		$text = $_INPUT['object']['text'];

		if (isset($_INPUT['object']['attachments']) and count($_INPUT['object']['attachments']) > 0) {
			$text_to_include = $text;
		} else {
			$api->sendMessage($reciever, $text);
		}
	}

	if (isset($_INPUT['object']['attachments']) and count($_INPUT['object']['attachments']) > 0) {
		$a = $_INPUT['object']['attachments'];

		foreach ($a as $item) {
			switch ($item['type']) {
				case 'photo': {
					unset($url);
					$file_path = 'image.jpg';

					$api->sendChatAction($reciever, 'upload_photo');
					
					$photo = $item['photo'];
					foreach ($photo as $key => $value) {
						$exp_key = explode('_', $key);

						if ($exp_key[0] == 'photo' and is_numeric($exp_key[1])) {
							$size = (int) $exp_key[1];

							if (isset($url)) {
								if ($size > $url['size']) {
									$url = array(
										'url' => $value,
										'size' => $size
									);
								}
							} else {
								$url = array(
									'url' => $value,
									'size' => $size
								);
							}
						}
					}

					if (isset($url)) {
						$image = get_file_by_url($url['url']);
						file_put_contents($file_path, $image);


						if (mb_strlen($text_to_include) > 0) {
							$caption = $text_to_include;
							$text_to_include = NULL;
						} else {
							$caption = '';
						}
						$api->sendPhoto($reciever, $file_path, array(
							'caption' => $caption
						));
					} else {
						throw new Exception("Image url not found");
					}

					unset($file_path);


					break;
				}
				case 'audio': {
					$file_path = "audio.mp3";

					$api->sendChatAction($reciever, 'upload_audio');
					$audio = $item['audio'];

					if (isset($audio['url'])) {
						$file = get_file_by_url($audio['url']);
						file_put_contents($file_path, $file);

						$api->sendAudio($reciever, $file_path, array(
							'performer' => $audio['artist'],
							'title' => $audio['title'],
							'disable_notification' => true
						));

						// unset($file_path);
					} else {
						throw new Exception("Audio url not found");
					}


					break;
				}
				case 'doc': {
					$collection = array(
						// Docs
						'pdf' => 'application/pdf',

						// Photo
						'gif' => 'image/gif',
						'jpg' => 'image/jpeg',
						'jpeg' => 'image/jpeg',
						'png' => 'image/png',

						// Video
						'mp4' => 'video/mp4',
						'flv' => 'video/x-flv',
						'3gpp' => 'video/3gpp',
						'3gp' => 'video/3gpp',
						'3gpp2' => 'video/3gpp2',
						'3g2' => 'video/3gpp2',

						// Audio
						'mp3' => 'audio/mpeg',
						'ogg' => 'audio/ogg',

						// Other
						'css' => 'text/css',
						'html' => 'text/html',

						'doc' => 'application/msword',
						'dot' => 'application/msword',
						'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
						'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
						'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
						'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',

						'xls' => 'application/vnd.ms-excel',
						'xlt' => 'application/vnd.ms-excel',
						'xla' => 'application/vnd.ms-excel',
						'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
						'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
						'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
						'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
						'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
						'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',


						'ppt' => 'application/vnd.ms-powerpoint',
						'pot' => 'application/vnd.ms-powerpoint',
						'pps' => 'application/vnd.ms-powerpoint',
						'ppa' => 'application/vnd.ms-powerpoint',
						'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
						'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
						'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
						'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
						'potm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
						'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12'
					);

					$doc = $item['doc'];
					$file_name = "document.".$doc['ext'];
					$file_path = "document.".$doc['ext'];

					
					if (isset($collection[$doc['ext']]) and $doc['size'] <= 5000000) {
						$api->sendChatAction($reciever, 'upload_document');


						if (isset($doc['url'])) {
							$file = get_file_by_url($doc['url']);
							file_put_contents($file_path, $file);


							if (mb_strlen($text_to_include) > 0) {
								$caption = $text_to_include;
								$text_to_include = NULL;
							} else {
								$caption = NULL;
							}
							$api->sendDocument($reciever, $file_path, $file_name, $collection[$doc['ext']], array(
								'caption' => $caption,
								'disable_notification' => true
							));

							unset($file_path);
						} else {
							throw new Exception("Document url not found");
						}
					}


					break;
				}
				case 'video': {
					unset($url);
					$file_path = 'image.jpg';

					// $api->sendChatAction($reciever, 'upload_photo');
					$video = $item['video'];

					$video_url = 'http://vk.com/video'.$video['owner_id'].'_'.$video['id'];
					
					$video = $item['video'];
					foreach ($video as $key => $value) {
						$exp_key = explode('_', $key);

						if ($exp_key[0] == 'photo' and is_numeric($exp_key[1])) {
							$size = (int) $exp_key[1];

							if (isset($url)) {
								if ($size > $url['size']) {
									$url = array(
										'url' => $value,
										'size' => $size
									);
								}
							} else {
								$url = array(
									'url' => $value,
									'size' => $size
								);
							}
						}
					}


					

					if (isset($url)) {
						$image = get_file_by_url($url['url']);
						file_put_contents($file_path, $image);


						if (mb_strlen($text_to_include) > 0) {
							$caption = $text_to_include."\n".$video_url;
							$text_to_include = NULL;
						} else {
							$caption = $video_url;
						}

						$api->sendPhoto($reciever, $file_path, array(
							'caption' => $caption
						));
					} else {
						if (mb_strlen($text_to_include) > 0) {
							$caption = $text_to_include."\n".htmlspecialchars('<a href="'.$video_url.'">Открыть видео</a>');
							$text_to_include = NULL;
						} else {
							$caption = htmlspecialchars('<a href="'.$video_url.'">Открыть видео</a>');
						}

						$api->sendMessage($reciever, $caption, ['parse_mode' => 'HTML']);
					}

					unset($file_path);


					break;
				}
				case 'link': {
					$link = $item['link'];

					if (mb_strlen($text_to_include) > 0) {
						$caption = $text_to_include."\n\n".'<a href="'.$link['url'].'">Открыть ссылку</a>';
						$text_to_include = NULL;
					} else {
						$caption = '<a href="'.$link['url'].'">Открыть ссылку</a>';
					}					
					$api->sendMessage($reciever, $caption, ['parse_mode' => "HTML"]);
					break;
				}
				default: {

					break;
				}
			}
		}
	}
}


echo 'ok';


// echo json_encode($_RESPONSE, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
file_put_contents('out.txt', $_DATETIME->format('Y-m-d H:i:s').PHP_EOL.json_encode($_INPUT))
?>