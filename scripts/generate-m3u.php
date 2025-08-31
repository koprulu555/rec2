<?php
// Config değerleri doğrudan burada
$config = [
    'mainUrl' => 'https://m.prectv55.lol',
    'swKey' => '4F5A9C3D9A86FA54EACEDDD635185/64f9535b-bd2e-4483-b234-89060b1e631c',
    'userAgent' => 'Dart/3.7 (dart:io)',
    'referer' => 'https://twitter.com/'
];

$mainUrl = $config['mainUrl'];
$swKey = $config['swKey'];
$userAgent = $config['userAgent'];
$referer = $config['referer'];
$m3uUserAgent = 'googleusercontent';

echo "M3U Oluşturucu Başlıyor...\n";
echo "API: $mainUrl\n";

// M3U içeriğini oluştur
$m3uContent = "#EXTM3U\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: $userAgent\r\nReferer: $referer\r\n",
        'timeout' => 15,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

// CANLI YAYINLAR
echo "📺 Canlı yayınlar alınıyor...\n";
$totalChannels = 0;

for ($page = 0; $page < 4; $page++) {
    $apiUrl = "$mainUrl/api/channel/by/filtres/0/0/$page/$swKey";
    echo "Sayfa $page: $apiUrl\n";
    
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        if (is_array($data)) {
            $pageChannels = 0;
            foreach ($data as $content) {
                if (isset($content['sources']) && is_array($content['sources'])) {
                    foreach ($content['sources'] as $source) {
                        if (($source['type'] ?? '') === 'm3u8' && isset($source['url'])) {
                            $pageChannels++;
                            $totalChannels++;
                            $title = $content['title'] ?? 'Başlıksız';
                            $image = $content['image'] ?? '';
                            $categories = isset($content['categories']) ? implode(", ", array_column($content['categories'], 'title')) : 'Genel';
                            
                            $m3uContent .= "#EXTINF:-1 tvg-id=\"{$content['id']}\" tvg-name=\"$title\" tvg-logo=\"$image\" group-title=\"$categories\", $title\n";
                            $m3uContent .= "#EXTVLCOPT:http-user-agent=$m3uUserAgent\n";
                            $m3uContent .= "#EXTVLCOPT:http-referrer=$referer\n";
                            $m3uContent .= "{$source['url']}\n";
                        }
                    }
                }
            }
            echo "Sayfa $page: $pageChannels kanal bulundu\n";
        }
    } else {
        echo "Sayfa $page: API erişilemedi\n";
    }
}

echo "✅ Toplam $totalChannels kanal eklendi\n";

// Dosyaya yaz
$outputDir = __DIR__ . '/../m3u-output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$outputFile = "$outputDir/rectv-playlist.m3u";
file_put_contents($outputFile, $m3uContent);
echo "🎉 M3U dosyası oluşturuldu: $outputFile\n";
echo "📊 Dosya boyutu: " . round(filesize($outputFile) / 1024, 2) . " KB\n";
?>
