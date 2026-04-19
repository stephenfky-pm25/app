<?php
require '../../_base.php';
//-------------------------------------------------------------

$days = $_GET['days'] ?? 7;
$orders = $_db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$products = $_db->query("SELECT COUNT(*) FROM product")->fetchColumn();
$customers = $_db->query("SELECT COUNT(*) FROM user")->fetchColumn();
$sales = $_db->query("
    SELECT SUM(po.quantity * p.price)
    FROM orders o
    JOIN product_order po ON o.o_id = po.o_id
    JOIN product p ON po.p_id = p.p_id
    WHERE o.status = 'paid'
")->fetchColumn() ?? 0;

$population = $_db->query("
    SELECT 
        CASE 
            WHEN HOUR(o.datetime) BETWEEN 6 AND 11 THEN 'Morning'
            WHEN HOUR(o.datetime) BETWEEN 12 AND 17 THEN 'Afternoon'
            WHEN HOUR(o.datetime) BETWEEN 18 AND 23 THEN 'Evening'
            ELSE 'Night'
        END as period,
        COUNT(*) as total
    FROM orders o
    GROUP BY period
    ORDER BY 
        CASE 
            WHEN period = 'Morning' THEN 1
            WHEN period = 'Afternoon' THEN 2
            WHEN period = 'Evening' THEN 3
            WHEN period = 'Night' THEN 4
        END
")->fetchAll();

$rating = $_db->query("
    SELECT 
        AVG(rate) as avg_rate,
        MAX(rate) as max_rate,
        MIN(rate) as min_rate
    FROM orders
    WHERE rate IS NOT NULL
    AND status = 'paid'
")->fetch();

$states = [];
$counts = [];

foreach ($population as $p) {
    $states[] = $p->period;
    $counts[] = $p->total;
}
//-------------------------------------------------------------
$_title = 'Admin | Dashboard';
include '../../_adminhead.php';
include '../../_adminsidebar.php';

$data = $_db->query("
    SELECT DATE(o.datetime) as d, SUM(po.quantity * p.price) as total
    FROM orders o
    JOIN product_order po ON o.o_id = po.o_id
    JOIN product p ON po.p_id = p.p_id
    WHERE o.status = 'paid'
    AND o.datetime >= DATE_SUB(NOW(), INTERVAL $days DAY)
    GROUP BY d
    ORDER BY d
")->fetchAll();

$dates = [];
$totals = [];

foreach ($data as $row) {
    $dates[] = $row->d;
    $totals[] = $row->total;
}
?>

<h1> DASHBOARD</h1>
<style>
    .top-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center; 
    }

    .chart-header h3{
        margin: 0;
    }
    
    .card {
        background: linear-gradient(135deg, #d4fcd4, #e8fff6);
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }

    .card p {
        font-size: 24px;
        font-weight: bold;
    }

    .middle {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }

    .chart {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
    }
    .middle .chart{
        height: 300px;
        position: relative;  
    }

    .range-btns {
        margin-bottom: 10px;
    }

    .range-btns button {
        padding: 6px 12px;
        margin-right: 5px;
        border: none;
        background: #e8fff6;
        border-radius: 6px;
        cursor: pointer;
    }

    .range-btns a {
        padding: 6px 12px;
        margin-right: 5px;
        background: #e8fff6;
        border-radius: 6px;
        text-decoration: none;
        color: #333;
    }

    .range-btns a.active {
        background: #0b3d0b;
        color: white;
    }

    .range-btns button:hover {
        background: #c8f7dc;
    }

    .review {
        background: linear-gradient(135deg, #f8ffe9,  #ffd8a8 );
        padding: 20px;
        border-radius: 10px;

    }

    .bottom .chart{
        width: 600px;
        height: 350px;
        position: relative;
    }

    .bottom{
        margin-top: 50px;
    }

</style>

<div class="top-cards">
    <div class="card">
        <h4>Total Order</h4>
        <p><?= $orders ?></p>
    </div>

    <div class="card">
        <h4>Total Product</h4>
        <p><?= $products ?></p>
    </div>

    <div class="card">
        <h4>Total Customer</h4>
        <p><?= $customers ?></p>
    </div>

    <div class="card">
        <h4>Total Sales</h4>
        <p>RM <?= $sales ?? 0 ?></p>
    </div>
</div>

<div class="middle">
    <div class="chart">
        <div class="chart-header">
            <h3>Sales Trend</h3>
            <div class="range-btns">
                <a href="?days=7" class="<?= $days==7 ? 'active' : '' ?>">1 Week</a>
                <a href="?days=30" class="<?= $days==30 ? 'active' : '' ?>">1 Month</a>
                <a href="?days=365" class="<?= $days==365 ? 'active' : '' ?>">1 Year</a>
            </div>
        </div>
        <canvas id="salesChart"></canvas>
    </div>

    <div class="review">
        <h3>Review of the day</h3>
         <p>
            Average: <?= round($rating->avg_rate, 1) ?> 
            <?= str_repeat('⭐', round($rating->avg_rate)) ?>
        </p>

        <p>
            Highest: <?= $rating->max_rate ?> 
            <?= str_repeat('⭐', $rating->max_rate) ?>
        </p>

        <p>
            Lowest: <?= $rating->min_rate ?> 
            <?= str_repeat('⭐', $rating->min_rate) ?>
        </p>
    </div>
</div>

<div class="bottom">

    <div class="chart">
        <h3>Population Analysis</h3>
        <canvas id="popChart"></canvas>
    </div>
</div>

<?php
include '../../_adminfoot.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const salesCtx = document.getElementById('salesChart');

if (salesCtx) {
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Sales',
                data: <?= json_encode($totals) ?>,
                borderWidth: 2,
                tension: 0.3,
                borderColor: '#0b3d0b',
                backgroundColor: 'rgba(11,61,11,0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
}

const popCtx = document.getElementById('popChart');

if (popCtx) {
    new Chart(popCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($states) ?>,
            datasets: [{
                data: <?= json_encode($counts) ?>,
                backgroundColor: ['#84fab0','#fccb90','#a1c4fd','#f6d365']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            
        }
    });
}
</script>
