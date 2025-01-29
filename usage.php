<?php
include("db_config.php");
session_start();
if(!isset($_SESSION["user_id"]))
{
    header('Location: login.php');
    exit;
}
else{
    $customerID = $_SESSION['user_id'];
    $sql_usage="SELECT * FROM Cust_Usage";
    $stmt_usage=$conn->prepare($sql_usage);
    $stmt_usage->execute();
    $result_usage=$stmt_usage->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="static/css/style.css">
        <style>
                    table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px;
                border-radius: 10px;
            }

            td {
                padding: 8px;
                text-align: left;
            }
            th {
                text-align: center;
                padding: 8px;
                background-color:rgb(247, 181, 1);
            }
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            thead:first-child tr:first-child th:first-child {
                border-radius: 10px 0 0 0;

            }

            thead:first-child tr:last-child th:last-child {
                border-radius: 0 10px 0 0;

            }
            tbody tr:nth-child(even) td:last-child{
                background-color: #ffffff;
            }
            /* Add a wrapper for horizontal scrolling */
            .table-responsive {
                overflow-x: auto; /* Allow horizontal scrolling on smaller screens */
                margin: 0 auto;
            }

            /* Adjust for smaller screens */
            @media screen and (max-width: 768px) {
                table {
                    font-size: 14px; /* Reduce font size */
                }

                td, th {
                    padding: 6px; /* Reduce padding */
                }

                th {
                    font-size: 16px; /* Larger font size for headers */
                }
            }

            @media screen and (max-width: 480px) {
                table {
                    font-size: 12px;
                }

                td, th {
                    padding: 4px;
                }
            }
        </style>
    </head>
    <body>
        <?php
        include("header.html");
        ?>

        <div class="usage_container">
        <h1>Your Usage</h1>
             <div class="table_responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Used Minutes</th>
                            <th>Used SMS</th>
                            <th>Used Data(GB)</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($usage = $result_usage->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo(htmlspecialchars($usage['UsedMinutes']));?></td>
                            <td><?php echo(htmlspecialchars($usage['UsedSMS']));?></td>
                            <td><?php echo(htmlspecialchars($usage['UsedData']));?></td>
                            <td><?php echo(htmlspecialchars($usage['UsageDate']));?></td>
                        </tr>
                        <?php endwhile?>
                    </tbody>
                </table>
            </div>
            <div class="simulation_container">
        <button><a href="simulate_usage.php">Simulate</a></button>
        </div>
        </div>

        

    </body>
</html>