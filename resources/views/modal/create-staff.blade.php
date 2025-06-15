<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addStaffModalLabel">Tambah Data Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Form -->
            <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data" id="formStaff">
                @csrf
                <div class="modal-body">
                    <!-- ID Staf -->
                    <div class="mb-3">
                        <label for="id_staf" class="form-label">ID Staf</label>
                        <input type="text" class="form-control" id="id_staf" name="id_staf" required>
                    </div>
                    <!-- Nama Staff -->
                    <div class="mb-3">
                        <label for="nama_staff" class="form-label">Nama Staff</label>
                        <input type="text" class="form-control" id="nama_staff" name="nama_staff" required>
                    </div>
                    <div class="mb-3">
                        <label for="posisi_staff" class="form-label">Posisi Staff</label>
                        <select name="posisi" id="posisi" required>
                            <option value="Pustakawan">Pustakawan</option>
                            <option value="Staff IT">Staff IT</option>
                            <option value="Teknik Pustakawan">Teknik Pustakawan</option>
                        </select>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="reset" class="btn btn-secondary" onclick="document.getElementById('addStaffModal').querySelector('.btn-close').click()">Clear</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>