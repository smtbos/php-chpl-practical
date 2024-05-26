<?php
include '_config.php';
auth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
}

// Get the customers
$sql = "SELECT * FROM customer_mst";
$result = mysqli_query($conn, $sql);
$customerRows = mysqli_fetch_all($result, MYSQLI_ASSOC);

$customers = [];
foreach ($customerRows as $customer) {
    $customers[$customer['cust_id']] = $customer['cust_name'];
}


// Get the invoices
$sql = "SELECT * FROM invoices";
$result = mysqli_query($conn, $sql);
$invoices = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<?php include '_header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-12 pt-4">
            <div class="row">
                <div class="col-6">
                    <h1>Invoices</h1>
                </div>
                <div class="col-6 text-end">
                    <a href="invoice.php" class="btn btn-primary">Add Invoice</a>
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Invoice No</th>
                        <th>Customer</th>
                        <th>Invoice Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice) : ?>
                        <tr>
                            <td><?php echo $invoice['id']; ?></td>
                            <td><?php echo $invoice['invoice_no']; ?></td>
                            <td><?php echo $customers[$invoice['customer_id']]; ?></td>
                            <td><?php echo $invoice['invoice_date']; ?></td>
                            <td><?php echo $invoice['total']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
function scripts()
{
?>
    <script>
        $(document).ready(function() {});
    </script>
<?php
}
?>

<?php include '_footer.php'; ?>