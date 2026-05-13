<?php
set_time_limit(600);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = $_POST['url'];

    if (strpos($url, 'tiktok.com') === false) {
        die("Domain tidak diizinkan.");
    }

    $temp_dir = __DIR__ . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }

    // Variabel untuk memanggil yt-dlp.exe di folder tools
    $ytDlp = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'yt-dlp.exe';

    $file_id = time();
    $output_file = $temp_dir . 'tiktok_' . $file_id . '.mp4';
    
    $escaped_ytDlp = escapeshellarg($ytDlp);
    $escaped_url = escapeshellarg($url);
    $escaped_output = escapeshellarg($output_file);

    $cmd = "$escaped_ytDlp --no-check-certificates --no-playlist --force-overwrites -f \"best\" -o $escaped_output $escaped_url 2>&1";
    
    $output_log = shell_exec($cmd);

    if (file_exists($output_file) && filesize($output_file) > 1000) {
        if (ob_get_level()) ob_end_clean();

        header('Content-Description: File Transfer');
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="tiktok_video_' . $file_id . '.mp4"');
        header('Content-Length: ' . filesize($output_file));
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        readfile($output_file);

        // Menghapus file di folder temp setelah user selesai download
        unlink($output_file);
        exit;
    } else {
        echo "<h1>Gagal mendownload video.</h1>";
        echo "<p>Analisis Error Terakhir:</p>";
        echo "<pre style='background:#eee; padding:10px; border-radius:5px;'>" . htmlspecialchars($output_log) . "</pre>";
    }
} else {
    header("Location: index.html");
    exit;
}