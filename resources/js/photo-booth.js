import QRCode from 'qrcode';
import { FACE_STICKER_STYLES, GLASSES_STYLES, PhotoBoothAR } from './photo-booth-ar';

const app = document.getElementById('photo-booth');

if (app) {
    const config = {
        eventId: Number(app.dataset.eventId),
        eventSlug: app.dataset.eventSlug,
        eventName: app.dataset.eventName,
        eventStatus: app.dataset.eventStatus,
        activeDate: app.dataset.activeDate,
        uploadUrl: app.dataset.uploadUrl,
        csrf: app.dataset.csrf,
        title: app.dataset.title,
        label: app.dataset.label,
        subtitle: app.dataset.subtitle || '',
        frameColor: app.dataset.frameColor,
        frameBackgroundColor: app.dataset.frameBackgroundColor || '#54172F',
        frameTheme: app.dataset.frameTheme || 'classic',
        mediapipeAssets: app.dataset.mediapipeAssets,
        countdown: Number(app.dataset.countdown),
    };

    const dbName = 'events-photo-booth';
    const storeName = 'photos';
    const video = document.getElementById('camera-video');
    const arCanvas = document.getElementById('ar-canvas');
    const faceStatus = document.getElementById('face-status');
    const cameraSelect = document.getElementById('camera-select');
    const captureButton = document.getElementById('capture-photo');
    const message = document.getElementById('booth-message');
    const connectionStatus = document.getElementById('connection-status');
    const countdownOverlay = document.getElementById('countdown-overlay');
    const countdownNumber = document.getElementById('countdown-number');
    const gallery = document.getElementById('photo-gallery');
    const galleryEmpty = document.getElementById('gallery-empty');
    const frameLogo = document.getElementById('booth-frame-logo');
    const stickers = new Set();
    const activeTheme = { name: 'All Continents', icon: '🌍', flag: '🌍', sticker: '' };
    let db;
    let stream;
    const ar = new PhotoBoothAR(
        video,
        arCanvas,
        faceStatus,
        config.frameColor,
        config.frameBackgroundColor,
        config.mediapipeAssets,
    );
    let latestUuid = null;
    let latestPreviewUrl = null;
    let queueBusy = false;
    let retryTimer = null;
    let renderVersion = 0;
    let galleryUrls = [];
    let confettiTimer = null;
    let countdownDuration = config.countdown;
    let defaultDownloadFormat = 'jpg';
    let audioContext;

    const playTone = async (frequency, duration = 0.12) => {
        if (!document.getElementById('check-sound-effects').checked) return;

        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) throw new Error('Browser tidak mendukung Web Audio.');
            audioContext ||= new AudioContextClass();
            if (audioContext.state === 'suspended') await audioContext.resume();
            const oscillator = audioContext.createOscillator();
            const gain = audioContext.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.value = frequency;
            gain.gain.setValueAtTime(0.08, audioContext.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + duration);
            oscillator.connect(gain);
            gain.connect(audioContext.destination);
            oscillator.start();
            oscillator.stop(audioContext.currentTime + duration);
        } catch (error) {
            console.warn('Efek suara tidak dapat diputar:', error);
        }
    };

    const showMessage = (text, type = 'error') => {
        message.textContent = text;
        message.className = `absolute left-1/2 top-24 z-40 w-[calc(100%-2rem)] max-w-xl -translate-x-1/2 rounded-2xl border px-4 py-3 text-sm shadow-xl backdrop-blur-md ${
            type === 'success'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                : 'border-rose-200/30 bg-rose-950/90 text-rose-100'
        }`;
    };

    const hideMessage = () => {
        message.classList.add('hidden');
        message.textContent = '';
    };

    const openDatabase = () => new Promise((resolve, reject) => {
        const request = indexedDB.open(dbName, 2);
        request.onupgradeneeded = () => {
            const database = request.result;
            if (!database.objectStoreNames.contains(storeName)) {
                const store = database.createObjectStore(storeName, { keyPath: 'uuid' });
                store.createIndex('eventSlug', 'eventSlug', { unique: false });
                store.createIndex('eventId', 'eventId', { unique: false });
            } else {
                const store = request.transaction.objectStore(storeName);
                if (!store.indexNames.contains('eventId')) {
                    store.createIndex('eventId', 'eventId', { unique: false });
                }
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error || new Error('IndexedDB tidak dapat dibuka.'));
        request.onblocked = () => reject(new Error('Penyimpanan lokal sedang digunakan tab lain. Tutup tab booth duplikat lalu coba lagi.'));
    });

    const putRecord = (record) => new Promise((resolve, reject) => {
        const transaction = db.transaction(storeName, 'readwrite');
        transaction.objectStore(storeName).put(record);
        transaction.oncomplete = () => resolve();
        transaction.onerror = () => reject(transaction.error || new Error('Foto gagal disimpan di browser.'));
        transaction.onabort = () => reject(transaction.error || new Error('Penyimpanan foto dibatalkan.'));
    });

    const getRecord = (uuid) => new Promise((resolve, reject) => {
        const request = db.transaction(storeName, 'readonly').objectStore(storeName).get(uuid);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error || new Error('Foto lokal gagal dibaca.'));
    });

    const getEventRecords = () => new Promise((resolve, reject) => {
        const transaction = db.transaction(storeName, 'readonly');
        const request = transaction.objectStore(storeName).index('eventId').getAll(IDBKeyRange.only(config.eventId));
        request.onsuccess = () => resolve(request.result || []);
        request.onerror = () => reject(request.error || new Error('Daftar foto lokal gagal dibaca.'));
    });

    const newUuid = () => {
        if (crypto.randomUUID) return crypto.randomUUID();
        const bytes = crypto.getRandomValues(new Uint8Array(16));
        bytes[6] = (bytes[6] & 0x0f) | 0x40;
        bytes[8] = (bytes[8] & 0x3f) | 0x80;
        const hex = [...bytes].map((byte) => byte.toString(16).padStart(2, '0')).join('');
        return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    };

    const updateConnectionStatus = () => {
        const online = navigator.onLine;
        connectionStatus.textContent = online ? 'Online · upload aktif' : 'Offline · foto disimpan lokal';
        connectionStatus.className = `mt-0.5 text-[11px] ${online ? 'text-emerald-300' : 'text-amber-300'}`;
    };

    const updateStickerPreview = () => {
        const target = document.getElementById('live-stickers');
        target.replaceChildren();
        [activeTheme.sticker, ...stickers].filter(Boolean).forEach((sticker) => {
            const item = document.createElement('span');
            item.className = 'booth-sticker';
            item.textContent = sticker;
            target.append(item);
        });
        if (ar) {
            ar.setTheme(activeTheme.name, activeTheme.flag);
        }
    };

    const updateCameraStatus = () => {
        const isReady = Boolean(stream);
        document.getElementById('camera-status').classList.toggle('hidden', !isReady);
        document.getElementById('camera-status-text').textContent = `Kamera siap · ${activeTheme.name}`;
    };

    document.querySelectorAll('.sticker-choice').forEach((button) => {
        button.addEventListener('click', () => {
            const sticker = button.dataset.sticker;
            if (stickers.has(sticker)) {
                stickers.delete(sticker);
                button.setAttribute('aria-pressed', 'false');
                button.classList.remove('border-amber-400', 'bg-amber-100', 'ring-2', 'ring-amber-200');
            } else {
                stickers.add(sticker);
                button.setAttribute('aria-pressed', 'true');
                button.classList.add('border-amber-400', 'bg-amber-100', 'ring-2', 'ring-amber-200');
            }
            updateStickerPreview();
        });
    });

    document.querySelectorAll('.booth-theme').forEach((button) => {
        button.addEventListener('click', () => {
            activeTheme.name = button.dataset.region;
            activeTheme.icon = button.dataset.regionIcon;
            activeTheme.flag = button.dataset.regionFlag;
            activeTheme.sticker = button.dataset.regionSticker;
            document.querySelectorAll('.booth-theme').forEach((themeButton) => {
                themeButton.setAttribute('aria-pressed', String(themeButton === button));
            });
            updateStickerPreview();
            if (stream) updateCameraStatus();
        });
    });

    const togglePanel = (panelId, trigger) => {
        const panel = document.getElementById(panelId);
        const shouldOpen = panel.classList.contains('hidden');
        ['sticker-panel', 'settings-panel', 'gallery-drawer'].forEach((otherId) => {
            document.getElementById(otherId).classList.add('hidden');
        });
        ['open-stickers', 'open-settings', 'open-gallery'].forEach((buttonId) => {
            document.getElementById(buttonId).setAttribute('aria-expanded', 'false');
        });
        if (shouldOpen) {
            panel.classList.remove('hidden');
            trigger?.setAttribute('aria-expanded', 'true');
        }
    };

    document.getElementById('open-stickers').addEventListener('click', (event) => {
        togglePanel('sticker-panel', event.currentTarget);
    });
    document.getElementById('open-settings').addEventListener('click', (event) => {
        togglePanel('settings-panel', event.currentTarget);
    });
    document.getElementById('open-gallery').addEventListener('click', (event) => {
        togglePanel('gallery-drawer', event.currentTarget);
    });
    document.querySelectorAll('[data-close-panel]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById(button.dataset.closePanel).classList.add('hidden');
            const triggerId = {
                'sticker-panel': 'open-stickers',
                'settings-panel': 'open-settings',
                'gallery-drawer': 'open-gallery',
            }[button.dataset.closePanel];
            document.getElementById(triggerId).setAttribute('aria-expanded', 'false');
        });
    });

    document.getElementById('toggle-fullscreen').addEventListener('click', async () => {
        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();
            } else {
                await app.requestFullscreen();
            }
        } catch (error) {
            showMessage(`Layar penuh tidak dapat diaktifkan: ${error.message || 'browser menolak permintaan.'}`);
        }
    });

    const refreshCameraOptions = async () => {
        if (!navigator.mediaDevices?.enumerateDevices) return;
        const devices = await navigator.mediaDevices.enumerateDevices();
        const cameras = devices.filter((device) => device.kind === 'videoinput');
        const selected = cameraSelect.value;
        cameraSelect.replaceChildren(new Option('Pilih kamera', ''));
        cameras.forEach((camera, index) => {
            cameraSelect.add(new Option(camera.label || `Kamera ${index + 1}`, camera.deviceId));
        });
        if (selected && cameras.some((camera) => camera.deviceId === selected)) cameraSelect.value = selected;
        else if (cameras.length === 1) cameraSelect.value = cameras[0].deviceId;
    };

    const stopCamera = () => {
        stream?.getTracks().forEach((track) => track.stop());
        stream = null;
        video.srcObject = null;
        ar?.clear();
    };

    const initializeFaceTracking = async () => {
        try {
            await ar.initialize();
        } catch (error) {
            faceStatus.textContent = 'AR · Tidak tersedia';
            faceStatus.classList.add('text-rose-200');
            console.error('Inisialisasi AR gagal:', error);
            showMessage(`Kamera tetap dapat digunakan, tetapi filter AR gagal dimuat: ${error.message}`);
        }
    };

    const startCamera = async (deviceId = '') => {
        hideMessage();
        if (!navigator.mediaDevices?.getUserMedia) {
            const isLocalhost = ['localhost', '127.0.0.1', '[::1]'].includes(window.location.hostname);
            showMessage(window.isSecureContext || isLocalhost
                ? 'Browser ini tidak menyediakan akses kamera. Coba browser modern atau periksa izin kamera.'
                : 'Halaman dapat dibuka lewat HTTP, tetapi kamera dibatasi browser. Untuk uji lokal gunakan http://localhost; untuk domain publik gunakan HTTPS.');
            return;
        }

        try {
            stopCamera();
            stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: deviceId
                    ? { deviceId: { exact: deviceId }, width: { ideal: 1920 }, height: { ideal: 1080 } }
                    : { facingMode: 'user', width: { ideal: 1920 }, height: { ideal: 1080 } },
            });
            video.srcObject = stream;
            await video.play();
            document.getElementById('camera-placeholder').classList.add('hidden');
            updateCameraStatus();
            await refreshCameraOptions();
            await initializeFaceTracking();
            if (deviceId) cameraSelect.value = deviceId;
        } catch (error) {
            document.getElementById('camera-placeholder').classList.remove('hidden');
            stopCamera();
            updateCameraStatus();
            faceStatus.textContent = 'AR · Kamera belum aktif';
            showMessage(error.name === 'NotAllowedError'
                ? 'Akses kamera ditolak. Izinkan kamera pada pengaturan browser, lalu coba lagi.'
                : `Kamera tidak dapat diaktifkan: ${error.message || 'periksa sambungan kamera dan coba lagi.'}`);
        }
    };

    document.getElementById('start-camera').addEventListener('click', () => startCamera(cameraSelect.value));
    document.getElementById('start-camera-empty').addEventListener('click', () => startCamera(cameraSelect.value));
    cameraSelect.addEventListener('change', () => {
        if (cameraSelect.value) startCamera(cameraSelect.value);
    });

    const wait = (milliseconds) => new Promise((resolve) => setTimeout(resolve, milliseconds));

    const runCountdown = async () => {
        if (countdownDuration < 1) return;
        countdownOverlay.classList.remove('hidden');
        countdownOverlay.classList.add('flex');
        for (let seconds = countdownDuration; seconds > 0; seconds -= 1) {
            countdownNumber.textContent = String(seconds);
            await playTone(620 - seconds * 40, 0.1);
            await wait(1000);
        }
        countdownNumber.textContent = '📸';
        await playTone(880, 0.18);
        await wait(350);
        countdownOverlay.classList.add('hidden');
        countdownOverlay.classList.remove('flex');
    };

    const drawCover = (context, source, x, y, width, height) => {
        const sourceWidth = source.videoWidth || source.width;
        const sourceHeight = source.videoHeight || source.height;
        const scale = Math.max(width / sourceWidth, height / sourceHeight);
        const cropWidth = width / scale;
        const cropHeight = height / scale;
        const sourceX = (sourceWidth - cropWidth) / 2;
        const sourceY = (sourceHeight - cropHeight) / 2;
        context.drawImage(source, sourceX, sourceY, cropWidth, cropHeight, x, y, width, height);
    };

    const colorWithOpacity = (hex, opacity) => {
        const value = Number.parseInt(hex.slice(1), 16);
        const red = (value >> 16) & 255;
        const green = (value >> 8) & 255;
        const blue = value & 255;

        return `rgba(${red}, ${green}, ${blue}, ${opacity})`;
    };

    const shadeColor = (hex, amount) => {
        const value = Number.parseInt(hex.slice(1), 16);
        const target = amount > 0 ? 255 : 0;
        const ratio = Math.abs(amount);
        const channels = [(value >> 16) & 255, (value >> 8) & 255, value & 255]
            .map((channel) => Math.round(channel + (target - channel) * ratio));

        return `#${channels.map((channel) => channel.toString(16).padStart(2, '0')).join('')}`;
    };

    const activeDate = config.activeDate
        ? new Date(`${config.activeDate}T12:00:00`)
        : new Date();

    const drawHibiscus = (context, x, y, radius, accent) => {
        context.save();
        context.translate(x, y);
        for (let petal = 0; petal < 5; petal += 1) {
            context.save();
            context.rotate((Math.PI * 2 * petal) / 5);
            context.fillStyle = petal % 2 ? '#ff8b9e' : '#f45f91';
            context.beginPath();
            context.ellipse(0, -radius * 0.53, radius * 0.31, radius * 0.56, 0, 0, Math.PI * 2);
            context.fill();
            context.restore();
        }
        context.fillStyle = accent;
        context.beginPath();
        context.arc(0, 0, radius * 0.2, 0, Math.PI * 2);
        context.fill();
        context.strokeStyle = '#fff4d6';
        context.lineWidth = Math.max(1.5, radius * 0.07);
        context.beginPath();
        context.moveTo(0, 0);
        context.quadraticCurveTo(radius * 0.55, -radius * 0.22, radius * 0.72, -radius * 0.62);
        context.stroke();
        context.restore();
    };

    const drawPalmFrond = (context, x, y, size, rotation) => {
        context.save();
        context.translate(x, y);
        context.rotate(rotation);
        context.strokeStyle = '#53d3a6';
        context.lineWidth = Math.max(2, size * 0.045);
        context.lineCap = 'round';
        context.beginPath();
        context.moveTo(0, size * 0.58);
        context.quadraticCurveTo(-size * 0.12, size * 0.05, 0, -size * 0.42);
        context.stroke();

        for (let leaf = 0; leaf < 5; leaf += 1) {
            const progress = leaf / 5;
            const baseY = size * (0.34 - progress * 0.72);
            const spread = size * (0.17 + progress * 0.31);
            [-1, 1].forEach((side) => {
                context.beginPath();
                context.moveTo(0, baseY);
                context.quadraticCurveTo(side * spread * 0.34, baseY - size * 0.12, side * spread, baseY - size * (0.15 + progress * 0.12));
                context.stroke();
            });
        }
        context.restore();
    };

    const drawTropicalCornerDetails = (context, width, height, padding) => {
        const corner = padding * 0.5;
        const motifSize = padding * 0.52;
        context.save();
        context.globalAlpha = 0.96;

        context.fillStyle = config.frameColor;
        context.beginPath();
        context.arc(corner, corner, motifSize * 0.2, 0, Math.PI * 2);
        context.fill();
        for (let ray = 0; ray < 8; ray += 1) {
            const angle = (Math.PI * 2 * ray) / 8;
            context.strokeStyle = config.frameColor;
            context.lineWidth = 3;
            context.beginPath();
            context.moveTo(corner + Math.cos(angle) * motifSize * 0.28, corner + Math.sin(angle) * motifSize * 0.28);
            context.lineTo(corner + Math.cos(angle) * motifSize * 0.48, corner + Math.sin(angle) * motifSize * 0.48);
            context.stroke();
        }

        drawPalmFrond(context, width - corner, corner + motifSize * 0.1, motifSize, -0.45);
        drawPalmFrond(context, width - corner - motifSize * 0.24, corner + motifSize * 0.12, motifSize * 0.78, 0.12);
        drawPalmFrond(context, corner, height - corner, motifSize * 0.76, Math.PI + 0.48);
        drawHibiscus(context, width - corner, height - corner, motifSize * 0.52, config.frameColor);

        context.strokeStyle = colorWithOpacity(config.frameColor, 0.9);
        context.lineWidth = 3;
        for (let row = 0; row < 3; row += 1) {
            const waveY = height - padding * 0.32 - row * 9;
            context.beginPath();
            context.moveTo(padding * 0.25, waveY);
            context.bezierCurveTo(padding * 0.45, waveY - 8, padding * 0.7, waveY + 8, padding * 0.9, waveY);
            context.stroke();
        }
        context.restore();
    };

    const composePhoto = async () => {
        await frameLogo.decode();
        const width = 1600;
        const padding = 48;
        const header = 168;
        const footer = 96;
        const photoWidth = width - padding * 2;
        const sourceAspect = video.videoWidth / video.videoHeight || 4 / 3;
        const photoHeight = Math.round(photoWidth / sourceAspect);
        const photoX = padding;
        const photoY = padding + header;
        const footerY = photoY + photoHeight + 34;
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = footerY + footer + padding;
        const context = canvas.getContext('2d');

        context.save();
        context.beginPath();
        context.roundRect(1, 1, width - 2, canvas.height - 2, 34);
        context.clip();
        const background = context.createLinearGradient(0, 0, width, canvas.height);
        background.addColorStop(0, shadeColor(config.frameBackgroundColor, 0.12));
        background.addColorStop(0.55, config.frameBackgroundColor);
        background.addColorStop(1, shadeColor(config.frameBackgroundColor, -0.12));
        context.fillStyle = background;
        context.fillRect(0, 0, canvas.width, canvas.height);

        context.strokeStyle = colorWithOpacity(config.frameColor, 0.12);
        context.lineWidth = 1;
        for (let x = -canvas.height; x < width; x += 38) {
            context.beginPath();
            context.moveTo(x, canvas.height);
            context.lineTo(x + canvas.height, 0);
            context.stroke();
        }
        context.restore();

        context.strokeStyle = colorWithOpacity(config.frameColor, 0.78);
        context.lineWidth = 4;
        context.beginPath();
        context.roundRect(2, 2, width - 4, canvas.height - 4, 32);
        context.stroke();
        context.strokeStyle = colorWithOpacity(config.frameColor, 0.24);
        context.lineWidth = 1;
        context.beginPath();
        context.roundRect(9, 9, width - 18, canvas.height - 18, 27);
        context.stroke();

        const accent = context.createLinearGradient(padding, 0, width - padding, 0);
        accent.addColorStop(0, colorWithOpacity(config.frameColor, 0));
        accent.addColorStop(0.2, config.frameColor);
        accent.addColorStop(0.5, shadeColor(config.frameColor, 0.35));
        accent.addColorStop(0.8, config.frameColor);
        accent.addColorStop(1, colorWithOpacity(config.frameColor, 0));
        context.fillStyle = accent;
        context.fillRect(padding, 0, width - padding * 2, 5);

        const logoSize = 136;
        const logoX = padding + 4;
        const logoY = padding + 12;
        context.fillStyle = '#ffffff';
        context.beginPath();
        context.roundRect(logoX, logoY, logoSize, logoSize, 25);
        context.fill();
        const logoPadding = 12;
        const logoScale = Math.min(
            (logoSize - logoPadding * 2) / frameLogo.naturalWidth,
            (logoSize - logoPadding * 2) / frameLogo.naturalHeight,
        );
        const logoWidth = frameLogo.naturalWidth * logoScale;
        const logoHeight = frameLogo.naturalHeight * logoScale;
        context.drawImage(
            frameLogo,
            logoX + (logoSize - logoWidth) / 2,
            logoY + (logoSize - logoHeight) / 2,
            logoWidth,
            logoHeight,
        );

        const textX = logoX + logoSize + 24;
        const titleMaxWidth = photoWidth - logoSize - 220;
        context.textAlign = 'left';
        context.textBaseline = 'alphabetic';
        context.fillStyle = shadeColor(config.frameColor, 0.32);
        context.font = '700 24px Arial, sans-serif';
        context.fillText(config.label.toUpperCase(), textX, padding + 53, titleMaxWidth);
        context.fillStyle = '#ffffff';
        context.font = '800 52px Arial, sans-serif';
        context.fillText(config.title.toUpperCase(), textX, padding + 119, titleMaxWidth);

        const year = String(activeDate.getFullYear());
        const pillWidth = 116;
        const pillX = width - padding - pillWidth;
        context.fillStyle = colorWithOpacity(config.frameColor, 0.16);
        context.beginPath();
        context.roundRect(pillX, padding + 43, pillWidth, 54, 27);
        context.fill();
        context.strokeStyle = colorWithOpacity(config.frameColor, 0.76);
        context.lineWidth = 2;
        context.stroke();
        context.textAlign = 'center';
        context.fillStyle = shadeColor(config.frameColor, 0.48);
        context.font = '700 25px Arial, sans-serif';
        context.fillText(year, pillX + pillWidth / 2, padding + 78);

        context.save();
        context.beginPath();
        context.roundRect(photoX, photoY, photoWidth, photoHeight, 18);
        context.clip();
        drawCover(context, video, photoX, photoY, photoWidth, photoHeight);
        if (arCanvas.width && arCanvas.height) {
            drawCover(context, arCanvas, photoX, photoY, photoWidth, photoHeight);
        }
        context.restore();

        const selectedStickers = [activeTheme.sticker, ...stickers].filter(Boolean);
        if (selectedStickers.length) {
            const stickerSize = Math.min(96, Math.floor(width / (selectedStickers.length + 2)));
            context.textAlign = 'center';
            context.textBaseline = 'middle';
            context.font = `${stickerSize}px "Apple Color Emoji", "Segoe UI Emoji", sans-serif`;
            context.shadowColor = 'rgba(0, 0, 0, .45)';
            context.shadowBlur = 12;
            const gap = stickerSize * 1.25;
            const startX = width / 2 - (selectedStickers.length - 1) * gap / 2;
            selectedStickers.forEach((sticker, index) => {
                context.fillText(sticker, startX + index * gap, photoY + stickerSize * 0.9);
            });
            context.shadowBlur = 0;
        }

        context.fillStyle = colorWithOpacity(config.frameColor, 0.72);
        context.fillRect(padding, footerY, photoWidth, 2);
        context.textAlign = 'left';
        context.fillStyle = '#ffffff';
        context.font = '700 25px Arial, sans-serif';
        context.fillText((config.subtitle || config.title || config.eventName).toUpperCase(), padding + 2, footerY + 54, photoWidth * 0.68);
        context.textAlign = 'right';
        context.fillStyle = '#aeb8c9';
        context.font = '500 24px Arial, sans-serif';
        context.fillText(activeDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), width - padding - 2, footerY + 54);

        if (config.frameTheme === 'tropical') drawTropicalCornerDetails(context, width, canvas.height, padding);

        return new Promise((resolve, reject) => {
            canvas.toBlob((blob) => {
                if (!blob) {
                    reject(new Error('Browser gagal membuat file foto.'));
                    return;
                }
                resolve(blob);
            }, 'image/jpeg', 0.94);
        });
    };

    const setLatest = async (record, showResult = false) => {
        latestUuid = record.uuid;
        if (latestPreviewUrl) URL.revokeObjectURL(latestPreviewUrl);
        latestPreviewUrl = URL.createObjectURL(record.blob);
        document.getElementById('latest-image').src = latestPreviewUrl;
        await updateLatestStatus(record);
        if (showResult) openResult();
    };

    const celebrateCapture = () => {
        const layer = document.getElementById('result-confetti');
        const colors = ['#f5ba42', '#f27c58', '#52c6a5', '#7ca7ef', '#cf8ae5', '#f3e7cf'];
        layer.replaceChildren();
        layer.classList.remove('hidden');

        for (let index = 0; index < 48; index += 1) {
            const piece = document.createElement('span');
            piece.className = 'booth-confetti-piece';
            piece.style.left = `${Math.random() * 100}%`;
            piece.style.backgroundColor = colors[index % colors.length];
            piece.style.setProperty('--confetti-drift', `${Math.random() * 260 - 130}px`);
            piece.style.animationDelay = `${Math.random() * 450}ms`;
            layer.append(piece);
        }

        clearTimeout(confettiTimer);
        confettiTimer = setTimeout(() => {
            layer.classList.add('hidden');
            layer.replaceChildren();
        }, 2800);
    };

    const openResult = () => {
        const result = document.getElementById('capture-result');
        result.classList.remove('hidden');
        result.classList.add('flex');
        celebrateCapture();
    };

    const closeResult = () => {
        const result = document.getElementById('capture-result');
        result.classList.add('hidden');
        result.classList.remove('flex');
    };

    document.getElementById('take-another').addEventListener('click', closeResult);
    document.getElementById('open-latest').addEventListener('click', async () => {
        if (!latestUuid) {
            showMessage('Belum ada foto di event ini.');
            return;
        }

        const record = await getRecord(latestUuid);
        if (record) await setLatest(record, true);
    });

    const updateLatestStatus = async (record) => {
        const status = document.getElementById('latest-status');
        const qrPanel = document.getElementById('latest-qr-panel');
        const openDownload = document.getElementById('open-download');
        const errorPanel = document.getElementById('upload-error-panel');
        const errorMessage = document.getElementById('upload-error-message');
        const statusHeading = document.getElementById('upload-status-heading');
        const uploadMessage = document.getElementById('latest-upload-message');
        const statusIcon = document.getElementById('upload-status-icon');
        const progressTrack = document.getElementById('upload-progress-track');
        const progressBar = document.getElementById('upload-progress-bar');

        qrPanel.classList.add('hidden');
        openDownload.classList.add('hidden');
        errorPanel.classList.add('hidden');
        progressTrack.classList.remove('hidden');

        if (record.status === 'uploaded' && record.shareUrl) {
            status.textContent = 'QR siap dipindai';
            status.className = 'rounded-full bg-emerald-400/15 px-2.5 py-1 text-[9px] font-semibold text-emerald-200';
            statusHeading.textContent = 'Foto berhasil disimpan di server';
            uploadMessage.textContent = 'File server terverifikasi. Orang tua dapat scan QR menggunakan internet seluler.';
            statusIcon.className = 'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-400/10 text-sm text-emerald-300';
            statusIcon.innerHTML = '<i class="fas fa-circle-check"></i>';
            progressBar.className = 'h-full w-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400 transition-all duration-500';
            const canvas = document.getElementById('latest-qr');
            await QRCode.toCanvas(canvas, record.shareUrl, {
                width: 176,
                margin: 1,
                errorCorrectionLevel: 'M',
                color: { dark: '#122033', light: '#ffffff' },
            });
            document.getElementById('latest-expiry').textContent = `Link berlaku sampai ${new Date(record.expiresAt).toLocaleString('id-ID')}.`;
            qrPanel.classList.remove('hidden');
            openDownload.href = record.shareUrl;
            openDownload.classList.remove('hidden');
        } else if (record.status === 'uploading') {
            status.textContent = 'Sedang upload';
            status.className = 'rounded-full bg-cyan-400/15 px-2.5 py-1 text-[9px] font-semibold text-cyan-200';
            statusHeading.textContent = 'Mengirim foto ke server...';
            uploadMessage.textContent = 'Salinan lokal sudah aman. Mohon tunggu konfirmasi server untuk membuat QR.';
            statusIcon.className = 'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-cyan-400/10 text-sm text-cyan-300';
            statusIcon.innerHTML = '<i class="fas fa-cloud-arrow-up"></i>';
            progressBar.className = 'h-full w-2/3 animate-pulse rounded-full bg-gradient-to-r from-cyan-400 to-emerald-400 transition-all duration-500';
        } else {
            const failed = Boolean(record.errorMessage);
            const offline = !navigator.onLine;
            status.textContent = failed ? 'Upload gagal' : offline ? 'Offline' : 'Menunggu upload';
            status.className = failed
                ? 'rounded-full bg-rose-400/15 px-2.5 py-1 text-[9px] font-semibold text-rose-200'
                : 'rounded-full bg-amber-400/15 px-2.5 py-1 text-[9px] font-semibold text-amber-200';
            statusHeading.textContent = failed
                ? 'Foto tetap aman tersimpan lokal'
                : offline ? 'Menunggu koneksi internet' : 'Foto tersimpan di laptop';
            uploadMessage.textContent = failed
                ? 'Server belum mengonfirmasi penyimpanan foto.'
                : offline
                    ? 'QR akan muncul otomatis setelah koneksi kembali dan server menyimpan foto.'
                    : 'QR akan dibuat setelah server mengonfirmasi foto tersimpan.';
            statusIcon.className = failed
                ? 'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-400/10 text-sm text-rose-300'
                : 'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-400/10 text-sm text-amber-300';
            statusIcon.innerHTML = failed
                ? '<i class="fas fa-circle-exclamation"></i>'
                : '<i class="fas fa-hard-drive"></i>';
            if (failed) {
                progressTrack.classList.add('hidden');
                errorMessage.textContent = record.errorMessage;
                errorPanel.classList.remove('hidden');
            } else {
                progressBar.className = 'h-full w-1/3 animate-pulse rounded-full bg-gradient-to-r from-amber-300 to-amber-500 transition-all duration-500';
            }
        }
    };

    const createGalleryItem = async (record, version) => {
        const card = document.createElement('article');
        card.className = 'flex gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3';

        const thumbnail = document.createElement('img');
        thumbnail.src = URL.createObjectURL(record.blob);
        galleryUrls.push(thumbnail.src);
        thumbnail.alt = 'Foto event';
        thumbnail.className = 'h-20 w-20 shrink-0 rounded-xl bg-white object-cover';
        card.append(thumbnail);

        const details = document.createElement('div');
        details.className = 'min-w-0 flex-1';
        const time = document.createElement('p');
        time.className = 'text-[11px] text-slate-400';
        time.textContent = new Date(record.createdAt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const status = document.createElement('p');
        status.className = `mt-1 text-xs font-semibold ${record.status === 'uploaded' ? 'text-emerald-700' : 'text-amber-700'}`;
        status.textContent = record.status === 'uploaded' ? 'QR siap dipindai' : record.status === 'uploading' ? 'Mengirim ke server…' : 'Tersimpan lokal · menunggu upload';
        details.append(time, status);

        if (record.errorMessage && record.status !== 'uploaded') {
            const error = document.createElement('p');
            error.className = 'mt-1 line-clamp-2 text-[10px] leading-4 text-rose-600';
            error.textContent = record.errorMessage;
            details.append(error);
        }

        const actions = document.createElement('div');
        actions.className = 'mt-2 flex flex-wrap items-center gap-3';
        const download = document.createElement('button');
        download.type = 'button';
        download.dataset.localDownload = record.uuid;
        download.className = 'text-[11px] font-semibold text-slate-600 hover:text-amber-700';
        download.innerHTML = '<i class="fas fa-download mr-1"></i>Unduh lokal';
        actions.append(download);
        details.append(actions);
        card.append(details);

        if (record.status === 'uploaded' && record.shareUrl) {
            const qr = document.createElement('canvas');
            qr.className = 'h-[84px] w-[84px] shrink-0 self-center rounded-lg border border-slate-200 bg-white p-1';
            qr.setAttribute('aria-label', 'QR code untuk unduh foto');
            card.append(qr);
            await QRCode.toCanvas(qr, record.shareUrl, { width: 76, margin: 0, errorCorrectionLevel: 'M' });
        }

        if (version === renderVersion) return card;
        URL.revokeObjectURL(thumbnail.src);
        return null;
    };

    const renderGallery = async () => {
        const version = ++renderVersion;
        let records = await getEventRecords();
        records.sort((left, right) => right.createdAt - left.createdAt);
        const pending = records.filter((record) => record.status !== 'uploaded').length;
        document.getElementById('queue-count').textContent = `${pending} menunggu`;

        galleryUrls.forEach((url) => URL.revokeObjectURL(url));
        galleryUrls = [];
        gallery.replaceChildren();
        if (!records.length) {
            gallery.append(galleryEmpty);
            galleryEmpty.classList.remove('hidden');
            return;
        }

        galleryEmpty.classList.add('hidden');
        const items = await Promise.all(records.slice(0, 12).map((record) => createGalleryItem(record, version)));
        if (version !== renderVersion) return;
        items.filter(Boolean).forEach((item) => gallery.append(item));
    };

    const downloadLocal = async (uuid, format = 'jpg') => {
        const record = await getRecord(uuid);
        if (!record?.blob) {
            showMessage('Foto tidak ditemukan di penyimpanan lokal browser.');
            return;
        }

        let photoBlob = record.blob;
        if (format === 'png' && photoBlob.type !== 'image/png') {
            const bitmap = await createImageBitmap(photoBlob);
            const canvas = document.createElement('canvas');
            canvas.width = bitmap.width;
            canvas.height = bitmap.height;
            canvas.getContext('2d').drawImage(bitmap, 0, 0);
            bitmap.close();
            photoBlob = await new Promise((resolve, reject) => {
                canvas.toBlob((blob) => {
                    if (blob) resolve(blob);
                    else reject(new Error('Browser gagal menyiapkan file PNG.'));
                }, 'image/png');
            });
        }

        const url = URL.createObjectURL(photoBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `foto-${uuid}.${format}`;
        document.body.append(link);
        link.click();
        link.remove();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    };

    gallery.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-local-download]');
        if (button) {
            try {
                await downloadLocal(button.dataset.localDownload, defaultDownloadFormat);
            } catch (error) {
                showMessage(error.message || 'Foto lokal gagal diunduh.');
            }
        }
    });

    document.getElementById('download-latest-jpg').addEventListener('click', () => {
        if (latestUuid) downloadLocal(latestUuid, 'jpg').catch((error) => showMessage(error.message));
    });
    document.getElementById('download-latest-png').addEventListener('click', () => {
        if (latestUuid) downloadLocal(latestUuid, 'png').catch((error) => showMessage(error.message));
    });

    document.getElementById('select-countdown').addEventListener('change', (event) => {
        countdownDuration = Number(event.currentTarget.value);
    });
    document.getElementById('select-default-format').addEventListener('change', (event) => {
        defaultDownloadFormat = event.currentTarget.value;
    });
    document.getElementById('select-face-sensitivity').addEventListener('change', (event) => {
        ar.setSensitivity(event.currentTarget.value).catch((error) => {
            console.error('Sensitivitas face tracking gagal diubah:', error);
            showMessage(`Pengaturan sensitivitas tidak dapat diterapkan: ${error.message}`);
        });
    });

    const toggleInputs = {
        headband: 'check-headband',
        glasses: 'check-glasses',
        outfit: 'check-outfit',
        cap: 'check-cap',
        cheekSticker: 'check-face-sticker',
        label: 'check-face-label',
    };
    const setArToggle = (setting, enabled) => {
        ar.settings[setting] = enabled;
        const input = document.getElementById(toggleInputs[setting]);
        if (input) input.checked = enabled;
        document.querySelectorAll(`[data-ar-toggle="${setting}"]`).forEach((button) => {
            button.setAttribute('aria-pressed', String(enabled));
        });
    };
    const bindArToggle = (setting) => {
        const input = document.getElementById(toggleInputs[setting]);
        input.addEventListener('change', (event) => {
            setArToggle(setting, event.currentTarget.checked);
        });
        document.querySelectorAll(`[data-ar-toggle="${setting}"]`).forEach((button) => {
            button.addEventListener('click', () => {
                setArToggle(setting, !ar.settings[setting]);
            });
        });
        setArToggle(setting, input.checked);
    };
    Object.keys(toggleInputs).forEach(bindArToggle);

    const styleControls = {
        glassesStyle: {
            selectId: 'select-glasses-style',
            options: GLASSES_STYLES,
        },
        stickerStyle: {
            selectId: 'select-sticker-style',
            options: FACE_STICKER_STYLES,
        },
    };
    const updateStyleLabel = (setting) => {
        const { selectId, options } = styleControls[setting];
        const selected = options.find((option) => option.id === document.getElementById(selectId).value);
        document.querySelectorAll(`[data-cycle-label="${setting}"]`).forEach((label) => {
            label.textContent = selected?.label || '';
        });
    };
    const setStyle = (setting, value) => {
        const { selectId, options } = styleControls[setting];
        const next = options.find((option) => option.id === value);
        if (!next) return;
        document.getElementById(selectId).value = next.id;
        ar.settings[setting] = next.id;
        updateStyleLabel(setting);
    };
    Object.entries(styleControls).forEach(([setting, { selectId, options }]) => {
        const select = document.getElementById(selectId);
        select.addEventListener('change', (event) => {
            setStyle(setting, event.currentTarget.value);
        });
        document.querySelectorAll(`[data-ar-cycle="${setting}"]`).forEach((button) => {
            button.addEventListener('click', () => {
                const currentIndex = options.findIndex((option) => option.id === select.value);
                const next = options[(currentIndex + 1) % options.length];
                setStyle(setting, next.id);
            });
        });
        setStyle(setting, select.value);
    });
    document.getElementById('select-sticker-side').addEventListener('change', (event) => {
        ar.settings.stickerSide = event.currentTarget.value;
    });
    document.getElementById('check-mirror-camera').addEventListener('change', (event) => {
        const mirrored = event.currentTarget.checked;
        video.classList.toggle('booth-video', mirrored);
        arCanvas.classList.toggle('booth-video', mirrored);
    });

    const uploadRecord = async (record) => {
        if (!navigator.onLine) return false;
        record.status = 'uploading';
        record.errorMessage = '';
        await putRecord(record);
        await renderGallery();
        if (record.uuid === latestUuid) await updateLatestStatus(record);

        const formData = new FormData();
        formData.append('photo_uuid', record.uuid);
        formData.append('photo', record.blob, `photo-${record.uuid}.jpg`);

        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 30000);
        try {
            const response = await fetch(config.uploadUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': config.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
                signal: controller.signal,
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok || !result.download_url) {
                const validationMessage = result.errors
                    ? Object.values(result.errors).flat().join(' ')
                    : result.message;
                throw new Error(validationMessage || `Server menolak upload (${response.status}).`);
            }

            record.status = 'uploaded';
            record.shareUrl = result.download_url;
            record.expiresAt = result.expires_at;
            record.retryCount = 0;
            record.retryAt = null;
            record.errorMessage = '';
            await putRecord(record);
            if (record.uuid === latestUuid) await setLatest(record, true);
            await renderGallery();
            return true;
        } catch (error) {
            record.status = 'pending';
            record.retryCount = (record.retryCount || 0) + 1;
            record.retryAt = Date.now() + Math.min(60000, 2000 * (2 ** Math.min(record.retryCount - 1, 5)));
            record.errorMessage = error.message || 'Upload gagal. Foto aman tersimpan lokal dan akan dicoba lagi.';
            await putRecord(record);
            if (record.uuid === latestUuid) {
                await setLatest(record);
                document.getElementById('latest-upload-message').textContent = `Upload belum berhasil: ${record.errorMessage} Foto tetap aman tersimpan di laptop.`;
            }
            await renderGallery();
            console.warn('Photo upload is queued for retry:', error);
            return false;
        } finally {
            clearTimeout(timeout);
        }
    };

    const processQueue = async (force = false) => {
        if (!db || queueBusy || !navigator.onLine) return;
        queueBusy = true;
        let uploadedAny = false;
        let uploadFailed = false;
        clearTimeout(retryTimer);
        try {
            const records = (await getEventRecords())
                .filter((record) => record.status !== 'uploaded')
                .sort((left, right) => left.createdAt - right.createdAt);
            for (const record of records) {
                if (!force && record.retryAt && record.retryAt > Date.now()) continue;
                const uploaded = await uploadRecord(record);
                if (!uploaded) {
                    uploadFailed = true;
                    retryTimer = setTimeout(() => processQueue(), Math.max(1000, (record.retryAt || Date.now() + 5000) - Date.now()));
                    break;
                }
                uploadedAny = true;
            }
        } catch (error) {
            showMessage(`Antrean foto gagal dibaca: ${error.message || 'periksa penyimpanan browser.'}`);
        } finally {
            queueBusy = false;
            if (uploadedAny && !uploadFailed) setTimeout(() => processQueue(), 0);
        }
    };

    document.getElementById('retry-uploads').addEventListener('click', () => processQueue(true));
    window.addEventListener('online', () => {
        updateConnectionStatus();
        processQueue(true);
    });
    window.addEventListener('offline', updateConnectionStatus);
    window.addEventListener('focus', () => processQueue());
    window.addEventListener('pagehide', stopCamera);
    window.addEventListener('pagehide', () => ar.close());
    setInterval(() => processQueue(), 30000);

    captureButton.addEventListener('click', async () => {
        if (config.eventStatus === 'closed') {
            showMessage('Photo event ini sudah ditutup.');
            return;
        }
        if (!db) {
            showMessage('Penyimpanan lokal belum siap. Foto tidak diambil agar tidak berisiko hilang.');
            return;
        }
        if (!stream || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
            showMessage('Aktifkan kamera dan tunggu preview tampil sebelum mengambil foto.');
            return;
        }

        captureButton.disabled = true;
        hideMessage();
        try {
            await runCountdown();
            await playTone(280, 0.16);
            const blob = await composePhoto();
            const record = {
                uuid: newUuid(),
                eventId: config.eventId,
                eventSlug: config.eventSlug,
                theme: activeTheme.name,
                blob,
                status: 'pending',
                shareUrl: null,
                expiresAt: null,
                createdAt: Date.now(),
                retryCount: 0,
                retryAt: null,
                errorMessage: '',
            };

            await putRecord(record);
            await setLatest(record, true);
            await renderGallery();
            processQueue(true);
            if (!navigator.onLine) {
                showMessage('Foto tersimpan aman di IndexedDB laptop. Upload dan QR akan dilanjutkan otomatis saat koneksi pulih.', 'success');
            }
        } catch (error) {
            showMessage(`Foto belum dapat disimpan: ${error.message || 'periksa ruang penyimpanan browser.'}`);
        } finally {
            captureButton.disabled = false;
        }
    });

    const initialize = async () => {
        updateConnectionStatus();
        try {
            db = await openDatabase();
            const persistentStorage = navigator.storage?.persist
                ? await navigator.storage.persist()
                : false;
            document.getElementById('storage-persistence').textContent = persistentStorage
                ? 'Browser melindungi data lokal dari pembersihan otomatis. Foto tetap terikat ke laptop dan profil browser ini.'
                : 'Penyimpanan persisten tidak tersedia; unduh foto penting dan jangan hapus data browser sebelum upload tuntas.';
            const records = await getEventRecords();
            records.forEach((record) => {
                if (record.status === 'uploading') {
                    record.status = 'pending';
                    record.errorMessage = '';
                    putRecord(record);
                }
            });
            records.sort((left, right) => right.createdAt - left.createdAt);
            if (records[0]) await setLatest(records[0]);
            await renderGallery();
            processQueue();
        } catch (error) {
            captureButton.disabled = true;
            showMessage(`Penyimpanan lokal tidak tersedia: ${error.message || 'izinkan penyimpanan browser lalu muat ulang halaman.'}`);
        }

        try {
            await refreshCameraOptions();
        } catch (error) {
            console.warn('Camera list could not be loaded before permission:', error);
        }
        await startCamera(cameraSelect.value);
    };

    initialize();
}
