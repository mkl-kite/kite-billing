<?php
// данные для печати в документах
define("COMPANY_NAME","Net-Line");
define("COMPANY_UNIT","Хогвардс");
define("COMPANY_PHONE","066-123-45-67, 071-123-45-67");
define("COMPANY_SITE","https://domain.net/stat");
define("COMPANY_MAIL","sn_billing@net-line.org");
define("COMPANY_SOCIAL","https://vk.com/netline_grp");
define("COMPANY_PACT","http://net-line.org/dogovor.pdf");
define("COMPANY_LOGO","https://domain.net/pic/logo.png");
$COMPANY_LINKS = array("http://forum.net-line.org/", "http://iptv.net-line.org/", COMPANY_SOCIAL);
$LOGO='<FONT color="black" face="verdana">net</FONT><FONT color="#f84" face="verdana">line</FONT> - <FONT color="#33d">'.COMPANY_UNIT.'</FONT>';
$COMPANY_NAME=COMPANY_NAME."-".COMPANY_UNIT;

// данные для печати в документах
define("FIRMNAME","ФЛП  М.В.");
define("FIRMID","123456789");
define("FIRMBANK","отделение 1234/01 Центральный Республиканский Банк");
define("FIRMCODEBANK","401234");
define("FIRMMAIL","support@domain.org");
define("FIRMACC","123456789456789");
define("FIRMCERT","Серия ААAA &#8470; 01234");
define("FIRMLIC","&#8470; AB1234 от 01 декабря 2010 г.");
define("FIRMLICENSE","Лицензия ".FIRMLIC."<BR>Свидетельство ".FIRMCERT."<BR>банк p/c ".FIRMACC);
define("FIRMADDRESS","Весёлый район, улица Нечистой силы, дом 13");

// адрес для файлов автономной карты
define("AUTONOMOUS_MAP_NAME","");
define("AUTONOMOUS_MAP_URL","");

// адрес для запросов к nagios для карт
define("NAGIOS_URL","");
define("CONFIGURE_NAGIOS","0"); // использовать автоматическое изменение конфигов в NAGIOS

// адрес для запросов к icinga 
define("ICINGA_DOMAIN","domain.net");
define("ICINGA_URL","https://domain.net:5665");
define("ICINGA_LOGIN","root");
define("ICINGA_PASSWORD","632ab5a746454861");

define("ONU_SIGNAL_URL",NAGIOS_URL."?do=client_graph&host=1&period=week&mac=");

define("CITYCODE",13); // цифра для получения номера контракта пользователя из id

// Использование карточек
define("USE_CARDS","1"); // использовать или нет 0/1
define("POVOD_FOR_CARD","1"); // id в таблице povod
define("POVOD_FOR_PRESENT","17"); // id в таблице povod, нужен при включениии возможности передачи денег между пользователями

// Использовать отправку смс
define("USE_SMS","1");
define("SMS_SEND_OF_NEWCLAIM","0"); // посылать смс при приёмке заявления (наряды)
define("SMS_SEND_OF_PLANECLAIM","0"); // посылать смс при добавления задания в наряд

// Посылать пользователю уведомления по почте
define("USE_EMAIL","2");

// Использовать JABBER пристройку
define("USE_JABBER","0");

// скрипт для завершения пользовательской сессии
define("USERKILL","/usr/local/sbin/scripts/userkill.pl");
define("USERKILLDIR","/usr/local/sbin/scripts/userkill/"); // каталов со скриптами для различных NAS-ов

define("USE_CLIENT_PHOTO",0);
define("PHOTO_TARGET",0); // 0 - database; 1 - file
define("PHOTO_FOLDER","pic/photo/");
define("MAXPHOTOSIZE",65530);

// Количество байт в мегабайте (иногда требуется 1000000)
define("MBYTE","1048576");

// Настройки логирования
// для работы требуется доступ apache на запись
define("LOG_FILE","/var/log/freeradius/www_admin.log");

// Начислять сумму при переходе с пакета на пакет (не реализовано)
define("CHANGEPACKET_PAY","0"); // стоимость перехода с пакета на пакет
define("CHANGEPACKET","0"); // разрешение пользователям переключаться на др. пакет
define("IP_COST",60.00); // стоимость месячной аренды ip

