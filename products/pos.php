<?php include 'sidebar.php'; ?>
<?php
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>
<?php
// Fetch the products based on category and search term
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Database connection
include 'db_connection.php'; // Your DB connection file

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare SQL query for products based on category and search term
$sql = "SELECT * FROM products WHERE name LIKE '%$search%' ";
if ($category != 'all') {
    $sql .= "AND category = '$category'";
}

$result = $conn->query($sql);

// Fetch products
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch seller information from the staff table
$sellers = [];
$sql_sellers = "SELECT * FROM staff"; // Assuming staff table contains id and name fields
$seller_result = $conn->query($sql_sellers);
if ($seller_result->num_rows > 0) {
    while ($row = $seller_result->fetch_assoc()) {
        $sellers[] = $row;
    }
}

// Close connection
$conn->close();
?>

<script>
    let products = <?php echo json_encode($products); ?>; // Make PHP products available in JS
    let sellers = <?php echo json_encode($sellers); ?>; // Make PHP sellers available in JS
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- Load html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


    <style>
       /* Reset & Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f7f9fc;
    display: flex;
    min-height: 100vh;
    color: #333;
}
.main-content {
    margin-left: 250px;
    padding: 30px;
    flex: 1;
}

/* Search & Category Filter */
.search-bar {
    width: 60%;
    padding: 12px 15px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
    margin-bottom: 20px;
}
.category-buttons {
    margin: 20px 0;
}
.category-buttons button {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    margin-right: 10px;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
}
.category-buttons button:hover {
    background-color: #0056b3;
}

/* Product Display */
.product-table {
    width: 60%;
    border-collapse: collapse;
    background-color: white;
}

.product-table th, .product-table td {
    text-align: center;
    vertical-align: middle;
    padding: 10px;
    border: 1px solid #ddd;
}

.product-table img {
    max-width: 80px;
    height: auto;
    border-radius: 8px;
}

.add-to-cart {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.add-to-cart:hover {
    background-color: #218838;
}


/* Cart Styling */
.cart {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 500px;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 10px;
    z-index: 1000;
}
.cart h3 {
    margin-bottom: 15px;
    font-size: 20px;
    color: #007bff;
}
.cart-table {
    width: 100%;
    border-collapse: collapse;
}
.cart-table th, .cart-table td {
    padding: 10px;
    border: 1px solid #eee;
    text-align: center;
}
.cart-table th {
    background-color: #007bff;
    color: #fff;
}
.cart-table td button {
    background-color: #dc3545;
    border: none;
    padding: 5px 10px;
    color: #fff;
    border-radius: 5px;
    cursor: pointer;
}
.cart-total {
    margin-top: 15px;
    font-weight: bold;
    text-align: right;
}

/* Input Fields */
.cart input, .cart select {
    width: 100%;
    padding: 8px;
    margin-top: 6px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* Buttons */
.complete-sale {
    width: 100%;
    background-color: #007bff;
    color: #fff;
    padding: 12px;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 15px;
}
.complete-sale:hover {
    background-color: #0056b3;
}

/* Receipt Styling */
#receipt {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    width: 400px;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    display: none;
    z-index: 2000;
}

#receipt h2 {
    text-align: center;
    color: #007bff;
    margin-bottom: 10px;
}
#receipt p {
    text-align: center;
    font-size: 14px;
    color: #666;
}
#receipt table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
#receipt th, #receipt td {
    border: 1px solid #ddd;
    padding: 6px;
}
#receipt .total {
    font-weight: bold;
    text-align: right;
    margin-top: 10px;
}
.download-btn {
    display: block;
    background-color: #28a745;
    color: white;
    padding: 10px;
    text-align: center;
    border: none;
    border-radius: 5px;
    margin: 20px auto 0;
    cursor: pointer;
}
.download-btn:hover {
    background-color: #218838;
}

