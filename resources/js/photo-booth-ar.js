import flagAu from 'flag-icons/flags/4x3/au.svg';
import flagBw from 'flag-icons/flags/4x3/bw.svg';
import flagCi from 'flag-icons/flags/4x3/ci.svg';
import flagCn from 'flag-icons/flags/4x3/cn.svg';
import flagDe from 'flag-icons/flags/4x3/de.svg';
import flagDza from 'flag-icons/flags/4x3/dz.svg';
import flagEg from 'flag-icons/flags/4x3/eg.svg';
import flagFj from 'flag-icons/flags/4x3/fj.svg';
import flagFr from 'flag-icons/flags/4x3/fr.svg';
import flagGb from 'flag-icons/flags/4x3/gb.svg';
import flagGh from 'flag-icons/flags/4x3/gh.svg';
import flagId from 'flag-icons/flags/4x3/id.svg';
import flagIn from 'flag-icons/flags/4x3/in.svg';
import flagIt from 'flag-icons/flags/4x3/it.svg';
import flagJp from 'flag-icons/flags/4x3/jp.svg';
import flagMa from 'flag-icons/flags/4x3/ma.svg';
import flagNa from 'flag-icons/flags/4x3/na.svg';
import flagNg from 'flag-icons/flags/4x3/ng.svg';
import flagNz from 'flag-icons/flags/4x3/nz.svg';
import flagSn from 'flag-icons/flags/4x3/sn.svg';
import flagTh from 'flag-icons/flags/4x3/th.svg';
import flagTn from 'flag-icons/flags/4x3/tn.svg';
import flagZa from 'flag-icons/flags/4x3/za.svg';
import flagZw from 'flag-icons/flags/4x3/zw.svg';

const FACE_MESH_OPTIONS = {
    maxNumFaces: 6,
    refineLandmarks: false,
};

const FLAG_ASSETS = {
    au: flagAu,
    bw: flagBw,
    ci: flagCi,
    cn: flagCn,
    de: flagDe,
    dz: flagDza,
    eg: flagEg,
    fj: flagFj,
    fr: flagFr,
    gb: flagGb,
    gh: flagGh,
    id: flagId,
    in: flagIn,
    it: flagIt,
    jp: flagJp,
    ma: flagMa,
    na: flagNa,
    ng: flagNg,
    nz: flagNz,
    sn: flagSn,
    th: flagTh,
    tn: flagTn,
    za: flagZa,
    zw: flagZw,
};

export const GLASSES_STYLES = [
    { id: 'classic', label: 'Classic Clear' },
    { id: 'round', label: 'Round Clear' },
    { id: 'aviator', label: 'Gold Aviator' },
    { id: 'heart', label: 'Heart Party' },
];