// автоматически закрывать кассовую ведомость
define("AUTO_CLOSE_KO","1");
define("AUTO_ACCEPT_KO","7");

define("CREDIT_AUTOREMOVE",0); // снимать кредит пользователя при оплате
define("CREDIT_LIMIT",16000); // общий лимит на кредиты по всем пользователям
define("CHECK_ADDRESS",1); // проверять адрес на соответствие шаблону xxx.Xxxxxxxxx XXx/XX

// для синхронного обновления карт через websocket
define("WEBSOCKET_ENABLE","1");
define("USOCKET_FILE","/var/run/ws_server.sock");

// группы для предоставления особого доступа
$groups = array(
	'map'=>'картёжники',
	'bugh'=>'бухгалтеры',
	'stat'=>'статистики',
);
// type of service (tos) // за что снимать деньги
$tosname=array(
	'0' => 'Не снимать',
	'1' => 'За время',
	'2' => 'За трафик',
	'3' => 'За трафик + время'
);
// направление трафика
$dirtraf=array(
	'0' => 'Не учитывать',
	'1' => 'Входящий',
	'2' => 'Исходящий',
	'3' => 'Суммарный'
);
// способ снятия с депозита фиксированной суммы
$abonpl=array(
	'0'  => 'Не снимать',
	'1'  => '(Cron) Раз в сутки, если было подключение',
#	'2'  => 'за каждые сутки, в т.ч. и за предыдущие',
#	'3'  => 'При каждом подключении'.
#	'5'  => 'Раз в месяц, если было подключение'.
	'7'  => '(Cron) Каждый День',
	'8'  => '(Cron) Каждый Месяц 1-го числа',
	'9'  => '(Cron) Каждый месяц обязан потратить',
	'10' => '(Cron) Каждый раз в момент начала пакета'
);
// значение поля в таблице статьи платежей
$typeofpay=array(
	'0' => 'Не определено',
	'1' => 'Терминал',
	'2' => 'АбонОтдел',
	'3' => 'БезНал',
	'4' => 'Карточки',
	'5' => 'Клиент'
);

$mon=array(
	'1' => 'Январь', '01' => 'Январь',
	'2' => 'Февраль', '02' => 'Февраль',
	'3' => 'Март', '03' => 'Март',
	'4' => 'Апрель', '04' => 'Апрель',
	'5' => 'Май', '05' => 'Май',
	'6' => 'Июнь', '06' => 'Июнь',
	'7' => 'Июль', '07' => 'Июль',
	'8' => 'Август', '08' => 'Август',
	'9' => 'Сентябрь', '09' => 'Сентябрь',
	'10' => 'Октябрь',
	'11' => 'Ноябрь',
	'12' => 'Декабрь'
);

$week_days=array(
	'0'=>'Воскресенье', '00'=>'Воскресенье',
	'1'=>'Понедельник', '01'=>'Понедельник',
	'2'=>'Вторник', '02'=>'Вторник',
	'3'=>'Среда', '03'=>'Среда',
	'4'=>'Четверг', '04'=>'Четверг',
	'5'=>'Пятница', '05'=>'Пятница',
	'6'=>'Суббота', '06'=>'Суббота',
	'7'=>'Праздник', '07'=>'Праздник'
);
// уровень доступа для операторов
$op_status=array(
	'0'=>'не определен',
	'1'=>'гость',
	'2'=>'мл.оператор',
	'3'=>'оператор',
	'4'=>'ст.оператор',
	'5'=>'администратор'
);
// типы заявлений в нарядах
$claim_types=array(
	'0'=>'не определен',
	'1'=>'установка',
	'2'=>'ремонт',
	'3'=>'настройка',
	'4'=>'спец.задание',
	'5'=>'переключение'
);
// статус заявления в нарядах
$claim_status=array(
	'0'=>'Открыто',
	'1'=>'Отложено',
	'2'=>'В плане',
	'3'=>'Отказ клиента',
	'4'=>'Выполнено',
	'5'=>'Закрыто'
);
$claim_colors = array('#666','#070','#c00','#a0f','#000','#00f');
// картинки отображающие статус заявления в нарядах
$status_cl = array(
	'<img src="pic/bookmark_white.png">',
	'<img src="pic/bookmark_light.png">',
	'<img src="pic/bookmark_green.png">',
	'<img src="pic/bookmark_red.png">',
	'<img src="pic/bookmark.png">',
	'<img src="pic/bookmark_dark.png">',
);
// статус наряда
$worder_status=array(
  '0'=>'Открыт',
  '1'=>'В работе',
  '2'=>'Выполнен',
  '3'=>'Закрыт'
);

