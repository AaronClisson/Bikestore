<?php
// Remove session_start() because we are using cookies
// Check if cookies are set for the user
$isEmployee = isset($_COOKIE['employee_role']) && isset($_COOKIE['employee_id']);
include_once("../www/header.php");
require_once("../bootstrap.php"); // Load Doctrine and DB connection

use Doctrine\ORM\EntityManager;
use Entity\Product;
use Entity\Brand;
use Entity\Category;

/**
 * Retrieves filters from the URL parameters or sets default values.
 *
 * @return array Associative array of filter parameters including brand, category, year, price, and page.
 */
function getFiltersFromUrl() {
    return [
        'brandFilter' => $_GET['brand'] ?? '',
        'categoryFilter' => $_GET['category'] ?? '',
        'yearFilter' => $_GET['year'] ?? '',
        'price' => $_GET['price'] ?? '',
        'page' => isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1
    ];
}

$filters = getFiltersFromUrl();
$brandFilter = $filters['brandFilter'];
$categoryFilter = $filters['categoryFilter'];
$yearFilter = $filters['yearFilter'];
$price = $filters['price'];
$page = $filters['page'];
$limit = 10; // Number of products per page
$offset = ($page - 1) * $limit; // Offset for pagination

// Doctrine query to retrieve products based on filters
$queryBuilder = $entityManager->createQueryBuilder();
$queryBuilder->select('p')->from(Product::class, 'p');

// Apply filters if provided
if ($brandFilter) {
    $queryBuilder->andWhere('p.brand = :brand')->setParameter('brand', $brandFilter);
}
if ($categoryFilter) {
    $queryBuilder->andWhere('p.category = :category')->setParameter('category', $categoryFilter);
}
if ($yearFilter) {
    $queryBuilder->andWhere('p.model_year = :year')->setParameter('year', $yearFilter);
}
if ($price) {
    $queryBuilder->andWhere('p.list_price >= :price')->setParameter('price', $price);
}

// Add pagination
$queryBuilder->setFirstResult($offset)->setMaxResults($limit);
$products = $queryBuilder->getQuery()->getResult();

// Retrieve the total number of products for pagination
try {
    // Create a new QueryBuilder for the count with the same conditions
    $countQueryBuilder = $entityManager->createQueryBuilder();
    $countQueryBuilder->select('COUNT(p.product_id)')->from(Product::class, 'p');
    
    // Apply the same filters as the main query
    if ($brandFilter) {
        $countQueryBuilder->andWhere('p.brand = :brand')->setParameter('brand', $brandFilter);
    }
    if ($categoryFilter) {
        $countQueryBuilder->andWhere('p.category = :category')->setParameter('category', $categoryFilter);
    }
    if ($yearFilter) {
        $countQueryBuilder->andWhere('p.model_year = :year')->setParameter('year', $yearFilter);
    }
    if ($price) {
        $countQueryBuilder->andWhere('p.list_price >= :price')->setParameter('price', $price);
    }
    
    $totalProducts = $countQueryBuilder->getQuery()->getSingleScalarResult();
} catch (Doctrine\ORM\NoResultException $e) {
    $totalProducts = 0;
}

$totalPages = ceil($totalProducts / $limit);

// Retrieve brands and categories for filter options
$brands = $entityManager->getRepository(Brand::class)->findAll();
$categories = $entityManager->getRepository(Category::class)->findAll();
?>
<link rel="stylesheet" href="../css/products.css">
<h1>Product List</h1>

<!-- Filter form to select filters -->
<form method="GET" id="filterForm">
    <label>Brand:</label>
    <select name="brand" onchange="this.form.submit()">
        <option value="">All</option>
        <?php foreach ($brands as $brand) : ?>
            <option value="<?= $brand->getBrandId() ?>" <?= ($brandFilter == $brand->getBrandId()) ? 'selected' : '' ?>><?= $brand->getBrandName() ?></option>
        <?php endforeach; ?>
    </select>

    <label>Category:</label>
    <select name="category" onchange="this.form.submit()">
        <option value="">All</option>
        <?php foreach ($categories as $category) : ?>
            <option value="<?= $category->getCategoryId() ?>" <?= ($categoryFilter == $category->getCategoryId()) ? 'selected' : '' ?>><?= $category->getCategoryName() ?></option>
        <?php endforeach; ?>
    </select>

    <label>Year:</label>
    <input type="text" name="year" value="<?= htmlspecialchars($yearFilter) ?>" placeholder="Enter Year" onchange="this.form.submit()" />

    <label>Price:</label>
    <input type="range" name="price" min="0" max="12000" value="<?= htmlspecialchars($price) ?>" step="50" onchange="this.form.submit()" />
    <span><?= htmlspecialchars($price) ?> €</span>
    <?php if ($isEmployee) : ?>
        <button class="btn-edit" onclick="window.location.href='productEdit.php'">Add Product</button>
    <?php endif; ?>
</form>