const COUNTRIES_BY_REGION = {
    'All Continents': [
        { id: 'id', name: 'Indonesia', flag: '🇮🇩' },
        { id: 'jp', name: 'Japan', flag: '🇯🇵' },
        { id: 'cn', name: 'China', flag: '🇨🇳' },
        { id: 'in', name: 'India', flag: '🇮🇳' },
        { id: 'th', name: 'Thailand', flag: '🇹🇭' },
        { id: 'fr', name: 'France', flag: '🇫🇷' },
        { id: 'it', name: 'Italy', flag: '🇮🇹' },
        { id: 'de', name: 'Germany', flag: '🇩🇪' },
        { id: 'gb', name: 'United Kingdom', flag: '🇬🇧' },
        { id: 'eg', name: 'Egypt', flag: '🇪🇬' },
        { id: 'ma', name: 'Morocco', flag: '🇲🇦' },
        { id: 'dz', name: 'Algeria', flag: '🇩🇿' },
        { id: 'tn', name: 'Tunisia', flag: '🇹🇳' },
        { id: 'gh', name: 'Ghana', flag: '🇬🇭' },
        { id: 'ng', name: 'Nigeria', flag: '🇳🇬' },
        { id: 'sn', name: 'Senegal', flag: '🇸🇳' },
        { id: 'ci', name: 'Ivory Coast', flag: '🇨🇮' },
        { id: 'za', name: 'South Africa', flag: '🇿🇦' },
        { id: 'na', name: 'Namibia', flag: '🇳🇦' },
        { id: 'bw', name: 'Botswana', flag: '🇧🇼' },
        { id: 'zw', name: 'Zimbabwe', flag: '🇿🇼' },
        { id: 'au', name: 'Australia', flag: '🇦🇺' },
        { id: 'nz', name: 'New Zealand', flag: '🇳🇿' },
        { id: 'fj', name: 'Fiji', flag: '🇫🇯' },
    ],
    Asia: [
        { id: 'id', name: 'Indonesia', flag: '🇮🇩' },
        { id: 'jp', name: 'Japan', flag: '🇯🇵' },
        { id: 'cn', name: 'China', flag: '🇨🇳' },
        { id: 'in', name: 'India', flag: '🇮🇳' },
        { id: 'th', name: 'Thailand', flag: '🇹🇭' },
    ],
    Europe: [
        { id: 'fr', name: 'France', flag: '🇫🇷' },
        { id: 'it', name: 'Italy', flag: '🇮🇹' },
        { id: 'de', name: 'Germany', flag: '🇩🇪' },
        { id: 'gb', name: 'United Kingdom', flag: '🇬🇧' },
    ],
    'North Africa': [
        { id: 'eg', name: 'Egypt', flag: '🇪🇬' },
        { id: 'ma', name: 'Morocco', flag: '🇲🇦' },
        { id: 'dz', name: 'Algeria', flag: '🇩🇿' },
        { id: 'tn', name: 'Tunisia', flag: '🇹🇳' },
    ],
    'West Africa': [
        { id: 'gh', name: 'Ghana', flag: '🇬🇭' },
        { id: 'ng', name: 'Nigeria', flag: '🇳🇬' },
        { id: 'sn', name: 'Senegal', flag: '🇸🇳' },
        { id: 'ci', name: 'Ivory Coast', flag: '🇨🇮' },
    ],
    'Southern Africa': [
        { id: 'za', name: 'South Africa', flag: '🇿🇦' },
        { id: 'na', name: 'Namibia', flag: '🇳🇦' },
        { id: 'bw', name: 'Botswana', flag: '🇧🇼' },
        { id: 'zw', name: 'Zimbabwe', flag: '🇿🇼' },
    ],
    Oceania: [
        { id: 'au', name: 'Australia', flag: '🇦🇺' },
        { id: 'nz', name: 'New Zealand', flag: '🇳🇿' },
        { id: 'fj', name: 'Fiji', flag: '🇫🇯' },
    ],
};

export class PhotoBoothAR {
    constructor(video, canvas, status, frameColor, frameBackgroundColor, assetsUrl) {
        this.video = video;
        this.canvas = canvas;
        this.context = canvas.getContext('2d');
        this.status = status;
        this.frameColor = frameColor;
        this.frameBackgroundColor = frameBackgroundColor;
        this.assetsUrl = assetsUrl;
        this.faceMesh = null;
        this.initialization = null;
        this.processing = false;
        this.running = false;
        this.faceTracks = [];
        this.countries = COUNTRIES_BY_REGION['All Continents'];
        this.flagImages = {};
        this.settings = {
            sensitivity: 'balanced',
            headband: false,
            glasses: false,
            glassesStyle: 'classic',
            outfit: false,
            cap: false,
            cheekSticker: true,
            stickerSide: 'right',
            label: false,
            themeName: 'All Continents',
        };
    }

    initialize() {
        if (this.initialization) return this.initialization;
        this.initialization = this.loadFaceMesh();
        return this.initialization;
    }

    async loadFaceMesh() {
        if (typeof window.FaceMesh !== 'function') {
            throw new Error('MediaPipe FaceMesh tidak ditemukan. Periksa file lokal /vendor/mediapipe.');
        }

        this.setStatus('AR · Memuat model deteksi wajah');
        const faceMesh = new window.FaceMesh({
            locateFile: (file) => `${this.assetsUrl}/${file}`,
        });
        const confidence = this.confidenceFor(this.settings.sensitivity);
        faceMesh.setOptions({
            ...FACE_MESH_OPTIONS,
            minDetectionConfidence: confidence,
            minTrackingConfidence: confidence,
        });
        faceMesh.onResults((results) => this.render(results));
        this.faceMesh = faceMesh;
        await faceMesh.initialize();
        this.running = true;
        this.setStatus('AR · Mencari wajah');
        this.processFrame();
    }

