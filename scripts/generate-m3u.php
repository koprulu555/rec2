<?php
require_once __DIR__ . '/vendor/autoload.php';

// API config dosyasını oku
$apiConfig = json_decode(file_get_contents(__DIR__ . '/api-config.json'), true);

if (!$apiConfig) {
    die("API config yüklenemedi!\n");
}

// Config değerlerini ayarla
$mainUrl = $apiConfig['mainUrl'];
$swKey = $apiConfig['swKey'];
$userAgent = $apiConfig['userAgent'];
$referer = $apiConfig['referer'];
$m3uUserAgent = 'googleusercontent';

echo "API Config yüklendi:\n";
echo "Main URL: $mainUrl\n";
echo "SwKey: $swKey\n";
echo "User-Agent: $userAgent\n";
echo "Referer: $referer\n";

// M3U içeriğini oluştur
$m3uContent = "#EXTM3U\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: $userAgent\r\nReferer: $referer\r\n",
        'timeout' => 30,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

// CANLI YAYINLAR
echo "Canlı yayınlar alınıyor...\n";
for ($page = 0; $page < 4; $page++) {
    $apiUrl = "$mainUrl/api/channel/by/filtres/0/0/$page/$swKey";
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        if (is_array($data)) {
            foreach ($data as $content) {
                if (isset($content['sources']) && is_array($content['sources'])) {
                    foreach ($content['sources'] as $source) {
                        if (($source['type'] ?? '') === 'm3u8' && isset($source['url'])) {
                            $title = $content['title'] ?? '';
                            $image = $content['image'] ?? '';
                            $categories = isset($content['categories']) ? implode(", ", array_column($content['categories'], 'title')) : '';
                            
                            $m3uContent .= "#EXTINF:-1 tvg-id=\"{$content['id']}\" tvg-name=\"$title\" tvg-logo=\"$image\" group-title=\"$categories\", $title\n";
                            $m3uContent .= "#EXTVLCOPT:http-user-agent=$m3uUserAgent\n";
                            $m3uContent .= "#EXTVLCOPT:http-referrer=$referer\n";
                            $m3uContent .= "{$source['url']}\n";
                        }
                    }
                }
            }
        }
    }
}

// FİLMLER ve DİZİLER benzer şekilde eklenecek...

// Dosyaya yaz
$outputFile = __DIR__ . '/../m3u-output/rectv-playlist.m3u';
file_put_contents($outputFile, $m3uContent);
echo "M3U dosyası oluşturuldu: $outputFile\n";
?>