<!-- Product table -->
<table border="1">
    <tr>
        <th>Name</th>
        <th>Brand</th>
        <th>Category</th>
        <th>Year</th>
        <th>Price</th>
        <?php if ($isEmployee) : ?>
            <th>Actions</th>
        <?php endif; ?>
    </tr>
    <?php foreach ($products as $product) : ?>
        <tr>
            <td><?= htmlspecialchars($product->getProductName()) ?></td>
            <td><?= htmlspecialchars($product->getBrand()->getBrandName()) ?></td>
            <td><?= htmlspecialchars($product->getCategory()->getCategoryName()) ?></td>
            <td><?= htmlspecialchars($product->getModelYear()) ?></td>
            <td><?= htmlspecialchars($product->getListPrice()) ?> €</td>
            <?php if ($isEmployee) : ?>
                <td class="action-buttons">
                    <button class="btn-edit" onclick="window.location.href='productEdit.php?id=<?= $product->getProductId() ?>'">Edit</button>
                    <button class="delete-btn" onclick="deleteProduct('<?= $product->getProductId() ?>')">Delete</button>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Pagination -->
<div class="pagination">
    <?php if ($totalPages > 1): ?>
        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
            <?php
            // Construct the URL with all filter parameters
            $queryParams = [
                'brand' => $brandFilter,
                'category' => $categoryFilter,
                'year' => $yearFilter,
                'price' => $price,
                'page' => $i
            ];
            $queryString = http_build_query($queryParams);
            ?>
            <a href="?<?= $queryString ?>" <?= ($i == $page) ? 'class="active"' : '' ?>>
                <?= $i ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<script>
    /**
     * Auto-submit the filter form when any input field changes.
     */
    document.getElementById('filterForm').addEventListener('change', function() {
        this.submit();
    });

    /**
     * Prevent manual submission of the form for better UX.
     * @param {Event} e The form submission event.
     */
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
    });

    <?php if ($isEmployee) : ?>
        // API base URL
        const apiUrl = 'https://dev-clisson231.users.info.unicaen.fr/2eannee/DevBack/S401/controller/api.php';
        const apiKey = 'e8f1997c763'; // Your API key

        /**
         * Display the form for adding a new product.
         */
        function showAddForm() {
            document.getElementById('formTitle').textContent = 'Add New Product';
            document.getElementById('productId').value = '';
            document.getElementById('productForm').reset();
            document.getElementById('productFormContainer').style.display = 'block';
        }

        /**
         * Edit an existing product and populate the form with its details.
         * @param {number} id Product ID
         * @param {string} name Product name
         * @param {number} brandId Brand ID
         * @param {number} categoryId Category ID
         * @param {string} year Model year
         * @param {number} price Price
         */
        function editProduct(id, name, brandId, categoryId, year, price) {
            document.getElementById('productId').value = id;
            document.getElementById('productName').value = name;
            document.getElementById('productBrand').value = brandId;
            document.getElementById('productCategory').value = categoryId;
            document.getElementById('productYear').value = year;
            document.getElementById('productPrice').value = price;
            document.getElementById('productFormContainer').style.display = 'block';
        }

        /**
         * Cancel editing and hide the form.
         */
        function cancelEdit() {
            document.getElementById('productFormContainer').style.display = 'none';
        }

        /**
         * Handle the form submission for adding or updating a product.
         * @param {Event} e The submit event.
         */
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const productId = document.getElementById('productId').value;
            const isEdit = productId !== '';

            const productData = {
                product_name: document.getElementById('productName').value,
                brand: document.getElementById('productBrand').value,
                category: document.getElementById('productCategory').value,
                model_year: document.getElementById('productYear').value,
                list_price: document.getElementById('productPrice').value
            };

            if (isEdit) {
                // Update an existing product (PUT)
                productData.id = productId;
                productData.type = 'product_id';

                fetch(apiUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Api-Key': apiKey
                        },
                        body: JSON.stringify(productData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Product updated successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error updating product');
                    });
            } else {
                // Add a new product (POST)
                fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Api-Key': apiKey
                        },
                        body: JSON.stringify({
                            action: 'product',
                            ...productData
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Product added successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error adding product');
                    });
            }
        });

        /**
         * Delete a product after confirming the action.
         * @param {number} productId The product ID to delete.
         */
        async function deleteProduct(productId) {
            if (!confirm('Are you sure you want to delete this product?')) return;

            try {
                const response = await fetch(apiUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Api-Key': apiKey
                    },
                    body: JSON.stringify({
                        id: productId,
                        type: 'product_id'
                    })
                });

                // Check if the response is in HTML (server error)
                const text = await response.text();
                let data;

                try {
                    data = text ? JSON.parse(text) : {};
                } catch (e) {
                    console.error('Failed to parse response:', text);
                    throw new Error(`Server returned HTML instead of JSON. Status: ${response.status}`);
                }

                if (!response.ok) {
                    throw new Error(data.error || `Delete failed with status ${response.status}`);
                }

                alert('Product deleted successfully');
                location.reload();
            } catch (error) {
                console.error('Delete error:', error);
                alert(error.message);
            }
        }
    <?php endif; ?>
</script>

<?php include_once("../www/footer.php"); ?>
