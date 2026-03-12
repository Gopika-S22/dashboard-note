<?php
/**
 * Plugin Name: My Dashboard Note
 * Description: A stylish notepad on the WordPress Dashboard using Tailwind CSS.
 * Version: 2.0
 * Author: Gopika
 */

// Basic security: prevents someone from running this file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Tell WordPress to run our function when the dashboard is being set up
add_action('wp_dashboard_setup', 'my_custom_dashboard_widget');

function my_custom_dashboard_widget() {
    wp_add_dashboard_widget(
        'my_note_widget_id',         // Widget ID (Slug)
        'My Admin Notes',            // Title
        'my_note_display_content'    // Function to show content
    );
}

// 2. The Main Function to display and handle notes
function my_note_display_content() {
    // Loading Tailwind CSS via CDN
    echo '<script src="https://cdn.tailwindcss.com"></script>';
    
    // --- PART A: Handle Delete Action ---
    if ( isset( $_POST['delete_note_index'] ) ) {
        $index_to_delete = intval( $_POST['delete_note_index'] ); 
        $all_notes = get_option( 'my_admin_notes_list', array() );
        
        if ( isset( $all_notes[$index_to_delete] ) ) {
            unset( $all_notes[$index_to_delete] ); 
            $all_notes = array_values( $all_notes ); // Re-index array
            update_option( 'my_admin_notes_list', $all_notes ); 
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-2 mb-4 text-xs">Note deleted!</div>';
        }
    }

    // --- PART B: Handle Add New Note (Text + Date) ---
    if ( isset( $_POST['my_note_text'] ) && !empty( trim( $_POST['my_note_text'] ) ) ) {
        $all_notes = get_option( 'my_admin_notes_list', array() );
        if(!is_array($all_notes)) { $all_notes = array(); } // Double check it's an array

        $new_note_text = sanitize_textarea_field( $_POST['my_note_text'] );
        $current_date = date('M d, Y h:i A');

        // Saving as an associative array
        $new_entry = array(
            'text' => $new_note_text,
            'date' => $current_date
        );
        
        array_unshift( $all_notes, $new_entry );
        update_option( 'my_admin_notes_list', $all_notes );
        echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-2 mb-4 text-xs">New note added!</div>';
    }

    // --- PART C: Retrieve Notes ---
    $all_notes = get_option( 'my_admin_notes_list', array() );
    ?>

    <div class="tailwind-scope antialiased">
        <form method="post" class="mb-5">
            <textarea name="my_note_text" 
                      class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400 focus:outline-none text-sm shadow-sm" 
                      rows="3" 
                      placeholder="Type your note here..."></textarea>
            <button type="submit" 
                    class="mt-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 px-4 rounded shadow transition duration-200 text-sm">
                Add Note
            </button>
        </form>

        <h4 class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-3 border-b pb-1">Previous Notes</h4>

        <div class="max-h-80 overflow-y-auto space-y-3 pr-1">
            <?php if ( empty( $all_notes ) ) : ?>
                <p class="text-gray-400 italic text-sm">No notes saved yet.</p>
            <?php else : ?>
                <?php foreach ( $all_notes as $index => $note_data ) : ?>
                    <div class="relative group bg-white border border-gray-200 p-3 rounded-md shadow-sm hover:border-blue-300 transition-all border-l-4 border-blue-500">
                        
                        <?php 
                        // Error Fix: Check if data is array (new format) or string (old format)
                        if ( is_array( $note_data ) ) {
                            $display_text = isset($note_data['text']) ? $note_data['text'] : '';
                            $display_date = isset($note_data['date']) ? $note_data['date'] : 'No date';
                        } else {
                            $display_text = $note_data; // Legacy support for old strings
                            $display_date = "Older Entry";
                        }
                        ?>

                        <span class="text-[10px] text-gray-400 font-mono block mb-1">
                            <?php echo esc_html( $display_date ); ?>
                        </span>

                        <p class="text-gray-700 text-sm leading-snug break-words">
                            <?php echo nl2br( esc_html( $display_text ) ); ?>
                        </p>
                        
                        <form method="post" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <input type="hidden" name="delete_note_index" value="<?php echo $index; ?>">
                            <button type="submit" 
                                class="text-red-500 hover:text-red-700 text-[10px] font-bold uppercase border border-red-200 px-1 rounded bg-red-50"
                                onclick="return confirm('Delete this note?');">
                                     Delete
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}