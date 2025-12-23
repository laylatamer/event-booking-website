<!-- Dashboard Section -->
<div id="dashboard-section" class="section-content">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <p class="stat-label">Total Users</p>
                    <h3 class="stat-value"><?php echo number_format($totalUsers ?? 0); ?></h3>
                </div>
                <div class="stat-icon users-icon">
                    <i data-feather="users"></i>
                </div>
            </div>
            <div class="stat-trend positive">
                <i data-feather="trending-up"></i>
                <span>Active Accounts</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <p class="stat-label">Total Events</p>
                    <h3 class="stat-value"><?php echo number_format($totalEvents ?? 0); ?></h3>
                </div>
                <div class="stat-icon events-icon">
                    <i data-feather="calendar"></i>
                </div>
            </div>
            <div class="stat-trend positive">
                <i data-feather="trending-up"></i>
                <span>Active Events</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <p class="stat-label">Total Bookings</p>
                    <h3 class="stat-value"><?php echo number_format($totalBookings ?? 0); ?></h3>
                </div>
                <div class="stat-icon bookings-icon">
                    <i data-feather="ticket"></i>
                </div>
            </div>
            <div class="stat-trend positive">
                <i data-feather="trending-up"></i>
                <span>Lifetime Bookings</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <p class="stat-label">Revenue</p>
                    <h3 class="stat-value">$<?php echo number_format($totalRevenue ?? 0, 2); ?></h3>
                </div>
                <div class="stat-icon revenue-icon">
                    <i data-feather="dollar-sign"></i>
                </div>
            </div>
            <div class="stat-trend positive">
                <i data-feather="trending-up"></i>
                <span>Total Revenue</span>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="content-card">
        <div class="card-header">
            <h3>Recent Bookings</h3>
            <button class="view-all-btn">View All</button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Event</th>
                        <th>User</th>
                        <th>Date</th>
                        <th>Tickets</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentBookings)): ?>
                        <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($booking['booking_code'] ?? $booking['id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['event_title'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($booking['user_email'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                            <td><?php echo $booking['ticket_count'] ?? 1; ?></td>
                            <td>$<?php echo number_format($booking['final_amount'] ?? 0, 2); ?></td>
                            <td>
                                <?php 
                                    $statusClass = 'pending';
                                    if ($booking['status'] == 'confirmed') $statusClass = 'completed';
                                    if ($booking['status'] == 'cancelled') $statusClass = 'cancelled';
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">No recent bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-grid">
        <div class="content-card">
            <h3>Bookings Overview (Last 7 Days)</h3>
            <div class="chart-container" style="position: relative; height:300px; width:100%">
                <canvas id="bookingsChart"></canvas>
            </div>
        </div>
        <div class="content-card">
            <h3>Revenue by Category</h3>
            <div class="chart-container" style="position: relative; height:300px; width:100%">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bookings Chart
    const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
    new Chart(bookingsCtx, {
        type: 'line',
        data: {
            labels: <?php echo $chartLabels ?? '[]'; ?>,
            datasets: [{
                label: 'Bookings',
                data: <?php echo $chartValues ?? '[]'; ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo $revLabels ?? '[]'; ?>,
            datasets: [{
                data: <?php echo $revValues ?? '[]'; ?>,
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#4facfe',
                    '#00f2fe',
                    '#ff9a9e'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