/* Modal Styling */
.modal,
.modal-overlay {
    display: none;
}
.modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    width: 60%;
    max-width: 600px;
    padding: 25px;
    border-radius: 10px;
    z-index: 1001;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    background-color: rgba(0, 0, 0, 0.5);
    width: 100%;
    height: 100%;
    z-index: 1000;
}
.modal .close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #ff4d4d;
    color: #fff;
    border: none;
    border-radius: 50%;
    padding: 5px 10px;
    cursor: pointer;
}
.modal .close-btn:hover {
    background: #ff1a1a;
}

    </style>
</head>
<body>

    <!-- Include Sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <!-- Search Bar -->
        <input type="text" id="search-bar" class="search-bar" placeholder="Search products..." value="<?= $search ?>">
        <button onclick="window.location.href='manage_products.php';" class="btn btn-primary" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px;">
    Manage Products
</button>


        <!-- Category Buttons -->
        <div class="category-buttons">
            <button onclick="filterCategory('all')">All</button>
            <button onclick="filterCategory('food')">Food</button>
            <button onclick="filterCategory('drinks')">Drinks</button>
            <button onclick="filterCategory('services')">Services</button>
            <button onclick="filterCategory('amenities')">Amenities</button>
        </div>

        <table class="product-table" id="product-list">
    <thead>
        <tr>
            <th>Name</th>
            <th>Image</th>
            <th>Description</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
            <tr class="product-item" data-id="<?= $product['id'] ?>">
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td>
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </td>
                <td><?= nl2br(htmlspecialchars($product['description'])) ?></td>
                <td>Ksh<?= number_format($product['price'], 2) ?></td>
                <td><?= (int) $product['quantity'] ?></td>
                <td>
                    <button class="add-to-cart" data-id="<?= $product['id'] ?>">Add to Cart</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </div>

    <!-- Cart Section -->
    <div class="cart">
    <h3>Shopping Cart</h3>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="cart-items">
            <!-- Cart items will be populated here -->
        </tbody>
    </table>
    <div class="cart-total">
        Total: Ksh<span id="cart-total">0.00</span>
    </div>
    <div>
        <label for="seller">Select Seller:</label>
        <select id="seller">
            <?php foreach ($sellers as $seller): ?>
                <option value="<?= $seller['id'] ?>"><?= $seller['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Client Information -->
    <div>
        <label for="client_name">Client Name:</label>
        <input type="text" id="client_name" placeholder="Enter client name">
    </div>

    <div>
        <label for="client_phone">Client Phone Number:</label>
        <input type="text" id="client_phone" placeholder="Enter client phone number">
    </div>

    <!-- Payment Method -->
    <div>
        <label for="payment_method">Payment Method:</label>
        <select id="payment_method">
            <option value="bank">Bank</option>
            <option value="cash">Cash</option>
            <option value="mobile">Mobile Transfer</option>
        </select>
    </div>

    <!-- Amount Paid and Balance -->
    <div>
        <label for="amount_paid">Amount Paid:</label>
        <input type="number" id="amount_paid" placeholder="Enter amount paid">
    </div>

    <div>
        <label for="balance">Balance:</label>
        <span id="balance">0.00</span>
    </div>

    <!-- Complete Sale Button -->
    <button class="complete-sale">Complete Sale</button>
<!-- Receipt Section -->
<div id="receipt" class="receipt">
    <h2>Grand Horizon Hotel</h2>
    <p>123 Ocean View Ave, Paradise City</p>
    <p>Phone: +123 456 7890 | Email: info@grandhorizon.com</p>
    <hr>

    <h3>Receipt</h3>
    <p><strong>Receipt No:</strong> <span id="receipt-order-id">00123</span></p>
    <p><strong>Date:</strong> <span id="receipt-date">2025-04-11</span></p>
    <p><strong>Seller:</strong> <span id="receipt-seller">John Doe</span></p>
    <p><strong>Client:</strong> <span id="receipt-client">Jane Smith</span></p>
    <p><strong>Phone:</strong> <span id="receipt-client-phone">+987 654 3210</span></p>
    <p><strong>Payment Method:</strong> <span id="receipt-payment-method">Credit Card</span></p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;" border="1">
        <thead>
            <tr>
                <th style="padding: 5px;">Product</th>
                <th style="padding: 5px;">Qty</th>
                <th style="padding: 5px;">Price</th>
                <th style="padding: 5px;">Subtotal</th>
            </tr>
        </thead>
        <tbody id="receipt-items">
            <tr>
                <td style="padding: 5px;">Room Service</td>
                <td style="padding: 5px;">2</td>
                <td style="padding: 5px;">50</td>
                <td style="padding: 5px;">100</td>
            </tr>
        </tbody>
    </table>

    <p><strong>Total:</strong> Ksh<span id="receipt-total">100</span></p>
    <p><strong>Amount Paid:</strong> Ksh<span id="receipt-amount-paid">100</span></p>
    <p><strong>Balance:</strong> Ksh<span id="receipt-balance">0</span></p>

    <!-- PDF Download Button -->
    <button onclick="downloadStyledPDF()" class="download-btn no-print">Download as Styled PDF</button>
</div>
<div id="success-message" style="display: none; color: green; margin-top: 10px;">
    ✅ Sale completed successfully!
</div>

<script>
        async function downloadStyledPDF() {
        const { jsPDF } = window.jspdf;

        const receipt = document.getElementById("receipt");

        // Use html2canvas to capture the receipt
        html2canvas(receipt, {
            scale: 1, // Higher scale = better resolution
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');

            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'px',
                format: [canvas.width, canvas.height]
            });

            pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
            pdf.save("receipt.pdf");
        });
    }
