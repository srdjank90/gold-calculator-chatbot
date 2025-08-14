<div class="wrap">
    <h1>Gold Calculator Submits</h1>
    
    <div class="gcc-submits-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="gcc-submits">
            <input type="text" name="search" placeholder="Search submits..." value="<?php echo esc_attr($search); ?>">
            <select name="per_page">
                <option value="10" <?php selected($per_page, 10); ?>>10 per page</option>
                <option value="25" <?php selected($per_page, 25); ?>>25 per page</option>
                <option value="50" <?php selected($per_page, 50); ?>>50 per page</option>
                <option value="100" <?php selected($per_page, 100); ?>>100 per page</option>
            </select>
            <select name="order_by">
                <option value="created_date" <?php selected($order_by, 'created_date'); ?>>Date Created</option>
                <option value="name" <?php selected($order_by, 'name'); ?>>Name</option>
                <option value="email" <?php selected($order_by, 'email'); ?>>Email</option>
                <option value="budget" <?php selected($order_by, 'budget'); ?>>Budget</option>
                <option value="type" <?php selected($order_by, 'type'); ?>>Type</option>
            </select>
            <select name="order">
                <option value="DESC" <?php selected($order, 'DESC'); ?>>Descending</option>
                <option value="ASC" <?php selected($order, 'ASC'); ?>>Ascending</option>
            </select>
            <button type="submit" class="button">Filter</button>
            <a href="<?php echo admin_url('admin.php?page=gcc-submits'); ?>" class="button">Reset</a>
        </form>
    </div>
    
    <div class="gcc-submits-container">
        <?php if (!empty($submits)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Budget</th>
                        <th>Type</th>
                        <th>Delivery</th>
                        <th>Selected Products</th>
                        <th>Total Amount</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submits as $submit): ?>
                        <tr>
                            <td><?php echo esc_html($submit->id); ?></td>
                            <td><?php echo esc_html($submit->name); ?></td>
                            <td><a href="mailto:<?php echo esc_attr($submit->email); ?>"><?php echo esc_html($submit->email); ?></a></td>
                            <td><?php echo esc_html($submit->phone); ?></td>
                            <td><?php echo esc_html($submit->budget); ?></td>
                            <td><?php echo esc_html($submit->type); ?></td>
                            <td><?php echo esc_html($submit->delivery); ?></td>
                            <td>
                                <?php 
                                $products = json_decode($submit->selected_products, true);
                                if (!empty($products)) {
                                    echo '<ul class="gcc-product-list">';
                                    foreach ($products as $product) {
                                        echo '<li><strong>' . esc_html($product['name']) . '</strong> (Qty: ' . esc_html($product['quantity']) . ')</li>';
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<em>No products selected</em>';
                                }
                                ?>
                            </td>
                            <td><?php echo $submit->total_amount > 0 ? '€' . number_format($submit->total_amount, 2) : '-'; ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($submit->created_date)); ?></td>
                            <td>
                                <button class="button button-small gcc-delete-submit" data-submit-id="<?php echo $submit->id; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            // Pagination
            $total_pages = ceil($total_submits / $per_page);
            if ($total_pages > 1) {
                echo '<div class="gcc-pagination">';
                echo '<span class="gcc-pagination-info">Page ' . $page . ' of ' . $total_pages . ' (' . $total_submits . ' total submits)</span>';
                echo '<div class="gcc-pagination-links">';
                
                $base_url = admin_url('admin.php?page=gcc-submits');
                $query_params = array(
                    'per_page' => $per_page,
                    'search' => $search,
                    'order_by' => $order_by,
                    'order' => $order
                );
                
                if ($page > 1) {
                    $prev_url = add_query_arg(array_merge($query_params, array('paged' => $page - 1)), $base_url);
                    echo '<a href="' . esc_url($prev_url) . '" class="button">« Previous</a>';
                }
                
                for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
                    if ($i == $page) {
                        echo '<span class="button button-primary">' . $i . '</span>';
                    } else {
                        $page_url = add_query_arg(array_merge($query_params, array('paged' => $i)), $base_url);
                        echo '<a href="' . esc_url($page_url) . '" class="button">' . $i . '</a>';
                    }
                }
                
                if ($page < $total_pages) {
                    $next_url = add_query_arg(array_merge($query_params, array('paged' => $page + 1)), $base_url);
                    echo '<a href="' . esc_url($next_url) . '" class="button">Next »</a>';
                }
                
                echo '</div>';
                echo '</div>';
            }
            ?>
        <?php else: ?>
            <div class="gcc-empty-state">
                <p>No submits found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.gcc-delete-submit').on('click', function() {
        var submitId = $(this).data('submit-id');
        var row = $(this).closest('tr');
        
        if (confirm('Are you sure you want to delete this submit?')) {
            $.ajax({
                url: gcc_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'gcc_delete_submit',
                    submit_id: submitId,
                    nonce: gcc_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(function() {
                            row.remove();
                        });
                        alert('Submit deleted successfully.');
                    } else {
                        alert('Failed to delete submit: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Failed to delete submit.');
                }
            });
        }
    });
});
</script>

<style>
.gcc-submits-filters {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
}

.gcc-submits-filters form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.gcc-submits-filters input,
.gcc-submits-filters select {
    padding: 5px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.gcc-product-list {
    margin: 0;
    padding: 0;
    list-style: none;
}

.gcc-product-list li {
    margin: 2px 0;
    font-size: 12px;
}

.gcc-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}

.gcc-pagination-info {
    color: #666;
}

.gcc-pagination-links {
    display: flex;
    gap: 5px;
}

.gcc-pagination-links .button {
    text-decoration: none;
}

.gcc-pagination-links .button-primary {
    cursor: default;
}
</style>