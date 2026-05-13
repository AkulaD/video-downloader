<?php
set_time_limit(600);

if (isset($_GET['url'])) {
    $url = $_GET['url'];

    if (strpos($url, 'instagram.com') === false) {
        die("Hanya link Instagram yang diperbolehkan.");
    }

    $tempDir = __DIR__ . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $ytDlp = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'yt-dlp.exe';
    
    $fileId = time();
    $outputFile = $tempDir . 'ig_' . $fileId . '.mp4';
    
    $escapedYtDlp = escapeshellarg($ytDlp);
    $escapedUrl = escapeshellarg($url);
    $escapedOutput = escapeshellarg($outputFile);

    $command = "$escapedYtDlp --no-check-certificates --no-playlist --force-overwrites -f \"b[ext=mp4]\" -o $escapedOutput $escapedUrl 2>&1";
    
    exec($command, $output, $returnVar);

    if ($returnVar === 0 && file_exists($outputFile)) {
        if (ob_get_level()) ob_end_clean();

        header('Content-Description: File Transfer');
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="instagram_video_' . $fileId . '.mp4"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($outputFile));

        readfile($outputFile);

        unlink($outputFile);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error", 
            "message" => "Gagal mengambil video.",
            "debug" => $output 
        ]);
    }
} else {
    header("Location: index.html");
    exit;
}