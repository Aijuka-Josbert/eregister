/**
 * LMO Records Registry — Client-Side Interaction
 * - Mobile sidebar drawer toggle
 * - Conditional Event Date toggle for Invitation categories
 * - Interactive Accountability Donut hover readout
 * - Real-time Duplicate Reference Detection
 * - Multi-Page Mobile Camera Document Scanner & Drag-and-Drop Uploads
 * - PWA Service Worker & Install prompt
 */

document.addEventListener('DOMContentLoaded', () => {
    const baseUrlMeta = document.querySelector('meta[name="app-base-url"]');
    const APP_BASE_URL = baseUrlMeta ? baseUrlMeta.getAttribute('content').replace(/\/$/, '') : '';

    // 1. Mobile Navigation Toggle
    const menuBtn = document.getElementById('mobileMenuBtn');
    const navScrim = document.getElementById('mobileNavScrim');
    if (menuBtn) {
        menuBtn.addEventListener('click', () => document.body.classList.toggle('nav-open'));
    }
    if (navScrim) {
        navScrim.addEventListener('click', () => document.body.classList.remove('nav-open'));
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.body.classList.remove('nav-open');
        }
    });

    // 2. Conditional Event Date Field (shown only when an Invitation category is selected)
    const categorySelect = document.querySelector('[data-event-toggle]');
    const eventDateWrap = document.getElementById('eventDateWrap');
    const syncEventDateVisibility = () => {
        if (!categorySelect || !eventDateWrap) return;
        const opt = categorySelect.options[categorySelect.selectedIndex];
        const text = ((opt?.getAttribute('data-slug') || '') + ' ' + (opt?.textContent || '')).toLowerCase();
        const isInvitation = text.includes('invit');
        const hasExistingValue = (document.getElementById('event_date')?.value || '').trim() !== '';
        eventDateWrap.hidden = !(isInvitation || hasExistingValue);
    };
    if (categorySelect && eventDateWrap) {
        categorySelect.addEventListener('change', syncEventDateVisibility);
        syncEventDateVisibility();
    }

    // 3. Interactive Accountability Donut Chart Hover Readout
    const donutSvg = document.getElementById('accountabilityDonut');
    const donutVal = document.getElementById('donutCenterValue');
    const donutLbl = document.getElementById('donutCenterLabel');
    if (donutSvg && donutVal && donutLbl) {
        const defaultVal = donutVal.textContent;
        const defaultLbl = donutLbl.textContent;
        donutSvg.querySelectorAll('.lmo-donut-slice').forEach((slice) => {
            slice.addEventListener('mouseenter', () => {
                donutVal.textContent = slice.getAttribute('data-donut-pct') || defaultVal;
                donutLbl.textContent = slice.getAttribute('data-donut-count') + ' events';
            });
            slice.addEventListener('mouseleave', () => {
                donutVal.textContent = defaultVal;
                donutLbl.textContent = defaultLbl;
            });
        });
    }

    // 4. Register Secure Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(`${APP_BASE_URL}/service-worker.js`).catch(() => {});
    }

    // 5. PWA Install Button Handling
    let deferredInstallPrompt = null;
    const installBtn = document.getElementById('pwaInstallBtn');
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredInstallPrompt = e;
        if (installBtn) installBtn.classList.remove('d-none');
    });
    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (!deferredInstallPrompt) return;
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            installBtn.classList.add('d-none');
        });
    }

    // 6. Real-Time Duplicate Document Check
    const refInput = document.getElementById('official_reference');
    const orgInput = document.getElementById('organization');
    const titleInput = document.getElementById('title');
    const dupAlert = document.getElementById('duplicateWarningBox');
    const dupList = document.getElementById('duplicateWarningList');

    let dupTimer = null;
    const triggerDuplicateCheck = () => {
        if (!refInput || !dupAlert || !dupList) return;
        const refVal = refInput.value.trim();
        const orgVal = orgInput ? orgInput.value.trim() : '';
        const titleVal = titleInput ? titleInput.value.trim() : '';

        if (refVal.length < 2 && titleVal.length < 5) {
            dupAlert.classList.add('d-none');
            return;
        }

        clearTimeout(dupTimer);
        dupTimer = setTimeout(async () => {
            try {
                const params = new URLSearchParams({
                    official_reference: refVal,
                    organization: orgVal,
                    title: titleVal,
                });
                const res = await fetch(`${APP_BASE_URL}/documents/check-duplicate?${params.toString()}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.has_duplicates && data.duplicates.length > 0) {
                    dupList.innerHTML = data.duplicates
                        .map(
                            (d) =>
                                `<li><strong>${d.internal_reference}</strong> (${d.official_reference || 'No ref'}) &mdash; ${d.title} [${d.organization}] <a href="${d.url}" target="_blank" class="ms-1 fw-semibold">Open</a></li>`
                        )
                        .join('');
                    dupAlert.classList.remove('d-none');
                } else {
                    dupAlert.classList.add('d-none');
                }
            } catch (_) {}
        }, 350);
    };

    if (refInput) refInput.addEventListener('input', triggerDuplicateCheck);
    if (orgInput) orgInput.addEventListener('input', triggerDuplicateCheck);

    // 7. Multi-Page Mobile Camera Scanner & Live Stream Capture
    const startCameraBtn = document.getElementById('startCameraStreamBtn');
    const stopCameraBtn = document.getElementById('stopCameraStreamBtn');
    const snapPageBtn = document.getElementById('snapCameraPageBtn');
    const videoEl = document.getElementById('cameraVideoPreview');
    const cameraPanel = document.getElementById('liveCameraPanel');
    const capturedPagesContainer = document.getElementById('capturedPagesContainer');
    const hiddenCapturesWrapper = document.getElementById('hiddenCameraInputs');
    const mobileNativeCameraInput = document.getElementById('mobileNativeCameraInput');

    let mediaStream = null;
    const capturedPages = [];

    const renderCapturedPages = () => {
        if (!capturedPagesContainer || !hiddenCapturesWrapper) return;
        capturedPagesContainer.innerHTML = '';
        hiddenCapturesWrapper.innerHTML = '';

        capturedPages.forEach((dataUrl, idx) => {
            const pageNum = idx + 1;
            const col = document.createElement('div');
            col.className = 'col-6';
            col.innerHTML = `
                <div class="rounded p-2 position-relative" style="background: var(--surface); border: 1px solid var(--line);">
                    <span class="lmo-chip st-filed position-absolute top-0 start-0 m-2" style="background: var(--surface);">Page ${pageNum}</span>
                    <button type="button" class="lmo-btn sm danger position-absolute top-0 end-0 m-2" data-remove-page="${idx}" title="Remove page" style="padding: 2px 7px; min-height: 26px;">
                        <i class="bi bi-x"></i>
                    </button>
                    <img src="${dataUrl}" class="img-fluid rounded" style="max-height:130px; width:100%; object-fit:cover;" alt="Scanned Page ${pageNum}">
                </div>
            `;
            capturedPagesContainer.appendChild(col);

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'camera_captures[]';
            hiddenInput.value = dataUrl;
            hiddenCapturesWrapper.appendChild(hiddenInput);
        });

        capturedPagesContainer.querySelectorAll('[data-remove-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const removeIdx = parseInt(btn.getAttribute('data-remove-page'), 10);
                capturedPages.splice(removeIdx, 1);
                renderCapturedPages();
            });
        });
    };

    if (startCameraBtn && videoEl && cameraPanel) {
        startCameraBtn.addEventListener('click', async () => {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (mobileNativeCameraInput) mobileNativeCameraInput.click();
                return;
            }
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' }, width: { ideal: 1600 }, height: { ideal: 1200 } },
                    audio: false,
                });
                videoEl.srcObject = mediaStream;
                await videoEl.play();
                cameraPanel.classList.remove('d-none');
            } catch (_) {
                if (mobileNativeCameraInput) mobileNativeCameraInput.click();
            }
        });
    }

    if (stopCameraBtn && cameraPanel) {
        stopCameraBtn.addEventListener('click', () => {
            if (mediaStream) {
                mediaStream.getTracks().forEach((t) => t.stop());
                mediaStream = null;
            }
            cameraPanel.classList.add('d-none');
        });
    }

    if (snapPageBtn && videoEl) {
        snapPageBtn.addEventListener('click', () => {
            const canvas = document.createElement('canvas');
            canvas.width = videoEl.videoWidth || 1280;
            canvas.height = videoEl.videoHeight || 960;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
            capturedPages.push(dataUrl);
            renderCapturedPages();
        });
    }

    if (mobileNativeCameraInput) {
        mobileNativeCameraInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files || []);
            files.forEach((file) => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    if (ev.target && ev.target.result) {
                        capturedPages.push(ev.target.result);
                        renderCapturedPages();
                    }
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // 8. File Upload Selection Preview & Drag-and-Drop
    const fileInput = document.getElementById('documentFilesInput');
    const selectedFilesList = document.getElementById('selectedFilesPreviewList');
    const dropzone = document.getElementById('documentDropzone');

    const updateFilePreview = () => {
        if (!fileInput || !selectedFilesList) return;
        const files = Array.from(fileInput.files || []);
        if (files.length === 0) {
            selectedFilesList.innerHTML =
                '<p class="muted mb-0 mt-2" style="font-size: 13.5px;">No scan added yet. You can also save now and add the scan later.</p>';
            return;
        }
        selectedFilesList.innerHTML = files
            .map((f, i) => {
                const sizeMb = (f.size / 1048576).toFixed(2);
                const icon = f.type === 'application/pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image';
                return `<div class="lmo-file-item">
                    <div class="th"><i class="bi ${icon}"></i></div>
                    <div style="min-width: 0;">
                        <div class="fw-semibold text-truncate">${f.name}</div>
                        <div class="muted" style="font-size: 12.5px;">File #${i + 1} &middot; ${sizeMb} MB</div>
                    </div>
                    <span class="lmo-chip st-action">Ready</span>
                </div>`;
            })
            .join('');
    };

    if (fileInput) {
        fileInput.addEventListener('change', updateFilePreview);
    }

    if (dropzone && fileInput) {
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                updateFilePreview();
            }
        });
    }
});
