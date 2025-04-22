<?php
// Check if the cookies exist; if not, redirect to the login page
if (!isset($_COOKIE['employee_role']) || !isset($_COOKIE['employee_id'])) {
    header("Location: login.php");
    exit;
}

include_once("../www/header.php");

// API Configuration
define('API_BASE_URL', 'https://dev-clisson231.users.info.unicaen.fr/2eannee/DevBack/S401/controller/api.php');
define('API_KEY', 'e8f1997c763');

/**
 * Function to make API requests.
 *
 * @param string $url The API URL to fetch data from.
 * @param string $method The HTTP method to use (GET, POST, PUT, etc.).
 * @param array|null $data Optional data to send with the request.
 * @return array|null The decoded response from the API or null if the request failed.
 */
function fetchApiData($url, $method = 'GET', $data = null) {
    $options = [
        'http' => [
            'method' => $method,
            'header' => "Content-Type: application/json\r\n" . "X-Api-Key: " . API_KEY . "\r\n",
            'ignore_errors' => true
        ]
    ];
    
    // If data is provided, add it to the request body
    if ($data) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    return $response ? json_decode($response, true) : null;
}

// Retrieve the product ID from the URL
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$error = null;

if ($productId) {
    // Fetch product details from the API
    $product = fetchApiData(API_BASE_URL . '?product_id=' . $productId);
    if (!$product) {
        $error = "Product not found";
    }
}

// Fetch brands and categories
$brands = fetchApiData(API_BASE_URL . '?type=brands&fields=brand_id,brand_name');
$categories = fetchApiData(API_BASE_URL . '?type=categories&fields=category_id,category_name');

// Handle errors if brands or categories data cannot be fetched
if (!$brands || !$categories) {
    $error = "Failed to load required data from API";
}

// If an error occurred, display it and stop further execution
if ($error) {
    echo "<div class='alert alert-danger'>$error</div>";
    include_once("../www/footer.php");
    exit;
}
?>
<link rel="stylesheet" href="../css/edit.css">
<div class="container mt-4">
    <h1 class="mb-4"><?= $product ? "Edit Product" : "Add New Product" ?></h1>
    
    <div class="card">
        <div class="card-body">
            <form id="productForm">
                <input type="hidden" id="productId" name="productId" value="<?= htmlspecialchars($product['product_id'] ?? '') ?>">

                <div class="mb-3">
                    <label for="productName" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="productName" name="productName" 
                           value="<?= htmlspecialchars($product['product_name'] ?? '') ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="productBrand" class="form-label">Brand</label>
                        <select class="form-select" id="productBrand" name="productBrand" required>
                            <option value="">Select a brand</option>
                            <?php foreach ($brands as $brand) : ?>
                                <option value="<?= $brand['brand_id'] ?>" 
                                    <?= isset($product['brand_id']) && $product['brand_id'] == $brand['brand_id'] ? 'selected' : '' ?> >
                                    <?= htmlspecialchars($brand['brand_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="productCategory" class="form-label">Category</label>
                        <select class="form-select" id="productCategory" name="productCategory" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?= $category['category_id'] ?>" 
                                    <?= isset($product['category_id']) && $product['category_id'] == $category['category_id'] ? 'selected' : '' ?> >
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="productYear" class="form-label">Model Year</label>
                        <input type="number" class="form-control" id="productYear" name="productYear" 
                               value="<?= htmlspecialchars($product['model_year'] ?? date('Y')) ?>" 
                               min="2000" max="<?= date('Y') + 5 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="productPrice" class="form-label">Price ($)</label>
                            <input type="number" class="form-control" id="productPrice" name="productPrice" 
                                   value="<?= htmlspecialchars($product['list_price'] ?? '0.00') ?>" 
                                   step="0.01" min="0" required>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="products.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <?= $product ? "Save Changes" : "Add Product" ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * Waits for the document to load, then sets up form submission.
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('productForm');
    
    /**
     * Handles the form submission event.
     * Prevents default behavior, collects form data, 
     * and sends the data to the API.
     *
     * @param {Event} e The form submission event.
     */
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const isEdit = document.getElementById('productId').value !== '';
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        
        // Disable button during submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        
        const productData = {
            product_name: document.getElementById('productName').value,
            brand: document.getElementById('productBrand').value,
            category: document.getElementById('productCategory').value,
            model_year: document.getElementById('productYear').value,
            list_price: document.getElementById('productPrice').value
        };
        
        if (isEdit) {
            productData.id = document.getElementById('productId').value;
            productData.type = 'product_id';
            sendAjaxRequest('PUT', productData);
        } else {
            productData.action = 'product';
            sendAjaxRequest('POST', productData);
        }
        
        /**
         * Sends an AJAX request to the server with the product data.
         *
         * @param {string} method The HTTP method (POST or PUT).
         * @param {Object} data The data to send to the API.
         */
        function sendAjaxRequest(method, data) {
            const xhr = new XMLHttpRequest();
            xhr.open(method, '<?= API_BASE_URL ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Api-Key', '<?= API_KEY ?>');
            
            xhr.onload = function() {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                
                if (xhr.status >= 200 && xhr.status < 300) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showAlert('success', isEdit ? 'Product updated successfully!' : 'Product added successfully!');
                        setTimeout(() => window.location.href = 'products.php', 1500);
                    } else {
                        showAlert('danger', response.error || 'An error occurred');
                    }
                } else {
                    showAlert('danger', 'Request failed with status: ' + xhr.status);
                }
            };
            
            xhr.onerror = function() {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                showAlert('danger', 'Request failed');
            };
            
            xhr.send(JSON.stringify(data));
        }
        
        /**
         * Displays an alert message on the page.
         *
         * @param {string} type The type of alert (e.g., 'success', 'danger').
         * @param {string} message The message to display.
         */
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
            alertDiv.innerHTML = ` ${message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
            const container = document.querySelector('.container');
            container.prepend(alertDiv);
        }
    });
});
</script>

<?php include_once("../www/footer.php"); ?>
