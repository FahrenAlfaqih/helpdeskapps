<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
  <h1 class="text-2xl font-semibold mb-6">Dashboard</h1>


  <div class="grid grid-cols-2 md:grid-cols-3 md:grid-cols-4 gap-6">
    <div class="bg-blue-50 rounded-2xl p-6 shadow flex items-center space-x-6">
      <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2-2 4 4m1-10a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-3" />
        </svg>
      </div>
      <div class="flex flex-col">
        <span class="text-3xl font-extrabold text-blue-700"><?= esc($totalTiketUser) ?></span>
        <span class="text-sm font-medium text-blue-600 mt-1">Total Tiket Diajukan</span>
      </div>
    </div>

    <!-- Card 1: Tiket ke Unit Finance (E13) -->
    <div class="bg-blue-50 rounded-2xl p-6 shadow flex items-center space-x-6">
      <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2-2 4 4m1-10a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-3" />
        </svg>
      </div>
      <div class="flex flex-col">
        <span class="text-3xl font-extrabold text-blue-700"><?= esc($totalTiketToUnitF) ?></span>
        <span class="text-sm font-medium text-blue-600 mt-1">Tiket Ke Unit Finance</span>
      </div>
    </div>

    <!-- Card 2: Tiket ke Unit GA (E21) -->
    <div class="bg-blue-50 rounded-2xl p-6 shadow flex items-center space-x-6">
      <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2-2 4 4m1-10a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-3" />
        </svg>
      </div>
      <div class="flex flex-col">
        <span class="text-3xl font-extrabold text-blue-700"><?= esc($totalTiketToUnitG) ?></span>
        <span class="text-sm font-medium text-blue-600 mt-1">Tiket Ke Unit GA</span>
      </div>
    </div>

    <div class="bg-blue-50 rounded-2xl p-6 shadow flex items-center space-x-6">
      <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2-2 4 4m1-10a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-3" />
        </svg>
      </div>
      <div class="flex flex-col">
        <span class="text-3xl font-extrabold text-blue-700"><?= esc($totalTiketUnresolved) ?></span>
        <span class="text-sm font-medium text-blue-600 mt-1">Tiket yang belum diselesaikan</span>
      </div>
    </div>
  </div>

  <div class="max-w-7xl bg-white rounded-2xl p-6 shadow max-w-3xl">
    <h2 class="text-xl font-semibold mb-4">Status Tiket Anda</h2>
    <canvas id="statusChart" height="150"></canvas>
  </div>

  <?php if (in_array($unit_kerja_id, ['E13', 'E21'])): ?>

    <div class="max-w-7xl bg-white rounded-2xl p-6 shadow max-w-3xl mt-10">
      <h2 class="text-xl font-semibold mb-4">Tiket Masuk per Bulan (<?= date('Y') ?>)</h2>
      <canvas id="monthlyChart" height="150"></canvas>
    </div>

    <div class="grid grid-cols-2 gap-6 mt-6">
      <div class="bg-green-100 rounded-xl p-6 shadow text-center">
        <h3 class="text-sm text-green-800 font-semibold">Rata-Rata Rating Waktu</h3>
        <p class="text-4xl font-bold text-green-900 mt-2"><?= esc($avgTime) ?></p>
      </div>
      <div class="bg-yellow-100 rounded-xl p-6 shadow text-center">
        <h3 class="text-sm text-yellow-800 font-semibold">Rata-Rata Rating Layanan</h3>
        <p class="text-4xl font-bold text-yellow-900 mt-2"><?= esc($avgService) ?></p>
      </div>
    </div>

  <?php endif; ?>


</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('statusChart').getContext('2d');

  const statusData = <?= json_encode($statusCounts) ?>;

  const labels = statusData.map(item => item.status);
  const data = statusData.map(item => item.total);

  const backgroundColors = {
    'Open': 'rgba(34, 113, 179, 0.7)',
    'In Progress': 'rgba(0, 123, 255, 0.6)',
    'Done': 'rgba(75, 92, 168, 0.7)',
    'Closed': 'rgba(21, 48, 103, 0.7)'
  };


  const colors = labels.map(status => backgroundColors[status]);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Jumlah Tiket Berdasarkan Status',
        data: data,
        backgroundColor: colors,
        borderColor: colors.map(color => color.replace('0.7', '1')), // Border yang lebih gelap
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          precision: 0,
          ticks: {
            stepSize: 1
          }
        }
      },
      plugins: {
        legend: {
          display: false
        }
      }
    }
  });

  const ctx2 = document.getElementById('monthlyChart').getContext('2d');
  const bulanLabels = <?= json_encode($bulanLabels) ?>;
  const jumlahTiketBulan = <?= json_encode($jumlahTiketBulan) ?>;

  new Chart(ctx2, {
    type: 'line',
    data: {
      labels: bulanLabels,
      datasets: [{
        label: 'Tiket Masuk',
        data: jumlahTiketBulan,
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      plugins: {
        legend: {
          display: false
        }
      }
    }
  });
</script>

<?= $this->endSection() ?>