<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
  
        <!-- Modal Header -->
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="exampleModalLabel">Tambah Data Ijazah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
  
        <!-- Form -->
        <form action="{{ route('ijazah.store') }}" method="POST" enctype="multipart/form-data" id="formIjazah">
          @csrf
          <div class="modal-body">
            <!-- ID Staf -->
            <div class="mb-3">
              <label for="id_staf" class="form-label">ID Staf</label>
              <input type="text" class="form-control" id="id_staf" name="id_staf" required>
            </div>
  
            <!-- Nama Ijazah -->
            <div class="mb-3">
              <label for="judul_ijazah" class="form-label">Nama Ijazah</label>
              <input type="text" class="form-control" id="judul_ijazah" name="judul_ijazah" required>
            </div>
  
            <!-- File Dokumen -->
            <div class="mb-3">
              <label for="file_dokumen" class="form-label">File Ijazah</label>
              <input type="text" class="form-control" id="file_dokumen" name="file_dokumen" required>
            </div>
  
            <!-- Tahun -->
            <div class="mb-3">
              <label for="tahun" class="form-label">Tahun</label>
              <input type="text" class="form-control" id="tahun" name="tahun" required>
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