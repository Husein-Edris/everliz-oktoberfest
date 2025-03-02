<?php
/* Template Name: Tent Results */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header();

// Dummy data for tents
$tents = [
    [
        'image' => 'tent1.jpg',
        'title' => 'Paulaner Festzelt',
        'desc' => 'Traditional Bavarian tent with capacity of 8,000 people. Live music and authentic cuisine.',
        'id' => '1'
    ],
    [
        'image' => 'tent2.jpg',
        'title' => 'Hofbräu-Festzelt',
        'desc' => 'Famous for its party atmosphere and huge beer steins. Capacity: 6,000 seats.',
        'id' => '2'
    ],
    [
        'image' => 'tent3.jpg',
        'title' => 'Augustiner-Festhalle',
        'desc' => 'Most traditional tent serving Augustiner beer from wooden barrels. Capacity: 6,000.',
        'id' => '3'
    ]
];
?>

<div class="tent-results-container">
    <h1>Available Tents for <?php echo esc_html($_GET['date']); ?></h1>
    <div class="tent-grid">
        <?php foreach ($tents as $tent): ?>
        <div class="tent-card">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/' . $tent['image']); ?>"
                alt="<?php echo esc_attr($tent['title']); ?>" class="tent-image">
            <div class="tent-content">
                <h2><?php echo esc_html($tent['title']); ?></h2>
                <p><?php echo esc_html($tent['desc']); ?></p>
                <button class="book-tent-btn" data-tent-id="<?php echo esc_attr($tent['id']); ?>">
                    Book Now
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.tent-results-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.tent-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.tent-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.tent-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.tent-content {
    padding: 15px;
}

.tent-content h2 {
    margin: 0 0 10px 0;
    font-size: 1.5em;
}

.book-tent-btn {
    background: #0073aa;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 10px;
}

.book-tent-btn:hover {
    background: #005177;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.book-tent-btn').on('click', function() {
        const tentId = $(this).data('tent-id');
        const date = '<?php echo esc_js($_GET['date']); ?>';
        const location = '<?php echo esc_js($_GET['location']); ?>';

        // Dummy booking function - replace with actual API call
        alert(`Booking tent ${tentId} for ${date} at ${location}`);
    });
});
</script>

<?php get_footer(); ?>