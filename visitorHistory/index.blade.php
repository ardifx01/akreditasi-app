<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kunjungan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Data Kunjungan</h1>

        <!-- Form Filter Tahun -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="from_year" class="form-label">Dari Tahun</label>
                    <input type="number" name="from_year" id="from_year" class="form-control" value="{{ request('from_year', date('Y') - 1) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="to_year" class="form-label">Sampai Tahun</label>
                    <input type="number" name="to_year" id="to_year" class="form-control" value="{{ request('to_year', date('Y')) }}" required>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <!-- Diagram Batang -->
        <div class="card mt-4">
            <div class="card-header">
                Diagram Batang Jumlah Kunjungan
            </div>
            <div class="card-body">
                <canvas id="barChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Siapkan data untuk chart -->
<script>
    const chartData = {
        labels: @json($results->pluck('year')),
        datasets: [{
            label: 'Jumlah Kunjungan',
            data: @json($results->pluck('total_visits')),
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };
</script>

    <script>
        const ctx = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Kunjungan',
                        },
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan dan Tahun',
                        },
                    },
                },
            },
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>