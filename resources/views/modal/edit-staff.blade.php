<div class="modal fade" id="editStaff{{ $item->id }}" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editStaffModalLabel">Tambah Data Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Form -->
            <form action="{{ route('staff.update', $item->id) }}" method="POST" enctype="multipart/form-data" id="formStaff">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- ID Staf -->
                    <div class="mb-3">
                        <label for="id_staf" class="form-label">ID Staf</label>
                        <input type="text" class="form-control" id="id_staf" name="id_staf" value="{{ $item->id_staf }}" required>
                    </div>
                    <!-- Nama Staff -->
                    <div class="mb-3">
                        <label for="nama_staff" class="form-label">Nama Staff</label>
                        <input type="text" class="form-control" id="nama_staff" name="nama_staff" value="{{ $item->nama_staff }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="posisi_staff" class="form-label">Posisi Staff</label>
                        <select name="posisi" id="posisi" value="{{ $item->nama_staff }}" required>
                            <option value="Pustakawan">Pustakawan</option>
                            <option value="Staff IT">Staff IT</option>
                            <option value="Teknik Pustakawan">Teknik Pustakawan</option>
                        </select>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="reset" class="btn btn-secondary" onclick="document.getElementById('editStaff').querySelector('.btn-close').click()">Clear</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>