$nas_types=array(
	'linux'=>'linux',
	'mikrotik'=>'mikrotik',
	'mt_telnet'=>'mikrotik_telnet',
	'cisco'=>'cisco'
);
// статус задания в наряде
$clperf_status=array(
	'0'=>'запланировано',
	'1'=>'выполняется',
	'2'=>'не выполнено',
	'3'=>'выполнено'
);
// картинки отображающие статус задания в наряде
$status_cp = array(
	'<img src="pic/bookmark_green.png">',
	'<img src="pic/bookmark_green.png">',
	'<img src="pic/bookmark_dark.png">',
	'<img src="pic/bookmark.png">'
);
// используется при формировании логина из фамилии
$charconv=array(
	'а'=>'a',  'б'=>'b', 'в'=>'v',  'г'=>'g',  'д'=>'d',  'е'=>'e',   'ё'=>'e',
	'ж'=>'zh', 'з'=>'z', 'и'=>'i',  'й'=>'i',  'к'=>'k',  'л'=>'l',   'м'=>'m',
	'н'=>'n',  'о'=>'o', 'п'=>'p',  'р'=>'r',  'с'=>'s',  'т'=>'t',   'у'=>'u',
	'ф'=>'f',  'х'=>'h', 'ц'=>'ts', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'sch', 'ъ'=>'',
	'ы'=>'y',  'ь'=>'',  'э'=>'e',  'ю'=>'yu', 'я'=>'ya' 
);
// используется при отображении статистики пользователя по соединениям
$explain_packet = array(
	"debtors"=>			"задолжник",
	"unknown"=>			"ошибка логина",
	"wrongpass"=>		"ошибка пароля",
	"blocked"=>			"заблокирован",
	"unauthorized"=>	"не то оборудование",
	"simultaneous"=>	"одновременное подкл.",
	"simultaneous1"=>	"одновременное подкл."
);
// типы устройств в таблице devices
$devtype=array(
	'unknown' => '',
	'cable' => 'Кабель',
	'switch' => 'Свич',
	'onu' => 'опт.конвертер (onu)',
	'server' => 'Сервер',
	'patchpanel' => 'Патч панель',
	'divisor' => 'Делитель',
	'splitter' => 'Сплиттер',
	'client' => 'Клиент',
	'wifi' => 'Станция Wi-Fi',
	'mconverter' => 'Медиаконвертер',
	'ups' => 'Блок БП'
);
// типы объектов в таблице map
$objecttype=array(
	'home' => 'Дом',
	'node' => 'Узел',
	'cable' => 'Кабель',
	'client' => 'Клиент',
	'reserv' => 'Запас кабеля'
);
// типы модулей для свичей (используется при работе в картах)
$SFP=array(
	'unknown' => 'неизвестный',
	'1310nm' => '1310nm',
	'1550nm' => '1550nm'
);
// типы портов в таблице devports
$porttype=array(
	'unknown' => 'не определен',
	'cuper' => 'RJ45',
	'fiber' => 'Волокно',
	'coupler' => 'Каплер',
	'access' => 'Access',
	'trunk' => 'Trunk',
	'wifi' => 'Wi-Fi'
);

