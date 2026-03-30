<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/SupportTicket.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../includes/helpers.php';

class AdminController
{
    private User $userModel;
    private Order $orderModel;
    private SupportTicket $ticketModel;
    private Product $productModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->userModel     = new User();
        $this->orderModel    = new Order();
        $this->ticketModel   = new SupportTicket();
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
    }

    private function ensureStaff(): void
    {
        $role = $_SESSION['user']['role_name'] ?? '';
        if (!in_array($role, ['Admin', 'Support'], true)) {
            redirect('index.php?page=dashboard');
        }
    }

    private function ensureAdmin(): void
    {
        if (($_SESSION['user']['role_name'] ?? '') !== 'Admin') {
            if (($_SESSION['user']['role_name'] ?? '') === 'Support') {
                redirect('index.php?page=admin-helpdesk');
            }
            redirect('index.php?page=dashboard');
        }
    }

    private function withCommonData(array $data = []): array
    {
        $data['adminRole']    = $_SESSION['user']['role_name'] ?? '';
        $data['isAdminStaff'] = ($_SESSION['user']['role_name'] ?? '') === 'Admin';
        return $data;
    }

    // ── Upload a single image file field ─────────────────────────────────
    private function uploadImageField(string $fieldName, string $existing = ''): array
    {
        // If a "clear" checkbox was ticked, wipe the image
        if (!empty($_POST['clear_' . $fieldName])) {
            return ['image' => '', 'error' => null];
        }

        // No file uploaded — keep existing
        if (!isset($_FILES[$fieldName]) ||
            ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['image' => $existing, 'error' => null];
        }

        if (($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['image' => $existing, 'error' => 'Upload failed for ' . $fieldName . '.'];
        }

        $tmpPath = $_FILES[$fieldName]['tmp_name'] ?? '';
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['image' => $existing, 'error' => 'Invalid upload for ' . $fieldName . '.'];
        }

        $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $origName  = $_FILES[$fieldName]['name'] ?? 'product';
        $ext       = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            return ['image' => $existing, 'error' => 'Only JPG, PNG, WEBP, GIF allowed.'];
        }

        $uploadDir = __DIR__ . '/../assets/images/products/uploads';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            return ['image' => $existing, 'error' => 'Could not create upload directory.'];
        }

        $base        = preg_replace('/[^a-zA-Z0-9_-]/', '-', pathinfo($origName, PATHINFO_FILENAME));
        $base        = trim((string)$base, '-') ?: 'product';
        $filename    = $base . '-' . time() . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $destination)) {
            return ['image' => $existing, 'error' => 'Could not save uploaded image.'];
        }

        return ['image' => 'uploads/' . $filename, 'error' => null];
    }

    // ── Process all 4 image slots and return [images[], error|null] ──────
    private function processAllImages(array $existing = []): array
    {
        $slots = ['image_file', 'image_file_2', 'image_file_3', 'image_file_4'];
        $urls  = [];
        foreach ($slots as $i => $field) {
            $result = $this->uploadImageField($field, $existing[$i] ?? '');
            if ($result['error']) {
                return ['images' => [], 'error' => $result['error']];
            }
            $urls[] = $result['image'];
        }
        return ['images' => $urls, 'error' => null];
    }

    // ── Resolve category_id from parent + sub selects ────────────────────
    // If a sub-category is selected, that becomes category_id.
    // If only parent is selected, category_id = parent_category_id.
    private function resolveCategoryId(): ?int
    {
        $subId    = (int)($_POST['category_id']        ?? 0);
        $parentId = (int)($_POST['parent_category_id'] ?? 0);

        if ($subId > 0) return $subId;
        if ($parentId > 0) return $parentId;
        return null;
    }

    public function dashboard(): array
    {
        $this->ensureAdmin();
        $users        = $this->userModel->all();
        $orders       = $this->orderModel->all();
        $tickets      = $this->ticketModel->all();
        $products     = $this->productModel->allForAdmin();
        $totalRevenue = $this->orderModel->totalRevenue();
        $flash        = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
            $this->orderModel->updateStatus((int)$_POST['order_id'], $_POST['status']);
            redirect('index.php?page=admin');
        }

        return [
            'view' => 'admin/dashboard',
            'data' => $this->withCommonData([
                'users'        => $users,
                'orders'       => $orders,
                'tickets'      => $tickets,
                'products'     => $products,
                'totalRevenue' => $totalRevenue,
                'flash'        => $flash,
                'section'      => 'dashboard',
            ])
        ];
    }

    public function products(): array
    {
        $this->ensureAdmin();
        $products = $this->productModel->allForAdmin();
        $flash    = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return [
            'view' => 'admin/products',
            'data' => $this->withCommonData([
                'products' => $products,
                'flash'    => $flash,
                'section'  => 'products',
            ])
        ];
    }

    public function createProduct(): array
    {
        $this->ensureAdmin();
        $error      = null;
        $categories = $this->categoryModel->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name']     ?? '');
            $sku      = trim($_POST['sku']      ?? '');
            $price    = (float)($_POST['price']    ?? 0);
            $stockQty = (int)($_POST['stock_qty']  ?? 0);

            $imgResult = $this->processAllImages();

            if ($name === '' || $sku === '' || $price < 0 || $stockQty < 0) {
                $error = 'Please fill all required fields with valid values.';
            } elseif ($imgResult['error']) {
                $error = $imgResult['error'];
            } else {
                [$img1, $img2, $img3, $img4] = $imgResult['images'];

                $ok = $this->productModel->create([
                    'category_id'    => $this->resolveCategoryId(),
                    'name'           => $name,
                    'brand'          => trim($_POST['brand']       ?? ''),
                    'sku'            => $sku,
                    'description'    => trim($_POST['description'] ?? ''),
                    'price'          => $price,
                    'stock_qty'      => $stockQty,
                    'image_url'      => $img1,
                    'image_url_2'    => $img2,
                    'image_url_3'    => $img3,
                    'image_url_4'    => $img4,
                    'is_active'      => isset($_POST['is_active']) ? 1 : 0,
                ]);

                if ($ok) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Product created successfully.'];
                    redirect('index.php?page=admin-products');
                }
                $error = 'Failed to create product. SKU may already exist.';
            }
        }

        return [
            'view' => 'admin/product_form',
            'data' => $this->withCommonData([
                'title'       => 'Add Product',
                'submitLabel' => 'Create Product',
                'categories'  => $categories,
                'product'     => null,
                'error'       => $error,
                'section'     => 'products',
            ])
        ];
    }

    public function editProduct(): array
    {
        $this->ensureAdmin();
        $id      = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->find($id);

        if (!$product) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Product not found.'];
            redirect('index.php?page=admin-products');
        }

        $error      = null;
        $categories = $this->categoryModel->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name']     ?? '');
            $sku      = trim($_POST['sku']      ?? '');
            $price    = (float)($_POST['price']    ?? 0);
            $stockQty = (int)($_POST['stock_qty']  ?? 0);

            $existing  = [
                $product['image_url']   ?? '',
                $product['image_url_2'] ?? '',
                $product['image_url_3'] ?? '',
                $product['image_url_4'] ?? '',
            ];
            $imgResult = $this->processAllImages($existing);

            if ($name === '' || $sku === '' || $price < 0 || $stockQty < 0) {
                $error = 'Please fill all required fields with valid values.';
            } elseif ($imgResult['error']) {
                $error = $imgResult['error'];
            } else {
                [$img1, $img2, $img3, $img4] = $imgResult['images'];

                $ok = $this->productModel->update($id, [
                    'category_id'    => $this->resolveCategoryId(),
                    'name'           => $name,
                    'brand'          => trim($_POST['brand']       ?? ''),
                    'sku'            => $sku,
                    'description'    => trim($_POST['description'] ?? ''),
                    'price'          => $price,
                    'stock_qty'      => $stockQty,
                    'image_url'      => $img1,
                    'image_url_2'    => $img2,
                    'image_url_3'    => $img3,
                    'image_url_4'    => $img4,
                    'is_active'      => isset($_POST['is_active']) ? 1 : 0,
                ]);

                if ($ok) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Product updated successfully.'];
                    redirect('index.php?page=admin-products');
                }
                $error = 'Failed to update product. Check if SKU is unique.';
            }
        }

        return [
            'view' => 'admin/product_form',
            'data' => $this->withCommonData([
                'title'       => 'Edit Product',
                'submitLabel' => 'Update Product',
                'categories'  => $categories,
                'product'     => $product,
                'error'       => $error,
                'section'     => 'products',
            ])
        ];
    }

    public function deleteProduct(): void
    {
        $this->ensureAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->productModel->delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Product deleted successfully.'];
        }
        redirect('index.php?page=admin-products');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  USERS
    // ─────────────────────────────────────────────────────────────────────
    public function users(): array
    {
        $this->ensureAdmin();
        $users = $this->userModel->all();
        $roles = $this->userModel->roles();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return [
            'view' => 'admin/users',
            'data' => $this->withCommonData([
                'users'   => $users,
                'roles'   => $roles,
                'flash'   => $flash,
                'section' => 'users',
            ])
        ];
    }

    public function createUserForm(): array
    {
        $this->ensureAdmin();
        return [
            'view' => 'admin/user_form',
            'data' => $this->withCommonData([
                'user'      => null,
                'roles'     => $this->userModel->roles(),
                'error'     => null,
                'section'   => 'users',
                'formTitle' => 'Add user',
            ])
        ];
    }

    public function storeUser(): void
    {
        $this->ensureAdmin();
        $email    = trim($_POST['email']     ?? '');
        $password = $_POST['password']       ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $roleId   = (int)($_POST['role_id']  ?? 0);
        $status   = $_POST['status']         ?? 'Active';

        if ($fullName === '' || $email === '' || $password === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Name, email, and password are required.'];
            redirect('index.php?page=admin-user-create');
        }
        if ($this->userModel->findByEmail($email)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'That email is already registered.'];
            redirect('index.php?page=admin-user-create');
        }
        if ($roleId < 1 || !in_array($status, ['Active', 'Inactive'], true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid role or status.'];
            redirect('index.php?page=admin-user-create');
        }

        $ok = $this->userModel->create([
            'role_id'            => $roleId,
            'full_name'          => $fullName,
            'email'              => $email,
            'password_hash'      => password_hash($password, PASSWORD_DEFAULT),
            'contact_no'         => trim($_POST['contact_no']         ?? ''),
            'address'            => trim($_POST['address']            ?? ''),
            'payment_preference' => trim($_POST['payment_preference'] ?? ''),
            'status'             => $status,
        ]);

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'User created successfully.']
            : ['type' => 'danger',  'message' => 'Could not create user.'];
        redirect('index.php?page=admin-users');
    }

    public function editUserForm(): array
    {
        $this->ensureAdmin();
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'User not found.'];
            redirect('index.php?page=admin-users');
        }
        if ($id === (int)($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Edit your profile from the customer dashboard.'];
            redirect('index.php?page=admin-users');
        }
        return [
            'view' => 'admin/user_form',
            'data' => $this->withCommonData([
                'user'      => $user,
                'roles'     => $this->userModel->roles(),
                'error'     => null,
                'section'   => 'users',
                'formTitle' => 'Edit user',
            ])
        ];
    }

    public function updateUserFull(): void
    {
        $this->ensureAdmin();
        $userId   = (int)($_POST['id']        ?? 0);
        $fullName = trim($_POST['full_name']   ?? '');
        $email    = trim($_POST['email']       ?? '');
        $roleId   = (int)($_POST['role_id']    ?? 0);
        $status   = $_POST['status']           ?? 'Active';
        $newPass  = $_POST['new_password']     ?? '';

        if ($userId === (int)($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Cannot edit your own account on this form.'];
            redirect('index.php?page=admin-users');
        }
        $existing = $this->userModel->findById($userId);
        if (!$existing) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'User not found.'];
            redirect('index.php?page=admin-users');
        }
        if ($fullName === '' || $email === '' || $roleId < 1 || !in_array($status, ['Active', 'Inactive'], true)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Please fill all required fields correctly.'];
            redirect('index.php?page=admin-user-edit&id=' . $userId);
        }
        if ($this->userModel->emailExistsForOtherUser($email, $userId)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Another account already uses that email.'];
            redirect('index.php?page=admin-user-edit&id=' . $userId);
        }

        $ok = $this->userModel->updateFull($userId, [
            'role_id'            => $roleId,
            'full_name'          => $fullName,
            'email'              => $email,
            'contact_no'         => trim($_POST['contact_no']         ?? ''),
            'address'            => trim($_POST['address']            ?? ''),
            'payment_preference' => trim($_POST['payment_preference'] ?? ''),
            'status'             => $status,
        ]);

        if ($ok && $newPass !== '') {
            $this->userModel->updatePassword($userId, password_hash($newPass, PASSWORD_DEFAULT));
        }

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'User updated successfully.']
            : ['type' => 'danger',  'message' => 'Could not update user.'];
        redirect('index.php?page=admin-users');
    }

    public function deleteUser(): void
    {
        $this->ensureAdmin();
        $userId = (int)($_POST['id'] ?? 0);
        if ($userId === (int)($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'You cannot delete your own account.'];
            redirect('index.php?page=admin-users');
        }
        if ($userId < 1) redirect('index.php?page=admin-users');

        $hasHistory = $this->userModel->countOrders($userId) > 0
                   || $this->userModel->countSupportTickets($userId) > 0;

        if ($hasHistory) {
            if ($this->userModel->setInactive($userId)) {
                $_SESSION['flash'] = ['type' => 'warning', 'message' => 'User has orders/tickets — account deactivated instead.'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Could not deactivate user.'];
            }
            redirect('index.php?page=admin-users');
        }

        try {
            if ($this->userModel->deleteById($userId)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'User deleted successfully.'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Could not delete user.'];
            }
        } catch (\PDOException $e) {
            if ($this->userModel->setInactive($userId)) {
                $_SESSION['flash'] = ['type' => 'warning', 'message' => 'User still referenced in DB. Account deactivated.'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Could not remove or deactivate user.'];
            }
        }
        redirect('index.php?page=admin-users');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  ORDERS
    // ─────────────────────────────────────────────────────────────────────
    public function orders(): array
    {
        $this->ensureAdmin();
        $orders = $this->orderModel->all();
        $flash  = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return [
            'view' => 'admin/orders',
            'data' => $this->withCommonData([
                'orders'  => $orders,
                'flash'   => $flash,
                'section' => 'orders',
            ])
        ];
    }

    public function updateOrderStatus(): void
    {
        $this->ensureAdmin();
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status  = $_POST['status']          ?? '';
        $valid   = in_array($status, ['Pending', 'Processing', 'Shipped', 'Delivered'], true);

        if ($orderId > 0 && $valid && $this->orderModel->updateStatus($orderId, $status)) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Order status updated.'];
        } else {
            $_SESSION['flash'] = ['type' => 'danger',  'message' => 'Could not update order status.'];
        }
        redirect('index.php?page=admin-orders');
    }

    public function deleteOrder(): void
    {
        $pdo     = Database::connection();
        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId > 0) {
            $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
            $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Order #' . $orderId . ' deleted successfully.'];
        }
        header('Location: index.php?page=admin-orders');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  HELPDESK
    // ─────────────────────────────────────────────────────────────────────
    public function helpdesk(): array
    {
        $this->ensureStaff();
        $tickets      = $this->ticketModel->all();
        $ticketReplies = [];
        foreach ($tickets as $ticket) {
            $ticketReplies[(int)$ticket['id']] = $this->ticketModel->repliesByTicketId((int)$ticket['id']);
        }
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return [
            'view' => 'admin/helpdesk',
            'data' => $this->withCommonData([
                'tickets'       => $tickets,
                'ticketReplies' => $ticketReplies,
                'flash'         => $flash,
                'section'       => 'helpdesk',
            ])
        ];
    }

    public function helpdeskReply(): void
    {
        $this->ensureStaff();
        $ticketId    = (int)($_POST['ticket_id']  ?? 0);
        $status      = $_POST['status']            ?? '';
        $replyText   = trim($_POST['reply_text']   ?? '');
        $validStatus = in_array($status, ['Open', 'In Progress', 'Resolved', 'Closed'], true);

        if ($ticketId <= 0 || !$validStatus) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid ticket update request.'];
            redirect('index.php?page=admin-helpdesk');
        }

        $this->ticketModel->updateStatus($ticketId, $status);
        if ($replyText !== '') {
            $this->ticketModel->addReply($ticketId, (int)$_SESSION['user']['id'], $replyText);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ticket updated successfully.'];
        redirect('index.php?page=admin-helpdesk');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  PROMOTIONS / CATEGORIES / REPORTS
    // ─────────────────────────────────────────────────────────────────────
    public function promotions(): array
    {
        $this->ensureAdmin();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return [
            'view' => 'admin/promotions',
            'data' => $this->withCommonData(['flash' => $flash, 'section' => 'promotions'])
        ];
    }

    public function categories(): array
    {
        $this->ensureAdmin();
        $categories = $this->categoryModel->all();
        $flash      = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return [
            'view' => 'admin/categories',
            'data' => $this->withCommonData([
                'categories' => $categories,
                'flash'      => $flash,
                'section'    => 'categories',
            ])
        ];
    }

    public function reports(): array
    {
        $this->ensureAdmin();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return [
            'view' => 'admin/reports',
            'data' => $this->withCommonData(['flash' => $flash, 'section' => 'reports'])
        ];
    }
}