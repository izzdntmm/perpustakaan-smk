import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf.mjs';
import workerUrl from 'pdfjs-dist/legacy/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

const ZOOM_MIN = 0.5;
const ZOOM_MAX = 3;
const ZOOM_STEP = 0.25;
const MAX_PIXELS = 16_000_000; // batas aman ukuran kanvas di HP

const $ = (id) => document.getElementById(id);

const root = $('pdf-reader');
const stage = $('pdf-stage');
const canvas = $('pdf-canvas');
const ctx = canvas.getContext('2d');

const ui = {
    prev: $('pdf-prev'),
    next: $('pdf-next'),
    input: $('pdf-page-input'),
    total: $('pdf-total'),
    jump: $('pdf-jump'),
    zoomIn: $('pdf-zoom-in'),
    zoomOut: $('pdf-zoom-out'),
    zoomReset: $('pdf-zoom-reset'),
    loading: $('pdf-loading'),
    error: $('pdf-error'),
};

let pdfDoc = null;
let pageNum = 1;
let zoom = 1; // 1 = pas selebar layar
let busy = false;
let queued = false;

function showError() {
    ui.loading.hidden = true;
    canvas.hidden = true;
    ui.error.hidden = false;
}

function sync() {
    ui.input.value = pageNum;
    ui.prev.disabled = pageNum <= 1;
    ui.next.disabled = pageNum >= pdfDoc.numPages;
    ui.zoomOut.disabled = zoom <= ZOOM_MIN;
    ui.zoomIn.disabled = zoom >= ZOOM_MAX;
    ui.zoomReset.textContent = `${Math.round(zoom * 100)}%`;
}

async function render() {
    if (!pdfDoc) return;

    // Jika sedang menggambar, catat saja; akan diulang dengan pengaturan terbaru
    if (busy) {
        queued = true;
        return;
    }
    busy = true;

    try {
        const page = await pdfDoc.getPage(pageNum);
        const base = page.getViewport({ scale: 1 });

        const style = getComputedStyle(stage);
        const padding = parseFloat(style.paddingLeft) + parseFloat(style.paddingRight);
        const fit = Math.max(stage.clientWidth - padding, 200) / base.width;
        const viewport = page.getViewport({ scale: fit * zoom });

        // Tajam di layar retina, tapi dibatasi agar tidak berat di HP
        let ratio = Math.min(window.devicePixelRatio || 1, 2);
        if (viewport.width * viewport.height * ratio * ratio > MAX_PIXELS) {
            ratio = Math.sqrt(MAX_PIXELS / (viewport.width * viewport.height));
        }

        canvas.width = Math.floor(viewport.width * ratio);
        canvas.height = Math.floor(viewport.height * ratio);
        canvas.style.width = `${Math.floor(viewport.width)}px`;
        canvas.style.height = `${Math.floor(viewport.height)}px`;

        await page.render({
            canvasContext: ctx,
            viewport,
            transform: [ratio, 0, 0, ratio, 0, 0],
        }).promise;

        canvas.hidden = false;
    } catch (e) {
        console.error(e);
        showError();
    } finally {
        busy = false;
        if (queued) {
            queued = false;
            render();
        }
    }
}

function goTo(n) {
    if (!pdfDoc) return;
    pageNum = Math.min(Math.max(parseInt(n, 10) || pageNum, 1), pdfDoc.numPages);
    sync();
    stage.scrollTo({ top: 0, left: 0 });
    render();
}

function setZoom(z) {
    zoom = Math.min(Math.max(Math.round(z * 100) / 100, ZOOM_MIN), ZOOM_MAX);
    sync();
    render();
}

/* ---------- Kontrol ---------- */
ui.prev.addEventListener('click', () => goTo(pageNum - 1));
ui.next.addEventListener('click', () => goTo(pageNum + 1));
ui.zoomIn.addEventListener('click', () => setZoom(zoom + ZOOM_STEP));
ui.zoomOut.addEventListener('click', () => setZoom(zoom - ZOOM_STEP));
ui.zoomReset.addEventListener('click', () => setZoom(1));

ui.jump.addEventListener('submit', (e) => {
    e.preventDefault();
    goTo(ui.input.value);
    ui.input.blur();
});
ui.input.addEventListener('focus', () => ui.input.select());

document.addEventListener('keydown', (e) => {
    if (e.target === ui.input) return;

    const canPage = stage.scrollWidth <= stage.clientWidth + 1; // tidak sedang digeser ke samping

    if (e.key === 'ArrowRight' && canPage) goTo(pageNum + 1);
    else if (e.key === 'ArrowLeft' && canPage) goTo(pageNum - 1);
    else if (e.key === '+' || e.key === '=') setZoom(zoom + ZOOM_STEP);
    else if (e.key === '-') setZoom(zoom - ZOOM_STEP);
    else return;

    e.preventDefault();
});

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(render, 150);
});

/* ---------- Mode baca saja (menyulitkan penyalinan biasa) ---------- */
['contextmenu', 'dragstart', 'selectstart'].forEach((type) => {
    root.addEventListener(type, (e) => e.preventDefault());
});

document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && ['s', 'p', 'u'].includes(e.key.toLowerCase())) {
        e.preventDefault();
    }
});

/* ---------- Mulai ---------- */
(async () => {
    try {
        pdfDoc = await pdfjsLib.getDocument({
            url: root.dataset.url,
            isEvalSupported: false,
        }).promise;

        ui.total.textContent = pdfDoc.numPages;
        ui.input.max = pdfDoc.numPages;
        ui.loading.hidden = true;

        sync();
        render();
    } catch (e) {
        console.error(e);
        showError();
    }
})();