    confidenceFor(sensitivity) {
        if (sensitivity === 'high') return 0.25;
        if (sensitivity === 'strict') return 0.55;
        return 0.35;
    }

    async setSensitivity(sensitivity) {
        this.settings.sensitivity = sensitivity;
        if (!this.faceMesh) return;

        const confidence = this.confidenceFor(sensitivity);
        await this.faceMesh.setOptions({
            minDetectionConfidence: confidence,
            minTrackingConfidence: confidence,
        });
    }

    setTheme(themeName) {
        if (this.settings.themeName !== themeName) {
            this.faceTracks = [];
        }
        this.settings.themeName = themeName;
        this.countries = COUNTRIES_BY_REGION[themeName] || COUNTRIES_BY_REGION['All Continents'];
    }

    setStatus(text, isError = false) {
        this.status.textContent = text;
        this.status.classList.toggle('text-rose-200', isError);
        this.status.classList.toggle('text-slate-200', !isError);
    }

    processFrame() {
        if (!this.running) return;
        requestAnimationFrame(async () => {
            if (this.video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA && !this.processing) {
                this.processing = true;
                try {
                    await this.faceMesh.send({ image: this.video });
                } catch (error) {
                    console.error('FaceMesh gagal memproses frame kamera:', error);
                    this.setStatus('AR · Gagal memproses kamera', true);
                } finally {
                    this.processing = false;
                }
            }
            this.processFrame();
        });
    }

    render(results) {
        const width = this.video.videoWidth;
        const height = this.video.videoHeight;
        if (!width || !height) return;

        if (this.canvas.width !== width || this.canvas.height !== height) {
            this.canvas.width = width;
            this.canvas.height = height;
        }

        const faces = results.multiFaceLandmarks || [];
        const countries = this.assignCountries(faces, width);
        this.context.clearRect(0, 0, width, height);
        faces.forEach((landmarks, index) => this.renderFace(landmarks, width, height, countries[index]));
        this.setStatus(faces.length
            ? `AR · ${faces.length} wajah terdeteksi`
            : 'AR · Mencari wajah');
    }

    point(landmarks, index, width, height) {
        const point = landmarks[index];
        return point ? { x: point.x * width, y: point.y * height } : null;
    }

    assignCountries(faces, width) {
        const now = Date.now();
        const tracks = this.faceTracks.filter((track) => now - track.lastSeen < 800);
        const matched = new Set();
        const usedCountryIds = new Set();
        const countriesByFace = [];
        const orderedFaces = faces.map((landmarks, index) => {
            const nose = landmarks[1] || landmarks[10];
            return { index, x: (nose?.x || 0) * width };
        }).sort((left, right) => left.x - right.x);

        orderedFaces.forEach(({ index, x }) => {
            const closest = tracks
                .filter((track) => !matched.has(track.id))
                .map((track) => ({ track, distance: Math.abs(track.x - x) }))
                .sort((left, right) => left.distance - right.distance)[0];
            const searchRadius = Math.max(50, width * 0.12);
            let track = closest && closest.distance <= searchRadius ? closest.track : null;

            if (!track) {
                const availableCountries = this.countries.filter((country) => !usedCountryIds.has(country.id));
                const candidates = availableCountries.length ? availableCountries : this.countries;
                const country = candidates[Math.floor(Math.random() * candidates.length)];
                track = { id: Symbol('face'), x, lastSeen: now, country };
                tracks.push(track);
            }

            track.x = x;
            track.lastSeen = now;
            matched.add(track.id);
            usedCountryIds.add(track.country.id);
            countriesByFace[index] = track.country;
        });

        this.faceTracks = tracks.filter((track) => now - track.lastSeen < 800);
        return countriesByFace;
    }

