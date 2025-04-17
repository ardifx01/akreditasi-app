@extends('layouts.app')

@section('title', 'Kunjungan Fakultas')

@section('content')
    <div class="row">
    <!-- Chart Data Kunjungan -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Fakultas Komunikasi dan Informatika</span>
                <button class="btn btn-sm btn-primary" onclick="exportChart('visitsChart')">
                    <i class="fas fa-download me-1"></i> Export Chart
                </button>
            </div>
            <div class="card-body">
                <canvas id="visitsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    // Contoh
    const visitsChartData = {
        labels: ['2020', '2021', '2022', '2023'],
        datasets: [{
            label: 'Jumlah Kunjungan FKI',
            data: [1200, 1500, 1800, 2000],
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };

    // Inisialisasi
    const visitsChart = new Chart(document.getElementById('visitsChart').getContext('2d'), {
        type: 'bar',
        data: visitsChartData,
        options: { responsive: true }
    });
</script>

{{-- export chart ke gambar --}}
<script>
function exportChart(chartId) {
    const canvas = document.getElementById(chartId);
    
    const exportCanvas = document.createElement('canvas');
    const exportContext = exportCanvas.getContext('2d');
    
    exportCanvas.width = canvas.width;
    exportCanvas.height = canvas.height;
    
    exportContext.fillStyle = '#ffffff';
    exportContext.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
    
    exportContext.drawImage(canvas, 0, 0);
    
    const link = document.createElement('a');
    link.download = 'chart-export.png';
    
    link.href = exportCanvas.toDataURL('image/png');

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection