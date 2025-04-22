<?php
// Allow cross-origin requests (CORS headers)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token");
header("Content-Type: application/json");

// Enable error reporting for debugging (only in development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary dependencies and classes
require_once "../bootstrap.php";

use Entity\Brand;
use Entity\Category;
use Entity\Store;
use Entity\Stock;
use Entity\Employee;
use Entity\Product;

// Define the API Key for secured operations
define("API_KEY", "e8f1997c763");

// Function to validate API key for non-GET requests (POST, PUT, DELETE)
function validateApiKey()
{
    $headers = getallheaders();
    if (!isset($headers["X-Api-Key"]) || $headers["X-Api-Key"] !== API_KEY) {
        http_response_code(403);  // Forbidden response code
        echo json_encode(["error" => "Forbidden: Invalid API Key"]);
        exit;  // Exit if invalid API key
    }
}

// Ensure the entity manager is available for ORM operations
if (!isset($entityManager)) {
    http_response_code(500);  // Internal server error code
    echo json_encode(["error" => "Database connection error"]);
    exit;
}

// Retrieve the request method (GET, POST, PUT, DELETE)
$request_method = $_SERVER['REQUEST_METHOD'];

// Define the mapping between request parameters and entity classes
$repositories = [
    'brand_id'    => Brand::class,
    'category_id' => Category::class,
    'store_id'    => Store::class,
    'stock_id'    => Stock::class,
    'employee_id' => Employee::class,
    'product_id'  => Product::class,
];

