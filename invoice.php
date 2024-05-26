<?php
include '_config.php';
auth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invoiceNo = $_POST['invoice_no'] ?? '';
    $customerId = $_POST['customer_id'] ?? '';
    $invoiceDate = $_POST['invoice_date'] ?? '';
    $products = $_POST['products'] ?? [];
    $remarks = $_POST['remarks'] ?? '';
    $total = $_POST['total'] ?? '';

    try {
        if (empty($invoiceNo) || empty($customerId) || empty($invoiceDate) || empty($products) || empty($total)) {
            throw new Exception('Please fill all the required fields.');
        }

        // Validate products
        foreach ($products as $product) {
            $productId = $product['product_id'] ?? '';
            $quantity = $product['quantity'] ?? '';
            $rate = $product['rate'] ?? '';
            $amount = $product['amount'] ?? '';

            if (empty($productId) || empty($quantity) || empty($rate) || empty($amount)) {
                throw new Exception('Please fill all the required fields.');
            }
        }

        // Escape the form data
        $invoiceNo = mysqli_real_escape_string($conn, $invoiceNo);
        $customerId = mysqli_real_escape_string($conn, $customerId);
        $invoiceDate = mysqli_real_escape_string($conn, $invoiceDate);
        $remarks = mysqli_real_escape_string($conn, $remarks);
        $total = mysqli_real_escape_string($conn, $total);

        $conn->begin_transaction();

        // Insert the invoice
        $sql = "INSERT INTO invoices (invoice_no, customer_id, invoice_date, remarks, total) VALUES ('$invoiceNo', '$customerId', '$invoiceDate', '$remarks', '$total')";

        if (!mysqli_query($conn, $sql)) {
            throw new Exception('Error creating invoice.');
        }

        $invoiceId = mysqli_insert_id($conn);

        // Insert the invoice products
        foreach ($products as $product) {
            $productId = $product['product_id'] ?? '';
            $description = $product['description'] ?? '';
            $quantity = $product['quantity'] ?? '';
            $rate = $product['rate'] ?? '';
            $amount = $product['amount'] ?? '';

            $productId = mysqli_real_escape_string($conn, $productId);
            $description = mysqli_real_escape_string($conn, $description);
            $quantity = mysqli_real_escape_string($conn, $quantity);
            $rate = mysqli_real_escape_string($conn, $rate);
            $amount = mysqli_real_escape_string($conn, $amount);

            $sql = "INSERT INTO invoice_products (invoice_id, product_id, description, quantity, rate, amount) VALUES ('$invoiceId', '$productId', '$description', '$quantity', '$rate', '$amount')";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception('Error creating invoice.');
            }
        }

        $conn->commit();

        // Return the success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Invoice created successfully.'
        ]);
    } catch (\Throwable $th) {
        $conn->rollback();

        // Return the error response 403
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => $th->getMessage()
        ]);
    }
    exit();
}