    renderFace(landmarks, width, height, country) {
        const leftEye = this.point(landmarks, 33, width, height);
        const rightEye = this.point(landmarks, 263, width, height);
        const forehead = this.point(landmarks, 10, width, height);
        const chin = this.point(landmarks, 152, width, height);
        if (!leftEye || !rightEye || !forehead || !chin) return;

        const ctx = this.context;
        const eyeDistance = Math.hypot(rightEye.x - leftEye.x, rightEye.y - leftEye.y);
        if (eyeDistance < 5) return;
        const eyeCenter = {
            x: (leftEye.x + rightEye.x) / 2,
            y: (leftEye.y + rightEye.y) / 2,
        };
        const angle = Math.atan2(rightEye.y - leftEye.y, rightEye.x - leftEye.x);
        const faceHeight = Math.hypot(chin.x - forehead.x, chin.y - forehead.y);
        const faceCountry = country || this.countries[0];

        ctx.save();
        ctx.translate(eyeCenter.x, eyeCenter.y);
        ctx.rotate(angle);
        if (this.settings.glasses) this.drawGlasses(eyeDistance);
        ctx.restore();

        if (this.settings.headband) this.drawHeadband(landmarks, width, height, eyeDistance);
        if (this.settings.cap) this.drawCap(forehead, eyeDistance, angle);
        if (this.settings.outfit) this.drawOutfit(chin, eyeDistance);
        if (this.settings.cheekSticker) {
            this.drawCheekStickers(landmarks, width, height, eyeDistance, faceCountry);
        }
        if (this.settings.label) {
            this.drawLabel(forehead, eyeDistance, faceHeight, faceCountry);
        }
    }

    drawGlasses(eyeDistance) {
        const ctx = this.context;
        const lensWidth = eyeDistance * 0.43;
        const lensHeight = eyeDistance * 0.28;
        const lensY = -lensHeight * 0.47;
        const bridgeWidth = eyeDistance * 0.14;
        const lensX = eyeDistance * 0.035;
        const style = this.settings.glassesStyle;
        ctx.lineWidth = Math.max(2, eyeDistance * 0.035);
        ctx.strokeStyle = style === 'aviator' ? '#e8bd66' : '#f8fafc';
        ctx.fillStyle = 'rgba(190, 225, 255, .18)';

        [-1, 1].forEach((side) => {
            const x = side < 0 ? -lensWidth - lensX : lensX;
            ctx.beginPath();
            if (style === 'round') {
                ctx.ellipse(x + lensWidth / 2, lensY + lensHeight / 2, lensWidth / 2, lensHeight / 2, 0, 0, Math.PI * 2);
            } else if (style === 'heart') {
                const centerX = x + lensWidth / 2;
                const centerY = lensY + lensHeight / 2;
                ctx.moveTo(centerX, centerY + lensHeight * 0.42);
                ctx.bezierCurveTo(x - lensWidth * 0.15, centerY, x + lensWidth * 0.08, centerY - lensHeight * 0.52, centerX, centerY - lensHeight * 0.2);
                ctx.bezierCurveTo(x + lensWidth * 0.92, centerY - lensHeight * 0.52, x + lensWidth * 1.15, centerY, centerX, centerY + lensHeight * 0.42);
            } else {
                ctx.roundRect(x, lensY, lensWidth, lensHeight, style === 'aviator' ? lensHeight * 0.35 : lensHeight * 0.22);
            }
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
        });

        ctx.beginPath();
        ctx.moveTo(-bridgeWidth / 2, lensY + lensHeight * 0.2);
        ctx.quadraticCurveTo(0, lensY + lensHeight * 0.02, bridgeWidth / 2, lensY + lensHeight * 0.2);
        ctx.stroke();
    }

    drawHeadband(landmarks, width, height, eyeDistance) {
        const ctx = this.context;
        const left = this.point(landmarks, 127, width, height);
        const center = this.point(landmarks, 10, width, height);
        const right = this.point(landmarks, 356, width, height);
        if (!left || !center || !right) return;

        ctx.save();
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = 'rgba(15, 23, 42, .65)';
        ctx.lineWidth = Math.max(eyeDistance * 0.2, 6);
        ctx.beginPath();
        ctx.moveTo(left.x, left.y + eyeDistance * 0.12);
        ctx.quadraticCurveTo(center.x, center.y - eyeDistance * 0.14, right.x, right.y + eyeDistance * 0.12);
        ctx.stroke();
        ctx.strokeStyle = this.frameColor;
        ctx.lineWidth = Math.max(eyeDistance * 0.15, 4);
        ctx.beginPath();
        ctx.moveTo(left.x, left.y + eyeDistance * 0.1);
        ctx.quadraticCurveTo(center.x, center.y - eyeDistance * 0.16, right.x, right.y + eyeDistance * 0.1);
        ctx.stroke();
        ctx.restore();
    }