</script>


<script>
// JavaScript to dynamically calculate the balance as soon as the amount is entered
document.getElementById('amount_paid').addEventListener('input', function() {
    const amountPaid = parseFloat(this.value) || 0; // Get the value of 'Amount Paid' or 0 if empty
    const cartTotal = parseFloat(document.getElementById('cart-total').textContent) || 0; // Get the current cart total

    // Calculate the balance: Amount Paid - Cart Total
    const balance = amountPaid - cartTotal;

    // Update the balance field
    document.getElementById('balance').textContent = balance.toFixed(2); // Display with 2 decimal places
});
//success message on sale


        // Function to add products to cart
        function addToCart(product) {
            const existingProduct = cart.find(item => item.id === product.id);
            if (existingProduct) {
                existingProduct.quantity += 1;
            } else {
                product.quantity = 1;
                cart.push(product);
            }
            updateCart();
            saveCartToLocalStorage();
        }

        let cart = [];
        let currentCategory = 'all';
        let seller = "";
        let client = "";

        // Function to filter products by category
        function filterCategory(category) {
    window.location.href = `?category=${category}&search=${document.getElementById('search-bar').value}`;
}


        // Function to search products by name
        document.getElementById('search-bar').addEventListener('input', function() {
    const searchQuery = this.value;
    window.location.href = `?category=${currentCategory}&search=${searchQuery}`;
});


        // Function to add products to cart
        function addToCart(product) {
            const existingProduct = cart.find(item => item.id === product.id);
            if (existingProduct) {
                existingProduct.quantity += 1;
            } else {
                product.quantity = 1;
                cart.push(product);
            }
            updateCart();

        }

        // Update the cart display
        function updateCart() {
            const cartItemsDiv = document.getElementById('cart-items');
            cartItemsDiv.innerHTML = '';
            let total = 0;

            cart.forEach((item, index) => {
                const cartItemRow = document.createElement('tr');
                cartItemRow.innerHTML = `
                    <td>${item.name}</td>
                    <td>Ksh${item.price}</td>
                    <td>
                        <button onclick="changeQuantity(${index}, 'decrease')">-</button>
                        ${item.quantity}
                        <button onclick="changeQuantity(${index}, 'increase')">+</button>
                    </td>
                    <td><button onclick="removeFromCart(${index})">Remove</button></td>
                `;
                cartItemsDiv.appendChild(cartItemRow);
                total += item.price * item.quantity;
            });

            document.getElementById("cart-total").textContent = total.toFixed(2);

        }
    

        // Change the quantity of an item in the cart
        function changeQuantity(index, action) {
            if (action === 'increase') {
                cart[index].quantity += 1;
            } else if (action === 'decrease' && cart[index].quantity > 1) {
                cart[index].quantity -= 1;
            }
            updateCart();
        }

        // Remove product from cart
        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
        }

        // Function to handle "Add to Cart" button clicks
        function handleAddToCartClick(event) {
            const productId = event.target.getAttribute('data-id');
            const product = products.find(p => p.id == productId);
            if (product) {
                addToCart(product);
                updateCart();

            }
           
        }

