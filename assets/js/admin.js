jQuery(document).ready(function ($) {
  // Prevent any other scripts from interfering with our tabs
  $(".nav-tab").off("click.tab"); // Remove any tab click handlers

  // Override any existing click handlers on nav-tab elements that might be causing issues
  $('a.nav-tab[href*="admin.php?page=gcc-settings"]')
    .off("click")
    .on("click", function (e) {
      // Let the default link behavior work (URL navigation)
      // Don't prevent default or do any custom handling
      return true;
    });

  // Copy shortcode functionality
  $(document).on("click", ".gcc-copy-shortcode", function () {
    var shortcode = $(this).data("shortcode");

    // Create temporary textarea
    var temp = $("<textarea>");
    $("body").append(temp);
    temp.val(shortcode).select();
    document.execCommand("copy");
    temp.remove();

    // Show feedback
    $(this).text("Copied!").prop("disabled", true);
    setTimeout(() => {
      $(this).text("Copy").prop("disabled", false);
    }, 2000);
  });

  // Auto-save settings
  $(
    "#gcc-settings-form input, #gcc-settings-form textarea, #gcc-settings-form select"
  ).on("change", function () {
    var $form = $("#gcc-settings-form");
    var formData = $form.serialize();

    $.ajax({
      url: gcc_admin_ajax.ajax_url,
      type: "POST",
      data:
        formData + "&action=gcc_save_settings&nonce=" + gcc_admin_ajax.nonce,
      success: function (response) {
        if (response.success) {
          showNotice("Settings saved successfully!", "success");
        } else {
          showNotice(
            "Failed to save settings: " + response.data.message,
            "error"
          );
        }
      },
      error: function () {
        showNotice("Failed to save settings.", "error");
      },
    });
  });

  // Product management
  $(".gcc-edit-product").on("click", function () {
    var productId = $(this).data("product-id");
    openProductModal(productId);
  });

  $(".gcc-delete-product").on("click", function () {
    var productId = $(this).data("product-id");
    var productName = $(this).data("product-name");

    if (confirm('Are you sure you want to delete "' + productName + '"?')) {
      deleteProduct(productId);
    }
  });

  // Quote status updates
  $(".gcc-quote-status").on("change", function () {
    var quoteId = $(this).data("quote-id");
    var newStatus = $(this).val();

    updateQuoteStatus(quoteId, newStatus);
  });

  // Bucket management
  $(".gcc-edit-bucket").on("click", function () {
    var bucketId = $(this).data("bucket-id");
    openBucketModal(bucketId);
  });

  $(".gcc-delete-bucket").on("click", function () {
    var bucketId = $(this).data("bucket-id");

    if (confirm("Are you sure you want to delete this bucket?")) {
      deleteBucket(bucketId);
    }
  });

  // API sync functionality
  $("#gcc-sync-api").on("click", function () {
    var $button = $(this);
    $button.prop("disabled", true).text("Syncing...");

    $.ajax({
      url: gcc_admin_ajax.ajax_url,
      type: "POST",
      data: {
        action: "gcc_manual_api_update",
        nonce: gcc_admin_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotice(
            "API sync successful! Updated " +
              response.data.count +
              " products.",
            "success"
          );
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          showNotice("API sync failed: " + response.data.message, "error");
        }
      },
      error: function () {
        showNotice("API sync failed.", "error");
      },
      complete: function () {
        $button.prop("disabled", false).text("Sync Now");
      },
    });
  });

  // Test API connection
  $("#gcc-test-api").on("click", function () {
    var $button = $(this);
    $button.prop("disabled", true).text("Testing...");

    $.ajax({
      url: gcc_admin_ajax.ajax_url,
      type: "POST",
      data: {
        action: "gcc_test_api_connection",
        nonce: gcc_admin_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotice(
            "API connection test successful! Found " +
              response.data.product_count +
              " products.",
            "success"
          );
        } else {
          showNotice(
            "API connection test failed: " + response.data.message,
            "error"
          );
        }
      },
      error: function () {
        showNotice("API connection test failed.", "error");
      },
      complete: function () {
        $button.prop("disabled", false).text("Test Connection");
      },
    });
  });

  // Helper functions
  function showNotice(message, type) {
    var noticeClass = "notice-" + type;
    var notice = $(
      '<div class="notice ' +
        noticeClass +
        ' is-dismissible"><p>' +
        message +
        "</p></div>"
    );

    $(".wrap h1").after(notice);

    // Auto-hide after 5 seconds
    setTimeout(() => {
      notice.fadeOut(() => {
        notice.remove();
      });
    }, 5000);
  }

  function openProductModal(productId) {
    // This would open a modal for editing products
    // For now, just show a placeholder
    // alert('Product edit modal would open here for product ID: ' + productId);
  }

  function deleteProduct(productId) {
    $.ajax({
      url: gcc_admin_ajax.ajax_url,
      type: "POST",
      data: {
        action: "gcc_delete_product",
        product_id: productId,
        nonce: gcc_admin_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotice("Product deleted successfully!", "success");
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          showNotice(
            "Failed to delete product: " + response.data.message,
            "error"
          );
        }
      },
      error: function () {
        showNotice("Failed to delete product.", "error");
      },
    });
  }

  function updateQuoteStatus(quoteId, newStatus) {
    $.ajax({
      url: gcc_admin_ajax.ajax_url,
      type: "POST",
      data: {
        action: "gcc_update_quote_status",
        quote_id: quoteId,
        status: newStatus,
        nonce: gcc_admin_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotice("Quote status updated successfully!", "success");
        } else {
          showNotice(
            "Failed to update quote status: " + response.data.message,
            "error"
          );
        }
      },
      error: function () {
        showNotice("Failed to update quote status.", "error");
      },
    });
  }

  function openBucketModal(bucketId) {
    // This would open a modal for editing buckets
    // For now, just show a placeholder
    alert("Bucket edit modal would open here for bucket ID: " + bucketId);
  }

  function deleteBucket(bucketId) {
    $.ajax({
      url: gcc_admin_ajax.ajax_url,
      type: "POST",
      data: {
        action: "gcc_delete_bucket",
        bucket_id: bucketId,
        nonce: gcc_admin_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotice("Bucket deleted successfully!", "success");
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          showNotice(
            "Failed to delete bucket: " + response.data.message,
            "error"
          );
        }
      },
      error: function () {
        showNotice("Failed to delete bucket.", "error");
      },
    });
  }

  // Real-time validation
  $('input[type="email"]').on("blur", function () {
    var email = $(this).val();
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email && !emailRegex.test(email)) {
      $(this).addClass("error");
      showNotice("Please enter a valid email address.", "error");
    } else {
      $(this).removeClass("error");
    }
  });

  $('input[type="url"]').on("blur", function () {
    var url = $(this).val();
    var urlRegex =
      /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;

    if (url && !urlRegex.test(url)) {
      $(this).addClass("error");
      showNotice("Please enter a valid URL.", "error");
    } else {
      $(this).removeClass("error");
    }
  });

  // Auto-refresh dashboard stats every 30 seconds
  if ($(".gcc-dashboard-stats").length > 0) {
    setInterval(function () {
      $.ajax({
        url: gcc_admin_ajax.ajax_url,
        type: "POST",
        data: {
          action: "gcc_get_dashboard_stats",
          nonce: gcc_admin_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            updateDashboardStats(response.data);
          }
        },
      });
    }, 30000); // 30 seconds
  }

  function updateDashboardStats(stats) {
    $(".gcc-stat-card").each(function () {
      var type = $(this).data("stat-type");
      if (stats[type]) {
        $(this).find(".gcc-stat-number").text(stats[type]);
      }
    });
  }

  // Initialize tooltips if available
  if (typeof $.fn.tooltip !== "undefined") {
    $("[data-tooltip]").tooltip();
  }

  // Form validation
  $("form").on("submit", function (e) {
    var valid = true;

    // Check required fields
    $(this)
      .find("input[required], textarea[required], select[required]")
      .each(function () {
        if (!$(this).val()) {
          $(this).addClass("error");
          valid = false;
        } else {
          $(this).removeClass("error");
        }
      });

    // Check email fields
    $(this)
      .find('input[type="email"]')
      .each(function () {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !emailRegex.test(email)) {
          $(this).addClass("error");
          valid = false;
        } else {
          $(this).removeClass("error");
        }
      });

    if (!valid) {
      e.preventDefault();
      showNotice("Please fix the errors before submitting.", "error");
    }
  });
});