$config = array(
/*	'owns' => array( // поле source в таблице users при нескольких владельцах для учёта денег
		'busines' => 'Пигин А.Л.',
		'netline' => 'Рога и Копыта',
	),*/
	'town'=>'г.Хогвардс', // используется при печати нарядов
	'pay'=>array(
		'service'=>array(
			'internet'=>'интернет',
			'static-ip'=>'статический IP',
			'iptv'=>'ipTV',
		),
	),
	'log'=>array(
		'tables'=>array( // для функции dblog
			'claimperform'=>'задание',
			'claims'=>'заявление',
			'devices'=>'устройство',
			'devports'=>'порт',
			'devprofiles'=>'профиль',
			'docdata'=>'',
			'documents'=>'документ',
			'employers'=>'служащего',
			'entrances'=>'подъезд',
			'homes'=>'дом',
			'leases'=>'аренду',
			'map'=>'объект',
			'news'=>'новость',
			'operators'=>'оператора',
			'orders'=>'пл.ведомость',
			'packets'=>'тарифный план',
			'pay'=>'платеж',
			'povod'=>'статью платежа',
			'prices'=>'прайс',
			'radusergroup'=>'группу',
			'radgroupcheck'=>'проверяемое значение',
			'radgroupreply'=>'отсылаемое значение',
			'rayon'=>'район',
			'rayon_packet'=>'пакет по району',
			'users'=>'клиента',
			'workorders'=>'наряд',
			'workpeople'=>'исполнителя',
			'nas'=>'сервер доступа',
			'kassa'=>'офис',
		),
	),
	'wo'=>array( // настройки нарядов
		'type'=>array(
			'decree'=>'наряд-распоряжение',
			'permit'=>'наряд-допуск'
		),
		'worktype'=>array(
			'elevation'=>array(
				'name'=>'работы на высоте',
				'before'=>array(
					'осмотр поясов, касок',
					'осмотр лестницы'
				),
				'during'=>array(
					'осмотр оптического кабеля, муфт',
					'монтаж и демонтаж оптического кабеля'
				)
			),
			'sump'=>array(
				'name'=>'работы в колодцах, коллекторах, венткамерах',
				'before'=>array(
					'осмотр поясов, касок',
					'осмотр лестницы'
				),
				'during'=>array(
					'осмотр оптического кабеля, муфт',
					'монтаж и демонтаж оптического кабеля'
				),
			)
		),
		'seat'=>array( // должности
			'manager'=>'руководитель ср.звена',
			'monger'=>'монтажник',
			'operator'=>'оператор',
			'admin'=>'сисадмин',
		),
		'work_begin'=>'08:30', 	// начало рабочего дня
		'min_wo_exec'=>900, 	// минимальное время для выполнения задания
		'max_wo_exec'=>7200, 	// максимальное время для выполнения задания
		'min_pause'=>300		// интервал между заданиями
	),
	'fixed' => array( // варианты снятия фиксированных сумм для разных тарифов
		'day_exist_connect'	=>  1,
		'day'				=>  7,
		'month'				=> 10
	),
	'service' => array(
		'ip_on_month' => 60.00, // сумма за статический IP за месяц
	),
	'db' => array(
		'username' => 'sqladm',
		'password'=>'IL2CTkuLSDFpQ',
		'server'=>'localhost',
		'db'=>'radius', 
		'charset'=>'utf8' 
	),
	'db_cards' => array(
		'username' => 'cards',
		'password'=>'fc1SIx72wlyjv',
		'server'=>'localhost',
		'db'=>'cards', 
		'charset'=>'utf8' 
	),
	'db_jabber' => array(
		'username' => 'ejabberd',
		'password'=>'',
		'server'=>'127.0.0.1', 
		'db'=>'ejabberd', 
		'charset'=>'utf8' 
	),
	'rra_url'=>'https://domain.net/nagios/no3state.php',
	'min_sum_sms'=>40, // минимальная сумма платежа для отсылки СМС
	'fading' => array( // таблица затуханий для GPON
		'sfp' => 4.0, // уровень начального сигнала по умолчанию
		'welding' => -0.05, // затухание при сварке
		'coupler' => -0.5, // затухание на каплере
		'cable' => -0.36, // затухание на 1км кабеля
		'data' => array( // затухание для делителей и сплиттеров
			'1x2' =>	array('fade'=>-4.3,	'div'=>'50/50',	'1310nm'=>'-3.17/-3.19',	'1550nm'=>'-3.12/-3.17'),
			'1x3' =>	array('fade'=>-6.2,	'div'=>'45/55',	'1310nm'=>'-3.73/-2.71',	'1550nm'=>'-3.73/-2.72'),
			'1x4' =>	array('fade'=>-7.4,	'div'=>'40/60',	'1310nm'=>'-4.01/-2.34',	'1550nm'=>'-3.92/-2.32'),
			'1x6' =>	array('fade'=>-9.5,	'div'=>'35/65',	'1310nm'=>'-4.56/-1.93',	'1550nm'=>'-4.69/-1.96'),
			'1x8' =>	array('fade'=>-10.7,	'div'=>'30/70',	'1310nm'=>'-5.39/-1.56',	'1550nm'=>'-5.53/-1.57'),
			'1x12' =>	array('fade'=>-12.5,	'div'=>'25/75',	'1310nm'=>'-6.29/-1.42',	'1550nm'=>'-6.28/-1.28'),
			'1x16' =>	array('fade'=>-13.9,	'div'=>'20/80',	'1310nm'=>'-7.11/-1.06',	'1550nm'=>'-7.21/-1.06'),
			'1x24' =>	array('fade'=>-16.0,	'div'=>'15/85',	'1310nm'=>'-8.16/-0.76',	'1550nm'=>'-8.17/-0.82'),
			'1x32' =>	array('fade'=>-17.2,	'div'=>'10/90',	'1310nm'=>'-10.08/-0.49',	'1550nm'=>'-10.21/-0.60'),
			'1x64' =>	array('fade'=>-21.5,	'div'=>'5/95',	'1310nm'=>'-13.70/-0,32',	'1550nm'=>'-12.83/-0.35'),
			'1x128' =>	array('fade'=>-25.5,	'div'=>false,	'1310nm'=>false,	'1550nm'=>false)
		),
		'numstyle'=>array(-20.0, -22.0, -24.0, -26.0, -28.0), // границы изменения цвета по уровню сигнала для клиентов на карте
		'clamount'=>array(50, 55, 60, 62, 64)
	),
	'map'=>array(
		'max_entrances'=>10, // максимальное кол-во подъездов в доме
		'max_apartments'=>400, // максимальное кол-во квартир в доме
		'client_cable_cores'=>2, // при создании клиента в картах кол-во жил в кабеле на клиента
		'sfp'=>array(
			'unknown' => '',
			'1310nm'=>'1310nm',
			'1550nm'=>'1550nm'
		),
		'modules'=>array( // модули с уровнями сигналов для GPON
			'unknown' => '',
			'EPON-C+' => '4.0',
			'EPON-C++' => '6.5'
		),
		'cabletypes'=>array( // типы укладки кабелей (карты)
			'ground'=>'подземный',
			'air'=>'воздушный',
			'private'=>'частный',
		),
		'cablecolors'=>array( // цвета для типов кабелей (карты)
			'ground'=>'#444',
			'air'=>'#66f',
			'private'=>'#4b4',
		),
		'clienttypes'=>array(
			'ftth'=>'FTTH',
			'pon'=>'PON',
			'wifi'=>'Wi-Fi'
		),
		'clientdevtypes'=>array(
			'pon'=>'onu',
			'ftth'=>'mconverter',
			'wifi'=>'wifi'
		),
		'typewifi'=>array(
			'ap'=>'Точка доступа',
			'station'=>'клиентская станция',
			'bridge'=>'мост',
		),
		'default_position'=>'48.02612,38.77225,12', // широта/долгота для начальной позиции при открытии карты
		'default_client_type'=>'pon'
	),
	'sms'=>array( // настройки для различных провайдеров (отправка СМС)
		'sms_day_limit'=>2000,
		'provider'=>'dummy',
		'providers'=>array(
			'dummy'=>array(
				'prefix' => 'ТЕСТ ОТСЫЛКИ SMS',
				'log' => true,
			),
			'mirage'=>array(
				'counter' => '392',
				'sender' => 'net-Line',
				'server' => '192.168.0.1',
				'port' => '5038',
				'login' => 'snsender',
				'password' => 'ae464ewhr3yd',
				'dongle' => array(
					'MTS5' => 1000,
					'MTS6' => 294,
					'MTS7' => 196,
					'MTS8' => 98
				)
			),
			'turbosms'=>array(
				'sender' => 'net-Line',
				'server' => 'http://turbosms.in.ua/api/wsdl.html',
				'login' => 'met_line',
				'password' => 'asdqwe1234'
			),
			'bsgroup'=>array(
				'login' => 'netline_sn',
				'password' => 'netasdsn',
				'server' => '192.168.0.1',
				'sender' => 'Net-Line',
				'port' => '2775'
			),
			'smstosrv'=>array(
				'server' => 'http://192.168.0.1/en/dosend.php',
				'USERNAME' => 'snsender',
				'PASSWORD' => 'zxcasdqwe1234',
				'smsprovider' => 1,
				'method' => 2
			),
			'star'=>array(
				'server' => 'http://192.168.1.150/default/en_US/send.html',
				'attempt' => 100,
				'login' => 'admin',
				'password' => 'admin',
				'linelimit' => array(
					'3' => 148,
					'4' => 148
				)
			),
			'phoenix'=>array(
				'host'=>"10.128.1.50",
				'port'=>"13000",
				'portAPI'=>"7890",
				'login'=>"",
				'password'=>"",
				'token'=>"",
				'attempt' => 3
			)
		),
		'phone_admins'=>array(
			'0951234567',
			'0661234567'
		),
		'mobile_operators'=>array(
			'071'=>'Оникс',
			'072'=>'Переком',
			'050|095|099'=>'МТС',
		),
		'pattern'=>array( // шаблоны SMS
			'expired'=>"Ваш пакет ':NAME:' истекает :EXPIRED: 24:00. Счет No :CONTRACT:",
			'pay'=>"Ваш лиц.счет :CONTRACT: пополнен на :SUMMA: :VALUTE:. Ваш баланс: :DEPOSIT: :BASEVALUTE:.",
			'new_claim'=>"Ваша заявка N :UNIQUE_ID: на :TYPE: принята :DATE:",
			'new_job' => "Ваша заявка N :CID: на :TYPE: запланирована на :DATE: на :TO: пол. дня",
			'move_job' => "Ваша заявка N :CID: на :TYPE: перенесена на :DATE: на :TO: пол. дня",
			'cancel_job' => "Ваша заявка N :UNIQUE_ID: на :TYPE: отменена",
			'end_job' => "Выполнение вашей заявки N :UNIQUE_ID: на :TYPE: прекращено",
		),
		'phone_filter'=>'Феникс',
	),
	'email'=>array(
		'subject'=>array( // шаблон заголовка письма
			'expired'=>"Напоминание об оплате ".COMPANY_NAME." (".COMPANY_UNIT.")",
			'pay'=>"Пополнение лицевого счёта ".COMPANY_NAME." (".COMPANY_UNIT.")",
			'new_claim'=>"Ваше заявление на :TYPE: принято",
			'new_job' => "Ваша заявка N :CID: на :TYPE: принята :DATE:",
			'move_job' => "Ваша заявка N :CID: на :TYPE: перенесена на :DATE:",
			'cancel_job' => "Ваша заявка N :UNIQUE_ID: на :TYPE: отменена",
			'end_job' => "Выполнение вашей заявки N :CID: на :TYPE: прекращено",
		),
		'pattern'=>array( // шаблон содержимого письма
			'expired'=>"\n\nУв. :FIO:\n\n".
				"Компания ".COMPANY_NAME." Напоминает что ваш пакет ':NAME:' заканчивается :EXPIRED: в 24:00\n".
				"Если Вы хотите продолжить пользоваться нашими услугами - пополните Ваш счет :CONTRACT:\n\n".
				"Стоимость пакета ':NAME:' составляет :FIXED_COST%.2f: руб.\n".
				"<IP>Стоимость аренды IP адреса (:IP:) составляет :IP_COST: руб.\n</IP>".
				"Ваш текущий баланс: :DEPOSIT%.2f: руб.\n",
			'pay'=>"\n\n\tУв. :FIO:\n\n".
				"Ваш лицевой счет :CONTRACT: пополнен на :SUMMA: :VALUTE:\n".
				"Ваш текущий баланс: :DEPOSIT: :BASEVALUTE:\n\n",
				"Получить дополнительную информацию можно \n".
				"по телефонам: ".COMPANY_PHONE,
			'new_claim'=>"\n\n\tУв. :FIO:\n\n".
				"Ваше заявления на :TYPE: принято\n".
				"и зарегистрировано под N :UNIQUE_ID: от :DATE:\n\n".
				"Получить дополнительную информацию можно \n".
				"по телефонам: ".COMPANY_PHONE,
			'new_job' => "\tУв. :FIO:\n\n".
				"Ваше заявления N :CID: на :TYPE: от :CLAIMTIME:\n".
				"поставлено в план на :DATE: на :TO:ю половину дня\n".
				"номер наряда :WOID:\n\n".
				"Получить дополнительную информацию можно \n".
				"по телефонам: ".COMPANY_PHONE,
			'move_job' => "\tУв. :FIO:\n\n".
				"Дата выполнения Вашего заявления N :CID: на :TYPE: от :CLAIMTIME:\n".
				"перенесена на :DATE: на :TO:ю половину дня.\n".
				"номер наряда :WOID:\n\n".
				"Получить дополнительную информацию можно \n".
				"по телефонам: ".COMPANY_PHONE,
			'cancel_job' => "\tУв. :FIO:\n\n".
				"Выполнение Вашего заявления N :UNIQUE_ID: на :TYPE: от :CLAIMTIME:\n".
				"Было прекращено по причине: :PERFORM_NOTE:\n\n".
				"Получить дополнительную информацию можно \n".
				"по телефонам: ".COMPANY_PHONE,
			'end_job' => "\tУв. :FIO:\n\n".
				"Ваше заявление N :UNIQUE_ID: на :TYPE: от :CLAIMTIME:\n".
				"помечено как :STATUS:\n".
				"номер наряда :WOID:\n\n".
				"Получить дополнительную информацию можно \n".
				"по телефонам: ".COMPANY_PHONE,
		)
	),
	'claim_types'=>$claim_types,
	'tos'=>$tosname,
	'direction'=>$dirtraf,
	'op_status'=>$op_status,
	'authorize'=>array( // используется в логотипе для личного кабинета
		'company'=>'<div class="company"><span>net</span>|<span>line</span></div>',
		'picture'=>'<img id="logo" src="pic/logo.png" style="width:160px;height:160px">'
	),
	'fixed' =>array(
		'0' => array(
			'title'=>'Не снимается',
			'property'=>'',
			'active'=>true
		),
		'1' => array(
			'title'=>'Раз в сутки, если было подключение',
			'property'=>'Cron',
			'active'=>true
		),
		'2' => array(
			'title'=>'за каждые сутки, в т.ч. и за предыдущие',
			'property'=>'',
			'active'=>false
		),
		'3' => array(
			'title'=>'При каждом подключении',
			'property'=>'',
			'active'=>false
		),
		'5' => array(
			'title'=>'Раз в месяц, если было подключение',
			'property'=>'',
			'active'=>false
		),
		'7' => array(
			'title'=>'Каждый День',
			'property'=>'Cron',
			'active'=>true
		),
		'8' => array(
			'title'=>'Каждый Месяц 1-го числа',
			'property'=>'Cron',
			'active'=>true
		),
		'9' => array(
			'title'=>'Каждый месяц обязан потратить',
			'note'=>'Cron',
			'active'=>true
		),
		'10'=> array(
			'title'=>'Каждый раз в момент начала пакета',
			'note'=>'Cron',
			'active'=>true
		)
	),
	'menu'=>array( // меню для личного кабинета
		'money'=>array(
			'label'=>'Пополнить СЧЕТ',
			'module'=>'menu',
			'enable'=>true
		),
		'tarif'=>array(
			'label'=>'Информация о ТАРИФЕ',
			'module'=>'menu',
			'enable'=>true
		),
		'claim'=>array(
			'label'=>'Подать ЗАЯВЛЕНИЕ',
			'module'=>'menu',
			'enable'=>true
		),
		'stat'=>array(
			'label'=>'Посмотреть ПОДКЛЮЧЕНИЯ',
			'module'=>'menu',
			'enable'=>true
		),
		'paystat'=>array(
			'label'=>'Посмотреть ПЛАТЕЖИ',
			'module'=>'menu',
			'enable'=>true
		),
		'documents'=>array(
			'label'=>'Документы',
			'module'=>'menu',
			'enable'=>true
		),
		'smsstat'=>array(
			'label'=>'Посмотреть SMS',
			'module'=>'menu',
			'enable'=>true
		),
		'password'=>array(
			'label'=>'Сменить ПАРОЛЬ',
			'module'=>'menu',
			'enable'=>true
		),
		'changepacket'=>array(
			'label'=>'Перейти на ДРУГОЙ пакет',
			'module'=>'menu',
			'enable'=>false
		),
		'present'=>array(
			'label'=>'Поделиться с другом',
			'module'=>'menu',
			'enable'=>true,
			'summ'=>array(10,20,30,50,100,150,300)
		),
		'macaddress'=>array(
			'label'=>'Аппаратный адрес (МАК)',
			'module'=>'menu',
			'enable'=>false
		),
		'newuser'=>array(
			'label'=>'Зарегистрировать НОВОГО пользователя',
			'module'=>'menu',
			'enable'=>false
		),
		'newphone'=>array(
			'label'=>'Новый телефон',
			'module'=>'menu',
			'enable'=>true
		),
		'newmail'=>array(
			'label'=>'Электронная почта',
			'module'=>'menu',
			'enable'=>true
		),
	),
);

