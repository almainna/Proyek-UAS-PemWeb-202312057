<?php
require_once '../config/init.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = new Database();
$message = '';
$messageType = '';

// Handle form submission
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_user':
                $nama = trim($_POST['nama']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $role = $_POST['role'];
                
                // Validate input
                if (empty($nama) || empty($email) || empty($password) || empty($role)) {
                    throw new Exception('All fields are required');
                }
                
                // Check if email already exists
                $db->query("SELECT id FROM users WHERE email = :email");
                $db->bind(':email', $email);
                if ($db->single()) {
                    throw new Exception('Email already in use');
                }
                
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $db->query("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, :role)");
                $db->bind(':nama', $nama);
                $db->bind(':email', $email);
                $db->bind(':password', $hashedPassword);
                $db->bind(':role', $role);
                $db->execute();
                
                $message = 'User successfully added';
                $messageType = 'success';
                break;
                
            case 'update_user':
                $userId = $_POST['user_id'];
                $nama = trim($_POST['nama']);
                $email = trim($_POST['email']);
                $role = $_POST['role'];
                
                if (empty($nama) || empty($email) || empty($role)) {
                    throw new Exception('Name, email, and role are required');
                }
                
                // Check if email is used by another user
                $db->query("SELECT id FROM users WHERE email = :email AND id != :id");
                $db->bind(':email', $email);
                $db->bind(':id', $userId);
                if ($db->single()) {
                    throw new Exception('Email already in use');
                }
                
                // Update user
                $db->query("UPDATE users SET nama = :nama, email = :email, role = :role WHERE id = :id");
                $db->bind(':nama', $nama);
                $db->bind(':email', $email);
                $db->bind(':role', $role);
                $db->bind(':id', $userId);
                $db->execute();
                
                $message = 'User data successfully updated';
                $messageType = 'success';
                break;
                
            case 'delete_user':
                $userId = $_POST['user_id'];
                
                // Check if user exists
                $db->query("SELECT id FROM users WHERE id = :id");
                $db->bind(':id', $userId);
                if (!$db->single()) {
                    throw new Exception('User not found');
                }
                
                // Delete user
                $db->query("DELETE FROM users WHERE id = :id");
                $db->bind(':id', $userId);
                $db->execute();
                
                $message = 'User successfully deleted';
                $messageType = 'success';
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Get all users
$db->query("SELECT * FROM users ORDER BY nama");
$users = $db->resultset();

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">

        <!-- Main content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users me-2"></i>Manage Users</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i>Add User
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="fas fa-users text-muted mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-muted">No user data available</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['nama']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge bg-info"> <?= htmlspecialchars($user['role']) ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="editUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nama']) ?>', '<?= htmlspecialchars($user['email']) ?>', '<?= htmlspecialchars($user['role']) ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nama']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <small class="form-text text-muted">Minimum 6 characters</small>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="dokter">Doctor</option>
                            <option value="pasien">Patient</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="dokter">Doctor</option>
                            <option value="pasien">Patient</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Forms -->
<form id="deleteUserForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="user_id" id="delete_user_id">
</form>

<script>
function editUser(id, nama, email, role) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(id, nama) {
    if (confirm('Are you sure you want to delete user "' + nama + '"?')) {
        document.getElementById('delete_user_id').value = id;
        document.getElementById('deleteUserForm').submit();
    }
}
</script>

<?php include '../includes/admin-footer.php'; ?>

