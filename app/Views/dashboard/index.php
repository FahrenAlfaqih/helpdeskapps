<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
  <h1 class="text-2xl font-semibold mb-6">Dashboard</h1>

  <?php if ($roleId == 6): // Requestor 
  ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="max-w-7xl bg-blue-50 rounded-2xl p-6 shadow flex items-center space-x-6">
        <!-- Icon Tiket -->
        <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2-2 4 4m1-10a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-3" />
          </svg>
        </div>

        <!-- Data Text -->
        <div class="flex flex-col">
          <span class="text-3xl font-extrabold text-blue-700"><?= esc($totalIT) ?></span>
          <span class="text-sm font-medium text-blue-600 mt-1">Total Tiket Tujuan IT</span>
        </div>
      </div>


      <div class="max-w-7xl bg-blue-50 rounded-2xl p-6 shadow flex items-center space-x-6">
        <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2-2 4 4m1-10a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-3" />
          </svg>
        </div>

        <div class="flex flex-col">
          <span class="text-3xl font-extrabold text-blue-700"><?= esc($totalGA) ?></span>
          <span class="text-sm font-medium text-blue-600 mt-1">Total Tiket Tujuan GA</span>
        </div>
      </div>

    </div>

    <div class="max-w-7xl bg-white rounded-2xl p-6 shadow max-w-3xl">
      <h2 class="text-xl font-semibold mb-4">Status Tiket Anda</h2>
      <canvas id="statusChart" height="150"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('statusChart').getContext('2d');
  const statusData = <?= json_encode($statusCounts) ?>;

  const labels = statusData.map(item => item.status);
  const data = statusData.map(item => item.total);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Jumlah Tiket',
        data: data,
        backgroundColor: 'rgba(34,197,94,0.7)',
        borderColor: 'rgba(21,128,61,1)',
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
</script>

<?php elseif ($roleId == 1): 
?>
  <div class="bg-blue-100 rounded-2xl p-6 shadow max-w-xl">
    <h2 class="text-xl font-semibold mb-4">Total Tiket Seluruh Sistem</h2>
    <p class="text-5xl font-bold text-blue-800"><?= esc($totalTickets) ?></p>
  </div>


<?php else: 
?>
  <p>Dashboard  belum tersedia.</p>
<?php endif; ?>

<?= $this->endSection() ?>