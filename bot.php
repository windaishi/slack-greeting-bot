<?php
use Symfony\Component\Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotEnv = new Dotenv();
$dotEnv->load(__DIR__.'/.env');

$requestData = json_decode(file_get_contents('php://input'), true);

if ($requestData['challenge']) {
    die($requestData['challenge']);
}

if ($requestData['token'] !== $_ENV['VERIFICATION_TOKEN']) {
    die('Kein Zugriff');
}

$event = $requestData['event'];
if ($event['type'] !== 'message' || isset($event['thread_ts'])) {
    return;
}

$matchers = [
    'bye',
    'bin.*raus',
    'bin.*weg',
    'verabschiede',
    'ciao',
    'see.*ya',
    'meld.*mich.*ab',
    'tschüss',
    'servus',
    'bis.*morgen',
    'mach.*los',
    'adios',
    'mach.*gut',
    'verabschied.*mich',
];

$replies = [
    'Ade war schee!',
    'Adele!',
    'Adieu Mathieu!',
    'Adios Embryos!',
    'Adios amigos!',
    'Au reservoir statt au revoir!',
    'Auf Video sehen!',
    'Auf Wiese gehn!',
    'Auf wieder-tschüss!',
    'Auf wiedergesehen!',
    'Auf wiederhörnchen!',
    'Auf wiederklatsch!',
    'Aus die Maus!',
    'Bis Baldrian!',
    'Bis Danni!',
    'Bis Dannimannski!',
    'Bis Danzig!',
    'Bis Dennis"',
    'Bis Denver!',
    'Bis baldrian',
    'Bis dann, Hermann!',
    'Bis dann, Weihnachtsmann!',
    'Bis danny Honey!',
    'Bis denn, Sven!',
    'Bis denne, Antenne',
    'Bis speda, Peda!',
    'Bis später Attentäter',
    'Bis später, Attentäter!',
    'Bis später, Peter!',
    'Bleib sauber, so wie ich immer sein wollte',
    'Bye bye Kartoffelbrei!',
    'Bye bye juchhei!',
    'Bye bye, butterfly!',
    'Chapeau mit o!',
    'Ciao Kakao!',
    'Ciao Bello!',
    'Ciao du Pfau!',
    'Ciao for now!',
    'Ciao Miau!',
    'Ciao mit Au!',
    'Ciao Panthao!',
    'Ciaosen Banausen!',
    'Ciaosky',
    'Ende Gelände!',
    'Erst die Rechte, dann die Linke. Beide machen Winke, Winke!',
    'Geh mit Gott, aber nicht ohne Gummi!',
    'Geh mit Gott, aber Flott',
    'Gruß an den Rest vom Schützenfest sendet der Held im Erdbeerfeld!',
    'Gute Nacht, Schicht im Schacht!',
    'Gute Nacht, ab in den Schacht!',
    'Halb Acht, Schicht im Schacht!',
    'Halt die Ohren steif!',
    'Hasta la vista, baby!',
    'Hastalavista, mister!',
    'Hau Reinhardt!',
    'Hau rein, Brian!',
    'Hau rein, Hein!',
    'Hau rein, aber nicht so tief!',
    'Hau rein, du Stein!',
    'Hauste rein!',
    'Heiter weiter!',
    'Husch husch, hau ab! Geh mir nicht auf\'n Sack!',
    'Ich bedanke mich herz rechtlich!',
    'Ich verabscheue mich!',
    'Ich verschwind, wie der Furz im Wind!',
    'In a while, crocodile!',
    'Küsschen auf\'s Nüsschen!',
    'Lasst Dich nicht ansprechen und wenn, nehm Geld!',
    'Mach\'s gut Zuckerhut!',
    'Mach\'s gut, Knut!',
    'Machs gut, aber nicht zu oft',
    'Mach’s gut, aber nicht zu oft!',
    'Mach’s gut, ich machs besser!',
    'Mach’s gut, schwing den Hut!',
    'Man siebt sich!',
    'Man sieht sich! Wir ham ja Augen!',
    'Paris, Athen, auf wiederseh\'n!',
    'Piss dann ... aber nicht vor meiner Haustür!',
    'Piss dann, aber nicht vor meiner Haustür!',
    'Salut, bis morgen früh!',
    'San frantschüssko!',
    'Sayonara carbonara!',
    'Schlaf schön!',
    'Schönes Knochenende!',
    'See you later aligator!',
    'See you later alligator, in a while crocodile!',
    'See you soon Sailor Moon!',
    'Servus Fötus!',
    'Sleep very well in your Bettgestell!',
    'Tschau bella Frikadella!',
    'Tschau mit Au!',
    'Tschau, du Sau!',
    'Tschaui-haui!',
    'Tschautschesko!',
    'Tschuss!',
    'Tschö mit ö!',
    'Tschüsli müsli!',
    'Tschüsselchen mit Küsselchen auf\'s Rüsselchen!',
    'Tschüsseldorf!',
    'Tschüssen!',
    'Tschüssi mit Küssi!',
    'Tschüssikowski!',
    'Tschüssilinski!',
    'Tschüssing!',
    'Tschüssinger!',
    'Tudelu Känguru!',
    'Tüdelü!',
    'Winke winke, denn ich stinke!',
    'Wir riechen uns!',
    'Wirsing!',
    //'Ich mach mich vom acker!',
    //'Ich mach nen Schuh!',
    //'Ich mach nen Sittich!',
];


$matched = false;
foreach ($matchers as $matcher) {
    if (preg_match("/$matcher/i", $event['text'])) {
        $matched = true;
        break;
    }
}
if (!$matched) {
    return;
}


$url = 'https://slack.com/api/chat.postMessage';
$postData = [
    'channel' => $event['channel'],
    'thread_ts' => $event['ts'],
    'text' => $replies[random_int(0, count($replies) - 1)],
];

// use key 'http' even if you send the request to https://...
$options = [
    'http' => [
        'header' => "Content-type: application/json\r\nAuthorization: Bearer " . $_ENV['BOT_USER_OAUTH_ACCESS_TOKEN'],
        'method' => 'POST',
        'content' => json_encode($postData),
    ],
];
$context = stream_context_create($options);
file_get_contents($url, false, $context);

