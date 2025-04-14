document.addEventListener('DOMContentLoaded', function() {
    // Initialize POS functionality
    initPOS();

    function initPOS() {
        const categoryButtons = document.querySelectorAll('.category-btn');
        const productItems = document.querySelectorAll('.product-item');
        const cartItemsContainer = document.querySelector('.cart-items');
        const emptyCartMessage = document.querySelector('.empty-cart-message');
        const clearCartButton = document.getElementById('clear-cart');
        const completeSaleButton = document.getElementById('complete-sale');
        const paymentButtons = document.querySelectorAll('.payment-btn');
        const staffSelect = document.getElementById('staff-member');
        const customerNameInput = document.getElementById('customer-name');
        const roomNumberInput = document.getElementById('room-number');
        
        // Cart state
        let cart = [];
        let selectedPaymentMethod = null;

        // Filter products by category
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                
                // Update active button
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter products
                productItems.forEach(item => {
                    const itemCategory = item.getAttribute('data-category');
                    if (category === 'all' || itemCategory === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Add product to cart
        productItems.forEach(item => {
            item.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                const name = this.querySelector('h4').textContent;
                const price = parseFloat(this.getAttribute('data-price'));
                
                addToCart(id, name, price);
            });
        });

        // Add to cart function
        function addToCart(id, name, price) {
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id,
                    name,
                    price,
                    quantity: 1
                });
            }
            
            updateCart();
        }

        // Update cart display
        function updateCart() {
            if (cart.length === 0) {
                emptyCartMessage.style.display = 'flex';
                cartItemsContainer.innerHTML = '';
                completeSaleButton.disabled = true;
            } else {
                emptyCartMessage.style.display = 'none';
                
                let cartHTML = '';
                cart.forEach(item => {
                    cartHTML += `
                        <div class="cart-item" data-id="${item.id}">
                            <div class="cart-item-info">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-price">$${item.price.toFixed(2)} each</div>
                            </div>
                            <div class="cart-item-quantity">
                                <button class="quantity-btn decrease-quantity">-</button>
                                <input type="number" class="quantity-input" value="${item.quantity}" min="1">
                                <button class="quantity-btn increase-quantity">+</button>
                            </div>
                            <div class="cart-item-total">$${(item.price * item.quantity).toFixed(2)}</div>
                            <div class="cart-item-remove"><i class="fas fa-times"></i></div>
                        </div>
                    `;
                });
                
                cartItemsContainer.innerHTML = cartHTML;
                
                // Add event listeners to quantity buttons and remove buttons
                const decreaseButtons = document.querySelectorAll('.decrease-quantity');
                const increaseButtons = document.querySelectorAll('.increase-quantity');
                const quantityInputs = document.querySelectorAll('.quantity-input');
                const removeButtons = document.querySelectorAll('.cart-item-remove');
                
                decreaseButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const cartItem = this.closest('.cart-item');
                        const id = parseInt(cartItem.getAttribute('data-id'));
                        updateItemQuantity(id, -1);
                    });
                });
                
                increaseButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const cartItem = this.closest('.cart-item');
                        const id = parseInt(cartItem.getAttribute('data-id'));
                        updateItemQuantity(id, 1);
                    });
                });
                
                quantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const cartItem = this.closest('.cart-item');
                        const id = parseInt(cartItem.getAttribute('data-id'));
                        const quantity = parseInt(this.value);
                        
                        if (quantity < 1) {
                            this.value = 1;
                            return;
                        }
                        
                        setItemQuantity(id, quantity);
                    });
                });
                
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const cartItem = this.closest('.cart-item');
                        const id = parseInt(cartItem.getAttribute('data-id'));
                        removeFromCart(id);
                    });
                });
                
                completeSaleButton.disabled = !(cart.length > 0 && staffSelect.value && customerNameInput.value);
            }
            
            updateCartTotals();
        }

        // Update item quantity
        function updateItemQuantity(id, change) {
            const item = cart.find(item => item.id === id);
            
            if (item) {
                item.quantity += change;
                
                if (item.quantity < 1) {
                    item.quantity = 1;
                }
                
                updateCart();
            }
        }

        // Set item quantity
        function setItemQuantity(id, quantity) {
            const item = cart.find(item => item.id === id);
            
            if (item) {
                item.quantity = quantity;
                updateCart();
            }
        }

        // Remove item from cart
        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCart();
        }

        // Update cart totals
        function updateCartTotals() {
            const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
            const tax = subtotal * 0.1; // 10% tax
            const total = subtotal + tax;
            
            document.getElementById('cart-subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('cart-tax').textContent = `$${tax.toFixed(2)}`;
            document.getElementById('cart-total').textContent = `$${total.toFixed(2)}`;
        }

        // Clear cart
        clearCartButton.addEventListener('click', function() {
            cart = [];
            updateCart();
        });

        // Payment method selection
        paymentButtons.forEach(button => {
            button.addEventListener('click', function() {
                const method = this.getAttribute('data-method');
                
                // Update active button
                paymentButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                selectedPaymentMethod = method;
                
                // Enable complete sale button if all required fields are filled
                completeSaleButton.disabled = !(cart.length > 0 && staffSelect.value && customerNameInput.value);
            });
        });

        // Form input validation
        [staffSelect, customerNameInput].forEach(input => {
            input.addEventListener('input', function() {
                // Enable complete sale button if all required fields are filled
                completeSaleButton.disabled = !(cart.length > 0 && staffSelect.value && customerNameInput.value && selectedPaymentMethod);
            });
        });

        // Complete sale
        completeSaleButton.addEventListener('click', function() {
            if (cart.length === 0 || !staffSelect.value || !customerNameInput.value || !selectedPaymentMethod) {
                return;
            }
            
            // Show payment modal based on selected method
            const paymentModal = document.getElementById('payment-modal');
            const paymentModalTitle = document.getElementById('payment-modal-title');
            const cashPaymentForm = document.getElementById('cash-payment-form');
            const cardPaymentForm = document.getElementById('card-payment-form');
            const roomPaymentForm = document.getElementById('room-payment-form');
            const paymentTotal = document.getElementById('payment-total');
            
            // Set payment modal title and show appropriate form
            switch (selectedPaymentMethod) {
                case 'cash':
                    paymentModalTitle.textContent = 'Cash Payment';
                    cashPaymentForm.classList.remove('hidden');
                    cardPaymentForm.classList.add('hidden');
                    roomPaymentForm.classList.add('hidden');
                    break;
                case 'card':
                    paymentModalTitle.textContent = 'Card Payment';
                    cashPaymentForm.classList.add('hidden');
                    cardPaymentForm.classList.remove('hidden');
                    roomPaymentForm.classList.add('hidden');
                    break;
                case 'room':
                    paymentModalTitle.textContent = 'Room Charge';
                    cashPaymentForm.classList.add('hidden');
                    cardPaymentForm.classList.add('hidden');
                    roomPaymentForm.classList.remove('hidden');
                    
                    // Pre-fill room number if provided
                    if (roomNumberInput.value) {
                        document.getElementById('room-charge-number').value = roomNumberInput.value;
                    }
                    break;
            }
            
            // Set payment total
            const total = cart.reduce((total, item) => total + (item.price * item.quantity), 0) * 1.1; // Including 10% tax
            paymentTotal.textContent = `$${total.toFixed(2)}`;
            
            // Show payment modal
            paymentModal.classList.add('active');
            
            // Cash payment calculation
            const cashAmountInput = document.getElementById('cash-amount');
            const cashChangeDisplay = document.getElementById('cash-change');
            
            cashAmountInput.addEventListener('input', function() {
                const cashAmount = parseFloat(this.value) || 0;
                const change = cashAmount - total;
                
                if (change >= 0) {
                    cashChangeDisplay.textContent = `$${change.toFixed(2)}`;
                } else {
                    cashChangeDisplay.textContent = '$0.00';
                }
            });
            
            // Signature pad for room charge
            if (selectedPaymentMethod === 'room') {
                const canvas = document.querySelector('#signature-pad canvas');
                const clearButton = document.getElementById('clear-signature');
                const ctx = canvas.getContext('2d');
                
                // Set canvas dimensions
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
                
                let isDrawing = false;
                let lastX = 0;
                let lastY = 0;
                
                canvas.addEventListener('mousedown', startDrawing);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('mouseup', stopDrawing);
                canvas.addEventListener('mouseout', stopDrawing);
                
                // Touch events
                canvas.addEventListener('touchstart', startDrawingTouch);
                canvas.addEventListener('touchmove', drawTouch);
                canvas.addEventListener('touchend', stopDrawing);
                
                function startDrawing(e) {
                    isDrawing = true;
                    [lastX, lastY] = [e.offsetX, e.offsetY];
                }
                
                function draw(e) {
                    if (!isDrawing) return;
                    
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(e.offsetX, e.offsetY);
                    ctx.stroke();
                    
                    [lastX, lastY] = [e.offsetX, e.offsetY];
                }
                
                function startDrawingTouch(e) {
                    e.preventDefault();
                    const touch = e.touches[0];
                    const rect = canvas.getBoundingClientRect();
                    const offsetX = touch.clientX - rect.left;
                    const offsetY = touch.clientY - rect.top;
                    
                    isDrawing = true;
                    [lastX, lastY] = [offsetX, offsetY];
                }
                
                function drawTouch(e) {
                    if (!isDrawing) return;
                    e.preventDefault();
                    
                    const touch = e.touches[0];
                    const rect = canvas.getBoundingClientRect();
                    const offsetX = touch.clientX - rect.left;
                    const offsetY = touch.clientY - rect.top;
                    
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(offsetX, offsetY);
                    ctx.stroke();
                    
                    [lastX, lastY] = [offsetX, offsetY];
                }
                
                function stopDrawing() {
                    isDrawing = false;
                }
                
                clearButton.addEventListener('click', function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                });
            }
        });

        // Confirm payment
        const confirmPaymentButton = document.getElementById('confirm-payment');
        confirmPaymentButton.addEventListener('click', function() {
            const paymentModal = document.getElementById('payment-modal');
            
            // Create transaction object
            const transaction = {
                id: 'TRX-' + Date.now(),
                date: new Date().toISOString(),
                customer: customerNameInput.value,
                roomNumber: roomNumberInput.value || null,
                staff: {
                    id: staffSelect.value,
                    name: staffSelect.options[staffSelect.selectedIndex].text.split(' (')[0]
                },
                items: cart.map(item => ({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity
                })),
                subtotal: cart.reduce((total, item) => total + (item.price * item.quantity), 0),
                tax: cart.reduce((total, item) => total + (item.price * item.quantity), 0) * 0.1,
                total: cart.reduce((total, item) => total + (item.price * item.quantity), 0) * 1.1,
                paymentMethod: selectedPaymentMethod,
                synced: false
            };
            
            // Save transaction to IndexedDB
            hotelDB.addTransaction(transaction)
                .then(() => {
                    // Close payment modal
                    paymentModal.classList.remove('active');
                    
                    // Show receipt
                    showReceipt(transaction);
                    
                    // Clear cart
                    cart = [];
                    updateCart();
                    
                    // Clear form
                    customerNameInput.value = '';
                    roomNumberInput.value = '';
                    staffSelect.value = '';
                    selectedPaymentMethod = null;
                    paymentButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Update pending transactions count
                    updatePendingTransactionsCount();
                })
                .catch(error => {
                    console.error('Error saving transaction:', error);
                    alert('There was an error processing your transaction. Please try again.');
                });
        });

        // Show receipt
        function showReceipt(transaction) {
            const receiptModal = document.getElementById('receipt-modal');
            const receiptId = document.getElementById('receipt-id');
            const receiptDate = document.getElementById('receipt-date');
            const receiptCustomer = document.getElementById('receipt-customer');
            const receiptStaff = document.getElementById('receipt-staff');
            const receiptItemsBody = document.getElementById('receipt-items-body');
            const receiptSubtotal = document.getElementById('receipt-subtotal');
            const receiptTax = document.getElementById('receipt-tax');
            const receiptTotal = document.getElementById('receipt-total');
            
            // Set receipt details
            receiptId.textContent = transaction.id;
            receiptDate.textContent = new Date(transaction.date).toLocaleString();
            receiptCustomer.textContent = transaction.customer;
            receiptStaff.textContent = transaction.staff.name;
            
            // Set receipt items
            let itemsHTML = '';
            transaction.items.forEach(item => {
                itemsHTML += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>$${(item.price * item.quantity).toFixed(2)}</td>
                    </tr>
                `;
            });
            receiptItemsBody.innerHTML = itemsHTML;
            
            // Set receipt totals
            receiptSubtotal.textContent = `$${transaction.subtotal.toFixed(2)}`;
            receiptTax.textContent = `$${transaction.tax.toFixed(2)}`;
            receiptTotal.textContent = `$${transaction.total.toFixed(2)}`;
            
            // Show receipt modal
            receiptModal.classList.add('active');
            
            // Download receipt button
            const downloadReceiptButton = document.getElementById('download-receipt');
            downloadReceiptButton.onclick = function() {
                downloadReceipt(transaction);
            };
            
            // Print receipt button
            const printReceiptButton = document.getElementById('print-receipt');
            printReceiptButton.onclick = function() {
                printReceipt();
            };
        }

        // Download receipt as PDF
        function downloadReceipt(transaction) {
            const receiptContainer = document.getElementById('receipt-container');
            
            html2canvas(receiptContainer).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jspdf.jsPDF();
                
                // Calculate the PDF dimensions based on the canvas
                const imgWidth = 210; // A4 width in mm
                const pageHeight = 297; // A4 height in mm
                const imgHeight = canvas.height * imgWidth / canvas.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
                pdf.save(`receipt_${transaction.id}.pdf`);
            });
        }

        // Print receipt
        function printReceipt() {
            const receiptContainer = document.getElementById('receipt-container');
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>Receipt</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                            max-width: 400px;
                            margin: 0 auto;
                        }
                        .receipt-header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .receipt-info {
                            margin-bottom: 20px;
                        }
                        .receipt-items {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                        }
                        .receipt-items th, .receipt-items td {
                            padding: 8px;
                            text-align: left;
                            border-bottom: 1px solid #ddd;
                        }
                        .receipt-summary {
                            margin-bottom: 20px;
                        }
                        .summary-row {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 5px;
                        }
                        .total {
                            font-weight: bold;
                            font-size: 1.1em;
                            margin-top: 10px;
                            padding-top: 10px;
                            border-top: 1px dashed #ddd;
                        }
                        .receipt-footer {
                            text-align: center;
                            margin-top: 30px;
                            font-size: 0.9em;
                        }
                    </style>
                </head>
                <body>
                    ${receiptContainer.innerHTML}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }

        // Update pending transactions count
        function updatePendingTransactionsCount() {
            hotelDB.getUnsyncedTransactions()
                .then(transactions => {
                    const pendingCount = document.querySelector('.pending-count');
                    pendingCount.textContent = transactions.length;
                    
                    if (transactions.length > 0) {
                        pendingCount.style.display = 'flex';
                    } else {
                        pendingCount.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error getting unsynced transactions:', error);
                });
        }

        // Initialize pending transactions count
        updatePendingTransactionsCount();

        // Sync transactions button
        const syncTransactionsButton = document.getElementById('sync-transactions');
        syncTransactionsButton.addEventListener('click', function() {
            if (navigator.onLine) {
                syncTransactions();
            } else {
                showOfflineNotification();
            }
        });

        // Sync transactions with server
        function syncTransactions() {
            hotelDB.getUnsyncedTransactions()
                .then(transactions => {
                    if (transactions.length === 0) {
                        return;
                    }
                    
                    // In a real app, you would send the transactions to the server
                    // For this demo, we'll just mark them as synced after a delay
                    setTimeout(() => {
                        const transactionIds = transactions.map(transaction => transaction.id);
                        
                        hotelDB.markTransactionsSynced(transactionIds)
                            .then(() => {
                                updatePendingTransactionsCount();
                                showSyncNotification();
                            })
                            .catch(error => {
                                console.error('Error marking transactions as synced:', error);
                            });
                    }, 1500);
                })
                .catch(error => {
                    console.error('Error getting unsynced transactions:', error);
                });
        }

        // Show offline notification
        function showOfflineNotification() {
            const notification = document.getElementById('offline-notification');
            notification.classList.remove('hidden');
            
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 5000);
        }

        // Show sync notification
        function showSyncNotification() {
            const notification = document.getElementById('sync-notification');
            notification.classList.remove('hidden');
            
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 5000);
        }
    }
});