// Function to handle "Complete Sale" button click
document.querySelector('.complete-sale').addEventListener('click', function() {
    // Get the selected seller ID from the dropdown
    const sellerId = document.getElementById("seller").value;

    // Other fields
    const clientName = document.getElementById("client_name").value;
    const clientPhone = document.getElementById("client_phone").value;
    const paymentMethod = document.getElementById("payment_method").value;
    const amountPaid = parseFloat(document.getElementById("amount_paid").value);
    const total = parseFloat(document.getElementById("cart-total").textContent);
    const balance = amountPaid - total;

    // Validate the form
    if (!clientName || !clientPhone || isNaN(amountPaid) || cart.length === 0) {
        alert("Please fill in all required fields and make sure the cart is not empty.");
        return;
    }

    if (amountPaid < total) {
        alert("Amount paid is less than the total.");
        return;
    }

    // Send order data to backend to save the order
    fetch('save_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                seller_id: sellerId,
                client_name: clientName,
                client_phone: clientPhone,
                payment_method: paymentMethod,
                amount_paid: amountPaid,
                balance: balance,
                total: total,
                items: cart.map(item => ( {
                    id: item.id,
                    quantity: item.quantity,
                    price: item.price
                }))
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const orderId = data.order_id;
                generateReceipt(clientName, clientPhone, paymentMethod, amountPaid, orderId, sellerId);
            } else {
                alert("Order failed: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while saving the order.");
        });
    });

    function generateReceipt(clientName, clientPhone, paymentMethod, amountPaid, orderId, sellerId) {
    const receiptDiv = document.getElementById("receipt");
    const receiptItems = document.getElementById("receipt-items");
    const total = parseFloat(document.getElementById("cart-total").textContent).toFixed(2);
    const balance = (amountPaid - total).toFixed(2);

    // Set values in receipt
    document.getElementById("receipt-order-id").textContent = orderId;
    document.getElementById("receipt-seller").textContent = sellers.find(s => s.id == sellerId).name;
    document.getElementById("receipt-client").textContent = clientName;
    document.getElementById("receipt-client-phone").textContent = clientPhone;
    document.getElementById("receipt-payment-method").textContent = paymentMethod;
    document.getElementById("receipt-amount-paid").textContent = amountPaid.toFixed(2);
    document.getElementById("receipt-balance").textContent = balance;
    document.getElementById("receipt-total").textContent = total;
    document.getElementById("receipt-date").textContent = new Date().toLocaleString();

    // Build item rows
    receiptItems.innerHTML = '';
    cart.forEach(item => {
        let price = parseFloat(item.price);
        if (isNaN(price)) {
            price = 0; // Set to zero if price is invalid
        }
        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="padding: 5px;">${item.name}</td>
            <td style="padding: 5px;">${item.quantity}</td>
            <td style="padding: 5px;">Ksh${price.toFixed(2)}</td>
            <td style="padding: 5px;">Ksh${(price * item.quantity).toFixed(2)}</td>
        `;
        receiptItems.appendChild(row);
    });

    // Show the receipt
    receiptDiv.style.display = "block";
}
// Attach event listener for "Add to Cart" buttons
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', handleAddToCartClick);
});
</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Attach event listeners to "Add to Cart" buttons
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', handleAddToCartClick);
        });
        document.getElementById('complete-sale').addEventListener('click', function() {
    // Here, you'd typically also handle form submission or sending data to the backend.
    
    // Show the success message
    const message = document.getElementById('success-message');
    message.style.display = 'block';

    // Optional: hide it after 3 seconds
    setTimeout(() => {
        message.style.display = 'none';
    }, 3000);
});

    </script>
</body>
</html>