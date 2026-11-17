/**
 * LMO DRMS - Client-Side Application Logic
 * - PWA Service Worker Registration & Install Prompt
 * - Real-time Duplicate Reference Detection
 * - Multi-Page Mobile Camera Document Scanner
 * - Drag-and-Drop File Upload Preview & Page Ordering
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Register Secure Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js').catch(() => {});
    }

    // 2. PWA Install Button Handling
    let deferredInstallPrompt = null;
    const installBtn = document.getElementById('pwaInstallBtn');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredInstallPrompt = e;
        if (installBtn) {
            installBtn.classList.remove('d-none');
        }
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

    // 3. Real-Time Duplicate Document Check on Registration Form
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
                    title: titleVal
                });
                const res = await fetch(`/documents/check-duplicate?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.has_duplicates && data.duplicates.length > 0) {
                    dupList.innerHTML = data.duplicates.map(d =>
                        `<li><strong>${d.internal_reference}</strong> (Official Ref: <code>${d.official_reference || 'N/A'}</code>) — ${d.title} [${d.organization}] <a href="${d.url}" target="_blank" class="ms-1 fw-semibold">View Record</a></li>`
                    ).join('');
                    dupAlert.classList.remove('d-none');
                } else {
                    dupAlert.classList.add('d-none');
                }
            } catch (_) {}
        }, 350);
    };

    if (refInput) refInput.addEventListener('input', triggerDuplicateCheck);
    if (orgInput) orgInput.addEventListener('input', triggerDuplicateCheck);

    // 4. Multi-Page Mobile Camera Scanner & Live Stream Capture
    const startCameraBtn = document.getElementById('startCameraStreamBtn');
    const stopCameraBtn = document.getElementById('stopCameraStreamBtn');
    const snapPageBtn = document.getElementById('snapCameraPageBtn');
    const videoEl = document.getElementById('cameraVideoPreview');
    const cameraPanel = document.getElementById('liveCameraPanel');
    const capturedPagesContainer = document.getElementById('capturedPagesContainer');
    const hiddenCapturesWrapper = document.getElementById('hiddenCameraInputs');
    const mobileNativeCameraInput = document.getElementById('mobileNativeCameraInput');

    let mediaStream = null;
    let capturedPages = [];

    const renderCapturedPages = () => {
        if (!capturedPagesContainer || !hiddenCapturesWrapper) return;
        capturedPagesContainer.innerHTML = '';
        hiddenCapturesWrapper.innerHTML = '';

        capturedPages.forEach((dataUrl, idx) => {
            const pageNum = idx + 1;
            const col = document.createElement('div');
            col.className = 'col-6 col-md-3';
            col.innerHTML = `
                <div class="border rounded p-2 bg-white shadow-sm position-relative">
                    <span class="badge bg-dark position-absolute top-0 start-0 m-2">Page ${pageNum}</span>
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" data-remove-page="${idx}" title="Remove page">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <img src="${dataUrl}" class="img-fluid rounded" style="max-height:140px; width:100%; object-fit:cover;" alt="Scanned Page ${pageNum}">
                </div>
            `;
            capturedPagesContainer.appendChild(col);

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'camera_captures[]';
            hiddenInput.value = dataUrl;
            hiddenCapturesWrapper.appendChild(hiddenInput);
        });

        capturedPagesContainer.querySelectorAll('[data-remove-page]').forEach(btn => {
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
                    audio: false
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
                mediaStream.getTracks().forEach(t => t.stop());
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
            files.forEach(file => {
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

    // 5. File Upload Selection Preview
    const fileInput = document.getElementById('documentFilesInput');
    const selectedFilesList = document.getElementById('selectedFilesPreviewList');
    const dropzone = document.getElementById('documentDropzone');

    const updateFilePreview = () => {
        if (!fileInput || !selectedFilesList) return;
        const files = Array.from(fileInput.files || []);
        if (files.length === 0) {
            selectedFilesList.innerHTML = '';
            return;
        }
        selectedFilesList.innerHTML = files.map((f, i) => {
            const sizeKb = (f.size / 1024).toFixed(1);
            return `<div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-1 bg-white">
                <div><i class="bi bi-file-earmark-check text-primary me-2"></i><strong>#${i + 1}:</strong> ${f.name}</div>
                <span class="badge bg-light text-dark border">${sizeKb} KB</span>
            </div>`;
        }).join('');
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
