<?php
require_once '../../includes/db_connect.php';
require_once '../AdminPortal/includes/auth_check.php';
require_once '../AdminPortal/includes/header.php';

// Handle Form Submission (Add/Update/Delete) would go here
// For now, we fetch data
$sql = "SELECT * FROM products ORDER BY category";
$result = $conn->query($sql);
?>

<div class="section-title flex-between">
    <div style="display:flex; align-items:center; gap:15px;">
        <h2>Product Management</h2>
        <span style="background:var(--primary-orange); color:white; padding:5px 12px; border-radius:20px; font-size:0.9rem; font-weight:bold;">
            Total: <?= $result->num_rows ?>
        </span>
    </div>
    <a href="add_product.php" class="btn btn-primary">
        <i class="ph ph-plus"></i> Add New Product
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width: 80px;">Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Stock Status</th>
                <th>Price / Ton (₹)</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../../<?= $row['image_url'] ?>" alt="prod"
                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['name']) ?></strong>
                        </td>
                        <td><span class="badge badge-info"><?= ucfirst($row['category']) ?></span></td>
                        <td>
                            <?php
                            $statusClass = 'badge-success';
                            if ($row['stock_status'] == 'low_stock')
                                $statusClass = 'badge-warning';
                            if ($row['stock_status'] == 'out_of_stock')
                                $statusClass = 'badge-danger';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= str_replace('_', ' ', ucfirst($row['stock_status'])) ?></span>
                        </td>
                        <td>
                            <strong>₹ <?= number_format($row['price_per_ton']) ?></strong>
                        </td>
                        <td>
                            <div style="display: flex; gap: 10px; justify-content: center;">
                                <button class="btn btn-outline btn-sm"
                                    style="color: var(--primary-navy); border-color: var(--primary-navy); display:inline-flex; align-items:center; justify-content:center; cursor:pointer;"
                                    title="Edit Product"
                                    onclick='openEditModal(<?php echo json_encode($row); ?>)'>
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn btn-outline btn-sm"
                                    style="color: #ef4444; border-color: #ef4444; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;"
                                    title="Delete Product"
                                    onclick="openDeleteModal(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Fallback if DB is empty/connection fails -->
                <tr>
                    <td colspan="6" class="text-center">No products found. Import database.sql to see data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ERROR NOTIFICATION -->
<div id="admin-error" 
     style="display:none; position:fixed; top:20px; right:20px; background:#fee2e2; color:#dc2626; padding:15px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:1000; animation: slideIn 0.3s ease-out;">
    <div style="display:flex; align-items:center; gap:10px;">
        <i class="ph-fill ph-warning-circle" style="font-size:1.2rem;"></i>
        <span id="error-text">Something went wrong</span>
        <button onclick="document.getElementById('admin-error').style.display='none'" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:1.2rem;">&times;</button>
    </div>
</div>

