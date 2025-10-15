<?php declare(strict_types = 1);

// odsl-/var/www/html/app
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    '/var/www/html/app/Providers/HttpsServiceProvider.php' => 
    array (
      0 => '00c54ec9ab2a6a301233722ed0c9b7af6f4ef18d',
      1 => 
      array (
        0 => 'app\\providers\\httpsserviceprovider',
      ),
      2 => 
      array (
        0 => 'app\\providers\\register',
        1 => 'app\\providers\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Providers/AppServiceProvider.php' => 
    array (
      0 => '5198c332f4523c8baad66f18b5fcfb4c9457b898',
      1 => 
      array (
        0 => 'app\\providers\\appserviceprovider',
      ),
      2 => 
      array (
        0 => 'app\\providers\\register',
        1 => 'app\\providers\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Contracts/MessengerInterface.php' => 
    array (
      0 => '9ccebdb74f43d0feb232f08fbf9151283c0af1d2',
      1 => 
      array (
        0 => 'app\\contracts\\messengerinterface',
      ),
      2 => 
      array (
        0 => 'app\\contracts\\send',
        1 => 'app\\contracts\\formatmessage',
        2 => 'app\\contracts\\buildbuttons',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/InvalidWagerStateException.php' => 
    array (
      0 => 'db76590ba0bc063e18983cbb0c39298b4f28d9f4',
      1 => 
      array (
        0 => 'app\\exceptions\\invalidwagerstateexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\__construct',
        1 => 'app\\exceptions\\getwager',
        2 => 'app\\exceptions\\getattemptedaction',
        3 => 'app\\exceptions\\getvalidstatuses',
        4 => 'app\\exceptions\\getusermessage',
        5 => 'app\\exceptions\\getstatuscode',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/WagerNotOpenException.php' => 
    array (
      0 => '19a581231950a943e0184083f7af9916201e2340',
      1 => 
      array (
        0 => 'app\\exceptions\\wagernotopenexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\__construct',
        1 => 'app\\exceptions\\getwager',
        2 => 'app\\exceptions\\getusermessage',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/InvalidAnswerException.php' => 
    array (
      0 => 'f85c1727b2d2687707ec62492ba5c890ece404ef',
      1 => 
      array (
        0 => 'app\\exceptions\\invalidanswerexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\__construct',
        1 => 'app\\exceptions\\getwagertype',
        2 => 'app\\exceptions\\getusermessage',
        3 => 'app\\exceptions\\forbinary',
        4 => 'app\\exceptions\\formultiplechoice',
        5 => 'app\\exceptions\\fornumeric',
        6 => 'app\\exceptions\\fordate',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/BeatWagerException.php' => 
    array (
      0 => '71d5d79dc930f4dcd314a3b67120f32b1822669d',
      1 => 
      array (
        0 => 'app\\exceptions\\beatwagerexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\getstatuscode',
        1 => 'app\\exceptions\\getusermessage',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/InvalidStakeException.php' => 
    array (
      0 => '155da986cdc7a0f000f77112c0b057f5a89a2d82',
      1 => 
      array (
        0 => 'app\\exceptions\\invalidstakeexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\__construct',
        1 => 'app\\exceptions\\getprovided',
        2 => 'app\\exceptions\\getrequired',
        3 => 'app\\exceptions\\getusermessage',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/UserAlreadyJoinedException.php' => 
    array (
      0 => 'a0d70f057b497d98dad08e520804614cbc7636c0',
      1 => 
      array (
        0 => 'app\\exceptions\\useralreadyjoinedexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\__construct',
        1 => 'app\\exceptions\\getuser',
        2 => 'app\\exceptions\\getwager',
        3 => 'app\\exceptions\\getusermessage',
        4 => 'app\\exceptions\\getstatuscode',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/InsufficientPointsException.php' => 
    array (
      0 => 'da4abd0cf8b1632c504f28138f67ef93847eff13',
      1 => 
      array (
        0 => 'app\\exceptions\\insufficientpointsexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\__construct',
        1 => 'app\\exceptions\\getrequired',
        2 => 'app\\exceptions\\getavailable',
        3 => 'app\\exceptions\\getusermessage',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/InvalidTokenException.php' => 
    array (
      0 => '0642e4d54f7f91d73229bb2e117eca2520d030a9',
      1 => 
      array (
        0 => 'app\\exceptions\\invalidtokenexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\__construct',
        1 => 'app\\exceptions\\getusermessage',
        2 => 'app\\exceptions\\getstatuscode',
        3 => 'app\\exceptions\\expired',
        4 => 'app\\exceptions\\alreadyused',
        5 => 'app\\exceptions\\notfound',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Exceptions/WagerAlreadySettledException.php' => 
    array (
      0 => 'a167a684cfe56a1a4487b042a9c13a41e166e98e',
      1 => 
      array (
        0 => 'app\\exceptions\\wageralreadysettledexception',
      ),
      2 => 
      array (
        0 => 'app\\exceptions\\__construct',
        1 => 'app\\exceptions\\getwager',
        2 => 'app\\exceptions\\getusermessage',
        3 => 'app\\exceptions\\getstatuscode',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Console/Commands/SendSettlementReminders.php' => 
    array (
      0 => 'bcc4f4bee00c35a4b2c12baab687d0c803a023df',
      1 => 
      array (
        0 => 'app\\console\\commands\\sendsettlementreminders',
      ),
      2 => 
      array (
        0 => 'app\\console\\commands\\handle',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Console/Commands/Telegram/SetWebhook.php' => 
    array (
      0 => 'c98449310d0be84d420ad69f8473386b570c8675',
      1 => 
      array (
        0 => 'app\\console\\commands\\telegram\\setwebhook',
      ),
      2 => 
      array (
        0 => 'app\\console\\commands\\telegram\\handle',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Console/Commands/Telegram/WebhookInfo.php' => 
    array (
      0 => '14157d435bf778eb555bf100b5886ee7fc621d32',
      1 => 
      array (
        0 => 'app\\console\\commands\\telegram\\webhookinfo',
      ),
      2 => 
      array (
        0 => 'app\\console\\commands\\telegram\\handle',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/DTOs/Button.php' => 
    array (
      0 => '218cb8c5614ceab22cc304e92d975ebbe926f969',
      1 => 
      array (
        0 => 'app\\dtos\\button',
        1 => 'app\\dtos\\buttonaction',
        2 => 'app\\dtos\\buttonstyle',
      ),
      2 => 
      array (
        0 => 'app\\dtos\\__construct',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/DTOs/Message.php' => 
    array (
      0 => 'ad728390e73924109605c2da0e6c1283ecd1b4f7',
      1 => 
      array (
        0 => 'app\\dtos\\message',
      ),
      2 => 
      array (
        0 => 'app\\dtos\\__construct',
        1 => 'app\\dtos\\getformattedcontent',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/DTOs/MessageType.php' => 
    array (
      0 => '182f832b45e33401ffc3dbbc4f6333d50450625e',
      1 => 
      array (
        0 => 'app\\dtos\\messagetype',
      ),
      2 => 
      array (
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Http/Middleware/HandleInertiaRequests.php' => 
    array (
      0 => 'c10d6f9b0bd0ba0d04d3742d95b82bea776de840',
      1 => 
      array (
        0 => 'app\\http\\middleware\\handleinertiarequests',
      ),
      2 => 
      array (
        0 => 'app\\http\\middleware\\version',
        1 => 'app\\http\\middleware\\share',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php' => 
    array (
      0 => 'b763917b507b475925cb42dfe9486da5682288a4',
      1 => 
      array (
        0 => 'app\\http\\middleware\\authenticatefromsignedurl',
      ),
      2 => 
      array (
        0 => 'app\\http\\middleware\\handle',
        1 => 'app\\http\\middleware\\finduserbyidentifier',
        2 => 'app\\http\\middleware\\redirecttocleanurl',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Http/Controllers/ShortUrlController.php' => 
    array (
      0 => 'ae97efe2bfd3328b9b884d3f86a03eac75ce1f89',
      1 => 
      array (
        0 => 'app\\http\\controllers\\shorturlcontroller',
      ),
      2 => 
      array (
        0 => 'app\\http\\controllers\\redirect',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Http/Controllers/Controller.php' => 
    array (
      0 => '75cadca8afa5982965d1ac316df3c693271b4902',
      1 => 
      array (
        0 => 'app\\http\\controllers\\controller',
      ),
      2 => 
      array (
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php' => 
    array (
      0 => '7712b935238f8d40f51e30bfe4046d6cbc4fc7ee',
      1 => 
      array (
        0 => 'app\\http\\controllers\\api\\telegramwebhookcontroller',
      ),
      2 => 
      array (
        0 => 'app\\http\\controllers\\api\\__construct',
        1 => 'app\\http\\controllers\\api\\handle',
        2 => 'app\\http\\controllers\\api\\verifywebhooksecret',
        3 => 'app\\http\\controllers\\api\\processupdate',
        4 => 'app\\http\\controllers\\api\\handlemessage',
        5 => 'app\\http\\controllers\\api\\handlecommand',
        6 => 'app\\http\\controllers\\api\\handlestartcommand',
        7 => 'app\\http\\controllers\\api\\handlehelpcommand',
        8 => 'app\\http\\controllers\\api\\handlenewwagercommand',
        9 => 'app\\http\\controllers\\api\\handlejoincommand',
        10 => 'app\\http\\controllers\\api\\handlemywagerscommand',
        11 => 'app\\http\\controllers\\api\\escapemarkdown',
        12 => 'app\\http\\controllers\\api\\handlebalancecommand',
        13 => 'app\\http\\controllers\\api\\handleleaderboardcommand',
        14 => 'app\\http\\controllers\\api\\handlecallbackquery',
        15 => 'app\\http\\controllers\\api\\handleunknowncommand',
        16 => 'app\\http\\controllers\\api\\getupdatetype',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Http/Controllers/DashboardController.php' => 
    array (
      0 => 'ba45b7083c6b4c4ad530e897655894555b8f8962',
      1 => 
      array (
        0 => 'app\\http\\controllers\\dashboardcontroller',
      ),
      2 => 
      array (
        0 => 'app\\http\\controllers\\show',
        1 => 'app\\http\\controllers\\updateprofile',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Http/Controllers/WagerController.php' => 
    array (
      0 => 'ead27232a74bf75f8a6fa4ba968efd92f01ded07',
      1 => 
      array (
        0 => 'app\\http\\controllers\\wagercontroller',
      ),
      2 => 
      array (
        0 => 'app\\http\\controllers\\__construct',
        1 => 'app\\http\\controllers\\create',
        2 => 'app\\http\\controllers\\store',
        3 => 'app\\http\\controllers\\getorcreateuser',
        4 => 'app\\http\\controllers\\getorcreategroup',
        5 => 'app\\http\\controllers\\postwagertotelegram',
        6 => 'app\\http\\controllers\\success',
        7 => 'app\\http\\controllers\\showsettlementform',
        8 => 'app\\http\\controllers\\settle',
        9 => 'app\\http\\controllers\\settlementsuccess',
        10 => 'app\\http\\controllers\\postsettlementtotelegram',
        11 => 'app\\http\\controllers\\show',
        12 => 'app\\http\\controllers\\settlefromshow',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/OneTimeToken.php' => 
    array (
      0 => '6845515c04c4ddc80a510bb7218b3951bf160bad',
      1 => 
      array (
        0 => 'app\\models\\onetimetoken',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\user',
        2 => 'app\\models\\generate',
        3 => 'app\\models\\isvalid',
        4 => 'app\\models\\isexpired',
        5 => 'app\\models\\isused',
        6 => 'app\\models\\markasused',
        7 => 'app\\models\\getcontext',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/Transaction.php' => 
    array (
      0 => 'ac437540d2e18c6cd004f8ff8b3ac98af285f1fb',
      1 => 
      array (
        0 => 'app\\models\\transaction',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\user',
        2 => 'app\\models\\group',
        3 => 'app\\models\\wager',
        4 => 'app\\models\\wagerentry',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/Group.php' => 
    array (
      0 => 'd3781a9c67101f884b35513c9ff7edee72f25aa7',
      1 => 
      array (
        0 => 'app\\models\\group',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\users',
        2 => 'app\\models\\wagers',
        3 => 'app\\models\\wagertemplates',
        4 => 'app\\models\\transactions',
        5 => 'app\\models\\sendmessage',
        6 => 'app\\models\\getchatid',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/ShortUrl.php' => 
    array (
      0 => '0c3a77039ab65a093129d20400cd9d8893ef4777',
      1 => 
      array (
        0 => 'app\\models\\shorturl',
      ),
      2 => 
      array (
        0 => 'app\\models\\generateuniquecode',
        1 => 'app\\models\\generatecode',
        2 => 'app\\models\\isexpired',
        3 => 'app\\models\\scopeactive',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/AuditLog.php' => 
    array (
      0 => 'bf15226777deb315c73061a8b939302729f70793',
      1 => 
      array (
        0 => 'app\\models\\auditlog',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\actor',
        2 => 'app\\models\\auditable',
        3 => 'app\\models\\scopeaction',
        4 => 'app\\models\\scopebyactor',
        5 => 'app\\models\\scoperecent',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/UserGroup.php' => 
    array (
      0 => '89d94d5cea726600681e0759a39352b0a6525956',
      1 => 
      array (
        0 => 'app\\models\\usergroup',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\user',
        2 => 'app\\models\\group',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/MessengerService.php' => 
    array (
      0 => 'fdcd415d03b982105f4d7c99582edb98ececfbdd',
      1 => 
      array (
        0 => 'app\\models\\messengerservice',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\user',
        2 => 'app\\models\\getdisplaynameattribute',
        3 => 'app\\models\\scopetelegram',
        4 => 'app\\models\\scopediscord',
        5 => 'app\\models\\scopeslack',
        6 => 'app\\models\\findbyplatform',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/User.php' => 
    array (
      0 => 'd56f42abae66ac0e8a2dc1ebd8cac6dbfe4f0f6a',
      1 => 
      array (
        0 => 'app\\models\\user',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\groups',
        2 => 'app\\models\\wagers',
        3 => 'app\\models\\wagerentries',
        4 => 'app\\models\\transactions',
        5 => 'app\\models\\messengerservices',
        6 => 'app\\models\\getmessengerservice',
        7 => 'app\\models\\gettelegramservice',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/Wager.php' => 
    array (
      0 => '858eb1e97c2076c4e3b745cc8b50160d4dfeaea9',
      1 => 
      array (
        0 => 'app\\models\\wager',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\group',
        2 => 'app\\models\\creator',
        3 => 'app\\models\\settler',
        4 => 'app\\models\\entries',
        5 => 'app\\models\\transactions',
        6 => 'app\\models\\onetimetokens',
        7 => 'app\\models\\isbinary',
        8 => 'app\\models\\ismultiplechoice',
        9 => 'app\\models\\isnumeric',
        10 => 'app\\models\\isdate',
        11 => 'app\\models\\getdisplayoptions',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/WagerSettlementToken.php' => 
    array (
      0 => '1b731e0d724a107bc5a0a5a3d5e22124c21637db',
      1 => 
      array (
        0 => 'app\\models\\wagersettlementtoken',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\wager',
        2 => 'app\\models\\creator',
        3 => 'app\\models\\generate',
        4 => 'app\\models\\isvalid',
        5 => 'app\\models\\isexpired',
        6 => 'app\\models\\markasused',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/WagerTemplate.php' => 
    array (
      0 => '9cfc40c926712e0b86d234ada5e07ce554836b38',
      1 => 
      array (
        0 => 'app\\models\\wagertemplate',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\group',
        2 => 'app\\models\\creator',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Models/WagerEntry.php' => 
    array (
      0 => '8e80f8d2f0b4a22fa5cf5189e73d8a02bbb4abc6',
      1 => 
      array (
        0 => 'app\\models\\wagerentry',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\wager',
        2 => 'app\\models\\user',
        3 => 'app\\models\\group',
        4 => 'app\\models\\getformattedanswer',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Services/AuditService.php' => 
    array (
      0 => 'df8c1814585cf42e31c8172a7dd063c7567b25cb',
      1 => 
      array (
        0 => 'app\\services\\auditservice',
      ),
      2 => 
      array (
        0 => 'app\\services\\log',
        1 => 'app\\services\\logfromrequest',
        2 => 'app\\services\\logsystem',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Services/PointService.php' => 
    array (
      0 => 'a3d9d2ef349af6b9155547f11b5eabbbe6335f79',
      1 => 
      array (
        0 => 'app\\services\\pointservice',
      ),
      2 => 
      array (
        0 => 'app\\services\\getbalance',
        1 => 'app\\services\\deductpoints',
        2 => 'app\\services\\awardpoints',
        3 => 'app\\services\\recordloss',
        4 => 'app\\services\\refundpoints',
        5 => 'app\\services\\initializeuserpoints',
        6 => 'app\\services\\applypointdecay',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Services/UserMessengerService.php' => 
    array (
      0 => 'e88979c4d2ac41c985021d161a6b96d9f0f53245',
      1 => 
      array (
        0 => 'app\\services\\usermessengerservice',
      ),
      2 => 
      array (
        0 => 'app\\services\\findorcreate',
        1 => 'app\\services\\generateusername',
        2 => 'app\\services\\findbyplatform',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Services/TokenService.php' => 
    array (
      0 => 'aec6f3a8af7a23e88be951a53ed2fd4a19df6044',
      1 => 
      array (
        0 => 'app\\services\\tokenservice',
      ),
      2 => 
      array (
        0 => 'app\\services\\generatesettlementtoken',
        1 => 'app\\services\\generatedisputetoken',
        2 => 'app\\services\\verifytoken',
        3 => 'app\\services\\consumetoken',
        4 => 'app\\services\\getactivetokensforwager',
        5 => 'app\\services\\invalidatetokensforwager',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Services/Messengers/TelegramMessenger.php' => 
    array (
      0 => 'dcad92a089931da011c421f95afd8695d1cdfb55',
      1 => 
      array (
        0 => 'app\\services\\messengers\\telegrammessenger',
      ),
      2 => 
      array (
        0 => 'app\\services\\messengers\\__construct',
        1 => 'app\\services\\messengers\\send',
        2 => 'app\\services\\messengers\\formatmessage',
        3 => 'app\\services\\messengers\\buildbuttons',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Services/MessageService.php' => 
    array (
      0 => '9c8d569f21049229fe0509e561789458aa6f4048',
      1 => 
      array (
        0 => 'app\\services\\messageservice',
      ),
      2 => 
      array (
        0 => 'app\\services\\wagerannouncement',
        1 => 'app\\services\\settlementresult',
        2 => 'app\\services\\settlementreminder',
        3 => 'app\\services\\viewprogressdm',
        4 => 'app\\services\\joinconfirmation',
        5 => 'app\\services\\buildwagerbuttons',
        6 => 'app\\services\\formatwagertype',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Services/MessengerFactory.php' => 
    array (
      0 => 'cd4b3a377c5434d0875ccb2a484df16beb11cffd',
      1 => 
      array (
        0 => 'app\\services\\messengerfactory',
      ),
      2 => 
      array (
        0 => 'app\\services\\for',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/html/app/Services/WagerService.php' => 
    array (
      0 => 'dd034de8d5fc28f42aaaef6d37b2d74821522fff',
      1 => 
      array (
        0 => 'app\\services\\wagerservice',
      ),
      2 => 
      array (
        0 => 'app\\services\\__construct',
        1 => 'app\\services\\createwager',
        2 => 'app\\services\\placewager',
        3 => 'app\\services\\validateanswer',
        4 => 'app\\services\\validatebinaryanswer',
        5 => 'app\\services\\validatemultiplechoiceanswer',
        6 => 'app\\services\\validatenumericanswer',
        7 => 'app\\services\\validatedateanswer',
        8 => 'app\\services\\lockwager',
        9 => 'app\\services\\settlewager',
        10 => 'app\\services\\settlecategoricalwager',
        11 => 'app\\services\\settlenumericwager',
        12 => 'app\\services\\settledatewager',
        13 => 'app\\services\\awardwinner',
        14 => 'app\\services\\recordloss',
        15 => 'app\\services\\refundentry',
        16 => 'app\\services\\cancelwager',
      ),
      3 => 
      array (
      ),
    ),
  ),
));