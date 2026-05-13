const videoLink = document.getElementById("videoLink");
const pasteBtn = document.getElementById("pasteBtn");

const downloadVideo = document.getElementById("downloadVideo");
const downloadAudio = document.getElementById("downloadAudio");

const previewSection = document.getElementById("previewSection");

function isYoutubeUrl(url) {
    const pattern = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|music\.youtube\.com)\/.+$/;
    return pattern.test(url);
}

pasteBtn.addEventListener("click", async () => {
    try {
        const text = await navigator.clipboard.readText();

        if (!isYoutubeUrl(text)) {
            alert("Hanya link Youtube/Youtube Music yang diperbolehkan");
            return;
        }

        videoLink.value = text;
        loadPreview(text);

    } catch (error) {
        alert("Gagal membaca clipboard");
    }
});

videoLink.addEventListener("input", () => {
    const url = videoLink.value.trim();

    if (isYoutubeUrl(url)) {
        loadPreview(url);
    } else {
        previewSection.innerHTML = "";
    }
});

downloadVideo.addEventListener("click", () => {
    const url = videoLink.value.trim();

    if (!isYoutubeUrl(url)) {
        alert("Masukkan link Youtube yang valid");
        return;
    }

    window.location.href = `download.php?type=video&url=${encodeURIComponent(url)}`;
});

downloadAudio.addEventListener("click", () => {
    const url = videoLink.value.trim();

    if (!isYoutubeUrl(url)) {
        alert("Masukkan link Youtube yang valid");
        return;
    }

    window.location.href = `download.php?type=audio&url=${encodeURIComponent(url)}`;
});

async function loadPreview(url) {

    previewSection.innerHTML = "Loading...";

    try {

        const response = await fetch(`download.php?preview=1&url=${encodeURIComponent(url)}`);
        const data = await response.json();

        if (data.error) {
            previewSection.innerHTML = `<p>${data.error}</p>`;
            return;
        }

        previewSection.innerHTML = `
            <div class="preview-card">
                <img src="${data.thumbnail}" alt="thumbnail">
                <h3>${data.title}</h3>
            </div>
        `;

    } catch (error) {
        previewSection.innerHTML = "<p>Gagal load preview</p>";
    }
}