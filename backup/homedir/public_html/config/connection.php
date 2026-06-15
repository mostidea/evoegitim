<?php
date_default_timezone_set('Europe/Istanbul'); // İstanbul saat dilimini ayarla
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

try {
    $db = new PDO("mysql:host=localhost;dbname=evoegiti_tech;charset=utf8mb4", "evoegiti_tech", "H~yZjT^w])t5", [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo "Veritabanı bağlantı hatası: " . $e->getMessage();
}

function sanitizeMessage($text) {
    $badWords = [
        'sik', 'sikerim', 'sikti', 'siktir', 'sikeyim', 'sikim', 'sikin', 'sikiyor',
        'amcık', 'amına', 'amını', 'amınım', 'amk', 'ananı', 'anan', 'orospu', 'piç',
        'yarrak', 'yarak', 'göt', 'kahpe', 'puşt', 'ibne', 'haysiyetsiz', 'pezevenk',
        'mk', 'aq', 'a.q', 'm.k', 'oç', '0ç', 'amq', 'a.m.q', 'g.t', 's.k', 's.k.m', 's.k.t.r',
        'instagram', 'twitter', 'tiktok', 'snapchat', 'telegram', 't.me/', 'linkedin',
        'whatsapp', 'watsapp', 'wp', 'wpden', 'wp den', 'numaram', 'numara at', 'tel no',
        'telefon numaram', 'mail', 'e-posta', 'gmail', 'hotmail', 'outlook', 'adresim',
        'ara beni', 'bana ulaş', 'mesaj at', 'sms gönder', 'ulaşmak istersen', 'dm at', 'dmden yaz',
        'link at', 'siteye gir', 'kanalıma abone ol', '.com', '.net', '.org', 'http://', 'https://', 'www.','instagramdan',"instagram'dan","instadan","insta"
    ];

    foreach ($badWords as $word) {
        $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
        $text = preg_replace($pattern, str_repeat('*', mb_strlen($word)), $text);
    }

    // E-posta adresleri
    $text = preg_replace('/[a-z0-9_\.\-]+@[a-z0-9\-]+\.[a-z]{2,6}/i', '***@***.***', $text);

    // Telefon numaraları
    $text = preg_replace('/(\+?90|0)?\s*\d{3}\s*\d{3}\s*\d{2}\s*\d{2}/', '*** *** ** **', $text);
    $text = preg_replace('/\b\d{10,11}\b/', '**********', $text);

    return $text;
}




function checkStudentInfo(PDO $db, string $email, int $studentId): void {
    // Veliye ait öğrenci e-maillerini çek
    $stmt = $db->prepare("SELECT user_email FROM invite_parent WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $userEmails = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($userEmails)) {
        // Hiçbir öğrenci atanmadıysa direkt geri gönder
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Bu emaillerden birine sahip öğrenci ID'si var mı?
    $placeholders = str_repeat('?,', count($userEmails) - 1) . '?';
    $check = $db->prepare("SELECT id FROM users WHERE id = ? AND email IN ($placeholders)");
    $params = array_merge([$studentId], $userEmails);
    $check->execute($params);
    $valid = $check->fetchColumn();

    if (!$valid) {
        // Bu veliye ait değil → geri gönder
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

if (isset($_GET['uid']) && strpos($_SERVER['REQUEST_URI'], 'vbs') !== false) {
    checkStudentInfo($db, $_SESSION['email'], (int)$_GET['uid']);
}


$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page != 'index.php') {
    $class = 'inner-header';
} else {
    $class = '';
}

function turkcetarih($f, $zt = 'now')
{

    if($zt==""){
        return "";
        exit();
    }
    $z = date("$f", strtotime($zt));
    $donustur = array(
        'Monday'    => 'Pazartesi',
        'Tuesday'   => 'Salı',
        'Wednesday' => 'Çarşamba',
        'Thursday'  => 'Perşembe',
        'Friday'    => 'Cuma',
        'Saturday'  => 'Cumartesi',
        'Sunday'    => 'Pazar',
        'January'   => 'Ocak',
        'February'  => 'Şubat',
        'March'     => 'Mart',
        'April'     => 'Nisan',
        'May'       => 'Mayıs',
        'June'      => 'Haziran',
        'July'      => 'Temmuz',
        'August'    => 'Ağustos',
        'September' => 'Eylül',
        'October'   => 'Ekim',
        'November'  => 'Kasım',
        'December'  => 'Aralık',
        'Mon'       => 'Pts',
        'Tue'       => 'Sal',
        'Wed'       => 'Çar',
        'Thu'       => 'Per',
        'Fri'       => 'Cum',
        'Sat'       => 'Cts',
        'Sun'       => 'Paz',
        'Jan'       => 'Oca',
        'Feb'       => 'Şub',
        'Mar'       => 'Mar',
        'Apr'       => 'Nis',
        'Jun'       => 'Haz',
        'Jul'       => 'Tem',
        'Aug'       => 'Ağu',
        'Sep'       => 'Eyl',
        'Oct'       => 'Eki',
        'Nov'       => 'Kas',
        'Dec'       => 'Ara',
    );
    foreach ($donustur as $en => $tr) {
        $z = str_replace($en, $tr, $z);
    }
    if (strpos($z, 'Mayıs') !== false && strpos($f, 'F') === false) $z = str_replace('Mayıs', 'May', $z);
    return $z;
}

function sendSms($message,$receiver){

    $receiver = array_unique($receiver);
    
$params = [
    'api_id' => '0fb1f6800a9e3d21ed53cd49',
    'api_key' => '79769c6a0d5fedacdfe7e2c2',
    'sender' => 'EVOEGITIM',
    'message_type' => 'normal',
    'message' => $message,
    'message_content_type' => 'bilgi', // ticari smsler için 'ticari'
    'phones' => $receiver
];

$curl = curl_init();
$curl_options = [
    CURLOPT_URL => 'https://api.vatansms.net/api/v1/1toN',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_POSTFIELDS => json_encode($params),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ]
];

curl_setopt_array($curl, $curl_options);

$response = curl_exec($curl);
curl_close($curl);
}

function checkSession(){
if (isset($_SESSION["user_id"])) {
    header("location: dashboard.php");
}

}

function checkUnSession(){
    if (!isset($_SESSION["user_id"])) {
        header("location: index.php");
    }

}

function getRateClass($rate) {
    switch ($rate) {
        case "Çok Kötü":
            return "text-danger bg-danger-100";
        case "Kötü":
            return "text-warning bg-warning-100";
        case "İyi":
        case "Çok İyi":
            return "text-success bg-success-100";

            case "Evet":
                return "text-success bg-success-100";

                case "Hayır":
                    return "text-danger bg-danger-100";
        default:
            return "text-primary-600 bg-primary-100"; // Eğer farklı bir değer gelirse varsayılan bir stil
    }
}


function getStudentInfo($db, $email) {
    // İlk önce invite_parent tablosundan bilgileri alıyoruz
    $getMyStudent = $db->prepare("SELECT * FROM invite_parent WHERE email = :email");
    $getMyStudent->bindParam(":email", $email);
    $getMyStudent->execute();
    $student = $getMyStudent->fetch(PDO::FETCH_ASSOC);

    // Eğer bir sonuç dönerse users tablosundan bilgileri alıyoruz
    if ($student) {
        $studentId = $db->prepare("SELECT * FROM users WHERE email = :user_email");
        $studentId->bindParam(":user_email", $student["user_email"]);
        $studentId->execute();
        $studentInfo = $studentId->fetch(PDO::FETCH_ASSOC);

        // Hem invite_parent hem de users tablosundaki bilgileri birleştirip döndürüyoruz
        return [
            'invite_parent_info' => $student,
            'user_info' => $studentInfo
        ];
    }

    // Eğer sonuç bulunmazsa false döndürüyoruz
    return false;
}


function getStudentInfoMultiple($db, $email) {
    $getMyStudents = $db->prepare("SELECT * FROM invite_parent WHERE email = :email");
    $getMyStudents->bindParam(":email", $email);
    $getMyStudents->execute();
    $students = $getMyStudents->fetchAll(PDO::FETCH_ASSOC);

    $results = [];

    foreach ($students as $student) {
        $studentId = $db->prepare("SELECT id, fullname FROM users WHERE email = :user_email");
        $studentId->bindParam(":user_email", $student["user_email"]);
        $studentId->execute();
        $studentInfo = $studentId->fetch(PDO::FETCH_ASSOC);

        if ($studentInfo) {
            $results[] = [
                'invite_parent_info' => $student,
                'user_info' => $studentInfo
            ];
        }
    }

    return $results;
}



function getParentInfo($db, $email) {


    $studentId = $db->prepare("SELECT * FROM invite_parent WHERE user_email = :user_email");
    $studentId->bindParam(":user_email", $email);
    $studentId->execute();
    $veliInfo = $studentId->fetch(PDO::FETCH_ASSOC);

    // Eğer invite_parent tablosunda bir sonuç varsa parent tablosundan bilgi alıyoruz
    $veliId = $db->prepare("SELECT * FROM parent WHERE email = :user_email");
    $veliId->bindParam(":user_email", $veliInfo["email"]);
    $veliId->execute();
    $veliid = $veliId->fetch(PDO::FETCH_ASSOC);

 

    // Eğer her şey yolundaysa veli id'yi döndürüyoruz
    return @$veliid["id"];
}

function getParentEmail($db, $email) {


    $studentId = $db->prepare("SELECT * FROM invite_parent WHERE user_email = :user_email");
    $studentId->bindParam(":user_email", $email);
    $studentId->execute();
    $veliInfo = $studentId->fetch(PDO::FETCH_ASSOC);



    // Eğer invite_parent tablosunda bir sonuç varsa parent tablosundan bilgi alıyoruz
    $veliId = $db->prepare("SELECT * FROM parent WHERE email = :user_email");
    $veliId->bindParam(":user_email", $veliInfo["email"]);
    $veliId->execute();
    $veliid = $veliId->fetch(PDO::FETCH_ASSOC);


    // Eğer her şey yolundaysa veli id'yi döndürüyoruz
    return @$veliid["email"];
}


function getStudentEmail($db, $email) {


    $studentId = $db->prepare("SELECT * FROM invite_parent WHERE email = :user_email");
    $studentId->bindParam(":user_email", $email);
    $studentId->execute();
    $veliInfo = $studentId->fetch(PDO::FETCH_ASSOC);

    // Eğer her şey yolundaysa veli id'yi döndürüyoruz
    return @$veliInfo["user_email"];
}

function getStudentId($db, $email) {


    $studentId = $db->prepare("SELECT * FROM invite_parent WHERE email = :user_email");
    $studentId->bindParam(":user_email", $email);
    $studentId->execute();
    $veliInfo = $studentId->fetch(PDO::FETCH_ASSOC);
  
    // Eğer her şey yolundaysa veli id'yi döndürüyoruz
    if($veliInfo){

        $studentData = $db->prepare("SELECT * FROM users WHERE email = :user_email");
        $studentData->bindParam(":user_email", $veliInfo["user_email"]);
        $studentData->execute();
        $si = $studentData->fetch(PDO::FETCH_ASSOC);
 
        return @$si["id"];
    } else {
        return 0;
    }
   
}

function getStudentIdParentId($db, $email) {


    $studentId = $db->prepare("SELECT * FROM parent WHERE id = :user_email");
    $studentId->bindParam(":user_email", $email);
    $studentId->execute();
    $veliInfo = $studentId->fetch(PDO::FETCH_ASSOC);
  
    // Eğer her şey yolundaysa veli id'yi döndürüyoruz
    if($veliInfo){

        $studentData = $db->prepare("SELECT * FROM invite_parent WHERE email = :user_email");
        $studentData->bindParam(":user_email", $veliInfo["email"]);
        $studentData->execute();
        $si = $studentData->fetch(PDO::FETCH_ASSOC);
 
        return @$si["user_email"];
    } else {
        return 0;
    }
   
}


function getLevel($level){

    switch ($level) {
        case 1:
            return "İlk Öğretim";
        case 2:
            return "Ortaokul";
        case 3:
            return "Lise";

            case 4:
                return "Yüksek Öğretim";

        default:
            return "Lütfen Müşteri Hizmetlerine Ulaşın"; // Eğer farklı bir değer gelirse varsayılan bir stil
    }
    
}

function getCategory($level){

    switch ($level) {
        case 1:
            return "Ana Dersler";
        case 2:
            return "Rehberlik";
        default:
            return "Lütfen Müşteri Hizmetlerine Ulaşın"; // Eğer farklı bir değer gelirse varsayılan bir stil
    }
    
}

function getGender($gender){

    switch ($gender) {
        case 1:
            return "Erkek";
        case 2:
            return "Kadın";
        case 0:
            return "Fark Etmez";

        default:
            return "Lütfen Müşteri Hizmetlerine Ulaşın"; // Eğer farklı bir değer gelirse varsayılan bir stil
    }
    
}

function randomTeacherText($val){
    $messages = [
        "Hayal gücü, bilgiden daha önemlidir.",
"Yapabileceğini düşünen yapabilir, yapamayacağını düşünen yapamaz. Bu değişmez ve tartışılmaz bir kuraldır.",
"En yükseğe erişmek isterseniz, en aşağıdan başlayın.",
"Matematikte zekâdan önce sabır gelir.",
"Kararsızlık ve gecikme başarısızlığın iki önemli sebebidir.",
"Kötü bir döneme girdiğinde ve her şey sana karşı gibi göründüğünde, bir dakika bile dayanamayacakmışsın gibi geldiğinde, sakın pes etme! Çünkü işte orası, gidişatın değişeceği yer ve zamandır.",
"Akıllı kişiler kararlarını aceleyle değil, düşünüp taşınarak verirler.",
"Kitap okumayan bir kimsenin, okumasını bilmeyene karşı bir üstünlüğü yoktur.",
"Cesaretini asla kaybetme; zorluklar içerisinde kilidi açabilecek en son anahtar çoğu zaman odur.",
"Büyük şeyler başarmak için sadece harekete geçmemiz değil, ama aynı zamanda hayal etmemiz; sadece plan yapmakla kalmayıp aynı zamanda inanmamız gerekir.",
"Büyük başarılar, ancak başarabileceklerine inanan insanlar tarafından elde edilmiştir.",
"Eğer kayda değer bir buluş yaptıysam, bunu herhangi bir yetenekten çok sabıra borçluyum.",
"Diğerleri uyurken siz çabalayın; diğerleri kaytarırken siz çalışın; diğerleri eğlenirken siz hazırlanın; ve diğerleri dilekte bulunurken siz hayal edin.",
"Hiç kimse, az şey yapabildiği için hiçbir şey yapmayan bir kişiden daha büyük bir hata yapamaz.",
"Bir problemi çözmeye başladığınızda yılmadan devam edin. İlk çözümler çok karmaşıktır, ancak devam ederseniz çoğunlukla basit çözümlere ulaşırsınız.",
"Başarılı girişimcilerle başarısız girişimcileri birbirinden ayıran şeylerin yarısının sadece sabır olduğunu gördüm.",
"Hayatım boyunca hata üstüne hata, hata ve hata yaptım, bu yüzden başardım.",
"Ben hiç başarısız olmadım. Sadece sonuç alamadığım 10 bin yeni şey öğrendim.",
"İstediğiniz an baştan başlayabilirsiniz. Başarısızlık denen şey, düşmek değil, yerde kalmaktır.",
"Başarısızlık her zaman hata demek değildir; yapabileceğiniz en iyi şey olabilir. Asıl hata denemekten vazgeçmektir.",
"İmkânsızlık, ancak akılsızların sözlüğünde bulunan bir kelimedir.",
"Aptallığın en büyük kanıtı, aynı şeyi defalarca yapıp farklı bir sonuç almayı ummaktır.",
"Bir hatayı iki defa tekrar etmeyen en mükemmel insandır.",
"Azim, paha biçilmezdir: Çok zeki olduğumdan değil, sorunlarla uğraşmaktan vazgeçmediğimden başarıyorum.",
"Siz erteleyebilirsiniz ama zaman ertelemez.",
"Yaradan size her şeye dayanabilecek bir vücut verdi. İkna etmeniz gereken zihninizdir.",
"Sıkı bir çalışmanın yerini hiçbir şey alamaz. Deha %1 ilham, %99 çalışmadır.",
"Sonuna kadar çaba gösterin ve asla şüpheye düşmeyin. Hiçbir şey o kadar zor değildir, araştırın yeter.",
"Bizler, hata yapmayan insanlara değil, sadece pes etmeyen insanlara dönüşmeliyiz.",
"Akıllı bir insan, ne gerektiğinden fazla üzülür, ne de gerektiğinden fazla ümitlenir.",
"İnsanlar başarısızlığa uğramazlar, denemekten vazgeçerler.",
"Hiç kimse başarı merdivenine elleri cebinde tırmanmamıştır.",
"Bugün çıktığınız her basamak, yarınki hayatınızın temelidir.",
"Uçamıyorsan, koş; koşamıyorsan, yürü. Eğer yürüyemiyorsan, sürün; ama hareket etmeye devam et. Geleceğe ilerlemeyi sürdür.",
"Kendine güven kazanmanın en iyi yolu, başarısızlığa imkân vermeyecek kadar iyi hazırlanmaktır.",
"Şimdiki zaman onlara ait olabilir; ama gelecek, ki ben hep bunun için çalıştım, bana ait.",
"Keyif zaferde değil; asıl mücadele, girişim ve çekilen ıstıraptadır.",
"Dikkate alınması gereken adımların büyüklüğü değil, hangi yönde olduğudur.",
"Hayattaki en zor şey; geçeceğin köprülerle, yakacağın köprüleri ayırt etmektir.",
"Karar verdiğimiz anlar, geleceğimizin şekillendiği anlardır.",
"Hedefinize ulaşamayacağınıza kanaat getirdiğinizde, hedefinizi değil, hedefinize giden yolu gözden geçirmelisiniz.",
"Her şeyin en mühim noktası, başlangıcıdır.",
"Düşünmeden öğrenmek, vakit kaybetmektir.",
"Siz yolunuza bakın. Böyle yaparsanız hedefe kendiliğinden varırsınız.",
"Hayattaki en büyük zafer hiçbir zaman düşmemek değil, her düştüğünde ayağa kalkmakta yatar.",
"Bir ülkenin geleceği o ülke insanlarının göreceği eğitime bağlıdır.",
"Hayattaki önceliklerimizi belirlersek, bunların dışında kalanlara gülümseyerek hayır demek daha da kolaylaşır.",
"Bir şeyleri başarmanın sırrı, harekete geçmektir.",
"Er ya da geç kazanan kişi, kazanabileceğini düşünen kişidir. Başarı istenmediği yere gelmez.",
"Başarıyı, nefes almayı istediğin kadar çok istediğinde, başarılı olacaksın.",
"Başlamak için mükemmel olmak zorunda değilsin; fakat mükemmel olmak için başlamak zorundasın."
    ];

    // Eğer $val değeri 0-10 aralığındaysa, uygun mesajı döndür
    if($val >= 0 && $val <= 55) {
        return $messages[$val];
    } else {
        return "Geçersiz değer, lütfen 0 ile 10 arasında bir sayı girin.";
    }
}

function sendEmail($to, $toName, $subject, $body, $altBody = '') {
    $mail = new PHPMailer(true);

    try {
        // Sunucu ayarları
        $mail->isSMTP();
        $mail->Host = 'mail.evoegitim.com'; // SMTP sunucusu
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@evoegitim.com'; // SMTP kullanıcı adı
        $mail->Password = 'gcoN3N9GhfIR';    // SMTP şifresi
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL kullan
        $mail->Port = 465; // SSL portu

        // Gönderici bilgileri
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('noreply@evoegitim.com', 'Evo Eğitim');
        $mail->addAddress($to, $toName);

        // İçerik
        $mail->isHTML(true);
        $mail->Timeout = 10; // Zaman aşımını 10 saniye olarak ayarlayın
        $mail->SMTPDebug = 0; // Gelişmiş hata ayıklama modu

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;

        // E-posta gönderme işlemi
        $mail->send();
        return true; // Başarılı gönderim

    } catch (Exception $e) {
        // Hata mesajını loglamak veya döndürmek için
        error_log("E-posta gönderim hatası: {$mail->ErrorInfo}");
        return false;
    }
}




