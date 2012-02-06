<?php
/*
Plugin Name: DateArchives
Version: 0.1
Plugin URI: http://maff.ailoo.net/2008/07/wordpress-plugin-datearchives/
Description: A simple plugin for showing post archives categorized by date.
Author: Mathias Geat
Author URI: http://ailoo.net
*/

function DateArchives($headingtag = 'h2', $splitformat = null, $limit = null)
{
    // get WordPress' database object
    global $wpdb;

    // set localization
    setlocale(LC_ALL, WPLANG);

    // set splitformat if not defined (default: July 2008)
    if (is_null($splitformat) || empty($splitformat))
        $splitformat = '%B %Y';

    // let's build a nice SQL query to fetch all published posts...
    $sql = "SELECT ID, post_title, post_date
                FROM " . $wpdb->posts . "
                WHERE post_type = 'post' AND post_status = 'publish'
                ORDER BY post_date DESC";

    // if the limit variable is set, update our SQL query
    if (!is_null($limit) && !empty($limit) && $limit != 0)
        $sql .= ' LIMIT 0,' . (int) $limit;

    // ...and get the results
    $posts = $wpdb->get_results($sql);

    // our archives array we'll build
    $archives = array();

    // let's fill the array
    foreach ($posts as $post) {
        // build an array key based on the $splitformat
        // this will result in an array structure like $archives['July 2008'][0]
        // depending on which splitformat you use
        $key = strftime($splitformat, strtotime($post->post_date));

        // and add our actual post to the array
        $archives[$key][] = $post;
    }

    // unset old stuff
    unset($post);
    unset($posts);

    // now for the output
    foreach ($archives as $heading => $posts) {
        // echo the results as heading tag followed by unordered list
        echo '<' . $headingtag . '>' . htmlentities($heading) . '</' . $headingtag . '>', "\n";
        echo '<ul>', "\n";

        foreach ($posts as $post) {
            if (empty($post->post_title)) {
                $postTitle = '(no title)';
            } else {
                $postTitle = $post->post_title;
            }

            echo '	<li><a href="' . get_permalink($post->ID) . '">' . $postTitle . '</a></li>', "\n";
        }

        echo '</ul>', "\n\n";
    }
}

// the handler for WordPress' shortcodes
function DateArchives_shortcode_handler($atts)
{
    // define attributes
    $a = shortcode_atts(
        array(
            'headingtag' => 'h2',
            'splitformat' => '%B %Y',
            'limit' => null
        ),
        $atts
    );

    // and call the actual function
    DateArchives($a['headingtag'], $a['splitformat'], $a['limit']);
}

// register our shortcode in WordPress
add_shortcode('DateArchives', 'DateArchives_shortcode_handler');