// Get the customers
$sql = "SELECT * FROM customer_mst";
$result = mysqli_query($conn, $sql);
$customers = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get the products
$sql = "SELECT * FROM product_mst";
$result = mysqli_query($conn, $sql);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<?php include '_header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-12 pt-4">
            <form id="invoice" action="invoice.php" method="post">
                <div class="row">
                    <!-- Invoice no. -->
                    <div class="col-md-4 form-group pb-3">
                        <label for="invoice_no" class="required">Invoice No.</label>
                        <input type="text" id="invoice_no" name="invoice_no" class="form-control" required>
                    </div>
                    <!-- Customer -->
                    <div class="col-md-4 form-group pb-3">
                        <label for="customer" class="required">Customer</label>
                        <select id="customer_id" name="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $customer) : ?>
                                <option value="<?php echo $customer['cust_id']; ?>">
                                    <?php echo $customer['cust_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Invoice date -->
                    <div class="col-md-4 form-group pb-3">
                        <label for="invoice_date" class="required">Invoice Date</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="form-control" value="<?php echo date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-decoration-underline">Add new product</h6>
                    </div>
                    <div class="col-md-4 form-group mb-2">
                        <label for="product_id" class="required">Product</label>
                        <select id="product_id" name="product_id" class="form-control">
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product) : ?>
                                <option value="<?php echo $product['product_id']; ?>" data-stock="<?php echo $product['product_stock']; ?>" data-description="<?php echo $product['product_des']; ?>">
                                    <?php echo $product['product_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <textarea name="description" id="description" class="form-control mt-2" rows="1" placeholder="Product description" disabled></textarea>
                    </div>
                    <div class="col-md-2 col-4 form-group">
                        <label for="quantity" class="required">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" disabled>
                    </div>
                    <!-- Rate -->
                    <div class="col-md-2 col-4 form-group">
                        <label for="rate" class="required">Rate</label>
                        <input type="number" id="rate" name="rate" class="form-control" disabled>
                    </div>
                    <!-- Amount -->
                    <div class="col-md-2 col-4 form-group">
                        <label for="amount" class="required">Amount</label>
                        <input type="number" id="amount" name="amount" class="form-control" readonly disabled>
                    </div>
                    <!-- Add product -->
                    <div class="col-md-2 form-group">
                        <label>&nbsp;</label>
                        <button type="button" id="add_product" class="btn btn-primary form-control">Add</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 pt-4">
                        <h5 class="text-decoration-underline ">Products</h6>
                    </div>
                    <div class="col-12 d-md-block d-none">
                        <div class="row">
                            <div class="col-4">
                                <h6 class="text-decoration-underline">Product</h6>
                            </div>
                            <div class="col-2">
                                <h6 class="text-decoration-underline">Quantity</h6>
                            </div>
                            <div class="col-2">
                                <h6 class="text-decoration-underline">Rate</h6>
                            </div>
                            <div class="col-2">
                                <h6 class="text-decoration-underline">Amount</h6>
                            </div>
                            <div class="col-2">
                                <h6 class="text-decoration-underline">Action</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" id="products">

                    </div>
                </div>
                <div class="row pt-2">
                    <div class="col-md-4 mb-2">
                        <label for="remarks">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control"></textarea>
                    </div>
                    <div class="col-md-2 mb-2 offset-md-4">
                        <label for="total">Net Ammount</label>
                        <input type="number" id="total" name="total" class="form-control" value="0" readonly>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary mt-4 me-3">Save</button>
                        <button type="reset" class="btn btn-secondary mt-4">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
function scripts()
{
?>
    <script>
        var index = 0;

        function renderProduct(product_id, name, description, quantity, rate, amount) {

            return `
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="hidden" name="products[${index}][product_id]" class="form-control" value="${product_id}">
                        <input type="hidden" name="products[${index}][description]" class="form-control" value="${description}">
                        <h6>${name}</h6>
                        <h6 style="font-size: 12px">${description}</h6>
                    </div>
                    <div class="col-md-2 col-4">
                        <input type="hidden" name="products[${index}][quantity]" class="form-control" value="${quantity}">
                        <h6><span class="d-md-none">quantity: </span> ${quantity}</h6>
                    </div>
                    <div class="col-md-2 col-4">
                        <input type="hidden" name="products[${index}][rate]" class="form-control" value="${rate}">
                        <h6><span class="d-md-none">rate: </span> ${rate}</h6>
                    </div>
                    <div class="col-md-2 col-4">
                        <input type="hidden" name="products[${index++}][amount]" class="form-control amount" value="${amount}">
                        <h6><span class="d-md-none">amount: </span> ${amount}</h6>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm btn-remove w-100">Remove <span class="d-md-none">${name}</span></button>
                    </div>
                </div>
            `;
        }

        function updateTotal() {
            var total = 0;
            $('.amount').each(function() {
                total += parseInt($(this).val());
            });
            $('#total').val(total);
        }

        $(document).ready(function() {
            // Handle product change
            $('#product_id').change(function() {
                var product_id = $(this).val();
                if (product_id === '') {
                    $('#description').val('').prop('disabled', true);
                    $('#quantity').val('').prop('disabled', true);
                    $('#rate').val('').prop('disabled', true);
                    $('#amount').val('');
                    return;
                }
                var product_stock = $(this).find('option:selected').data('stock');
                var description = $(this).find('option:selected').data('description');

                $('#description').val(description).prop('disabled', false);
                $('#quantity').attr('max', product_stock).val('').prop('disabled', false);
                $('#rate').val('').prop('disabled', false);
                $('#amount').val('');
            });

            // Handle quantity and rate change
            $('#quantity, #rate').keyup(function() {
                var quantity = $('#quantity').val();
                var rate = $('#rate').val();

                $('#amount').val(quantity * rate);
            });

            // Handle remove product
            $(document).on('click', '.btn-remove', function() {
                $(this).closest('.row').remove();
                updateTotal();
            });

            $('#add_product').click(function() {
                var product_id = $('#product_id').val();
                var product_name = $('#product_id option:selected').text();
                var product_stock = $('#product_id option:selected').data('stock');
                var description = $('#product_id option:selected').data('description');
                var quantity = $('#quantity').val();
                var rate = $('#rate').val();
                var amount = $('#amount').val();

                if (product_id === '') {
                    alert('Please select a product.');
                    return;
                }

                if (quantity === '') {
                    alert('Please enter the quantity.');
                    return;
                }

                if (rate === '') {
                    alert('Please enter the rate.');
                    return;
                }

                if (parseInt(quantity) > parseInt(product_stock)) {
                    alert('Quantity cannot be greater than stock.');
                    return;
                }

                $('#products').append(renderProduct(product_id, product_name, description, quantity, rate, amount));

                $('#product_id').val('').trigger('change').focus();
                updateTotal();
            });

            // Handle form submission
            $('#invoice').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'invoice.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        response = JSON.parse(response);
                        alert(response.message);

                        $('#invoice')[0].reset();
                        $('#products').html('');
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
    </script>
<?php
}
?>

<?php include '_footer.php'; ?>