const videoLink = document.getElementById('videoLink');
const pasteBtn = document.getElementById('pasteBtn');
const downloadBtn = document.getElementById('downloadBtn');

pasteBtn.addEventListener('click', async () => {
    try {
        const text = await navigator.clipboard.readText();
        videoLink.value = text;
    } catch (err) {
        alert('Gagal mengakses clipboard.');
    }
});

downloadBtn.addEventListener('click', () => {
    const url = videoLink.value.trim();

    if (!url) {
        alert('Silakan masukkan link terlebih dahulu!');
        return;
    }

    const tiktokRegex = /^(https?:\/\/)?(www\.|vt\.)?tiktok\.com\/.*$/;
    
    if (!tiktokRegex.test(url)) {
        alert('Format link salah! Hanya link TikTok (tiktok.com atau vt.tiktok.com) yang diperbolehkan.');
        return;
    }

    downloadBtn.innerText = 'Processing...';
    downloadBtn.disabled = true;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'download.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'url';
    input.value = url;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    setTimeout(() => {
        downloadBtn.innerText = 'Download';
        downloadBtn.disabled = false;
    }, 3000);
});