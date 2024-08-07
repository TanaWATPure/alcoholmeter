<?php
// เชื่อมต่อกับฐานข้อมูล
include_once "../db.php";

// สร้างตัวแปรสำหรับเก็บข้อมูลที่ดึงมา
$data = [];

// ดึงข้อมูลจากตาราง alcohol
$sql = "SELECT a.id, a.rfid, a.alcoholvalue, u.name, u.identification, a.updated_at 
        FROM alcohol AS a
        INNER JOIN users AS u ON a.rfid = u.rfid";$result = $conn->query($sql);

if ($result === false) {
    // แสดงข้อความข้อผิดพลาด
    echo "Error: " . $conn->error;
} else {
    if ($result->num_rows > 0) {
        // เก็บข้อมูลแต่ละแถวในตัวแปร data
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alcohol Data Chart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="https://i.ibb.co/stFW6JB/removebg-preview.png" type="image/png">
    <style>
        #myChart, #myBarChart, #myLineChart {
            max-width: 500px;
            max-height: 500px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>ตารางแสดงแผนภูมิ</h1>
        <a href="../admin" class="btn btn-secondary mb-3">กลับ</a>

        <div class="row">
            <div class="col-md-4">
                <canvas id="myChart"></canvas>
            </div>
            <div class="col-md-4">
                <canvas id="myBarChart"></canvas>
            </div>
            <div class="col-md-4">
                <canvas id="myLineChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // ฟังก์ชันสำหรับอัปเดตกราฟวงกลม
            function updatePieChart(chart) {
                $.ajax({
                    url: '../api',
                    method: 'GET',
                    success: function(data) {
                        var lowCount = 0;
                        var mediumCount = 0;
                        var highCount = 0;

                        data.forEach(function(row) {
                            if (row.alcoholvalue <= 20) {
                                lowCount++;
                            } else if (row.alcoholvalue <= 50) {
                                mediumCount++;
                            } else {
                                highCount++;
                            }
                        });

                        chart.data.datasets[0].data = [lowCount, mediumCount, highCount];
                        chart.update();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            // ฟังก์ชันสำหรับอัปเดตกราฟแท่ง
            function updateBarChart(chart) {
                $.ajax({
                    url: '../api',
                    method: 'GET',
                    success: function(data) {
                        var alcoholValues = [];
                        var labels = [];

                        data.forEach(function(row) {
                            alcoholValues.push(row.alcoholvalue);
                            labels.push(row.updated_at); 
                        });

                        chart.data.labels = labels.slice(-10); // แสดงข้อมูลล่าสุด 10 รายการ
                        chart.data.datasets[0].data = alcoholValues.slice(-10);
                        chart.update();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            // ฟังก์ชันสำหรับอัปเดตกราฟเส้น
            function updateLineChart(chart) {
                $.ajax({
                    url: '../api',
                    method: 'GET',
                    success: function(data) {
                        var alcoholValues = [];
                        var labels = [];

                        data.forEach(function(row) {
                            alcoholValues.push(row.alcoholvalue);
                            labels.push(row.updated_at);
                        });

                        chart.data.labels = labels.slice(-10); // แสดงข้อมูลล่าสุด 10 รายการ
                        chart.data.datasets[0].data = alcoholValues.slice(-10);
                        chart.update();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            }

            // สร้างกราฟวงกลม
            var ctx = document.getElementById('myChart').getContext('2d');
            var pieData = {
                labels: ['ระดับต่ำ', 'ระดับปานกลาง', 'ระดับสูง'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    hoverBackgroundColor: ['#218838', '#ffb300', '#dc2128']
                }]
            };

            var pieOptions = {
                responsive: true,
                maintainAspectRatio: false
            };

            var pieChart = new Chart(ctx, {
                type: 'pie',
                data: pieData,
                options: pieOptions
            });

            // สร้างกราฟแท่ง
            var barCtx = document.getElementById('myBarChart').getContext('2d');
            var barData = {
                labels: [],
                datasets: [{
                    label: 'ระดับแอลกอฮอล์รวม',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            };

            var barOptions = {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            };

            var barChart = new Chart(barCtx, {
                type: 'bar',
                data: barData,
                options: barOptions
            });

            // สร้างกราฟเส้น
            var lineCtx = document.getElementById('myLineChart').getContext('2d');
            var lineData = {
                labels: [],
                datasets: [{
                    label: 'ระดับแอลกอฮอล์สูง',
                    data: [],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 1,
                    fill: true
                }]
            };

            var lineOptions = {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            };

            var lineChart = new Chart(lineCtx, {
                type: 'line',
                data: lineData,
                options: lineOptions
            });

            // อัปเดตกราฟทุก ๆ 5 วินาที (5000 มิลลิวินาที)
            setInterval(function() {
                updatePieChart(pieChart);
                updateBarChart(barChart);
                updateLineChart(lineChart);
            }, 5000);

            // อัปเดตกราฟเมื่อโหลดหน้าเว็บครั้งแรก
            updatePieChart(pieChart);
            updateBarChart(barChart);
            updateLineChart(lineChart);
        });
    </script>
</body>

</html>