@extends('master')

@section('title', 'Detail Dokumen | ModalRakyat')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Sidebar -->
        @include('sidebar')

        <!-- Content -->
        <div class="main-content">
            <a href="/dashboard/admin" class="btn-back">← Kembali ke Dashboard</a>
            
            <h1 class="page-title">Detail Dokumen KYC</h1>
            <p class="page-desc">Periksa dan verifikasi dokumen pengguna.</p>

            <div class="detail-container">
                <!-- Info Pengguna -->
                <div class="user-info-card">
                    <h3>Informasi Pengguna</h3>
                    <div class="info-row">
                        <span class="label">Nama:</span>
                        <span class="value" id="userName">Loading...</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email:</span>
                        <span class="value" id="userEmail">Loading...</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Jenis Dokumen:</span>
                        <span class="value" id="docType">Loading...</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Tanggal Upload:</span>
                        <span class="value" id="uploadDate">Loading...</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Status:</span>
                        <span class="value" id="docStatus">Loading...</span>
                    </div>
                </div>

                <!-- Preview Dokumen -->
                <div class="document-preview">
                    <h3>Preview Dokumen</h3>
                    <div id="previewContainer">
                        <p class="loading-text">Memuat dokumen...</p>
                    </div>
                </div>

                <!-- Form Verifikasi -->
                <div class="verification-form">
                    <h3>Verifikasi Dokumen</h3>
                    
                    <div class="button-group">
                        <button id="btnVerify" class="btn-verify">✓ Verifikasi</button>
                        <button id="btnReject" class="btn-reject">✗ Tolak</button>
                    </div>

                    <div id="rejectReasonBox" class="reject-reason-box" style="display: none;">
                        <label for="rejectReason">Alasan Penolakan:</label>
                        <textarea id="rejectReason" rows="4" placeholder="Jelaskan alasan penolakan dokumen..."></textarea>
                        <div class="button-group">
                            <button id="btnConfirmReject" class="btn-danger">Konfirmasi Tolak</button>
                            <button id="btnCancelReject" class="btn-secondary">Batal</button>
                        </div>
                    </div>

                    <div id="messageBox" class="message-box" style="display: none;"></div>
                </div>

                <!-- Tombol Simpan -->
                <div class="save-section">
                    <button id="btnSave" class="btn-save">💾 Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-back {
    display: inline-block;
    margin-bottom: 20px;
    color: #4a8bff;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
}

.btn-back:hover {
    text-decoration: underline;
}

.detail-container {
    max-width: 1000px;
}

.user-info-card {
    background: #1b1b1b;
    padding: 25px;
    border-radius: 18px;
    margin-bottom: 25px;
}

.user-info-card h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #eaeaea;
    font-size: 18px;
}

.info-row {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #2b2b2b;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    font-weight: 600;
    width: 180px;
    color: #bbbbbb;
}

.info-row .value {
    flex: 1;
    color: #eaeaea;
}

.document-preview {
    background: #1b1b1b;
    padding: 25px;
    border-radius: 18px;
    margin-bottom: 25px;
}

.document-preview h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #eaeaea;
    font-size: 18px;
}

#previewContainer {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #2b2b2b;
    border-radius: 14px;
}

#previewContainer img {
    max-width: 100%;
    height: auto;
    border-radius: 14px;
}

.loading-text {
    color: #888;
}

.error-text {
    color: #ff6262;
}

.verification-form {
    background: #1b1b1b;
    padding: 25px;
    border-radius: 18px;
    margin-bottom: 25px;
}

.verification-form h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #eaeaea;
    font-size: 18px;
}