    drawCap(forehead, eyeDistance, angle) {
        const ctx = this.context;
        ctx.save();
        ctx.translate(forehead.x, forehead.y - eyeDistance * 0.23);
        ctx.rotate(angle);
        ctx.fillStyle = this.frameColor;
        ctx.strokeStyle = 'rgba(255,255,255,.9)';
        ctx.lineWidth = Math.max(2, eyeDistance * 0.025);
        ctx.beginPath();
        ctx.ellipse(0, 0, eyeDistance * 0.54, eyeDistance * 0.24, 0, Math.PI, Math.PI * 2);
        ctx.lineTo(eyeDistance * 0.54, eyeDistance * 0.05);
        ctx.quadraticCurveTo(0, eyeDistance * 0.16, -eyeDistance * 0.54, eyeDistance * 0.05);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.beginPath();
        ctx.ellipse(0, eyeDistance * 0.12, eyeDistance * 0.72, eyeDistance * 0.12, 0, 0, Math.PI);
        ctx.fill();
        ctx.stroke();
        ctx.restore();
    }

    drawOutfit(chin, eyeDistance) {
        const ctx = this.context;
        const width = eyeDistance * 1.55;
        const top = chin.y + eyeDistance * 0.12;
        const height = eyeDistance * 0.95;
        ctx.save();
        ctx.fillStyle = this.frameBackgroundColor;
        ctx.strokeStyle = this.frameColor;
        ctx.lineWidth = Math.max(2, eyeDistance * 0.03);
        ctx.beginPath();
        ctx.moveTo(chin.x - width * 0.18, top);
        ctx.lineTo(chin.x - width * 0.4, top + height * 0.12);
        ctx.lineTo(chin.x - width * 0.55, top + height);
        ctx.lineTo(chin.x + width * 0.55, top + height);
        ctx.lineTo(chin.x + width * 0.4, top + height * 0.12);
        ctx.lineTo(chin.x + width * 0.18, top);
        ctx.quadraticCurveTo(chin.x, top + height * 0.18, chin.x - width * 0.18, top);
        ctx.closePath();
        ctx.globalAlpha = 0.82;
        ctx.fill();
        ctx.globalAlpha = 1;
        ctx.stroke();
        ctx.restore();
    }

    drawCheekStickers(landmarks, width, height, eyeDistance, country) {
        const side = this.settings.stickerSide === 'left' ? 346 : 117;
        const cheek = this.point(landmarks, side, width, height);
        const nose = this.point(landmarks, 1, width, height);
        let flag = this.flagImages[country.id];
        if (!flag && FLAG_ASSETS[country.id]) {
            flag = new Image();
            flag.src = FLAG_ASSETS[country.id];
            this.flagImages[country.id] = flag;
        }
        if (!cheek || !nose || !flag?.complete || !flag.naturalWidth) return;

        const flagWidth = Math.max(14, eyeDistance * 0.16);
        const flagHeight = flagWidth * 0.75;
        this.context.drawImage(flag, cheek.x - flagWidth / 2, nose.y - flagHeight / 2, flagWidth, flagHeight);
    }

    drawLabel(forehead, eyeDistance, faceHeight, country) {
        const ctx = this.context;
        const text = country.name;
        ctx.save();
        ctx.font = `600 ${Math.max(12, eyeDistance * 0.16)}px Arial, sans-serif`;
        const padding = eyeDistance * 0.12;
        const width = ctx.measureText(text).width + padding * 2;
        const height = eyeDistance * 0.32;
        const x = forehead.x - width / 2;
        const y = forehead.y - faceHeight * 0.18 - height;
        ctx.fillStyle = 'rgba(15, 23, 42, .82)';
        ctx.strokeStyle = this.frameColor;
        ctx.lineWidth = Math.max(1, eyeDistance * 0.018);
        ctx.beginPath();
        ctx.roundRect(x, y, width, height, height / 2);
        ctx.fill();
        ctx.stroke();
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, forehead.x, y + height / 2, width - padding);
        ctx.restore();
    }

    clear() {
        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }

    close() {
        this.running = false;
        this.clear();
        if (this.faceMesh) {
            this.faceMesh.close();
            this.faceMesh = null;
        }
    }
}