// Switch based on the HTTP request method (GET, POST, PUT, DELETE)
switch ($request_method) {
    case 'GET':
        // Handle GET requests for specific fields (e.g., product_name)
        if (isset($_GET['product_name'])) {
            $repository = $entityManager->getRepository(Product::class);
            $products = $repository->findBy([], ['product_name' => 'ASC']);  // Fetch all products sorted by product_name

            // Map the products to just their names and return in JSON format
            $result = array_map(function ($product) {
                return $product->getProductName();  // Retrieve product names
            }, $products);

            echo json_encode($result);  // Output the result in JSON format
            exit;  // End script execution
        }

        // Handle GET requests for specific IDs (e.g., ?product_id=1)
        foreach ($repositories as $param => $entityClass) {
            if (isset($_GET[$param])) {
                $id = (int) $_GET[$param];
                $repository = $entityManager->getRepository($entityClass);
                $entity = $repository->find($id);

                if ($entity) {
                    echo json_encode($entity);  // Return entity details in JSON format
                } else {
                    http_response_code(404);  // Not found response code
                    echo json_encode(['error' => 'Entity not found']);
                }
                exit;  // End script execution
            }
        }

        // Fetch all entities of a given type (brands, categories, products, etc.)
        if (isset($_GET['type'])) {
            // Mapping of request type to the corresponding entity class
            $entityMap = [
                'brands' => Brand::class,
                'categories' => Category::class,
                'stores' => Store::class,
                'stocks' => Stock::class,
                'employees' => Employee::class,
                'products' => Product::class
            ];

            // Check if the requested type is valid
            if (!array_key_exists($_GET['type'], $entityMap)) {
                http_response_code(400);  // Bad request response code
                echo json_encode(['error' => 'Invalid entity type']);
                exit;
            }

            // Get the repository for the requested entity type
            $repository = $entityManager->getRepository($entityMap[$_GET['type']]);

            // Handle field filtering (return only specific fields for each entity)
            if (isset($_GET['fields'])) {
                $requestedFields = array_map('trim', explode(',', $_GET['fields']));  // Fields to be returned
                $entities = $repository->findAll();  // Fetch all entities
                $result = [];

                // Iterate over each entity and map requested fields
                foreach ($entities as $entity) {
                    $item = [];
                    foreach ($requestedFields as $field) {
                        $method = 'get' . str_replace('_', '', ucwords($field, '_'));  // Generate getter method name

                        if (method_exists($entity, $method)) {
                            $value = $entity->$method();  // Call the getter method

                            // Handle related entities (e.g., brand, category)
                            if (is_object($value) && method_exists($value, 'getId')) {
                                $item[$field] = $value->getId();  // If related entity, return its ID
                            } else {
                                $item[$field] = $value;  // Otherwise, return the value
                            }
                        }
                    }
                    $result[] = $item;
                }

                echo json_encode($result);  // Output the filtered result in JSON format
                exit;  // End script execution
            }

            // Default behavior: return all entities with pagination support
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;  // Default to page 1
            $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 50;  // Default to 50 items per page
            $offset = ($page - 1) * $limit;  // Calculate the offset for pagination

            // Create the query builder for pagination
            $queryBuilder = $repository->createQueryBuilder('e');
            $queryBuilder->setFirstResult($offset)
                ->setMaxResults($limit);

            // Handle simple filtering based on GET parameters (e.g., ?filter=product_name&value=search_term)
            if (isset($_GET['filter']) && isset($_GET['value'])) {
                $filterField = $_GET['filter'];
                $filterValue = $_GET['value'];
                $method = 'get' . str_replace('_', '', ucwords($filterField, '_'));  // Generate the getter method name

                if (method_exists($entityMap[$_GET['type']], $method)) {
                    // Apply the filter to the query
                    $queryBuilder->where("e.$filterField LIKE :value")
                        ->setParameter('value', '%' . $filterValue . '%');
                }
            }

            // Execute the query and return the results
            $entities = $queryBuilder->getQuery()->getResult();
            echo json_encode($entities);  // Output the results in JSON format
            exit;
        }

        // If no valid parameters are provided, return a list of available endpoints and their usage
        http_response_code(400);  // Bad request response code
        echo json_encode([
            'error' => 'Invalid request',
            'available_endpoints' => [
                'GET' => [
                    'Get by ID' => '?product_id=1 or ?brand_id=1 etc.',
                    'Get all entities' => '?type=products or ?type=brands etc.',
                    'Filter fields' => '?type=products&fields=product_name,list_price',
                    'Search' => '?type=products&filter=product_name&value=search_term',
                    'Pagination' => '?type=products&page=2&limit=20'
                ]
            ]
        ]);
        exit;

        $action = $input["action"];

        switch ($action) {
            case "employee":
                // Validate and create a new employee
                if (!empty($input["employee_name"]) && is_string($input["employee_name"])) {
                    $employee = new Employee();
                    $employee->setEmployeeName($input["employee_name"]);

                    // Validate and set email
                    if (!empty($input["employee_email"]) && filter_var($input["employee_email"], FILTER_VALIDATE_EMAIL)) {
                        $employee->setEmployeeEmail($input["employee_email"]);
                    } else {
                        // Respond with error if email is invalid
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid email format']);
                        exit;
                    }

                    // Validate and assign a store
                    if (!empty($input["store"]) && is_numeric($input["store"])) {
                        $store = $entityManager->find(Store::class, $input["store"]);
                        if ($store) {
                            $employee->setStore($store);
                        } else {
                            // Respond with error if store not found
                            http_response_code(400);
                            echo json_encode(['error' => 'Store not found']);
                            exit;
                        }
                    }

                    // Validate and hash password
                    if (!empty($input["employee_password"])) {
                        $employee->setEmployeePassword(password_hash($input["employee_password"], PASSWORD_DEFAULT));
                    } else {
                        // Respond with error if password is not provided
                        http_response_code(400);
                        echo json_encode(['error' => 'Password is required']);
                        exit;
                    }

                    // Assign role if provided
                    if (!empty($input["employee_role"])) {
                        $employee->setEmployeeRole($input["employee_role"]);
                    }

                    // Save the new employee to the database
                    $entityManager->persist($employee);
                    $entityManager->flush();
                    echo json_encode(['success' => 'Employee added successfully']);
                } else {
                    // Respond with error if employee name is invalid
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid Employee Name']);
                }
                break;

            case "store":
                // Validate and create a new store
                if (!empty($input["store_name"]) && is_string($input["store_name"])) {
                    $store = new Store();
                    $store->setStoreName($input["store_name"]);

                    // Set optional fields if provided
                    if (!empty($input["phone"])) $store->setPhone($input["phone"]);
                    if (!empty($input["email"]) && filter_var($input["email"], FILTER_VALIDATE_EMAIL)) $store->setEmail($input["email"]);
                    if (!empty($input["street"])) $store->setStreet($input["street"]);
                    if (!empty($input["city"])) $store->setCity($input["city"]);
                    if (!empty($input["state"])) $store->setState($input["state"]);
                    if (!empty($input["zip_code"])) $store->setZipCode($input["zip_code"]);

                    // Save the store to the database
                    $entityManager->persist($store);
                    $entityManager->flush();
                    echo json_encode(['success' => 'Store added successfully']);
                } else {
                    // Respond with error if store name is invalid
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid Store Name']);
                }
                break;

            case "product":
                // Validate and create a new product
                if (!empty($input["product_name"]) && is_string($input["product_name"])) {
                    $product = new Product();
                    $product->setProductName($input["product_name"]);

                    // Validate and assign brand
                    if (!empty($input["brand"]) && is_numeric($input["brand"])) {
                        $brand = $entityManager->find(Brand::class, $input["brand"]);
                        if ($brand) {
                            $product->setBrand($brand);
                        } else {
                            // Respond with error if brand not found
                            http_response_code(400);
                            echo json_encode(['error' => 'Brand not found']);
                            exit;
                        }
                    }

                    // Validate and assign category
                    if (!empty($input["category"]) && is_numeric($input["category"])) {
                        $category = $entityManager->find(Category::class, $input["category"]);
                        if ($category) {
                            $product->setCategory($category);
                        } else {
                            // Respond with error if category not found
                            http_response_code(400);
                            echo json_encode(['error' => 'Category not found']);
                            exit;
                        }
                    }

                    // Set optional fields if provided
                    if (!empty($input["model_year"]) && is_numeric($input["model_year"])) {
                        $product->setModelYear($input["model_year"]);
                    }
                    if (!empty($input["list_price"]) && is_numeric($input["list_price"])) {
                        $product->setListPrice($input["list_price"]);
                    }

                    // Save the product to the database
                    $entityManager->persist($product);
                    $entityManager->flush();
                    echo json_encode(['success' => 'Product added successfully']);
                } else {
                    // Respond with error if product name is invalid
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid Product Name']);
                }
                break;
        }

        exit;

    case 'POST':
        // Validate API Key to ensure the request is authenticated
        validateApiKey();

        // Decode the input JSON into an associative array
        $input = json_decode(file_get_contents("php://input"), true);

        // Check if the input is valid (i.e., contains data)
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON or no data received']);
            exit;
        }

        // Check if the action parameter is present in the input
        if (!isset($input["action"])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing action parameter']);
            exit;
        }

        // Get the action value to determine what process to execute
        $action = $input["action"];

        switch ($action) {
            /**
             * Handles the creation of a new employee.
             */
            case "employee":
                // Validate and create a new employee
                if (!empty($input["employee_name"]) && is_string($input["employee_name"])) {
                    $employee = new Employee();
                    $employee->setEmployeeName($input["employee_name"]);

                    // Validate and set email
                    if (!empty($input["employee_email"]) && filter_var($input["employee_email"], FILTER_VALIDATE_EMAIL)) {
                        $employee->setEmployeeEmail($input["employee_email"]);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid email format']);
                        exit;
                    }

                    // Validate and assign a store
                    if (!empty($input["store"]) && is_numeric($input["store"])) {
                        $store = $entityManager->find(Store::class, $input["store"]);
                        if ($store) {
                            $employee->setStore($store);
                        } else {
                            http_response_code(400);
                            echo json_encode(['error' => 'Store not found']);
                            exit;
                        }
                    }

                    // Validate and hash password
                    if (!empty($input["employee_password"])) {
                        $employee->setEmployeePassword(password_hash($input["employee_password"], PASSWORD_DEFAULT));
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Password is required']);
                        exit;
                    }

                    // Assign role if provided
                    if (!empty($input["employee_role"])) {
                        $employee->setEmployeeRole($input["employee_role"]);
                    }

                    // Save the new employee to the database
                    $entityManager->persist($employee);
                    $entityManager->flush();
                    echo json_encode(['success' => 'Employee added successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid Employee Name']);
                }
                break;

            /**
                 * Handles the creation of a new store.
                 */
            case "store":
                // Validate and create a new store
                if (!empty($input["store_name"]) && is_string($input["store_name"])) {
                    $store = new Store();
                    $store->setStoreName($input["store_name"]);

                    // Set optional fields if provided
                    if (!empty($input["phone"])) $store->setPhone($input["phone"]);
                    if (!empty($input["email"]) && filter_var($input["email"], FILTER_VALIDATE_EMAIL)) $store->setEmail($input["email"]);
                    if (!empty($input["street"])) $store->setStreet($input["street"]);
                    if (!empty($input["city"])) $store->setCity($input["city"]);
                    if (!empty($input["state"])) $store->setState($input["state"]);
                    if (!empty($input["zip_code"])) $store->setZipCode($input["zip_code"]);

                    // Save the new store to the database
                    $entityManager->persist($store);
                    $entityManager->flush();
                    echo json_encode(['success' => 'Store added successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid Store Name']);
                }
                break;

            /**
                 * Handles the creation of a new product.
                 */
            case "product":
                // Validate and create a new product
                if (!empty($input["product_name"]) && is_string($input["product_name"])) {
                    $product = new Product();
                    $product->setProductName($input["product_name"]);

                    // Validate and assign brand
                    if (!empty($input["brand"]) && is_numeric($input["brand"])) {
                        $brand = $entityManager->find(Brand::class, $input["brand"]);
                        if ($brand) {
                            $product->setBrand($brand);
                        } else {
                            http_response_code(400);
                            echo json_encode(['error' => 'Brand not found']);
                            exit;
                        }
                    }

                    // Validate and assign category
                    if (!empty($input["category"]) && is_numeric($input["category"])) {
                        $category = $entityManager->find(Category::class, $input["category"]);
                        if ($category) {
                            $product->setCategory($category);
                        } else {
                            http_response_code(400);
                            echo json_encode(['error' => 'Category not found']);
                            exit;
                        }
                    }

                    // Set optional fields if provided
                    if (!empty($input["model_year"]) && is_numeric($input["model_year"])) {
                        $product->setModelYear($input["model_year"]);
                    }
                    if (!empty($input["list_price"]) && is_numeric($input["list_price"])) {
                        $product->setListPrice($input["list_price"]);
                    }

                    // Save the new product to the database
                    $entityManager->persist($product);
                    $entityManager->flush();
                    echo json_encode(['success' => 'Product added successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid Product Name']);
                }
                break;
        }
        exit;

    case 'PUT':
        // Validate API Key to ensure the request is authenticated
        validateApiKey();

        // Decode the input JSON into an associative array
        $input = json_decode(file_get_contents("php://input"), true);

        // Check if the input is valid and contains the required parameters (id and type)
        if (!$input || !isset($input['id']) || !isset($input["type"])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID or type']);
            exit;
        }

        // Get the entity class based on the input type
        $entityClass = $repositories[$input["type"]] ?? null;
        if (!$entityClass) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid entity type']);
            exit;
        }

        // Find the entity by ID in the database
        $entity = $entityManager->find($entityClass, $input['id']);
        if (!$entity) {
            http_response_code(404);
            echo json_encode(['error' => 'Entity not found']);
            exit;
        }

        // Switch based on the entity type and update the respective fields
        switch ($input["type"]) {
            /**
             * Updates a product entity.
             */
            case 'product_id':
                // Update product name if provided and valid
                if (!empty($input["product_name"]) && is_string($input["product_name"])) {
                    $entity->setProductName($input["product_name"]);
                }

                // Update the brand if provided and valid
                if (!empty($input["brand"]) && is_numeric($input["brand"])) {
                    $brand = $entityManager->find(Brand::class, $input["brand"]);
                    $entity->setBrand($brand ?: null);
                }

                // Update the category if provided and valid
                if (!empty($input["category"]) && is_numeric($input["category"])) {
                    $category = $entityManager->find(Category::class, $input["category"]);
                    $entity->setCategory($category ?: null);
                }

                // Update model year if provided and valid
                if (!empty($input["model_year"]) && is_numeric($input["model_year"])) {
                    $entity->setModelYear($input["model_year"]);
                }

                // Update list price if provided and valid
                if (!empty($input["list_price"]) && is_numeric($input["list_price"])) {
                    $entity->setListPrice($input["list_price"]);
                }
                break;

            /**
                 * Updates a brand entity.
                 */
            case 'brand_id':
                // Update brand name if provided and valid
                if (!empty($input["brand_name"]) && is_string($input["brand_name"])) {
                    $entity->setBrandName($input["brand_name"]);
                }
                break;

            /**
                 * Updates a category entity.
                 */
            case 'category_id':
                // Update category name if provided and valid
                if (!empty($input["category_name"]) && is_string($input["category_name"])) {
                    $entity->setCategoryName($input["category_name"]);
                }
                break;

            /**
                 * Updates a store entity.
                 */
            case 'store_id':
                // Update store name if provided and valid
                if (!empty($input["store_name"]) && is_string($input["store_name"])) {
                    $entity->setStoreName($input["store_name"]);
                }

                // Update phone number if provided
                if (!empty($input["phone"])) {
                    $entity->setPhone($input["phone"]);
                }

                // Update email if provided and valid
                if (!empty($input["email"]) && filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
                    $entity->setEmail($input["email"]);
                }
                break;

            /**
                 * Updates an employee entity.
                 */
            case 'employee_id':
                // Update employee name if provided and valid
                if (!empty($input["employee_name"]) && is_string($input["employee_name"])) {
                    $entity->setEmployeeName($input["employee_name"]);
                }

                // Update employee email if provided and valid
                if (!empty($input["employee_email"]) && filter_var($input["employee_email"], FILTER_VALIDATE_EMAIL)) {
                    $entity->setEmployeeEmail($input["employee_email"]);
                }

                // Update employee password if provided
                if (!empty($input["employee_password"])) {
                    $entity->setEmployeePassword(password_hash($input["employee_password"], PASSWORD_DEFAULT));
                }
                break;
        }

        // Attempt to save the updated entity to the database
        try {
            $entityManager->flush();
            echo json_encode(['success' => 'Entity updated successfully']);
        } catch (\Exception $e) {
            // If a database error occurs, return an error response
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        exit;

    case 'DELETE':
        // Validate the API key to ensure that the request is authenticated
        validateApiKey();

        try {
            // Decode the incoming JSON input into an associative array
            $input = json_decode(file_get_contents("php://input"), true);

            // Check if JSON was valid
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON input');
            }

            // Ensure required parameters 'id' and 'type' are present in the input
            if (empty($input['id']) || empty($input['type'])) {
                throw new Exception('Missing required parameters');
            }

            // Determine the corresponding entity class based on the 'type' parameter
            $entityClass = $repositories[$input['type']] ?? null;
            if (!$entityClass) {
                throw new Exception('Invalid entity type');
            }

            // Find the entity by its ID in the database
            $entity = $entityManager->find($entityClass, $input['id']);
            if (!$entity) {
                http_response_code(404); // Entity not found
                echo json_encode(['error' => 'Entity not found']);
                exit;
            }

            // Proceed to remove the entity from the database
            $entityManager->remove($entity);
            $entityManager->flush(); // Commit the deletion

            // Return a success response indicating that the entity was successfully deleted
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Entity deleted successfully'
            ]);
        } catch (Exception $e) {
            // Catch any exceptions and return an error response
            http_response_code(400); // Bad request
            echo json_encode([
                'error' => $e->getMessage(),
                'trace' => (defined('DEBUG_MODE')) ? $e->getTrace() : null // Provide trace if in debug mode
            ]);
        }
        exit;
}
