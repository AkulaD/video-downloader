<?php
set_time_limit(600); 

$url = $_GET['url'] ?? '';
$type = $_GET['type'] ?? '';

if (!$url) exit('URL kosong');

function isYoutubeUrl($url) {
    return preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|music\.youtube\.com)\/.+$/', $url);
}

if (!isYoutubeUrl($url)) exit('URL tidak valid');

$escapedUrl = escapeshellarg($url);
$ytDlp = __DIR__ . '\\tools\\yt-dlp.exe';
$ffmpegBin = __DIR__ . '\\tools\\ffmpeg-2026-05-11-git-17bc88e67f-full_build\\bin';
$ffmpegExe = $ffmpegBin . '\\ffmpeg.exe';

putenv("PATH=" . getenv("PATH") . ";" . $ffmpegBin);

$tempDir = __DIR__ . '/downloads';
if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

// Ambil metadata judul terlebih dahulu agar kita bisa kasih nama file sendiri
$jsonCmd = '"' . $ytDlp . '" --dump-json ' . $escapedUrl;
exec($jsonCmd, $jsonOutput, $jsonReturn);

if ($jsonReturn !== 0) exit('Gagal mengambil informasi video');

$data = json_decode(implode("\n", $jsonOutput), true);
$title = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['title'] ?? 'video'); // Bersihkan judul dari karakter aneh
$ext = ($type === 'audio') ? 'mp3' : 'mp4';
$finalFileName = $title . '.' . $ext;
$fullPath = $tempDir . '/' . $finalFileName;

if (isset($_GET['preview'])) {
    echo json_encode([
        'title' => $data['title'] ?? 'Unknown',
        'thumbnail' => $data['thumbnail'] ?? ''
    ]);
    exit;
}

if ($type === 'video') {
    $command = '"' . $ytDlp . '" ' .
            '-f "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best" ' .
            '--merge-output-format mp4 ' .
            '--postprocessor-args "ffmpeg:-c:a aac" ' . 
            '--ffmpeg-location "' . $ffmpegExe . '" ' .
            '-o ' . escapeshellarg($fullPath) . ' ' .
            $escapedUrl . ' 2>&1';

    exec($command, $output, $returnCode);

    if (file_exists($fullPath)) {
        send_file($fullPath, 'video/mp4');
    } else {
        exit('Gagal mendownload video.');
    }
}

if ($type === 'audio') {
    $command = '"' . $ytDlp . '" ' .
            '-x --audio-format mp3 --audio-quality 0 ' .
            '--ffmpeg-location "' . $ffmpegExe . '" ' .
            '-o ' . escapeshellarg($fullPath) . ' ' .
            $escapedUrl . ' 2>&1';

    exec($command, $output, $returnCode);

    if (file_exists($fullPath)) {
        send_file($fullPath, 'audio/mpeg');
    } else {
        exit('Gagal mendownload audio.');
    }
}

function send_file($file, $contentType) {
    if (ob_get_level()) ob_end_clean();

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));

    readfile($file);
    unlink($file); // Hapus setelah user simpan
    exit;
}