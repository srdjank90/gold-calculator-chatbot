<div class="wrap">
    <h1>Gold Calculator Products</h1>

    <div class="gcc-products-header">
        <button id="gcc-add-product" class="button button-primary">Add New Product</button>

        <div class="gcc-products-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="gcc-products">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo esc_attr($search); ?>">
                <select name="per_page">
                    <option value="10" <?php selected($per_page, 10); ?>>10 per page</option>
                    <option value="25" <?php selected($per_page, 25); ?>>25 per page</option>
                    <option value="50" <?php selected($per_page, 50); ?>>50 per page</option>
                    <option value="100" <?php selected($per_page, 100); ?>>100 per page</option>
                </select>
                <select name="order_by">
                    <option value="created_at" <?php selected($order_by, 'created_at'); ?>>Date Created</option>
                    <option value="name" <?php selected($order_by, 'name'); ?>>Name</option>
                    <option value="external_id" <?php selected($order_by, 'external_id'); ?>>External ID</option>
                    <option value="price" <?php selected($order_by, 'price'); ?>>Price</option>
                    <option value="status" <?php selected($order_by, 'status'); ?>>Status</option>
                </select>
                <select name="order">
                    <option value="DESC" <?php selected($order, 'DESC'); ?>>Descending</option>
                    <option value="ASC" <?php selected($order, 'ASC'); ?>>Ascending</option>
                </select>
                <button type="submit" class="button">Filter</button>
                <a href="<?php echo admin_url('admin.php?page=gcc-products'); ?>" class="button">Reset</a>
            </form>
        </div>
    </div>

    <div class="gcc-products-container">
        <?php if (!empty($products)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>External ID</th>
                        <th>Price</th>
                        <th>Price Avans</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo esc_html($product->id); ?></td>
                            <td><?php echo esc_html($product->name); ?></td>
                            <td><?php echo esc_html($product->slug); ?></td>
                            <td><?php echo esc_html($product->external_id); ?></td>
                            <td><?php echo number_format($product->price, 2); ?> RSD</td>
                            <td><?php echo number_format($product->price_avans, 2); ?> RSD</td>
                            <td>
                                <span class="gcc-status-badge gcc-status-<?php echo $product->status; ?>">
                                    <?php echo ucfirst($product->status); ?>
                                </span>
                            </td>
                            <td>
                                <button class="button button-small gcc-edit-product" data-product-id="<?php echo $product->id; ?>">Edit</button>
                                <button class="button button-small gcc-delete-product" data-product-id="<?php echo $product->id; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            // Pagination
            $total_pages = ceil($total_products / $per_page);
            if ($total_pages > 1) {
                echo '<div class="gcc-pagination">';
                echo '<span class="gcc-pagination-info">Page ' . $page . ' of ' . $total_pages . ' (' . $total_products . ' total products)</span>';
                echo '<div class="gcc-pagination-links">';

                $base_url = admin_url('admin.php?page=gcc-products');
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
                <p>No products found. <a href="#" id="gcc-add-first-product">Add your first product</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Modal -->
<div id="gcc-product-modal" class="gcc-modal" style="display: none;">
    <div class="gcc-modal-overlay"></div>
    <div class="gcc-modal-content">
        <div class="gcc-modal-header">
            <h3 id="gcc-modal-title">Add New Product</h3>
            <button class="gcc-modal-close">&times;</button>
        </div>
        <div class="gcc-modal-body">
            <form id="gcc-product-form">
                <input type="hidden" id="product-id" name="product_id" value="">

                <div class="gcc-form-row">
                    <div class="gcc-form-group">
                        <label for="product-name">Name *</label>
                        <input type="text" id="product-name" name="name" required>
                    </div>
                    <div class="gcc-form-group">
                        <label for="product-slug">Slug *</label>
                        <input type="text" id="product-slug" name="slug" required>
                    </div>
                </div>

                <div class="gcc-form-row">
                    <div class="gcc-form-group">
                        <label for="product-external-id">External ID</label>
                        <input type="number" id="product-external-id" name="external_id">
                    </div>
                </div>

                <div class="gcc-form-row">
                    <div class="gcc-form-group">
                        <label for="product-price">Price *</label>
                        <input type="number" id="product-price" name="price" step="0.01" required>
                    </div>
                    <div class="gcc-form-group">
                        <label for="product-price-avans">Price Avans *</label>
                        <input type="number" id="product-price-avans" name="price_avans" step="0.01" required>
                    </div>
                </div>

                <div class="gcc-form-row">
                    <div class="gcc-form-group">
                        <label for="product-status">Status</label>
                        <select id="product-status" name="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                <div class="gcc-form-group">
                    <label for="product-description">Description</label>
                    <textarea id="product-description" name="description" rows="3"></textarea>
                </div>

                <div class="gcc-form-actions">
                    <button type="submit" class="button button-primary">Save Product</button>
                    <button type="button" class="button gcc-modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .gcc-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
    }

    .gcc-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .gcc-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .gcc-modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
    }

    .gcc-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
    }

    .gcc-modal-body {
        padding: 20px;
    }

    .gcc-form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }

    .gcc-form-group {
        flex: 1;
    }

    .gcc-form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .gcc-form-group input,
    .gcc-form-group select,
    .gcc-form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .gcc-form-actions {
        margin-top: 20px;
        text-align: right;
    }

    .gcc-form-actions .button {
        margin-left: 10px;
    }

    .gcc-status-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .gcc-status-published {
        background: #d4edda;
        color: #155724;
    }

    .gcc-status-draft {
        background: #f8d7da;
        color: #721c24;
    }

    .gcc-products-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .gcc-products-filters form {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .gcc-products-filters input,
    .gcc-products-filters select {
        padding: 5px 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
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

<script>
    jQuery(document).ready(function($) {
        // Add product button
        $('#gcc-add-product, #gcc-add-first-product').on('click', function() {
            $('#gcc-modal-title').text('Add New Product');
            $('#gcc-product-form')[0].reset();
            $('#product-id').val('');
            $('#gcc-product-modal').show();
        });

        // Edit product button
        $('.gcc-edit-product').on('click', function() {
            var productId = $(this).data('product-id');
            $('#gcc-modal-title').text('Edit Product');
            $('#product-id').val(productId);

            // Load product data
            $.ajax({
                url: gcc_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'gcc_get_product',
                    product_id: productId,
                    nonce: gcc_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var product = response.data;
                        $('#product-name').val(product.name);
                        $('#product-slug').val(product.slug);
                        $('#product-external-id').val(product.external_id);
                        $('#product-price').val(product.price);
                        $('#product-price-avans').val(product.price_avans);
                        $('#product-status').val(product.status);
                        $('#product-description').val(product.description);
                        $('#gcc-product-modal').show();
                    } else {
                        alert('Failed to load product data: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Failed to load product data.');
                }
            });
        });

        // Delete product button
        $('.gcc-delete-product').on('click', function() {
            var productId = $(this).data('product-id');
            var row = $(this).closest('tr');

            if (confirm('Are you sure you want to delete this product?')) {
                $.ajax({
                    url: gcc_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'gcc_delete_product',
                        product_id: productId,
                        nonce: gcc_admin_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            row.fadeOut(function() {
                                row.remove();
                            });
                            alert('Product deleted successfully.');
                        } else {
                            alert('Failed to delete product: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Failed to delete product.');
                    }
                });
            }
        });

        // Close modal
        $('.gcc-modal-close, .gcc-modal-overlay').on('click', function() {
            $('#gcc-product-modal').hide();
        });

        // Save product form
        $('#gcc-product-form').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var productId = $('#product-id').val();
            var action = productId ? 'gcc_update_product' : 'gcc_create_product';

            formData += '&action=' + action + '&nonce=' + gcc_admin_ajax.nonce;

            $.ajax({
                url: gcc_admin_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#gcc-product-modal').hide();
                        alert('Product saved successfully.');
                        location.reload();
                    } else {
                        alert('Failed to save product: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Failed to save product.');
                }
            });
        });
    });
</script>