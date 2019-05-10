<?php
    require_once '../vendor/autoload.php';
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    use Monolog\Handler\FirePHPHandler;
    $logger = new Logger('LineBot');
    $logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV["LINEBOT_ACCESS_TOKEN"]);
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV["LINEBOT_CHANNEL_SECRET"]]);
    $signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
    try {
        $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
    } catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
        error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
    } catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
        error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
    } catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
        error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
    } catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
        error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
    }
    foreach ($events as $event) {
        // Postback Event
        if (($event instanceof \LINE\LINEBot\Event\PostbackEvent)) {
            $logger->info('Postback message has come');
            continue;
        }
        // Location Event
        if  ($event instanceof LINE\LINEBot\Event\MessageEvent\LocationMessage) {
            $logger->info("location -> ".$event->getLatitude().",".$event->getLongitude());
            continue;
        }
        // Message Event = TextMessage
        if (($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
            $messageText=strtolower(trim($event->getText()));
            switch ($messageText) {
                case "ประวัติร้าน" :
                    $outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("ชานมไข่มุกสไตล์ไต้หวัน โดยทางร้านได้รับแรงบันดาลใจจากการเดินทางไปเที่ยวที่ไต้หวันและได้ลองรับประทานชานมของที่นั่นแล้วได้รับแรงบันดาลในทำให้เกิดเป็น 'KOMUKOMU' นั่นเอง");
                    break;
                case "ที่ตั้งร้าน" :
                    $outputText = new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder("KomuKomu", "อยู่ใกล้สยามนี่แหละ หาเอาเอง", 13.7466963, 100.5339218);
                    break;
                case "button" :
                    $actions = array (
                                      // general message action
                                      New \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder("button 1", "text 1"),
                                      // URL type action
                                      New \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("Google", "http://www.google.com"),
                                      // The following two are interactive actions
                                      New \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("next page", "page=3"),
                                      New \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Previous", "page=1")
                                      );
                    $img_url = "https://cdn.shopify.com/s/files/1/0379/7669/products/sampleset2_1024x1024.JPG?v=1458740363";
                    $button = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder("button text", "description", $img_url, $actions);
                    $outputText = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("this message to use the phone to look to the Oh", $button);
                    break;
                case "สั่งสินค้า" :
                    $columns = array();
                    $img_url = "https://scontent.fbkk2-6.fna.fbcdn.net/v/t1.15752-9/60275950_879913802339924_1047800809942679552_n.jpg?_nc_cat=107&_nc_eui2=AeEI_RUx39rC0WpvuvfxT1FmgZnPS4XOQ9KkO2F_f55DpqXSlquthwSGHDnQoEQi6Hk9HCIqV-xcHTrbc5Yk5WY_SxUlZEdPbzzulYp5uvRqFQ&_nc_ht=scontent.fbkk2-6.fna&oh=b33e4196965f41c7c64ed029194f7723&oe=5D5641E6";
//                    for($i=0;$i<5;$i++) {
                        $actions = array(
                                         new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("ตัวอย่างสินค้า","https://scontent.fbkk2-6.fna.fbcdn.net/v/t1.15752-9/60275950_879913802339924_1047800809942679552_n.jpg?_nc_cat=107&_nc_eui2=AeEI_RUx39rC0WpvuvfxT1FmgZnPS4XOQ9KkO2F_f55DpqXSlquthwSGHDnQoEQi6Hk9HCIqV-xcHTrbc5Yk5WY_SxUlZEdPbzzulYp5uvRqFQ&_nc_ht=scontent.fbkk2-6.fna&oh=b33e4196965f41c7c64ed029194f7723&oe=5D5641E6"),
                                         new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Add to Cart","action=carousel&button=".$i)
                                         );
                        $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("ชานมไข่มุกสไตล์ไต้หวัน", "โดยนำเข้าชามาจากไต้หวันแท้ๆ 50฿", $img_url , $actions);
                        $columns[] = $column;
//                    }
                    $img_url2 = "https://scontent.fbkk2-7.fna.fbcdn.net/v/t1.15752-9/59908823_1120334001478920_1198887468674318336_n.jpg?_nc_cat=108&_nc_eui2=AeEuXuw-TFHuWYR3bN8vmG-1VbiwLPZbDxHtl5jrir4FXKAgXDfPvZMlvKKQXq80NMXS-yx9ghtaBJkSSA9tRzN-NKFeJVQCJyQPPXd7ybAjQA&_nc_ht=scontent.fbkk2-7.fna&oh=877e0c6fed2b8ea0051303f99f6136e4&oe=5D74DCC8";
                    $actions = array(
                                     new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("ตัวอย่างสินค้า","https://scontent.fbkk2-7.fna.fbcdn.net/v/t1.15752-9/59908823_1120334001478920_1198887468674318336_n.jpg?_nc_cat=108&_nc_eui2=AeEuXuw-TFHuWYR3bN8vmG-1VbiwLPZbDxHtl5jrir4FXKAgXDfPvZMlvKKQXq80NMXS-yx9ghtaBJkSSA9tRzN-NKFeJVQCJyQPPXd7ybAjQA&_nc_ht=scontent.fbkk2-7.fna&oh=877e0c6fed2b8ea0051303f99f6136e4&oe=5D74DCC8"),
                                     new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Add to Cart","action=carousel&button=".$i)
                                     );
                    $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("Brown Sugar Fresh Milk", "Brown Sugar จากโอกินาวาที่หอมหวาน 60฿", $img_url2 , $actions);
                    $columns[] = $column;
                    $img_url3 = "https://scontent.fbkk12-3.fna.fbcdn.net/v/t1.15752-9/60059675_320271508665871_2443178037362032640_n.jpg?_nc_cat=102&_nc_eui2=AeFW1DMfy24Ocs2fxTWQbLMMmDTWWDGZEsWIDKF9yocLnS-FlEA43UEENgIWQQQVw-SQjtBhADHt2sdgv9bYDu4dcO_7EbuEuT1J1Fr1FzDClg&_nc_ht=scontent.fbkk12-3.fna&oh=450a46227c2190224a2e300514557bd5&oe=5D5E7F3E";
                    $actions = array(
                                     new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("ตัวอย่างสินค้า","https://scontent.fbkk12-3.fna.fbcdn.net/v/t1.15752-9/60059675_320271508665871_2443178037362032640_n.jpg?_nc_cat=102&_nc_eui2=AeFW1DMfy24Ocs2fxTWQbLMMmDTWWDGZEsWIDKF9yocLnS-FlEA43UEENgIWQQQVw-SQjtBhADHt2sdgv9bYDu4dcO_7EbuEuT1J1Fr1FzDClg&_nc_ht=scontent.fbkk12-3.fna&oh=450a46227c2190224a2e300514557bd5&oe=5D5E7F3E"),
                                     new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Add to Cart","action=carousel&button=".$i)
                                     );
                    $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("โกโก้", "ใช้โกโก้แท้จากประเทศเนเธอร์แลนด์ 40฿ ใส่ไข่มุกคิดเพิ่ม 10฿", $img_url3 , $actions);
                    $columns[] = $column;
                    $img_url4 = "https://scontent.fbkk2-5.fna.fbcdn.net/v/t1.15752-9/60039934_2420309821513747_5673405829533925376_n.jpg?_nc_cat=110&_nc_eui2=AeE72qPSnfEqIMvWKXxfsqDVotI_MzzdYBzQ7bj6zZWBMyuvs1sd02XeOoXc5ig5kmLTDI8y_elEo2-UEBa15tih2kYFLWkWbmtcR53EFfmvTg&_nc_ht=scontent.fbkk2-5.fna&oh=ddeca4516a5f75b48d25eed23cbfc250&oe=5D67D733";
                    $actions = array(
                                     new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("ตัวอย่างสินค้า","https://scontent.fbkk2-5.fna.fbcdn.net/v/t1.15752-9/60039934_2420309821513747_5673405829533925376_n.jpg?_nc_cat=110&_nc_eui2=AeE72qPSnfEqIMvWKXxfsqDVotI_MzzdYBzQ7bj6zZWBMyuvs1sd02XeOoXc5ig5kmLTDI8y_elEo2-UEBa15tih2kYFLWkWbmtcR53EFfmvTg&_nc_ht=scontent.fbkk2-5.fna&oh=ddeca4516a5f75b48d25eed23cbfc250&oe=5D67D733"),
                                     new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Add to Cart","action=carousel&button=".$i)
                                     );
                    $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("โกโก้โอริโอ้", "ใช้โกโก้แท้จากประเทศเนเธอร์แลนด์ มาผสมกับโอริโอ้ 45฿ ใส่ไข่มุกคิดเพิ่ม 10฿", $img_url4 , $actions);
                    $columns[] = $column;
                    $img_url5 = "https://scontent.fbkk8-2.fna.fbcdn.net/v/t1.15752-9/59775043_654470768329473_3277271664181641216_n.jpg?_nc_cat=103&_nc_eui2=AeEKFfrndi0d9L8l99g_H64_KrWpuW86kGV1yugB8mRFG5b46rRnQ3rVWFxSG5ykqbOOPly2Mx0oXSw7g-AgZsLbZML3_E8ufuCkiqJyP4ZbJQ&_nc_ht=scontent.fbkk8-2.fna&oh=0971aafa154e667a9cbbd278ffca3536&oe=5D6E110B";
                    $actions = array(
                                     new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("ตัวอย่างสินค้า","https://scontent.fbkk8-2.fna.fbcdn.net/v/t1.15752-9/59775043_654470768329473_3277271664181641216_n.jpg?_nc_cat=103&_nc_eui2=AeEKFfrndi0d9L8l99g_H64_KrWpuW86kGV1yugB8mRFG5b46rRnQ3rVWFxSG5ykqbOOPly2Mx0oXSw7g-AgZsLbZML3_E8ufuCkiqJyP4ZbJQ&_nc_ht=scontent.fbkk8-2.fna&oh=0971aafa154e667a9cbbd278ffca3536&oe=5D6E110B"),
                                     new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Add to Cart","action=carousel&button=".$i)
                                     );
                    $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("นมสดใส่ไข่มุก", "ใช้นมสดยี่ห้อดังอย่าง Meiji ที่มีความหอมมัน 40฿", $img_url5 , $actions);
                    $columns[] = $column;
                    $img_url6 = "https://scontent.fbkk2-5.fna.fbcdn.net/v/t1.15752-9/59767263_333660927353553_131252748866813952_n.jpg?_nc_cat=110&_nc_eui2=AeGI7dx_Jycwyd4redujPJ5IlHVIkAKxeEqf8OpqJIA_ufrFO74Iq_YVT8a8g9MZ9Nm93Kh5irBwitoEJwG_ln-pUofRy7cYjauRktjVZL6XSw&_nc_ht=scontent.fbkk2-5.fna&oh=b55be235b28b1c534487222c46c724fb&oe=5D2985CF";
                    $actions = array(
                                     new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("ตัวอย่างสินค้า","https://scontent.fbkk2-5.fna.fbcdn.net/v/t1.15752-9/59767263_333660927353553_131252748866813952_n.jpg?_nc_cat=110&_nc_eui2=AeGI7dx_Jycwyd4redujPJ5IlHVIkAKxeEqf8OpqJIA_ufrFO74Iq_YVT8a8g9MZ9Nm93Kh5irBwitoEJwG_ln-pUofRy7cYjauRktjVZL6XSw&_nc_ht=scontent.fbkk2-5.fna&oh=b55be235b28b1c534487222c46c724fb&oe=5D2985CF"),
                                     new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Add to Cart","action=carousel&button=".$i)
                                     );
                    $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("นมสดสตอเบอรี่", "ใช้นมสดยี่ห้อดังอย่าง Meiji โดยเพิ่มความเปรี้ยวหวานจากสตอเบอรี่สดๆ 60฿", $img_url6 , $actions);
                    $columns[] = $column;
                    $carousel = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columns);
                    $outputText = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("รายการอาหารมาแล้วจ้า", $carousel);
                    break;
                case "เจ้าของร้าน" :
                    $img_url = "https://scontent.fbkk12-2.fna.fbcdn.net/v/t31.0-8/11942332_1040995195924746_1517129740486406376_o.jpg?_nc_cat=104&_nc_eui2=AeH5JAw5tqDJ8n0rKMvtuqfSfY-MCcGEw4wbKTFQg8wFmn4jGPf0CBClm393bs90BMXy5bfW7bgkguQzJp8wtFtVH24JmM9TeX4Vif37n-oZjA&_nc_ht=scontent.fbkk12-2.fna&oh=ea69e1f1b813b3c1061133defd24a786&oe=5D2BFCE0";
                    $outputText = new LINE\LINEBot\MessageBuilder\ImageMessageBuilder($img_url, $img_url);
                    break;
                case "confirm" :
                    $actions = array (
                                      New \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("yes", "ans=y"),
                                      New \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("no", "ans=N")
                                      );
                    $button = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder("problem", $actions);
                    $outputText = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("this message to use the phone to look to the Oh", $button);
                    break;
                default :
                    $outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("ไม่มีฟังก์ชันนี้ค่ะ กรุณาพิมพ์ใหม่ค่ะ");
                    break;
            }
            $response = $bot->replyMessage($event->getReplyToken(), $outputText);
        }
    }
