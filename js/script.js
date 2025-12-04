// JavaScript for enhancing user experience
$(document).ready(function() {
    
    // Form validation
    $('#bookingForm').on('submit', function(e) {
        let isValid = true;
        
        // Name validation
        const name = $('#passenger_name').val().trim();
        if (name.length < 2) {
            showAlert('Name must be more than 2 characters', 'danger');
            isValid = false;
        }
        
        // Email validation
        const email = $('#passenger_email').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showAlert('Invalid email address', 'danger');
            isValid = false;
        }
        
        // Phone validation
        const phone = $('#passenger_phone').val();
        const phoneRegex = /^[0-9]{10,15}$/;
        if (!phoneRegex.test(phone)) {
            showAlert('Phone number must be between 10 and 15 digits', 'danger');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Show alerts
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.container').prepend(alertHtml);
        
        // Auto hide alert after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }
    
    // Date improvements
    const today = new Date().toISOString().split('T')[0];
    $('input[type="date"]').attr('min', today);
    
    // Card effects
    $('.flight-card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
    
    // Real-time search (advanced example)
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('.flight-card').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Confirm before deletion
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this booking?')) {
            e.preventDefault();
        }
    });
    
    // Dynamic data loading (example)
    function loadUserBookings() {
        if ($('#userBookings').length) {
            $.ajax({
                url: 'api/get_user_bookings.php',
                method: 'GET',
                success: function(data) {
                    $('#userBookings').html(data);
                },
                error: function() {
                    $('#userBookings').html('<div class="alert alert-danger">Error loading data</div>');
                }
            });
        }
    }
    
    // Call function if element exists
    loadUserBookings();
});

// Helper functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US');
}