$doctypes = array( // настройка получения из разных таблиц данных для создания документа по пользователю (соглашение,гарантия...)
	'contract' => array(
		'label' => 'Пользовательский договор',
		'fields' => array(
			'fio' => 'users',
			'psp' => 'users',
			'pspissue' => '',
			'inn' => '',
			'address' => 'users',
			'password' => 'users',
			'tarif' => 'packets.name',
			'contract' => 'users',
			'login' => 'users.user',
			'password' => 'users'
		),
		'keys' => array(
			'users' => 'val', // поле val должно быть в таблице documents и содержать id из таблицы в названии первого ключа
			'packets' => 'users.pid' // выборка записи для packets из поля предыдущей выборки в таб. users
		)
	),
	'notice' => array(
		'label' => 'Уведомление',
		'fields' => array(
			'fio' => 'claims',
			'address' => 'claims',
			'reply' => '',
			'claim'=>'claims.unique_id'
		),
		'keys' => array(
			'claims' => 'val',
		)
	),
	'agreement' => array(
		'label' => 'Соглашение',
		'fields' => array(
			'fio' => 'users',
			'psp' => 'users',
			'pspissue' => '',
			'address' => 'users',
			'contract' => 'users',
		),
		'keys' => array(
			'users' => 'val',
		)
	),
	'oferta' => array(
		'label' => 'Договор оферты',
		'fields' => array(
			'fio' => 'users',
			'psp' => 'users',
			'address' => 'users',
			'contract' => 'users',
		),
		'keys' => array(
			'users' => 'val',
		)
	),
	'warranty' => array(
		'label'=>'Гарантийное обязательство (ONU)',
		'fields'=>array(
			'document'=>'.id',
			'address'=>'users',
			'device'=>'devices.type',
			'model'=>'',
			'expired'=>'warranty_6month',
			'code'=>'devices.macaddress',
		),
		'keys' => array(
			'users' => 'val',
			'map.name' => 'users.user',
			'devices.node1' => 'map.id'
		),
		'conditions' => array(
			'map' => 'type="client"',
			'devices' => 'type="onu"'
		),
		'input' => array( // проверка правильности ввода оператора
			'code'=>'/^[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}$/i',
		)
	),
	'warranty1' => array(
		'label'=>'Гарантийное обязательство (роутер)',
		'fields'=>array(
			'document'=>'.id',
			'address'=>'users',
			'device'=>'',
			'model'=>'',
			'expired'=>'warranty_14days',
			'code'=>'devices.macaddress',
		),
		'keys' => array(
			'users' => 'val',
			'map.name' => 'users.user',
			'devices.node1' => 'map.id'
		),
		'conditions' => array(
			'map' => 'type="client"',
		)
	),
	'warranty2' => array(
		'label'=>'Гарантийное обязательство (медиаконвертер)',
		'fields'=>array(
			'document'=>'.id',
			'address'=>'users',
			'device'=>'',
			'model'=>'',
			'expired'=>'warranty_6month',
			'code'=>'',
		),
		'keys' => array(
			'users' => 'val',
		),
	),
	'warranty3' => array(
		'label'=>'Гарантийное обязательство (ipTV приставка)',
		'fields'=>array(
			'document'=>'.id',
			'address'=>'users',
			'device'=>'приставка ipTV',
			'model'=>'',
			'expired'=>'warranty_14days',
			'code'=>'',
		),
		'keys' => array(
			'users' => 'val',
		),
	)
);

$cache = array();

setlocale(LC_ALL, 'ru_RU.UTF-8');
setlocale(LC_NUMERIC, 'C');
setlocale(LC_TIME,"ru_RU.UTF-8");
?>
