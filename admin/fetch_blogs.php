<?php
require '../includes/db_connect.php';

$blogsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$month = isset($_GET['month']) ? trim($_GET['month']) : '';

$offset = ($page - 1) * $blogsPerPage;

// ✅ Query to Fetch Blogs with Filters
$query = "SELECT id, title, created_at FROM blogs WHERE 1";
if ($search !== '') $query .= " AND title LIKE '%$search%'";
if ($month !== '') $query .= " AND MONTH(created_at) = '$month'";
$query .= " ORDER BY created_at DESC LIMIT $blogsPerPage OFFSET $offset";

$result = $conn->query($query);

// ✅ Query to Get Total Blog Count
$totalQuery = "SELECT COUNT(*) as count FROM blogs WHERE 1";
if ($search !== '') $totalQuery .= " AND title LIKE '%$search%'";
if ($month !== '') $totalQuery .= " AND MONTH(created_at) = '$month'";

$totalBlogs = $conn->query($totalQuery)->fetch_assoc()['count'];
$totalPages = ceil($totalBlogs / $blogsPerPage);
?>

<!-- ✅ Table Display -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= date('d M Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <a href="edit_blog.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                    <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $row['id']; ?>">🗑️ Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- ✅ Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteModalLabel">⚠️ Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this blog? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirmDelete" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Pagination Section -->
<div class="pagination-container text-center mt-4">
    <p class="text-light">Showing <b><?= ($offset + 1); ?></b> to <b><?= min(($offset + $blogsPerPage), $totalBlogs); ?></b> of <b><?= $totalBlogs; ?></b> Entries</p>

    <div class="d-flex justify-content-center">
        <?php if ($page > 1): ?>
            <a href="#" class="pagination-btn mx-2 pagination-link" data-page="<?= ($page - 1); ?>">Prev</a>
        <?php else: ?>
            <span class="pagination-btn mx-2 disabled">Prev</span>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="#" class="pagination-btn mx-2 pagination-link" data-page="<?= ($page + 1); ?>">Next</a>
        <?php else: ?>
            <span class="pagination-btn mx-2 disabled">Next</span>
        <?php endif; ?>
    </div>
</div>
