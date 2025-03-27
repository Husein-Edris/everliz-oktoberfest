jQuery(document).ready(function($) {
    // Add new date range row
    $('#add-date-range').on('click', function() {
        const index = $('.date-range-row').length;
        const newRow = `
            <tr class="date-range-row">
                <td>
                    <input type="number" 
                           name="oktoberfest_date_ranges[${index}][year]"
                           min="2025" 
                           max="2028" 
                           required>
                </td>
                <td>
                    <input type="date" 
                           name="oktoberfest_date_ranges[${index}][start_date]" 
                           required>
                </td>
                <td>
                    <input type="date" 
                           name="oktoberfest_date_ranges[${index}][end_date]" 
                           required>
                </td>
                <td>
                    <button type="button" class="button remove-date-range">Remove</button>
                </td>
            </tr>
        `;
        $('#oktoberfest-dates tbody').append(newRow);
    });

    // Remove date range row
    $(document).on('click', '.remove-date-range', function() {
        const $row = $(this).closest('tr');
        
        // Don't remove if it's the last row
        if ($('.date-range-row').length > 1) {
            $row.fadeOut(300, function() {
                $(this).remove();
            });
        } else {
            alert('You must keep at least one date range.');
        }
    });

    // Validate date ranges
    function validateDateRange($row) {
        const year = $row.find('input[type="number"]').val();
        const startDate = $row.find('input[name*="[start_date]"]').val();
        const endDate = $row.find('input[name*="[end_date]"]').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (start > end) {
                alert('End date must be after start date.');
                return false;
            }
            
            if (start.getFullYear() != year || end.getFullYear() != year) {
                alert('Dates must be within the selected year.');
                return false;
            }
        }
        
        return true;
    }

    // Validate on form submit
    $('.oktoberfest-form').on('submit', function(e) {
        let isValid = true;
        
        $('.date-range-row').each(function() {
            if (!validateDateRange($(this))) {
                isValid = false;
                return false; // Break the loop
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    });

    // Update date input min/max when year changes
    $(document).on('change', '.date-range-row input[type="number"]', function() {
        const $row = $(this).closest('tr');
        const year = $(this).val();
        
        $row.find('input[type="date"]').each(function() {
            $(this).attr({
                'min': `${year}-01-01`,
                'max': `${year}-12-31`
            });
        });
    });

    // Initialize date input constraints
    $('.date-range-row').each(function() {
        const year = $(this).find('input[type="number"]').val();
        $(this).find('input[type="date"]').attr({
            'min': `${year}-01-01`,
            'max': `${year}-12-31`
        });
    });

    // Handle tent preference selection
    $('.booking-form-container .preference-option').on('click', function(e) {
        e.preventDefault();
        const radio = $(this).find('input[type="radio"]');
        
        // Remove selected class from all options
        $('.booking-form-container .preference-option').removeClass('selected');
        // Add selected class to clicked option
        $(this).addClass('selected');
        
        // Check the radio button
        radio.prop('checked', true);
        
        // Show/hide tent gallery based on selection
        const tentGallery = $('.booking-form-container .tent-gallery');
        if (radio.val() === 'specific') {
            tentGallery.slideDown(300);
        } else {
            tentGallery.slideUp(300);
            // Clear tent selection when switching to "Any tent"
            $('.booking-form-container .tent-card').removeClass('selected');
            $('#selected-tent').val('');
        }
    });

    // Handle tent card selection
    $('.booking-form-container .tent-card').on('click', function(e) {
        e.preventDefault();
        const tentId = $(this).data('tent-id');
        
        // Update hidden input
        $('#selected-tent').val(tentId);
        
        // Update visual selection
        $('.booking-form-container .tent-card').removeClass('selected');
        $(this).addClass('selected');
        
        // Ensure "Specific tent preference" is selected
        const specificOption = $('#specific-tent').closest('.preference-option');
        if (!specificOption.hasClass('selected')) {
            specificOption.trigger('click');
        }
    });

    // Initialize state based on selected tent
    const selectedTent = $('#selected-tent').val();
    if (selectedTent) {
        const tentCard = $(`.booking-form-container .tent-card[data-tent-id="${selectedTent}"]`);
        if (tentCard.length) {
            tentCard.addClass('selected');
            $('#specific-tent').closest('.preference-option').addClass('selected');
            $('.booking-form-container .tent-gallery').show();
        }
    } else {
        $('#any-tent').closest('.preference-option').addClass('selected');
        $('.booking-form-container .tent-gallery').hide();
    }
}); 