@extends('master')

@section('title', 'Dashboard User | ModalRakyat')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Sidebar User -->
        @include('sidebar-user')

        <!-- Content -->
        <div class="main-content">

            <h1 class="page-title">Dashboard User</h1>
            <p class="page-desc">Unggah dokumen Anda untuk proses verifikasi KYC.</p>

            <!-- Upload Card -->
            <div class="cards">
                <div class="card" style="grid-column: span 2;">
                    <h3>Upload Dokumen</h3>
                    <p class="page-desc">Format PDF, JPG, atau PNG.</p>

                    <form action="{{ route('upload.document') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <label for="type">Tipe Dokumen</label>
                            <select name="type" id="type" required>
                                <option value="KTP">KTP</option>
                                <option value="SlipGaji">Slip Gaji</option>
                            </select>
                        </div>
                        <input type="file" name="file" class="file-input" required>
                        <button type="submit" class="btn-upload" style="margin-top:12px;">
                            Upload
                         (Form)</button>
                        <button id="btn-presigned" type="button" class="btn-secondary">Upload via S3 (Presigned)</button>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <h2>Riwayat Upload Dokumen</h2>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uploads as $u)
                            <tr>
                                <td>{{ $u->file_name }}</td>

                                <td>
                                    @if($u->status == 'pending')
                                        <span class="badge pending">Pending</span>
                                    @elseif($u->status == 'verified')
                                        <span class="badge success">Verified</span>
                                    @else
                                        <span class="badge danger">Rejected</span>
                                    @endif
                                </td>

                                <td>{{ $u->created_at->format('d M Y') }}</td>

                                <td>
                                    <a href="/user/file/{{ $u->id }}" class="btn-download">Download</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada dokumen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    (function(){
        const presignedBtn = document.getElementById('btn-presigned');
        const fileInput = document.getElementById('file');
        const typeSelect = document.getElementById('type');

        function arrayBufferToHex(buffer) {
            const bytes = new Uint8Array(buffer);
            const hex = [];
            for (let b of bytes) {
                hex.push(b.toString(16).padStart(2, '0'));
            }
            return hex.join('');
        }

        async function computeHash(file) {
            const arrayBuffer = await file.arrayBuffer();
            const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
            return arrayBufferToHex(hashBuffer);
        }

        presignedBtn.addEventListener('click', async function(){
            if (!fileInput.files || !fileInput.files.length) {
                alert('Pilih file terlebih dahulu');
                return;
            }

            const file = fileInput.files[0];
            const type = typeSelect.value || 'KTP';

            // minta presigned url
            const resp = await fetch('/upload/presigned', { credentials: 'same-origin' });
            if (!resp.ok) {
                alert('Gagal mengambil presigned URL');
                return;
            }

            const data = await resp.json();
            const uploadUrl = data.upload_url;
            const fileName = data.file_name;

            // PUT ke S3
            const putResp = await fetch(uploadUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': file.type || 'image/jpeg'
                },
                body: file
            });

            if (!putResp.ok) {
                alert('Gagal mengunggah file ke penyimpanan');
                return;
            }

            // compute hash and register
            const hash = await computeHash(file);

            const token = document.querySelector('input[name="_token"]').value;
            const register = await fetch('/upload/validate', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    file_name: fileName,
                    size: file.size,
                    file_hash: hash,
                    type: type
                })
            });

            if (!register.ok) {
                alert('Gagal mendaftarkan upload di server');
                return;
            }

            alert('File berhasil diupload dan terdaftar');
            window.location.reload();
        });
    })();
</script>
@endsection