.button-group {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.btn-verify, .btn-reject, .btn-danger, .btn-secondary {
    flex: 1;
    padding: 14px 20px;
    border: none;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-verify {
    background: #00ff99;
    color: #0f0f0f;
}

.btn-verify:hover {
    background: #00dd88;
}

.btn-reject {
    background: #ff6262;
    color: white;
}

.btn-reject:hover {
    background: #dd4444;
}

.btn-danger {
    background: #ff6262;
    color: white;
}

.btn-danger:hover {
    background: #dd4444;
}

.btn-secondary {
    background: #2b2b2b;
    color: #eaeaea;
}

.btn-secondary:hover {
    background: #3b3b3b;
}

.reject-reason-box {
    margin-top: 20px;
}

.reject-reason-box label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #eaeaea;
}

.reject-reason-box textarea {
    width: 100%;
    padding: 14px;
    border: none;
    background: #2b2b2b;
    color: #eaeaea;
    border-radius: 14px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    resize: vertical;
    margin-bottom: 15px;
    box-sizing: border-box;
}

.reject-reason-box textarea::placeholder {
    color: #888;
}

.message-box {
    padding: 15px;
    border-radius: 14px;
    margin-top: 20px;
    font-size: 14px;
}

.message-box.success {
    background: #00ff9940;
    color: #00ff99;
    border: 1px solid #00ff99;
}

.message-box.error {
    background: #ff626240;
    color: #ff6262;
    border: 1px solid #ff6262;
}

.save-section {
    background: #1b1b1b;
    padding: 25px;
    border-radius: 18px;
}

.btn-save {
    width: 100%;
    padding: 16px;
    background: #0e57c2;
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-save:hover {
    background: #093f8a;
}

.btn-save:disabled {
    background: #2b2b2b;
    color: #888;
    cursor: not-allowed;
}

.badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge.pending {
    background: #ffb53440;
    color: #ffb534;
}

.badge.success {
    background: #00ff9940;
    color: #00ff99;
}

.badge.danger {
    background: #ff626240;
    color: #ff6262;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const pathParts = window.location.pathname.split('/');
    const uploadId = pathParts[pathParts.length - 1];

    let uploadData = null;
    let downloadUrl = null;
    let hasChanges = false;

    // Fetch detail dokumen
    try {
        const res = await fetch(`/api/admin/uploads/${uploadId}/detail`, {
            credentials: 'same-origin'
        });

        if (!res.ok) {
            throw new Error('Gagal memuat data');
        }

        const data = await res.json();
        uploadData = data.upload;
        downloadUrl = data.download_url;

        console.log('Upload data:', uploadData);
        console.log('Download URL:', downloadUrl);

        // Tampilkan info pengguna
        document.getElementById('userName').textContent = uploadData.user_name;
        document.getElementById('userEmail').textContent = uploadData.user_email;
        document.getElementById('docType').textContent = uploadData.type.toUpperCase();
        
        // Format tanggal dengan timezone yang benar
        const uploadDate = new Date(uploadData.created_at);
        document.getElementById('uploadDate').textContent = uploadDate.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            timeZone: 'Asia/Jakarta'
        });

        // Status badge
        updateStatusBadge(uploadData.status);

        // Preview dokumen
        const previewContainer = document.getElementById('previewContainer');
        if (downloadUrl) {
            const img = document.createElement('img');
            img.src = downloadUrl;
            img.alt = 'Preview Dokumen';
            img.onerror = function() {
                previewContainer.innerHTML = '<p class="error-text">Tidak dapat memuat preview (file mungkin PDF atau format tidak didukung)</p>';
            };
            previewContainer.innerHTML = '';
            previewContainer.appendChild(img);
        } else {
            previewContainer.innerHTML = '<p class="error-text">Tidak dapat memuat preview dokumen</p>';
        }

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('previewContainer').innerHTML = 
            '<p class="error-text">Gagal memuat dokumen</p>';
    }

    function updateStatusBadge(status) {
        const statusEl = document.getElementById('docStatus');
        let statusHTML = '';
        if (status === 'pending') {
            statusHTML = '<span class="badge pending">Pending</span>';
        } else if (status === 'verified') {
            statusHTML = '<span class="badge success">Verified</span>';
        } else {
            statusHTML = '<span class="badge danger">Rejected</span>';
        }
        statusEl.innerHTML = statusHTML;
    }

    // Handler tombol Verifikasi
    document.getElementById('btnVerify').addEventListener('click', () => {
        hasChanges = true;
        uploadData.status = 'verified';
        updateStatusBadge('verified');
        showMessage('Status diubah ke Verified. Klik "Simpan Perubahan" untuk menyimpan.', 'success');
    });

    // Handler tombol Tolak
    document.getElementById('btnReject').addEventListener('click', () => {
        document.getElementById('rejectReasonBox').style.display = 'block';
    });

    // Handler batal tolak
    document.getElementById('btnCancelReject').addEventListener('click', () => {
        document.getElementById('rejectReasonBox').style.display = 'none';
        document.getElementById('rejectReason').value = '';
    });

    // Handler konfirmasi tolak
    document.getElementById('btnConfirmReject').addEventListener('click', () => {
        const reason = document.getElementById('rejectReason').value.trim();

        if (!reason) {
            alert('Mohon isi alasan penolakan');
            return;
        }

        hasChanges = true;
        uploadData.status = 'rejected';
        uploadData.notes = reason;
        updateStatusBadge('rejected');
        document.getElementById('rejectReasonBox').style.display = 'none';
        showMessage('Status diubah ke Rejected. Klik "Simpan Perubahan" untuk menyimpan.', 'success');
    });

    // Handler tombol Simpan
    document.getElementById('btnSave').addEventListener('click', async () => {
        if (!hasChanges) {
            alert('Tidak ada perubahan untuk disimpan');
            return;
        }

        if (!confirm('Apakah Anda yakin ingin menyimpan perubahan?')) {
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                             document.querySelector('input[name="_token"]')?.value || '';
            
            const res = await fetch(`/api/admin/uploads/${uploadId}/verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    status: uploadData.status,
                    notes: uploadData.notes || ''
                })
            });

            if (!res.ok) {
                const errorData = await res.json();
                throw new Error(errorData.message || 'Gagal menyimpan perubahan');
            }

            const result = await res.json();
            hasChanges = false;

            showMessage(result.message || 'Perubahan berhasil disimpan!', 'success');

            // Redirect setelah 2 detik
            setTimeout(() => {
                window.location.href = '/dashboard/admin';
            }, 2000);

        } catch (error) {
            console.error('Save error:', error);
            showMessage('Gagal menyimpan perubahan: ' + error.message, 'error');
        }
    });

    function showMessage(text, type) {
        const msgBox = document.getElementById('messageBox');
        msgBox.style.display = 'block';
        msgBox.className = 'message-box ' + type;
        msgBox.textContent = text;
    }
});
</script>

@endsection