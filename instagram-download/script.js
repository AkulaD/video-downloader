const videoInput = document.querySelector('#videoLink');
const downloadBtn = document.querySelector('#downloadBtn');
const pasteBtn = document.querySelector('#pasteBtn');
const previewSection = document.querySelector('#previewSection');

const isInstagram = (url) => {
    const pattern = /(?:https?:\/\/)?(?:www\.)?instagram\.com\/(?:p|reel|reels|tv)\/([A-Za-z0-9_-]+)/;
    return url.match(pattern);
};

const handlePreview = (url) => {
    const match = isInstagram(url);
    if (match) {
        const id = match[1];
        previewSection.innerHTML = `
            <iframe 
                src="https://www.instagram.com/p/${id}/embed/" 
                height="480" 
                frameborder="0" 
                scrolling="no" 
                allowtransparency="true">
            </iframe>`;
    } else {
        previewSection.innerHTML = "";
    }
};

pasteBtn.addEventListener('click', async () => {
    try {
        window.focus();

        const text = await navigator.clipboard.readText();
        const trimmedText = text.trim();

        if (isInstagram(trimmedText)) {
            videoInput.value = trimmedText;
            handlePreview(trimmedText);
        } else if (trimmedText !== "") {
            alert("Teks di clipboard bukan link Instagram yang valid!");
        }
    } catch (err) {
        console.error("Clipboard Error: ", err);
        
        if (err.name === 'NotAllowedError') {
            alert("Izin clipboard ditolak. Silakan klik ikon kunci di address bar dan izinkan 'Clipboard'.");
        }
    }
});

videoInput.addEventListener('input', (e) => {
    handlePreview(e.target.value.trim());
});

downloadBtn.addEventListener('click', function() {
    const videoLink = videoInput.value.trim();

    if (!videoLink || !isInstagram(videoLink)) {
        alert("Masukkan link Instagram yang valid!");
        return;
    }

    window.location.href = `download.php?url=${encodeURIComponent(videoLink)}`;
});