<!-- FULL EDIT MODAL -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:white; padding:30px; border-radius:8px; width:450px; box-shadow:0 10px 25px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-top:0; color:var(--primary-navy); margin-bottom: 20px;">Edit Product</h3>
        
        <form id="editProductForm" onsubmit="saveProduct(event)">
            <input type="hidden" id="edit-id" name="id">
            
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; font-size:0.85rem; margin-bottom:5px; font-weight: 600;">Product Name</label>
                <input type="text" id="edit-name" name="name" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius: 4px;" required>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; font-size:0.85rem; margin-bottom:5px; font-weight: 600;">Category</label>
                <select id="edit-category" name="category" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius: 4px;">
                    <option value="sand">M-Sand / P-Sand</option>
                    <option value="aggregate">Blue Metal / Aggregates</option>
                    <option value="stone">Gravel / Stones</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; font-size:0.85rem; margin-bottom:5px; font-weight: 600;">Stock Status</label>
                <select id="edit-stock" name="stock_status" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius: 4px;">
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block; font-size:0.85rem; margin-bottom:5px; font-weight: 600;">Price per Ton (₹)</label>
                <input type="number" id="edit-price" name="price" step="0.01" class="form-control" style="width:100%; padding:10px; border:1px solid #ccc; border-radius: 4px;" required>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; font-size:0.85rem; margin-bottom:5px; font-weight: 600;">Update Image (Optional)</label>
                <input type="file" id="edit-image" name="image" class="form-control" accept="image/*" style="width:100%; padding:5px; border:1px solid #ccc; border-radius: 4px;">
                <small style="color: gray;">Leave empty to keep current image</small>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeModal()" class="btn btn-outline" style="padding:10px 20px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:white; padding:25px; border-radius:8px; width:300px; box-shadow:0 10px 25px rgba(0,0,0,0.2); text-align:center;">
        <div style="width:50px; height:50px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px;">
            <i class="ph-fill ph-trash" style="font-size:1.5rem; color:#dc2626;"></i>
        </div>
        <h3 style="margin-top:0; color:#1f2937;">Delete Product?</h3>
        <p style="color:gray; font-size:0.9rem; margin-bottom:20px;">Are you sure you want to delete <span id="del-modal-name" style="font-weight:bold;"></span>? This cannot be undone.</p>
        
        <input type="hidden" id="del-modal-id">
        <div style="display:flex; justify-content:center; gap:10px;">
            <button onclick="closeDelModal()" class="btn btn-outline" style="padding:8px 20px;">Cancel</button>
            <button onclick="confirmDelete()" class="btn" style="padding:8px 20px; background:#dc2626; color:white; border:none;">Yes, Delete</button>
        </div>
    </div>
</div>

<script>
    // --- Error Handling ---
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('error')) {
        showError("Action Failed: " + urlParams.get('error'));
    }

    function showError(msg) {
        const box = document.getElementById('admin-error');
        document.getElementById('error-text').innerText = msg;
        box.style.display = 'block';
        setTimeout(() => box.style.display = 'none', 5000);
    }

    // --- Edit Modal Functions ---
    function openEditModal(product) {
        document.getElementById('editModal').style.display = 'flex';
        
        // Populate fields
        document.getElementById('edit-id').value = product.id;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-category').value = product.category;
        document.getElementById('edit-stock').value = product.stock_status;
        document.getElementById('edit-price').value = product.price_per_ton;
        document.getElementById('edit-image').value = ""; // Reset file input
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('deleteModal').style.display = 'none';
    }

    function saveProduct(event) {
        event.preventDefault();
        
        const form = document.getElementById('editProductForm');
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        
        submitBtn.innerText = "Saving...";
        submitBtn.disabled = true;

        fetch('update_product.php', {
            method: 'POST',
            body: formData // Auto-sets multipart/form-data
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); 
            } else {
                submitBtn.innerText = originalText;
                submitBtn.disabled = false;
                showError("Update Failed: " + (data.message || "Unknown error"));
            }
        })
        .catch(err => {
            submitBtn.innerText = originalText;
            submitBtn.disabled = false;
            showError("Network Error: " + err.message);
        });
    }

    // --- Delete Modal Functions ---
    function openDeleteModal(id, name) {
        document.getElementById('deleteModal').style.display = 'flex';
        document.getElementById('del-modal-id').value = id;
        document.getElementById('del-modal-name').innerText = name;
    }

    function closeDelModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    function confirmDelete() {
        const id = document.getElementById('del-modal-id').value;
        fetch('../AdminPortal/delete_item.php?type=product&ajax=1&id=' + id)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                closeDelModal();
                showError("Delete Failed: " + (data.message || "Unknown error"));
            }
        })
        .catch(err => {
            closeDelModal();
            showError("Network Error: " + err.message);
        });
    }

    // Close on outside click
    window.onclick = function(event) {
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');
        if (event.target == editModal) closeModal();
        if (event.target == deleteModal) closeDelModal();
    }
</script>

<?php require_once '../AdminPortal/includes/footer.php'; ?>