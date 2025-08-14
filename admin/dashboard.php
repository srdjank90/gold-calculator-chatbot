<div class="wrap">
    <h1>Gold Calculator Chatbot Dashboard</h1>
    
    <div class="gcc-dashboard-stats">
        <div class="gcc-stat-card">
            <div class="gcc-stat-number"><?php echo number_format($total_submits); ?></div>
            <div class="gcc-stat-label">Total Submits</div>
        </div>
        
        <div class="gcc-stat-card">
            <div class="gcc-stat-number"><?php echo number_format($recent_submits_count); ?></div>
            <div class="gcc-stat-label">Recent Submits (24h)</div>
        </div>
        
        <div class="gcc-stat-card">
            <div class="gcc-stat-number"><?php echo number_format($total_products); ?></div>
            <div class="gcc-stat-label">Published Products</div>
        </div>
        
        <div class="gcc-stat-card">
            <div class="gcc-stat-number"><?php echo number_format($total_products_draft); ?></div>
            <div class="gcc-stat-label">Draft Products</div>
        </div>
    </div>
    
    <div class="gcc-dashboard-grid">
        <div class="gcc-dashboard-section">
            <h2>API Sync Status</h2>
            <div class="gcc-api-status">
                <div class="gcc-status-indicator <?php echo $api_status['status']; ?>">
                    <span class="gcc-status-dot"></span>
                    <span class="gcc-status-text">
                        <?php 
                        switch($api_status['status']) {
                            case 'active':
                                echo 'Active';
                                break;
                            case 'pending':
                                echo 'Pending First Sync';
                                break;
                            case 'not_configured':
                                echo 'Not Configured';
                                break;
                            default:
                                echo 'Unknown';
                        }
                        ?>
                    </span>
                </div>
                
                <div class="gcc-api-details">
                    <p><strong>Last Sync:</strong> <?php echo $api_status['last_sync_formatted']; ?></p>
                    <?php if ($api_status['url']): ?>
                        <p><strong>URL:</strong> <code><?php echo esc_html($api_status['url']); ?></code></p>
                    <?php endif; ?>
                </div>
                
                <div class="gcc-api-actions">
                    <button id="gcc-test-api" class="button button-secondary">Test Connection</button>
                    <button id="gcc-sync-api" class="button button-primary">Sync Now</button>
                </div>
            </div>
        </div>
        
        <div class="gcc-dashboard-section">
            <h2>Recent Submits</h2>
            <div class="gcc-recent-submits">
                <?php if (!empty($recent_submits)): ?>
                    <table class="gcc-submits-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Platform</th>
                                <th>IP Address</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_submits as $submit): ?>
                                <tr>
                                    <td><?php echo esc_html($submit->name); ?></td>
                                    <td><?php echo esc_html($submit->email); ?></td>
                                    <td><?php echo esc_html($submit->phone); ?></td>
                                    <td><?php echo esc_html($submit->platform); ?></td>
                                    <td><?php echo esc_html($submit->ip_address); ?></td>
                                    <td><?php echo date('M j, Y H:i', strtotime($submit->created_date)); ?></td>
                                    <td>
                                        <button class="button button-small gcc-delete-submit" data-submit-id="<?php echo $submit->id; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="gcc-view-all">
                        <a href="<?php echo admin_url('admin.php?page=gcc-submits'); ?>" class="button">View All Submits</a>
                    </div>
                <?php else: ?>
                    <div class="gcc-empty-state">
                        <p>No submits yet. The chatbot will generate submits when customers interact with it.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="gcc-dashboard-section">
        <h2>Quick Actions</h2>
        <div class="gcc-quick-actions">
            <a href="<?php echo admin_url('admin.php?page=gcc-settings'); ?>" class="button button-primary">
                <span class="dashicons dashicons-admin-settings"></span>
                Settings
            </a>
            <a href="<?php echo admin_url('admin.php?page=gcc-products'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-products"></span>
                Manage Products
            </a>
            <a href="<?php echo admin_url('admin.php?page=gcc-submits'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-feedback"></span>
                Manage Submits
            </a>
            <button id="gcc-test-chatbot" class="button button-secondary">
                <span class="dashicons dashicons-format-chat"></span>
                Test Chatbot
            </button>
        </div>
    </div>
    
    <div class="gcc-dashboard-section">
        <h2>Available Shortcodes</h2>
        <div class="gcc-shortcode-info">
            <p>Use these shortcodes to display the chatbot on any page or post:</p>
            
            <div class="gcc-shortcode-item">
                <h4>Full Chatbot Interface</h4>
                <div class="gcc-shortcode-box">
                    <code>[gold_calculator_chatbot]</code>
                    <button class="button button-small" onclick="navigator.clipboard.writeText('[gold_calculator_chatbot]')">Copy</button>
                </div>
                <p><small>Displays the complete chatbot interface embedded in the page.</small></p>
            </div>
            
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Test API connection
    $('#gcc-test-api').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('Testing...');
        
        $.ajax({
            url: gcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'gcc_test_api_connection',
                nonce: gcc_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Connection successful! Found ' + response.data.product_count + ' products.');
                } else {
                    alert('Connection failed: ' + response.data.message);
                }
            },
            error: function() {
                alert('Connection test failed.');
            },
            complete: function() {
                button.prop('disabled', false).text('Test Connection');
            }
        });
    });
    
    // Sync API
    $('#gcc-sync-api').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('Syncing...');
        
        $.ajax({
            url: gcc_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'gcc_manual_api_update',
                nonce: gcc_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Sync successful! Updated ' + response.data.count + ' products.');
                    location.reload();
                } else {
                    alert('Sync failed: ' + response.data.message);
                }
            },
            error: function() {
                alert('Sync failed.');
            },
            complete: function() {
                button.prop('disabled', false).text('Sync Now');
            }
        });
    });
    
    // Test chatbot
    $('#gcc-test-chatbot').on('click', function() {
        var testWindow = window.open('', 'chatbot_test', 'width=700,height=600');
        testWindow.document.write(`
            <html>
                <head>
                    <title>Chatbot Test</title>
                    <link rel="stylesheet" href="<?php echo GCC_PLUGIN_URL; ?>assets/css/chatbot.css">
                </head>
                <body>
                    <div style="padding: 20px;">
                        <h2>Chatbot Test</h2>
                        <div id="gcc-chatbot-container">
                            <!-- Chatbot will be loaded here -->
                        </div>
                    </div>
                    <script src="<?php echo includes_url('js/jquery/jquery.js'); ?>"></script>
                    <script src="<?php echo GCC_PLUGIN_URL; ?>assets/js/chatbot.js"></script>
                </body>
            </html>
        `);
    });
    
    // Delete submit functionality
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